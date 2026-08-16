<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

#[OA\Tag(
    name: "Supplier",
    description: "Supplier Management"
)]
class SupplierController extends Controller
{
    #[OA\Get(
        path: "/api/suppliers",
        summary: "Get All Suppliers",
        tags: ["Supplier"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "List of suppliers"
    )]
    public function index()
    {
        return response()->json([
            'data' => Supplier::all()
        ]);
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
        $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        $supplier = Supplier::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address
        ]);

        return response()->json([
            'message' => 'Supplier created successfully',
            'data' => $supplier
        ], 201);
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
        return response()->json([
            'data' => $supplier
        ]);
    }

    #[OA\Put(
        path: "/api/suppliers/{id}",
        summary: "Update Supplier",
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
        $supplier->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address
        ]);

        return response()->json([
            'message' => 'Supplier updated successfully',
            'data' => $supplier
        ]);
    }

    #[OA\Delete(
        path: "/api/suppliers/{id}",
        summary: "Delete Supplier",
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
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully'
        ]);
    }
}