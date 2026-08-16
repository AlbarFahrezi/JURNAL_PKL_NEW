<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Inventory Stock Management API",
    description: "REST API untuk mengelola data inventaris, produk, supplier, warehouse, dan stok."
)]

#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Local Server"
)]

    #[OA\SecurityScheme(
        securityScheme: "sanctum",
        type: "http",
        scheme: "bearer",
        bearerFormat: "JWT"
    )]

    

class Swagger
{
}