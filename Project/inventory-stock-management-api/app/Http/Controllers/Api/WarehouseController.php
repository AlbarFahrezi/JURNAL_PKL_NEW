<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;

#[OA\Tag(
    name: "Warehouse",
    description: "Warehouse Management"
)]
class WarehouseController extends Controller
{
    #[OA\Get(
        path: "/api/warehouses",
        summary: "Get All Warehouses",
        tags: ["Warehouse"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "List of warehouses"
    )]
    public function index()
    {
        return response()->json([
            'data' => Warehouse::all()
        ]);
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'location' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $warehouse = Warehouse::create([
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Warehouse created successfully',
            'data' => $warehouse
        ], 201);
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
    public function show(Warehouse $warehouse)
    {
        return response()->json([
            'data' => $warehouse
        ]);
    }

    #[OA\Put(
        path: "/api/warehouses/{id}",
        summary: "Update Warehouse",
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
                new OA\Property(property: "name", type: "string", example: "Warehouse B"),
                new OA\Property(property: "location", type: "string", example: "Bandung"),
                new OA\Property(property: "description", type: "string", example: "Updated Warehouse")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Warehouse updated successfully"
    )]
    public function update(Request $request, Warehouse $warehouse)
    {
        $warehouse->update([
            'name' => $request->name,
            'location' => $request->location,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Warehouse updated successfully',
            'data' => $warehouse
        ]);
    }

    #[OA\Delete(
        path: "/api/warehouses/{id}",
        summary: "Delete Warehouse",
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
    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json([
            'message' => 'Warehouse deleted successfully'
        ]);
    }
}