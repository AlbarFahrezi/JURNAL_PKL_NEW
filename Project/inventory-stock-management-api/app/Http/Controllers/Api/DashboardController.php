<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\StockHistory;
use Illuminate\Http\Request;

#[OA\Tag(
    name: "Dashboard",
    description: "Dashboard Summary"
)]
class DashboardController extends Controller
{
    #[OA\Get(
        path: "/api/dashboard",
        summary: "Get Dashboard Summary",
        tags: ["Dashboard"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Dashboard data retrieved successfully"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated"
    )]
    public function index(Request $request)
    {
        $user = $request->user();

        // Dashboard Administrator
        if ($user->role === 'administrator') {
            return response()->json([
                'success' => true,
                'role' => 'administrator',
                'message' => 'Dashboard administrator berhasil diakses.',
                'data' => [
                    'total_products' => Product::count(),
                    'total_categories' => Category::count(),
                    'total_suppliers' => Supplier::count(),
                    'total_warehouses' => Warehouse::count(),

                    'total_stock' => Product::sum('stock'),

                    'stock_in_today' => StockHistory::whereDate('created_at', today())
                        ->where('type', 'IN')
                        ->sum('quantity'),

                    'stock_out_today' => StockHistory::whereDate('created_at', today())
                        ->where('type', 'OUT')
                        ->sum('quantity'),

                    'stock_adjustment_today' => StockHistory::whereDate('created_at', today())
                        ->where('type', 'ADJUSTMENT')
                        ->count(),
                ]
            ]);
        }

        // Dashboard Operator
        if ($user->role === 'operator') {
            return response()->json([
                'success' => true,
                'role' => 'operator',
                'message' => 'Dashboard operator berhasil diakses.',
                'data' => [
                    'total_products' => Product::count(),
                    'total_stock' => Product::sum('stock'),

                    'stock_in_today' => StockHistory::whereDate('created_at', today())
                        ->where('type', 'IN')
                        ->sum('quantity'),

                    'stock_out_today' => StockHistory::whereDate('created_at', today())
                        ->where('type', 'OUT')
                        ->sum('quantity'),
                ]
            ]);
        }

        // Role tidak dikenali
        return response()->json([
            'success' => false,
            'message' => 'Role tidak memiliki akses dashboard.'
        ], 403);
    }
}