<?php

namespace App\Http\Controllers\Api;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Laporan transaksi harian.
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
     */
    public function period(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:date_from'
            ],
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
                $query = Transaction::query()
                    ->where('status', 'completed')
                    ->whereBetween(
                        'created_at',
                        [$dateFrom, $dateTo]
                    );

                if ($type !== null) {
                    $query->where('type', $type);
                }

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
     */
    public function statistics(Request $request)
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:date_from'
            ],
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

                $days = $dateFrom->diffInDays($dateTo) + 1;

                $previousTo = $dateFrom
                    ->copy()
                    ->subDay()
                    ->endOfDay();

                $previousFrom = $previousTo
                    ->copy()
                    ->subDays($days - 1)
                    ->startOfDay();

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

    /**
     * Mengambil data laporan untuk kebutuhan ekspor.
     */
    private function getExportData(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:date_from'
            ],
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

        $query = Transaction::query()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($type !== null) {
            $query->where('type', $type);
        }

        $summary = (clone $query)
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

        $transactions = collect();

        if ($view === 'detail') {
            $transactions = $query
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

        return [
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'view' => $view,
            'type' => $type ?? 'all',

            'summary' => [
                'total_transactions' => (int) $summary->total_transactions,
                'total_in' => (int) $summary->total_in,
                'total_out' => (int) $summary->total_out,
                'total_value' => (float) $summary->total_value,
                'average_value' => (float) $summary->average_value,
                'highest_value' => (float) $summary->highest_value,
                'lowest_value' => (float) $summary->lowest_value,
            ],

            'transactions' => $transactions,
        ];
    }

    /**
     * Ekspor laporan ke PDF.
     */
    public function exportPdf(Request $request)
    {
        $report = $this->getExportData($request);

        $pdf = Pdf::loadView('reports.export-pdf', [
            'report' => $report,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan-' .
            $report['date_from'] . '-sampai-' .
            $report['date_to'] . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Ekspor laporan ke Excel.
     */
    public function exportExcel(Request $request)
    {
        $report = $this->getExportData($request);

        if ($report['view'] === 'summary') {
            $headings = [
                'Keterangan',
                'Nilai',
            ];

            $rows = [
                ['Tanggal Mulai', $report['date_from']],
                ['Tanggal Selesai', $report['date_to']],
                ['Filter Tipe', $report['type']],
                [
                    'Total Transaksi',
                    $report['summary']['total_transactions']
                ],
                [
                    'Total Transaksi IN',
                    $report['summary']['total_in']
                ],
                [
                    'Total Transaksi OUT',
                    $report['summary']['total_out']
                ],
                [
                    'Total Nilai',
                    $report['summary']['total_value']
                ],
                [
                    'Rata-rata Transaksi',
                    $report['summary']['average_value']
                ],
                [
                    'Nilai Tertinggi',
                    $report['summary']['highest_value']
                ],
                [
                    'Nilai Terendah',
                    $report['summary']['lowest_value']
                ],
            ];
        } else {
            $headings = [
                'Kode Transaksi',
                'Tanggal',
                'Tipe',
                'Nama Pengguna',
                'Nama Produk',
                'SKU',
                'Jumlah',
                'Harga',
                'Subtotal',
                'Catatan',
            ];

            $rows = [];

            foreach ($report['transactions'] as $transaction) {
                foreach ($transaction->details as $detail) {
                    $rows[] = [
                        $transaction->transaction_code,
                        $transaction->created_at->format('Y-m-d H:i:s'),
                        $transaction->type,
                        $transaction->user->name ?? '-',
                        $detail->product->name ?? '-',
                        $detail->product->sku ?? '-',
                        $detail->quantity,
                        (float) $detail->price,
                        (float) $detail->subtotal,
                        $transaction->note ?? '-',
                    ];
                }
            }
        }

        $filename = 'laporan-' .
            $report['date_from'] . '-sampai-' .
            $report['date_to'] . '.xlsx';

        return Excel::download(
            new ReportExport($headings, $rows),
            $filename
        );
    }
}
