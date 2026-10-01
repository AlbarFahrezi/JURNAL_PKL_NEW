<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Laporan transaksi harian.
     *
     * Petunjuk pelaksanaan:
     * - Menggunakan agregasi COUNT dan SUM
     * - Hanya mengambil kolom yang diperlukan
     * - Detail transaksi menggunakan eager loading
     * - Hasil laporan di-cache selama 1 jam
     */
    public function daily(Request $request)
    {
        $date = $request->get(
            'date',
            now()->toDateString()
        );

        try {
            $reportDate = Carbon::createFromFormat(
                'Y-m-d',
                $date
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal harus YYYY-MM-DD.',
            ], 422);
        }

        $cacheKey = 'report:daily:' . $reportDate->toDateString();

        $report = Cache::remember(
            $cacheKey,
            now()->addHour(),
            function () use ($reportDate) {
                $start = $reportDate->copy()->startOfDay();
                $end = $reportDate->copy()->endOfDay();

                /*
                |--------------------------------------------------------------------------
                | Summary
                |--------------------------------------------------------------------------
                */

                $summary = Transaction::query()
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$start, $end])
                    ->selectRaw('COUNT(*) as total_transactions')
                    ->selectRaw(
                        "SUM(CASE WHEN type = 'IN' THEN 1 ELSE 0 END) as total_in"
                    )
                    ->selectRaw(
                        "SUM(CASE WHEN type = 'OUT' THEN 1 ELSE 0 END) as total_out"
                    )
                    ->selectRaw('COALESCE(SUM(total), 0) as total_value')
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Detail transaksi
                |--------------------------------------------------------------------------
                */

                $transactions = Transaction::query()
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$start, $end])
                    ->select([
                        'id',
                        'transaction_code',
                        'user_id',
                        'type',
                        'status',
                        'total',
                        'note',
                        'created_at',
                    ])
                    ->with([
                        'user:id,name',
                    ])
                    ->orderByDesc('created_at')
                    ->get();

                return [
                    'date' => $reportDate->toDateString(),

                    'summary' => [
                        'total_transactions' => (int) $summary->total_transactions,
                        'total_in' => (int) $summary->total_in,
                        'total_out' => (int) $summary->total_out,
                        'total_value' => number_format(
                            (float) $summary->total_value,
                            2,
                            '.',
                            ''
                        ),
                    ],

                    'transactions' => $transactions,
                ];
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Daily report retrieved successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Laporan berdasarkan periode tanggal.
     *
     * Parameter:
     * - date_from
     * - date_to
     * - view=summary|detail
     * - type=IN|OUT
     */
    public function period(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'view' => ['nullable', 'in:summary,detail'],
            'type' => ['nullable', 'in:IN,OUT'],
        ]);

        $dateFrom = Carbon::createFromFormat(
            'Y-m-d',
            $validated['date_from']
        )->startOfDay();

        $dateTo = Carbon::createFromFormat(
            'Y-m-d',
            $validated['date_to']
        )->endOfDay();

        $view = $validated['view'] ?? 'summary';
        $type = $validated['type'] ?? null;

        $cacheKey = 'report:period:' . md5(
            implode('|', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
                $view,
                $type ?? 'all',
            ])
        );

        $report = Cache::remember(
            $cacheKey,
            now()->addHour(),
            function () use (
                $dateFrom,
                $dateTo,
                $view,
                $type
            ) {
                /*
                |--------------------------------------------------------------------------
                | Base Query
                |--------------------------------------------------------------------------
                */

                $query = Transaction::query()
                    ->where('status', 'completed')
                    ->whereBetween(
                        'created_at',
                        [$dateFrom, $dateTo]
                    );

                if ($type !== null) {
                    $query->where('type', $type);
                }

                /*
                |--------------------------------------------------------------------------
                | Summary
                |--------------------------------------------------------------------------
                */

                $summaryQuery = clone $query;

                $summary = $summaryQuery
                    ->selectRaw('COUNT(*) as total_transactions')
                    ->selectRaw(
                        "SUM(CASE WHEN type = 'IN' THEN 1 ELSE 0 END) as total_in"
                    )
                    ->selectRaw(
                        "SUM(CASE WHEN type = 'OUT' THEN 1 ELSE 0 END) as total_out"
                    )
                    ->selectRaw('COALESCE(SUM(total), 0) as total_value')
                    ->selectRaw('COALESCE(AVG(total), 0) as average_value')
                    ->selectRaw('COALESCE(MAX(total), 0) as highest_value')
                    ->selectRaw('COALESCE(MIN(total), 0) as lowest_value')
                    ->first();

                $data = [
                    'date_from' => $dateFrom->toDateString(),
                    'date_to' => $dateTo->toDateString(),

                    'filter' => [
                        'type' => $type ?? 'all',
                    ],

                    'summary' => [
                        'total_transactions' => (int) $summary->total_transactions,
                        'total_in' => (int) $summary->total_in,
                        'total_out' => (int) $summary->total_out,
                        'total_value' => number_format(
                            (float) $summary->total_value,
                            2,
                            '.',
                            ''
                        ),
                        'average_value' => number_format(
                            (float) $summary->average_value,
                            2,
                            '.',
                            ''
                        ),
                        'highest_value' => number_format(
                            (float) $summary->highest_value,
                            2,
                            '.',
                            ''
                        ),
                        'lowest_value' => number_format(
                            (float) $summary->lowest_value,
                            2,
                            '.',
                            ''
                        ),
                    ],
                ];

                /*
                |--------------------------------------------------------------------------
                | Detail
                |--------------------------------------------------------------------------
                */

                if ($view === 'detail') {
                    $data['transactions'] = $query
                        ->select([
                            'id',
                            'transaction_code',
                            'user_id',
                            'type',
                            'status',
                            'total',
                            'note',
                            'created_at',
                        ])
                        ->with([
                            'user:id,name',

                            'details' => function ($detailQuery) {
                                $detailQuery->select([
                                    'id',
                                    'transaction_id',
                                    'product_id',
                                    'quantity',
                                    'price',
                                    'subtotal',
                                ])->with([
                                    'product:id,name,sku',
                                ]);
                            },
                        ])
                        ->orderByDesc('created_at')
                        ->get();
                }

                return $data;
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Period report retrieved successfully.',
            'data' => $report,
        ]);
    }

    /**
     * Statistik laporan.
     *
     * Menggunakan:
     * - COUNT
     * - SUM
     * - AVG
     * - MAX
     * - MIN
     *
     * Serta persentase perubahan terhadap periode sebelumnya.
     */
    public function statistics(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to' => ['required', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'type' => ['nullable', 'in:IN,OUT'],
        ]);

        $dateFrom = Carbon::createFromFormat(
            'Y-m-d',
            $validated['date_from']
        )->startOfDay();

        $dateTo = Carbon::createFromFormat(
            'Y-m-d',
            $validated['date_to']
        )->endOfDay();

        $type = $validated['type'] ?? null;

        $cacheKey = 'report:statistics:' . md5(
            implode('|', [
                $dateFrom->toDateString(),
                $dateTo->toDateString(),
                $type ?? 'all',
            ])
        );

        $statistics = Cache::remember(
            $cacheKey,
            now()->addHour(),
            function () use (
                $dateFrom,
                $dateTo,
                $type
            ) {
                /*
                |--------------------------------------------------------------------------
                | Periode sekarang
                |--------------------------------------------------------------------------
                */

                $currentQuery = Transaction::query()
                    ->where('status', 'completed')
                    ->whereBetween(
                        'created_at',
                        [$dateFrom, $dateTo]
                    );

                if ($type !== null) {
                    $currentQuery->where('type', $type);
                }

                $current = $currentQuery
                    ->selectRaw('COUNT(*) as total_transactions')
                    ->selectRaw('COALESCE(SUM(total), 0) as total_value')
                    ->selectRaw('COALESCE(AVG(total), 0) as average_value')
                    ->selectRaw('COALESCE(MAX(total), 0) as highest_value')
                    ->selectRaw('COALESCE(MIN(total), 0) as lowest_value')
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Tentukan periode sebelumnya
                |--------------------------------------------------------------------------
                */

                $days = $dateFrom->diffInDays(
                    $dateTo
                ) + 1;

                $previousTo = $dateFrom
                    ->copy()
                    ->subDay()
                    ->endOfDay();

                $previousFrom = $previousTo
                    ->copy()
                    ->subDays($days - 1)
                    ->startOfDay();

                /*
                |--------------------------------------------------------------------------
                | Periode sebelumnya
                |--------------------------------------------------------------------------
                */

                $previousQuery = Transaction::query()
                    ->where('status', 'completed')
                    ->whereBetween(
                        'created_at',
                        [$previousFrom, $previousTo]
                    );

                if ($type !== null) {
                    $previousQuery->where('type', $type);
                }

                $previous = $previousQuery
                    ->selectRaw('COUNT(*) as total_transactions')
                    ->selectRaw('COALESCE(SUM(total), 0) as total_value')
                    ->first();

                /*
                |--------------------------------------------------------------------------
                | Persentase perubahan
                |--------------------------------------------------------------------------
                */

                $currentTotal = (float) $current->total_value;
                $previousTotal = (float) $previous->total_value;

                $percentageChange = $previousTotal == 0
                    ? null
                    : (($currentTotal - $previousTotal) / $previousTotal) * 100;

                return [
                    'current_period' => [
                        'date_from' => $dateFrom->toDateString(),
                        'date_to' => $dateTo->toDateString(),
                        'total_transactions' => (int) $current->total_transactions,
                        'total_value' => number_format(
                            $currentTotal,
                            2,
                            '.',
                            ''
                        ),
                        'average_value' => number_format(
                            (float) $current->average_value,
                            2,
                            '.',
                            ''
                        ),
                        'highest_value' => number_format(
                            (float) $current->highest_value,
                            2,
                            '.',
                            ''
                        ),
                        'lowest_value' => number_format(
                            (float) $current->lowest_value,
                            2,
                            '.',
                            ''
                        ),
                    ],

                    'previous_period' => [
                        'date_from' => $previousFrom->toDateString(),
                        'date_to' => $previousTo->toDateString(),
                        'total_transactions' => (int) $previous->total_transactions,
                        'total_value' => number_format(
                            $previousTotal,
                            2,
                            '.',
                            ''
                        ),
                    ],

                    'percentage_change' => $percentageChange === null
                        ? null
                        : round($percentageChange, 2),

                    'filter' => [
                        'type' => $type ?? 'all',
                    ],
                ];
            }
        );

        return response()->json([
            'success' => true,
            'message' => 'Report statistics retrieved successfully.',
            'data' => $statistics,
        ]);
    }
}