<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

#[OA\Tag(
    name: "Warehouse",
    description: "Warehouse Management"
)]
class WarehouseController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: "/api/warehouses",
        summary: "Get All Warehouses",
        description: "Get warehouses with pagination, search, and sorting",
        tags: ["Warehouse"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "search",
        in: "query",
        required: false,
        description: "Search by warehouse name, location, or description",
        schema: new OA\Schema(type: "string"),
        example: "Main"
    )]
    #[OA\Parameter(
        name: "per_page",
        in: "query",
        required: false,
        description: "Number of warehouses per page",
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
            enum: ["name", "location", "created_at"]
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
        description: "List of warehouses"
    )]
    #[OA\Response(
        response: 422,
        description: "Validation error"
    )]
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
            'sort_by' => 'nullable|in:name,location,created_at',
            'sort_order' => 'nullable|in:asc,desc',
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';

        $query = Warehouse::query();

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy($sortBy, $sortOrder);

        $warehouses = $query
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginationResponse(
            'Warehouses retrieved successfully',
            $warehouses
        );
    }

    #[OA\Post(
        path: "/api/warehouses",
        summary: "Create Warehouse",
        tags: ["Warehouse"],
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
                    example: "Warehouse A"
                ),
                new OA\Property(
                    property: "location",
                    type: "string",
                    example: "Subang"
                ),
                new OA\Property(
                    property: "description",
                    type: "string",
                    example: "Main Warehouse"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Warehouse created successfully"
    )]
    #[OA\Response(
        response: 422,
        description: "Validation error"
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);

        $warehouse = Warehouse::create($validated);

        return $this->successResponse(
            'Warehouse created successfully',
            $warehouse,
            201
        );
    }

    #[OA\Get(
        path: "/api/warehouses/{id}",
        summary: "Get Warehouse Detail",
        tags: ["Warehouse"],
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
        description: "Warehouse detail"
    )]
    #[OA\Response(
        response: 404,
        description: "Warehouse not found"
    )]
    public function show(Warehouse $warehouse)
    {
        return $this->successResponse(
            'Warehouse retrieved successfully',
            $warehouse
        );
    }

    #[OA\Put(
        path: "/api/warehouses/{id}",
        summary: "Update Warehouse",
        description: "Update warehouse information",
        tags: ["Warehouse"],
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
                    example: "Warehouse B"
                ),
                new OA\Property(
                    property: "location",
                    type: "string",
                    example: "Bandung"
                ),
                new OA\Property(
                    property: "description",
                    type: "string",
                    example: "Updated Warehouse"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Warehouse updated successfully"
    )]
    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string'
        ]);

        $warehouse->update($validated);

        return $this->successResponse(
            'Warehouse updated successfully',
            $warehouse->fresh()
        );
    }

    #[OA\Delete(
        path: "/api/warehouses/{id}",
        summary: "Delete Warehouse",
        description: "Delete a warehouse. Warehouse cannot be deleted if it is still used by one or more products.",
        tags: ["Warehouse"],
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
        description: "Warehouse deleted successfully"
    )]
    #[OA\Response(
        response: 409,
        description: "Warehouse cannot be deleted because it is still used by products"
    )]
    public function destroy(Warehouse $warehouse)
    {
        $isUsed = Product::where(
            'warehouse_id',
            $warehouse->id
        )->exists();

        if ($isUsed) {
            return $this->errorResponse(
                'Warehouse cannot be deleted because it is still used by one or more products.',
                409
            );
        }

        $warehouse->delete();

        return $this->successResponse(
            'Warehouse deleted successfully'
        );
    }
}