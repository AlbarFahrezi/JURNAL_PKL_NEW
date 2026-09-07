<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

#[OA\Tag(
    name: "Supplier",
    description: "Supplier Management"
)]
class SupplierController extends Controller
{
    use ApiResponse;

    #[OA\Get(
        path: "/api/suppliers",
        summary: "Get All Suppliers",
        description: "Get suppliers with pagination, search, and sorting",
        tags: ["Supplier"],
        security: [["sanctum" => []]]
    )]
    #[OA\Parameter(
        name: "search",
        in: "query",
        required: false,
        description: "Search by supplier name, phone, or address",
        schema: new OA\Schema(type: "string"),
        example: "Dahana"
    )]
    #[OA\Parameter(
        name: "per_page",
        in: "query",
        required: false,
        description: "Number of suppliers per page",
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
        description: "List of suppliers"
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

        $query = Supplier::query();

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('address', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy($sortBy, $sortOrder);

        $suppliers = $query
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginationResponse(
            'Suppliers retrieved successfully',
            $suppliers
        );
    }

    #[OA\Post(
        path: "/api/suppliers",
        summary: "Create Supplier",
        tags: ["Supplier"],
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
                    example: "PT Dahana"
                ),
                new OA\Property(
                    property: "phone",
                    type: "string",
                    example: "081234567890"
                ),
                new OA\Property(
                    property: "address",
                    type: "string",
                    example: "Subang, Jawa Barat"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Supplier created successfully"
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string'
        ]);

        $supplier = Supplier::create($validated);

        return $this->successResponse(
            'Supplier created successfully',
            $supplier,
            201
        );
    }

    #[OA\Get(
        path: "/api/suppliers/{id}",
        summary: "Get Supplier Detail",
        tags: ["Supplier"],
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
        description: "Supplier detail"
    )]
    public function show(Supplier $supplier)
    {
        return $this->successResponse(
            'Supplier retrieved successfully',
            $supplier
        );
    }

    #[OA\Put(
        path: "/api/suppliers/{id}",
        summary: "Update Supplier",
        description: "Update supplier information",
        tags: ["Supplier"],
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
                    example: "PT Dahana Indonesia"
                ),
                new OA\Property(
                    property: "phone",
                    type: "string",
                    example: "081234567890"
                ),
                new OA\Property(
                    property: "address",
                    type: "string",
                    example: "Bandung"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Supplier updated successfully"
    )]
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|nullable|string'
        ]);

        $supplier->update($validated);

        return $this->successResponse(
            'Supplier updated successfully',
            $supplier->fresh()
        );
    }

    #[OA\Delete(
        path: "/api/suppliers/{id}",
        summary: "Delete Supplier",
        description: "Delete a supplier. Supplier cannot be deleted if it is still used by one or more products.",
        tags: ["Supplier"],
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
        description: "Supplier deleted successfully"
    )]
    #[OA\Response(
        response: 409,
        description: "Supplier cannot be deleted because it is still used by products"
    )]
    public function destroy(Supplier $supplier)
    {
        $isUsed = Product::where(
            'supplier_id',
            $supplier->id
        )->exists();

        if ($isUsed) {
            return $this->errorResponse(
                'Supplier cannot be deleted because it is still used by one or more products.',
                409
            );
        }

        $supplier->delete();

        return $this->successResponse(
            'Supplier deleted successfully'
        );
    }
}