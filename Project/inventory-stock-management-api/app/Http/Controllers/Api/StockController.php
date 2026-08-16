<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

#[OA\Tag(
    name: "Stock",
    description: "Stock Management"
)]
class StockController extends Controller
{
    #[OA\Post(
        path: "/api/stock-in",
        summary: "Stock In",
        tags: ["Stock"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["product_id", "quantity"],
            properties: [
                new OA\Property(property: "product_id", type: "integer", example: 1),
                new OA\Property(property: "quantity", type: "integer", example: 10),
                new OA\Property(property: "note", type: "string", example: "Barang masuk")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Stock In success"
    )]
    public function stockIn(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {

            $product = Product::findOrFail($request->product_id);

            $before = $product->stock;
            $after = $before + $request->quantity;

            $product->update([
                'stock' => $after
            ]);

            StockHistory::create([
                'product_id' => $product->id,
                'type' => 'IN',
                'quantity' => $request->quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $request->note
            ]);
        });

        return response()->json([
            'message' => 'Stock In success'
        ]);
    }

    #[OA\Post(
        path: "/api/stock-out",
        summary: "Stock Out",
        tags: ["Stock"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["product_id", "quantity"],
            properties: [
                new OA\Property(property: "product_id", type: "integer", example: 1),
                new OA\Property(property: "quantity", type: "integer", example: 5),
                new OA\Property(property: "note", type: "string", example: "Barang keluar")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Stock Out success"
    )]
    public function stockOut(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string'
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'message' => 'Stock is not enough'
            ], 400);
        }

        DB::transaction(function () use ($request, $product) {

            $before = $product->stock;
            $after = $before - $request->quantity;

            $product->update([
                'stock' => $after
            ]);

            StockHistory::create([
                'product_id' => $product->id,
                'type' => 'OUT',
                'quantity' => $request->quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $request->note
            ]);
        });

        return response()->json([
            'message' => 'Stock Out success'
        ]);
    }

    #[OA\Post(
        path: "/api/stock-adjustment",
        summary: "Stock Adjustment",
        tags: ["Stock"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["product_id", "stock"],
            properties: [
                new OA\Property(property: "product_id", type: "integer", example: 1),
                new OA\Property(property: "stock", type: "integer", example: 50),
                new OA\Property(property: "note", type: "string", example: "Stock opname")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Stock adjusted successfully"
    )]
    public function stockAdjustment(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock' => 'required|integer|min:0',
            'note' => 'nullable|string'
        ]);

        DB::transaction(function () use ($request) {

            $product = Product::findOrFail($request->product_id);

            $before = $product->stock;
            $after = $request->stock;

            $product->update([
                'stock' => $after
            ]);

            StockHistory::create([
                'product_id' => $product->id,
                'type' => 'ADJUSTMENT',
                'quantity' => abs($after - $before),
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $request->note
            ]);
        });

        return response()->json([
            'message' => 'Stock adjusted successfully'
        ]);
    }

    #[OA\Get(
        path: "/api/stock-history",
        summary: "Stock History",
        tags: ["Stock"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "List Stock History"
    )]
    public function history()
    {
        return response()->json([
            'data' => StockHistory::with('product')->latest()->get()
        ]);
    }
}