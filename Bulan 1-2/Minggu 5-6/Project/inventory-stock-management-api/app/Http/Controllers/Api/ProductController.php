<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

#[OA\Tag(
    name: "Product",
    description: "Product Management"
)]
class ProductController extends Controller
{
    #[OA\Get(
        path: "/api/products",
        summary: "Get All Products",
        tags: ["Product"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "List of products"
    )]
    public function index()
    {
        $products = Product::with([
            'category',
            'supplier',
            'warehouse'
        ])->get();

        return response()->json([
            'data' => $products
        ]);
    }

    #[OA\Post(
        path: "/api/products",
        summary: "Create Product",
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
                new OA\Property(property: "category_id", type: "integer", example: 1),
                new OA\Property(property: "supplier_id", type: "integer", example: 1),
                new OA\Property(property: "warehouse_id", type: "integer", example: 1),
                new OA\Property(property: "name", type: "string", example: "Laptop ASUS"),
                new OA\Property(property: "sku", type: "string", example: "SKU-001"),
                new OA\Property(property: "price", type: "number", format: "float", example: 15000000),
                new OA\Property(property: "description", type: "string", example: "Gaming Laptop")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Product created successfully"
    )]
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric',
            'description' => 'nullable|string'
        ]);

        $product = Product::create([
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'warehouse_id' => $request->warehouse_id,
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'stock' => 0,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    #[OA\Get(
        path: "/api/products/{id}",
        summary: "Get Product Detail",
        tags: ["Product"],
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
        description: "Product detail"
    )]
    public function show(Product $product)
    {
        return response()->json([
            'data' => $product->load([
                'category',
                'supplier',
                'warehouse'
            ])
        ]);
    }

    #[OA\Put(
        path: "/api/products/{id}",
        summary: "Update Product",
        tags: ["Product"],
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
                new OA\Property(property: "name", type: "string", example: "Laptop ASUS ROG"),
                new OA\Property(property: "price", type: "number", format: "float", example: 17000000),
                new OA\Property(property: "description", type: "string", example: "Updated Product")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Product updated successfully"
    )]
    public function update(Request $request, Product $product)
    {
        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    #[OA\Delete(
        path: "/api/products/{id}",
        summary: "Delete Product",
        tags: ["Product"],
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
        description: "Product deleted successfully"
    )]
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}