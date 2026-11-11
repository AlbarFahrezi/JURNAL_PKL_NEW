<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

#[OA\Tag(
    name: "Dashboard",
    description: "Dashboard Summary and Visual Statistics"
)]
class DashboardController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: "/api/dashboard",
        summary: "Get Dashboard Summary and Statistics",
        tags: ["Dashboard"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "period",
        description: "Dashboard chart period",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string",
            enum: ["7days", "30days", "year"],
            default: "7days"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Dashboard data retrieved successfully"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated"
    )]
    #[OA\Response(
        response: 403,
        description: "Role does not have dashboard access"
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['administrator', 'operator'])) {
            return $this->errorResponse(
                'Role tidak memiliki akses dashboard.',
                403
            );
        }

        $validated = $request->validate([
            'period' => 'sometimes|in:7days,30days,year',
        ]);

        $period = $validated['period'] ?? '7days';

        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | Menentukan Periode Grafik
        |--------------------------------------------------------------------------
        */

        if ($period === '30days') {
            $startDate = $today->copy()->subDays(29);
            $endDate = $today->copy()->endOfDay();
        } elseif ($period === 'year') {
            $startDate = $today->copy()->startOfYear();
            $endDate = $today->copy()->endOfDay();
        } else {
            $startDate = $today->copy()->subDays(6);
            $endDate = $today->copy()->endOfDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Cache Dashboard Selama 60 Detik
        |--------------------------------------------------------------------------
        */

        $cacheKey = 'dashboard:'
            . $user->role
            . ':'
            . $period
            . ':'
            . $today->toDateString();

        $data = Cache::remember(
            $cacheKey,
            now()->addSeconds(60),
            function () use (
                $user,
                $period,
                $today,
                $startDate,
                $endDate
            ) {

                /*
                |--------------------------------------------------------------------------
                | Summary Cards
                |--------------------------------------------------------------------------
                */

                $summary = [
                    'total_products' => Product::count(),

                    'total_stock' => (int) Product::sum('stock'),

                    'stock_in_today' => (int) StockHistory::whereDate(
                        'created_at',
                        $today
                    )
                        ->where('type', 'IN')
                        ->sum('quantity'),

                    'stock_out_today' => (int) StockHistory::whereDate(
                        'created_at',
                        $today
                    )
                        ->where('type', 'OUT')
                        ->sum('quantity'),
                ];

                if ($user->role === 'administrator') {
                    $summary['total_categories'] = Category::count();

                    $summary['total_suppliers'] = Supplier::count();

                    $summary['total_warehouses'] = Warehouse::count();

                    $summary['stock_adjustment_today'] = StockHistory::whereDate(
                        'created_at',
                        $today
                    )
                        ->where('type', 'ADJUSTMENT')
                        ->count();
                }

                /*
                |--------------------------------------------------------------------------
                | Indikator Stok
                |--------------------------------------------------------------------------
                */

                $lowStockQuery = Product::query()
                    ->whereBetween('stock', [1, 5]);

                $outOfStockQuery = Product::query()
                    ->where('stock', 0);

                $lowStockProducts = (clone $lowStockQuery)
                    ->select('id', 'name', 'sku', 'stock')
                    ->orderBy('stock')
                    ->limit(5)
                    ->get()
                    ->map(function ($product) {
                        return [
                            'id' => (int) $product->id,
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'stock' => (int) $product->stock,
                        ];
                    })
                    ->values()
                    ->toArray();

                $stockIndicators = [
                    'low_stock_threshold' => 5,

                    'low_stock_count' => (clone $lowStockQuery)->count(),

                    'out_of_stock_count' => $outOfStockQuery->count(),

                    'low_stock_products' => $lowStockProducts,
                ];

                /*
                |--------------------------------------------------------------------------
                | Grafik Tren Stock In, Stock Out, Adjustment
                |--------------------------------------------------------------------------
                */

                if ($period === 'year') {
                    $groupExpression = "DATE_FORMAT(created_at, '%Y-%m')";

                    $cursor = $startDate->copy()->startOfMonth();
                    $lastDate = $endDate->copy()->startOfMonth();

                    $labels = [];

                    while ($cursor->lessThanOrEqualTo($lastDate)) {
                        $labels[] = [
                            'key' => $cursor->format('Y-m'),
                            'label' => $cursor->translatedFormat('M Y'),
                        ];

                        $cursor->addMonth();
                    }
                } else {
                    $groupExpression = 'DATE(created_at)';

                    $cursor = $startDate->copy()->startOfDay();
                    $lastDate = $endDate->copy()->startOfDay();

                    $labels = [];

                    while ($cursor->lessThanOrEqualTo($lastDate)) {
                        $labels[] = [
                            'key' => $cursor->format('Y-m-d'),
                            'label' => $cursor->format('d M'),
                        ];

                        $cursor->addDay();
                    }
                }

                $historyData = StockHistory::query()
                    ->selectRaw(
                        "$groupExpression as period, type, SUM(quantity) as total"
                    )
                    ->whereBetween('created_at', [
                        $startDate,
                        $endDate,
                    ])
                    ->groupBy('period', 'type')
                    ->orderBy('period')
                    ->get();

                $historyMap = [];

                foreach ($historyData as $history) {
                    $historyMap[$history->period][$history->type] =
                        (int) $history->total;
                }

                $stockTrend = [];

                foreach ($labels as $label) {
                    $key = $label['key'];

                    $stockTrend[] = [
                        'date' => $label['label'],

                        'stock_in' => $historyMap[$key]['IN'] ?? 0,

                        'stock_out' => $historyMap[$key]['OUT'] ?? 0,

                        'adjustment' => $historyMap[$key]['ADJUSTMENT'] ?? 0,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Grafik Komposisi Produk Berdasarkan Kategori
                |--------------------------------------------------------------------------
                */

                $categoryComposition = DB::table('categories')
                    ->leftJoin(
                        'products',
                        function ($join) {
                            $join->on(
                                'categories.id',
                                '=',
                                'products.category_id'
                            )->whereNull('products.deleted_at');
                        }
                    )
                    ->select(
                        'categories.id',
                        'categories.name'
                    )
                    ->selectRaw('COUNT(products.id) as total_products')
                    ->groupBy(
                        'categories.id',
                        'categories.name'
                    )
                    ->orderBy('categories.name')
                    ->get()
                    ->map(function ($category) {
                        return [
                            'id' => (int) $category->id,
                            'name' => $category->name,
                            'total_products' => (int) $category->total_products,
                        ];
                    })
                    ->values()
                    ->toArray();

                /*
                |--------------------------------------------------------------------------
                | Grafik Komposisi Status Transaksi
                |--------------------------------------------------------------------------
                */

                $transactionStatuses = Transaction::query()
                    ->select('status')
                    ->selectRaw('COUNT(*) as total')
                    ->groupBy('status')
                    ->get();

                $transactionStatusMap = [
                    'draft' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                ];

                foreach ($transactionStatuses as $status) {
                    $transactionStatusMap[$status->status] =
                        (int) $status->total;
                }

                $transactionComposition = [
                    [
                        'status' => 'draft',
                        'total' => $transactionStatusMap['draft'],
                    ],
                    [
                        'status' => 'completed',
                        'total' => $transactionStatusMap['completed'],
                    ],
                    [
                        'status' => 'cancelled',
                        'total' => $transactionStatusMap['cancelled'],
                    ],
                ];

                /*
                |--------------------------------------------------------------------------
                | Hasil Dashboard
                |--------------------------------------------------------------------------
                */

                return array_merge(
                    $summary,
                    $stockIndicators,
                    [
                        'period' => $period,

                        'stock_trend' => $stockTrend,

                        'category_composition' => $categoryComposition,

                        'transaction_composition' => $transactionComposition,

                        'last_updated' => now()->toDateTimeString(),
                    ]
                );
            }
        );

        return $this->successResponse(
            'Dashboard berhasil diakses.',
            $data
        );
    }
}
