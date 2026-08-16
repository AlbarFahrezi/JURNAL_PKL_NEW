<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Authentication",
    description: "Authentication Management"
)]
class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/register",
        operationId: "registerUser",
        summary: "Register User",
        description: "Register a new user account",
        tags: ["Authentication"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name","email","password"],
            properties: [
                new OA\Property(
                    property: "name",
                    type: "string",
                    example: "John Doe"
                ),
                new OA\Property(
                    property: "email",
                    type: "string",
                    example: "john@gmail.com"
                ),
                new OA\Property(
                    property: "password",
                    type: "string",
                    example: "password123"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Register success"
    )]
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'operator'
        ]);

        return response()->json([
            'message' => 'Register success',
            'data' => $user
        ], 201);
    }

    #[OA\Post(
        path: "/api/login",
        operationId: "loginUser",
        summary: "Login User",
        description: "Login using email and password",
        tags: ["Authentication"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email","password"],
            properties: [
                new OA\Property(
                    property: "email",
                    type: "string",
                    example: "admin@gmail.com"
                ),
                new OA\Property(
                    property: "password",
                    type: "string",
                    example: "password123"
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Login success"
    )]
    #[OA\Response(
        response: 401,
        description: "Invalid credentials"
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('inventory-api')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'user' => $user
        ]);
    }

    #[OA\Get(
        path: "/api/profile",
        operationId: "profileUser",
        summary: "Get Profile",
        description: "Get authenticated user profile",
        tags: ["Authentication"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Profile retrieved successfully"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated"
    )]
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }

    #[OA\Post(
        path: "/api/logout",
        operationId: "logoutUser",
        summary: "Logout User",
        description: "Logout authenticated user",
        tags: ["Authentication"],
        security: [["sanctum" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Logout success"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated"
    )]
    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'message' => 'Logout success'
        ]);
    }
}