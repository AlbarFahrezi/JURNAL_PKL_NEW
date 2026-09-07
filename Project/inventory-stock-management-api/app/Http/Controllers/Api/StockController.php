<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockHistory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

#[OA\Tag(
    name: "Stock",
    description: "Stock Management"
)]
class StockController extends Controller
{
    use ApiResponse;

    /*
    |--------------------------------------------------------------------------
    | STOCK IN
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: "/api/stock-in",
        summary: "Stock In",
        description: "Add stock to a product and record the transaction in stock history.",
        tags: ["Stock"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["product_id", "quantity"],
            properties: [
                new OA\Property(
                    property: "product_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "quantity",
                    type: "integer",
                    minimum: 1,
                    example: 10
                ),
                new OA\Property(
                    property: "note",
                    type: "string",
                    example: "Barang masuk dari supplier"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Stock In success"
    )]
    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500'
        ]);

        $result = DB::transaction(function () use ($validated) {

            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $before = $product->stock;
            $after = $before + $validated['quantity'];

            $product->update([
                'stock' => $after
            ]);

            $history = StockHistory::create([
                'product_id' => $product->id,
                'type' => 'IN',
                'quantity' => $validated['quantity'],
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $validated['note'] ?? null
            ]);

            return [
                'product' => $product->fresh(),
                'history' => $history
            ];
        });

        return $this->successResponse(
            'Stock In success',
            $result
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK OUT
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: "/api/stock-out",
        summary: "Stock Out",
        description: "Reduce product stock and record the transaction in stock history. Stock cannot become negative.",
        tags: ["Stock"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["product_id", "quantity"],
            properties: [
                new OA\Property(
                    property: "product_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "quantity",
                    type: "integer",
                    minimum: 1,
                    example: 5
                ),
                new OA\Property(
                    property: "note",
                    type: "string",
                    example: "Barang keluar untuk digunakan"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Stock Out success"
    )]
    #[OA\Response(
        response: 422,
        description: "Stock is not enough"
    )]
    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string|max:500'
        ]);

        $result = DB::transaction(function () use ($validated) {

            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->stock < $validated['quantity']) {
                return null;
            }

            $before = $product->stock;
            $after = $before - $validated['quantity'];

            $product->update([
                'stock' => $after
            ]);

            $history = StockHistory::create([
                'product_id' => $product->id,
                'type' => 'OUT',
                'quantity' => $validated['quantity'],
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $validated['note'] ?? null
            ]);

            return [
                'product' => $product->fresh(),
                'history' => $history
            ];
        });

        if ($result === null) {
            return $this->errorResponse(
                'Stock is not enough',
                422
            );
        }

        return $this->successResponse(
            'Stock Out success',
            $result
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK ADJUSTMENT
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: "/api/stock-adjustment",
        summary: "Stock Adjustment",
        description: "Adjust product stock to a specific quantity and record the change in stock history.",
        tags: ["Stock"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["product_id", "stock"],
            properties: [
                new OA\Property(
                    property: "product_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "stock",
                    type: "integer",
                    minimum: 0,
                    example: 50
                ),
                new OA\Property(
                    property: "note",
                    type: "string",
                    example: "Stock opname"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Stock adjusted successfully"
    )]
    public function stockAdjustment(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'stock' => 'required|integer|min:0',
            'note' => 'nullable|string|max:500'
        ]);

        $result = DB::transaction(function () use ($validated) {

            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $before = $product->stock;
            $after = $validated['stock'];

            $product->update([
                'stock' => $after
            ]);

            $history = StockHistory::create([
                'product_id' => $product->id,
                'type' => 'ADJUSTMENT',
                'quantity' => abs($after - $before),
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $validated['note'] ?? null
            ]);

            return [
                'product' => $product->fresh(),
                'history' => $history
            ];
        });

        return $this->successResponse(
            'Stock adjusted successfully',
            $result
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STOCK HISTORY
    |--------------------------------------------------------------------------
    */

    #[OA\Get(
        path: "/api/stock-history",
        summary: "Get Stock History",
        description: "Get stock history with pagination, search, filtering, and sorting.",
        tags: ["Stock"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "search",
        in: "query",
        required: false,
        description: "Search by product name, SKU, or note",
        schema: new OA\Schema(type: "string"),
        example: "Laptop"
    )]
    #[OA\Parameter(
        name: "type",
        in: "query",
        required: false,
        description: "Filter stock history by transaction type",
        schema: new OA\Schema(
            type: "string",
            enum: ["IN", "OUT", "ADJUSTMENT"]
        ),
        example: "IN"
    )]
    #[OA\Parameter(
        name: "per_page",
        in: "query",
        required: false,
        description: "Number of history records per page",
        schema: new OA\Schema(
            type: "integer",
            minimum: 1,
            maximum: 100
        ),
        example: 10
    )]
    #[OA\Parameter(
        name: "sort_by",
        in: "query",
        required: false,
        description: "Column used for sorting",
        schema: new OA\Schema(
            type: "string",
            enum: [
                "type",
                "quantity",
                "stock_before",
                "stock_after",
                "created_at"
            ]
        ),
        example: "created_at"
    )]
    #[OA\Parameter(
        name: "sort_order",
        in: "query",
        required: false,
        description: "Sorting direction",
        schema: new OA\Schema(
            type: "string",
            enum: ["asc", "desc"]
        ),
        example: "desc"
    )]
    #[OA\Response(
        response: 200,
        description: "List of stock history"
    )]
    public function history(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'type' => 'nullable|in:IN,OUT,ADJUSTMENT',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:type,quantity,stock_before,stock_after,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = StockHistory::with('product');

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('note', 'like', '%' . $search . '%')

                    ->orWhereHas('product', function ($productQuery) use ($search) {

                        $productQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TYPE
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        /*
        |--------------------------------------------------------------------------
        | SORTING
        |--------------------------------------------------------------------------
        */

        $query->orderBy($sortBy, $sortOrder);

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $histories = $query
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginationResponse(
            'Stock history retrieved successfully',
            $histories
        );
    }
}