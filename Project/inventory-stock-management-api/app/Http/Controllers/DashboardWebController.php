<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\StockHistory;

class DashboardWebController extends Controller
{
    public function index()
    {
        return view('dashboard', [

            'total_products' => Product::count(),

            'total_categories' => Category::count(),

            'total_suppliers' => Supplier::count(),

            'total_warehouses' => Warehouse::count(),

            'total_stock' => Product::sum('stock'),

            'stock_in_today' => StockHistory::where('type','IN')
                ->whereDate('created_at',today())
                ->sum('quantity'),

            'stock_out_today' => StockHistory::where('type','OUT')
                ->whereDate('created_at',today())
                ->sum('quantity'),

            'stock_adjustment_today' => StockHistory::where('type','ADJUSTMENT')
                ->whereDate('created_at',today())
                ->sum('quantity'),
        ]);
    }
}