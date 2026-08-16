<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

#[OA\Tag(
    name: "Category",
    description: "Category Management"
)]
class CategoryController extends Controller
{
    #[OA\Get(
        path: "/api/categories",
        summary: "Get All Categories",
        tags: ["Category"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "List of categories"
    )]
    public function index()
    {
        return response()->json([
            'data' => Category::all()
        ]);
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
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
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
        return response()->json([
            'data' => $category
        ]);
    }

    #[OA\Put(
        path: "/api/categories/{id}",
        summary: "Update Category",
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
            required: ["name"],
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
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    #[OA\Delete(
        path: "/api/categories/{id}",
        summary: "Delete Category",
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
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}