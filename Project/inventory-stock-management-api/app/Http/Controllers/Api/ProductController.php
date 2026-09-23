<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Product;
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
        description: "Get products with search, filter, sorting, and pagination",
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
        name: "category_id",
        in: "query",
        required: false,
        description: "Filter products by category ID",
        schema: new OA\Schema(
            type: "integer",
            minimum: 1
        ),
        example: 1
    )]
    #[OA\Parameter(
        name: "date_from",
        in: "query",
        required: false,
        description: "Filter products created from this date",
        schema: new OA\Schema(
            type: "string",
            format: "date"
        ),
        example: "2026-09-01"
    )]
    #[OA\Parameter(
        name: "date_to",
        in: "query",
        required: false,
        description: "Filter products created until this date",
        schema: new OA\Schema(
            type: "string",
            format: "date"
        ),
        example: "2026-09-23"
    )]
    #[OA\Parameter(
        name: "per_page",
        in: "query",
        required: false,
        description: "Number of products per page",
        schema: new OA\Schema(
            type: "integer",
            enum: [10, 25, 50, 100]
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
                "id",
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
        | Validation Query Parameter
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'search' => 'nullable|string|max:100',

            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:date_from',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'in:10,25,50,100',
            ],

            'sort_by' => [
                'nullable',
                'in:id,name,sku,price,stock,created_at',
            ],

            'sort_order' => [
                'nullable',
                'in:asc,desc',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Default Query Parameter
        |--------------------------------------------------------------------------
        */

        $perPage = $request->input('per_page', 10);

        $sortBy = $request->input('sort_by', 'created_at');

        $sortOrder = $request->input('sort_order', 'desc');

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        |
        | Eager loading tetap digunakan agar relasi category, supplier,
        | dan warehouse tidak menyebabkan query berulang.
        |
        */

        $query = Product::with([
            'category',
            'supplier',
            'warehouse',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        |
        | Pencarian berdasarkan nama product atau SKU.
        |
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
        | Filter Category
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->input('category_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Date From
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->input('date_from')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Date To
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->input('date_to')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $query->orderBy(
            $sortBy,
            $sortOrder
        );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        |
        | withQueryString() memastikan search, filter, sorting,
        | dan parameter lainnya tetap terbawa ketika pindah halaman.
        |
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
        $validated = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['stock'] = 0;

        $product = Product::create($validated);

        return $this->successResponse(
            'Product created successfully',
            $product,
            201
        );
    }

    #[OA\Get(
        path: "/api/products/{id}",
        summary: "Get Product Detail",
        description: "Get detailed information about a product including lock version",
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
        $product->load([
            'category',
            'supplier',
            'warehouse',
        ]);

        return $this->successResponse(
            'Product retrieved successfully',
            $product
        );
    }

    #[OA\Put(
        path: "/api/products/{id}",
        summary: "Update Product",
        description: "Update product information using optimistic locking. The lock_version must match the latest version.",
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
            required: [
                "lock_version"
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
                ),
                new OA\Property(
                    property: "lock_version",
                    type: "integer",
                    minimum: 0,
                    example: 0,
                    description: "Current product version obtained from GET /api/products/{id}"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Product updated successfully"
    )]
    #[OA\Response(
        response: 409,
        description: "Optimistic locking conflict"
    )]
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|required|integer|exists:categories,id',
            'supplier_id' => 'sometimes|required|integer|exists:suppliers,id',
            'warehouse_id' => 'sometimes|required|integer|exists:warehouses,id',
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'sometimes|required|string|max:100|unique:products,sku,' . $product->id,
            'price' => 'sometimes|required|numeric|min:0',
            'description' => 'sometimes|nullable|string',
            'lock_version' => 'required|integer|min:0',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Optimistic Locking
        |--------------------------------------------------------------------------
        */

        $currentVersion = (int) $validated['lock_version'];

        unset($validated['lock_version']);

        $affected = Product::whereKey($product->id)
            ->where('lock_version', $currentVersion)
            ->update(array_merge($validated, [
                'lock_version' => $currentVersion + 1,
                'updated_at' => now(),
            ]));

        /*
        |--------------------------------------------------------------------------
        | Conflict Detection
        |--------------------------------------------------------------------------
        */

        if ($affected === 0) {
            return $this->errorResponse(
                'Product data has been modified by another request. Please refresh and try again.',
                409
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Refresh Product
        |--------------------------------------------------------------------------
        */

        $product->refresh();

        return $this->successResponse(
            'Product updated successfully',
            $product
        );
    }

    #[OA\Delete(
        path: "/api/products/{id}",
        summary: "Soft Delete Product",
        description: "Soft delete a product while preserving historical data",
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
    public function destroy(Product $product)
    {
        $product->delete();

        return $this->successResponse(
            'Product deleted successfully'
        );
    }
}