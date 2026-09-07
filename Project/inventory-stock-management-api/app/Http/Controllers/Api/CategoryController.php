<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

#[OA\Tag(
    name: "Category",
    description: "Category Management"
)]
class CategoryController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: "/api/categories",
        summary: "Get All Categories",
        description: "Get categories with pagination, search, and sorting",
        tags: ["Category"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "search",
        in: "query",
        required: false,
        description: "Search by category name or description",
        schema: new OA\Schema(type: "string"),
        example: "Laptop"
    )]
    #[OA\Parameter(
        name: "per_page",
        in: "query",
        required: false,
        description: "Number of categories per page",
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
            enum: ["name", "created_at"]
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
        description: "List of categories"
    )]
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:name,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';

        $query = Category::query();

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy($sortBy, $sortOrder);

        $categories = $query
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginationResponse(
            'Categories retrieved successfully',
            $categories
        );
    }

    #[OA\Post(
        path: "/api/categories",
        summary: "Create Category",
        tags: ["Category"],
        security: [["sanctum" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name"],
            properties: [
                new OA\Property(
                    property: "name",
                    type: "string",
                    example: "Electronics"
                ),
                new OA\Property(
                    property: "description",
                    type: "string",
                    example: "Electronic Products"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Category created successfully"
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category = Category::create($validated);

        return $this->successResponse(
            'Category created successfully',
            $category,
            201
        );
    }

    #[OA\Get(
        path: "/api/categories/{id}",
        summary: "Get Category Detail",
        tags: ["Category"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Category detail"
    )]
    public function show(Category $category)
    {
        return $this->successResponse(
            'Category retrieved successfully',
            $category
        );
    }

    #[OA\Put(
        path: "/api/categories/{id}",
        summary: "Update Category",
        description: "Update category information",
        tags: ["Category"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "name",
                    type: "string",
                    example: "Electronic"
                ),
                new OA\Property(
                    property: "description",
                    type: "string",
                    example: "Updated Description"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Category updated successfully"
    )]
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string'
        ]);

        $category->update($validated);

        return $this->successResponse(
            'Category updated successfully',
            $category->fresh()
        );
    }

    #[OA\Delete(
        path: "/api/categories/{id}",
        summary: "Delete Category",
        description: "Delete a category. Category cannot be deleted if it is still used by a product.",
        tags: ["Category"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Category deleted successfully"
    )]
    #[OA\Response(
        response: 409,
        description: "Category cannot be deleted because it is still used by products"
    )]
    public function destroy(Category $category)
    {
        $isUsed = Product::where(
            'category_id',
            $category->id
        )->exists();

        if ($isUsed) {
            return $this->errorResponse(
                'Category cannot be deleted because it is still used by one or more products.',
                409
            );
        }

        $category->delete();

        return $this->successResponse(
            'Category deleted successfully'
        );
    }
}