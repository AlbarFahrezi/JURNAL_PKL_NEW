<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

#[OA\Tag(
    name: "Product",
    description: "Product Management"
)]
class ProductController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: "/api/products",
        summary: "Get All Products",
        description: "Get products with pagination, search, and sorting",
        tags: ["Product"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "search",
        in: "query",
        required: false,
        description: "Search by product name or SKU",
        schema: new OA\Schema(
            type: "string",
            maxLength: 100
        ),
        example: "Laptop"
    )]
    #[OA\Parameter(
        name: "per_page",
        in: "query",
        required: false,
        description: "Number of products per page",
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
                "name",
                "sku",
                "price",
                "stock",
                "created_at"
            ]
        ),
        example: "name"
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
        example: "asc"
    )]
    #[OA\Response(
        response: 200,
        description: "List of products"
    )]
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:name,sku,price,stock,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Pagination & Sorting
        |--------------------------------------------------------------------------
        */

        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        /*
        |--------------------------------------------------------------------------
        | Query Product
        |--------------------------------------------------------------------------
        */

        $query = Product::with([
            'category',
            'supplier',
            'warehouse'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $query->orderBy($sortBy, $sortOrder);

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return $this->paginationResponse(
            'Products retrieved successfully',
            $products
        );
    }

    #[OA\Post(
        path: "/api/products",
        summary: "Create Product",
        description: "Create a new product",
        tags: ["Product"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: [
                "category_id",
                "supplier_id",
                "warehouse_id",
                "name",
                "sku",
                "price"
            ],
            properties: [
                new OA\Property(
                    property: "category_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "supplier_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "warehouse_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "name",
                    type: "string",
                    maxLength: 255,
                    example: "Laptop ASUS"
                ),
                new OA\Property(
                    property: "sku",
                    type: "string",
                    maxLength: 100,
                    example: "SKU-001"
                ),
                new OA\Property(
                    property: "price",
                    type: "number",
                    format: "float",
                    minimum: 0,
                    example: 15000000
                ),
                new OA\Property(
                    property: "description",
                    type: "string",
                    example: "Laptop untuk kebutuhan kerja"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Product created successfully"
    )]
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Stock
        |--------------------------------------------------------------------------
        */

        $validated['stock'] = 0;

        /*
        |--------------------------------------------------------------------------
        | Create Product
        |--------------------------------------------------------------------------
        */

        $product = Product::create($validated);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return $this->successResponse(
            'Product created successfully',
            $product,
            201
        );
    }

    #[OA\Get(
        path: "/api/products/{id}",
        summary: "Get Product Detail",
        description: "Get detailed information about a product",
        tags: ["Product"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Product ID",
        schema: new OA\Schema(
            type: "integer"
        ),
        example: 2
    )]
    #[OA\Response(
        response: 200,
        description: "Product detail"
    )]
    public function show(Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $product->load([
            'category',
            'supplier',
            'warehouse'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return $this->successResponse(
            'Product retrieved successfully',
            $product
        );
    }

    #[OA\Put(
        path: "/api/products/{id}",
        summary: "Update Product",
        description: "Update product information without changing stock",
        tags: ["Product"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Product ID",
        schema: new OA\Schema(
            type: "integer"
        ),
        example: 2
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "category_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "supplier_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "warehouse_id",
                    type: "integer",
                    example: 1
                ),
                new OA\Property(
                    property: "name",
                    type: "string",
                    maxLength: 255,
                    example: "Laptop ASUS Vivobook 14"
                ),
                new OA\Property(
                    property: "sku",
                    type: "string",
                    maxLength: 100,
                    example: "SKU-LAP-001"
                ),
                new OA\Property(
                    property: "price",
                    type: "number",
                    format: "float",
                    minimum: 0,
                    example: 8500000
                ),
                new OA\Property(
                    property: "description",
                    type: "string",
                    example: "Updated product description"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Product updated successfully"
    )]
    public function update(Request $request, Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'supplier_id' => 'sometimes|required|integer|exists:suppliers,id',
            'warehouse_id' => 'sometimes|required|integer|exists:warehouses,id',
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'sometimes|required|string|max:100|unique:products,sku,' . $product->id,
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'sometimes|nullable|string'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Product
        |--------------------------------------------------------------------------
        */

        $product->update($validated);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return $this->successResponse(
            'Product updated successfully',
            $product->fresh()
        );
    }

    #[OA\Delete(
        path: "/api/products/{id}",
        summary: "Delete Product",
        description: "Delete a product if it does not have stock history",
        tags: ["Product"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "Product ID",
        schema: new OA\Schema(
            type: "integer"
        ),
        example: 2
    )]
    #[OA\Response(
        response: 200,
        description: "Product deleted successfully"
    )]
    #[OA\Response(
        response: 422,
        description: "Product cannot be deleted because it has stock history"
    )]
    public function destroy(Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | Check Stock History
        |--------------------------------------------------------------------------
        */

        $hasStockHistory = StockHistory::where(
            'product_id',
            $product->id
        )->exists();

        /*
        |--------------------------------------------------------------------------
        | Prevent Delete
        |--------------------------------------------------------------------------
        */

        if ($hasStockHistory) {
            return $this->errorResponse(
                'Product cannot be deleted because it already has stock history.',
                422
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Product
        |--------------------------------------------------------------------------
        */

        $product->delete();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return $this->successResponse(
            'Product deleted successfully'
        );
    }
}