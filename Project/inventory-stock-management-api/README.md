# 📦 Inventory Stock Management API

RESTful API built with **Laravel 12**, **Laravel Sanctum**, **Swagger (OpenAPI)**, and **MySQL** for managing inventory, products, suppliers, warehouses, and stock transactions with role-based authentication.

---

## 🛠 Tech Stack

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql)
![Sanctum](https://img.shields.io/badge/Auth-Sanctum-success?style=for-the-badge)
![Swagger](https://img.shields.io/badge/API-Swagger-85EA2D?style=for-the-badge&logo=swagger)

---

# 📑 Table of Contents

- Features
- Role Access
- Business Logic
- Inventory Flow
- Authentication Flow
- Tech Stack
- Installation
- Default Account
- Swagger Documentation
- API Endpoints
- Authentication
- API Testing
- Future Improvements
- Author

---

# ✨ Features

## Authentication

- Register
- Login
- Logout
- User Profile
- Laravel Sanctum Authentication

---

## Role Management

- Admin
- User

---

## Category

- Create Category
- Read Category
- Update Category
- Delete Category
- View All Categories

---

## Supplier

- Create Supplier
- Read Supplier
- Update Supplier
- Delete Supplier
- View All Suppliers

---

## Warehouse

- Create Warehouse
- Read Warehouse
- Update Warehouse
- Delete Warehouse
- View All Warehouses

---

## Product

- Create Product
- Read Product
- Update Product
- Delete Product

---

## Stock Management

- Stock In
- Stock Out
- Stock Adjustment
- Stock History

---

## Dashboard

- Total Products
- Total Categories
- Total Suppliers
- Total Warehouses
- Total Stock
- Stock In Today
- Stock Out Today
- Stock Adjustment Today

---

# 👥 Role Access

| Feature | Admin | User |
|---------|:----:|:----:|
| Dashboard | ✅ | ✅ |
| Profile | ✅ | ✅ |
| Stock History | ✅ | ✅ |
| Category CRUD | ✅ | ❌ |
| Supplier CRUD | ✅ | ❌ |
| Warehouse CRUD | ✅ | ❌ |
| Product CRUD | ✅ | ❌ |
| Stock In | ✅ | ❌ |
| Stock Out | ✅ | ❌ |
| Stock Adjustment | ✅ | ❌ |

---

# 📌 Business Logic

- Stock cannot be negative.
- Every stock transaction is automatically stored in Stock History.
- Newly created products have an initial stock of **0**.
- Only **Admin** can manage master data and stock transactions.
- **User** can only access Dashboard, Profile, and Stock History.

---

# 📦 Inventory Flow

```text
Category
      │
      ▼
Supplier
      │
      ▼
Warehouse
      │
      ▼
Create Product
      │
      ▼
Initial Stock = 0
      │
      ▼
Stock In
      │
      ▼
Stock Out
      │
      ▼
Stock Adjustment
      │
      ▼
Stock History
      │
      ▼
Dashboard
```

---

# 🔐 Authentication Flow

```text
Register
    │
    ▼
Login
    │
    ▼
Generate Bearer Token
    │
    ▼
Access Protected API
    │
    ▼
Logout
```

---

# 💻 Tech Stack

- Laravel 12
- PHP 8.3
- MySQL
- Laravel Sanctum
- Swagger OpenAPI (L5-Swagger)

---

# 🚀 Installation

Clone repository

```bash
git clone https://github.com/AlbarFahrezi/Inventory-Stock-Management-API.git
```

Go to project directory

```bash
cd Inventory-Stock-Management-API
```

Install dependencies

```bash
composer install
```

Copy environment file

```bash
cp .env.example .env
```

Generate application key

```bash
php artisan key:generate
```

Configure database in `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_stock_management_api_new
DB_USERNAME=root
DB_PASSWORD=
```

Run migration

```bash
php artisan migrate
```

Run database seeder

```bash
php artisan db:seed
```

Generate Swagger Documentation

```bash
php artisan l5-swagger:generate
```

Run server

```bash
php artisan serve
```

---

# 👤 Default Account

## Admin

Email

```
admin@gmail.com
```

Password

```
password
```

---

## User

Email

```
user@gmail.com
```

Password

```
password
```

---

# 📖 Swagger Documentation

This project includes **Swagger OpenAPI Documentation**.

Generate documentation

```bash
php artisan l5-swagger:generate
```

Open Swagger UI

```
http://127.0.0.1:8000/api/documentation
```

Swagger Features

- Interactive API Documentation
- Bearer Token Authentication
- Request Body Example
- Response Example
- Endpoint Testing
- OpenAPI 3 Specification

---

# 📡 API Endpoints

## Authentication

| Method | Endpoint |
|---------|----------|
| POST | /api/register |
| POST | /api/login |
| GET | /api/profile |
| POST | /api/logout |

---

## Dashboard

| Method | Endpoint |
|---------|----------|
| GET | /api/dashboard |

---

## Category

| Method | Endpoint |
|---------|----------|
| GET | /api/categories |
| POST | /api/categories |
| GET | /api/categories/{id} |
| PUT | /api/categories/{id} |
| DELETE | /api/categories/{id} |

---

## Supplier

| Method | Endpoint |
|---------|----------|
| GET | /api/suppliers |
| POST | /api/suppliers |
| GET | /api/suppliers/{id} |
| PUT | /api/suppliers/{id} |
| DELETE | /api/suppliers/{id} |

---

## Warehouse

| Method | Endpoint |
|---------|----------|
| GET | /api/warehouses |
| POST | /api/warehouses |
| GET | /api/warehouses/{id} |
| PUT | /api/warehouses/{id} |
| DELETE | /api/warehouses/{id} |

---

## Product

| Method | Endpoint |
|---------|----------|
| GET | /api/products |
| POST | /api/products |
| GET | /api/products/{id} |
| PUT | /api/products/{id} |
| DELETE | /api/products/{id} |

---

## Stock

| Method | Endpoint |
|---------|----------|
| POST | /api/stock-in |
| POST | /api/stock-out |
| POST | /api/stock-adjustment |
| GET | /api/stock-history |

---

# 🔑 Authentication

All endpoints except **Register** and **Login** require a Bearer Token.

Example

```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

# 📄 Response Example

```json
{
  "message": "Login success",
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Administrator",
    "email": "admin@gmail.com",
    "role": "admin"
  }
}
```

---

# 🧪 API Testing

The API has been tested using:

- ✅ Swagger UI
- ✅ Postman

All endpoints have been successfully tested, including:

- Authentication
- Category CRUD
- Supplier CRUD
- Warehouse CRUD
- Product CRUD
- Stock Management
- Dashboard
- Role Authorization

---

# 🚀 Future Improvements

- Export Excel
- Export PDF
- Barcode Scanner
- Pagination
- Search & Filter
- Unit Testing
- Docker Support
- CI/CD GitHub Actions

---

# 👨‍💻 Author

**Albar Fahrezi**

GitHub

https://github.com/AlbarFahrezi

---

⭐ If you find this project useful, feel free to give it a star on GitHub.