# 📋 PROJECT PLAN — Backend Technical Assessment

> **Project:** REST API Technical Test — Product Management System  
> **Stack:** Laravel · JWT Authentication · SQLite · Blade (Visualization Only)  
> **Author:** Backend Division  
> **Date:** May 2026  
> **Version:** 2.0

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Functional Requirements](#2-functional-requirements)
3. [Technical Architecture](#3-technical-architecture)
4. [Database Design](#4-database-design)
5. [Backend Planning](#5-backend-planning)
6. [Blade Visualization Planning](#6-blade-visualization-planning)
7. [Folder Structure](#7-folder-structure)
8. [Testing Strategy](#8-testing-strategy)
9. [Development Phases](#9-development-phases)
10. [Technical Considerations](#10-technical-considerations)
11. [README Planning](#11-readme-planning)

---

## 1. Project Overview

### 1.1 Purpose

A **backend-focused technical assessment** to evaluate REST API design, JWT authentication, authorization, input validation, and automated testing using Laravel.

The deliverable is a **Product Management API** with JWT auth, SQLite storage, and a minimal Blade demo page.

### 1.2 Scope

| In Scope | Out of Scope |
|---|---|
| RESTful Product CRUD API | Complex frontend / SPA |
| JWT authentication (register, login) | OAuth / social login |
| Ownership authorization (Policy) | Role-based access control |
| Pagination, search, sort | Real-time features |
| Automated test suite | CI/CD pipeline |
| Lightweight Blade demo page (bonus) | Production deployment |
| Health check endpoint | File upload / media |

### 1.3 Main Features

- **User Registration & Login** — Account creation and JWT-based authentication.
- **Product CRUD** — Create, read, update (partial), and delete products.
- **Ownership Authorization** — Only product owners can update or delete; enforced via Laravel Policy.
- **Query Features** — Pagination, search by name, and sorting.
- **Health Endpoint** — Public endpoint to check API status.
- **Blade Visualization (Bonus)** — Minimal UI consuming the API via `fetch()` with JWT Bearer tokens.

### 1.4 Architecture

API-first architecture — all business logic lives in the API layer. Any client (Postman, Blade, mobile) is a consumer.

```
┌─────────────────────────────────────────────┐
│                 Clients                      │
│  ┌─────────┐  ┌────────────┐  ┌──────────┐  │
│  │ Postman  │  │ Blade Demo │  │  cURL    │  │
│  └────┬─────┘  └─────┬──────┘  └────┬─────┘  │
└───────┼──────────────┼──────────────┼────────┘
        │              │              │
        ▼              ▼              ▼
┌──────────────────────────────────────────────┐
│              Laravel REST API                │
│  Middleware → Controller → Policy → Eloquent │
│                     │                        │
│                  SQLite                       │
└──────────────────────────────────────────────┘
```

---

## 2. Functional Requirements

### 2.1 Authentication

| ID | Requirement | Description |
|---|---|---|
| AUTH-01 | Register | New users register with `name`, `email`, `password`. |
| AUTH-02 | Email Uniqueness | Duplicate emails rejected with 422. |
| AUTH-03 | Password Hashing | Passwords stored as bcrypt hashes. |
| AUTH-04 | Login | Users log in with `email` + `password`, receive a JWT. |
| AUTH-05 | JWT Issuance | Login returns `access_token`, `token_type`, `expires_in`. |
| AUTH-06 | Protected Routes | Requests without valid JWT return 401. |

### 2.2 Product Management

| ID | Requirement | Description |
|---|---|---|
| PROD-01 | Create Product | Authenticated users create products (`name`, `description`, `price`). |
| PROD-02 | List Products | Paginated product listing. |
| PROD-03 | Search Products | Search by product `name`. |
| PROD-04 | Sort Products | Sort by `name`, `price`, or `created_at` (asc/desc). |
| PROD-05 | View Product | View a single product by ID. |
| PROD-06 | Update Product | Owner-only partial update via `PATCH`. |
| PROD-07 | Delete Product | Owner-only deletion. |
| PROD-08 | Ownership Enforcement | Non-owners receive `403 Forbidden`. |

### 2.3 System

| ID | Requirement | Description |
|---|---|---|
| SYS-01 | Health Check | Public `GET /api/v1/health` returns status and timestamp. |
| SYS-02 | Standardized Responses | Consistent JSON envelope for all responses. |
| SYS-03 | Validation Errors | 422 responses with field-level error messages. |

---

## 3. Technical Architecture

### 3.1 Key Decisions

- **Stateless auth** — JWT via `tymon/jwt-auth`, no sessions.
- **JSON-only** — All endpoints return `application/json`.
- **API versioning** — Routes prefixed with `/api/v1/`.
- **Form Request validation** — Dedicated `FormRequest` classes per endpoint.
- **API Resources** — `JsonResource` classes for response transformation.
- **Policy authorization** — Ownership checks via `ProductPolicy`.

### 3.2 JWT Authentication Flow

```
Client                              Laravel API
  │                                      │
  │  POST /api/v1/auth/register          │
  │  {name, email, password}             │
  │─────────────────────────────────────→│
  │                                      │── Validate → Hash → Create user
  │  201 {user, access_token}            │
  │←─────────────────────────────────────│
  │                                      │
  │  POST /api/v1/auth/login             │
  │  {email, password}                   │
  │─────────────────────────────────────→│
  │                                      │── Verify credentials → Generate JWT
  │  200 {access_token, token_type,      │
  │       expires_in}                    │
  │←─────────────────────────────────────│
  │                                      │
  │  GET /api/v1/products                │
  │  Authorization: Bearer <token>       │
  │─────────────────────────────────────→│
  │                                      │── Validate JWT → Process request
  │  200 {data: [...], meta: {...}}      │
  │←─────────────────────────────────────│
```

### 3.3 Request Lifecycle (Protected Route)

```
HTTP Request
    │
    ▼
Route Matching  →  /api/v1/products/{id}  [PATCH]
    │
    ▼
Middleware (auth:api)  →  JWT validation
    │
    │  Invalid? → 401 Unauthorized
    ▼
Form Request (UpdateProductRequest)  →  Validation
    │
    │  Invalid? → 422 Unprocessable Entity
    ▼
Controller → $this->authorize()  →  ProductPolicy
    │
    │  Not owner? → 403 Forbidden
    ▼
Business Logic  →  Update product
    │
    ▼
API Resource  →  ProductResource → JSON Response (200 OK)
```

---

## 4. Database Design

### 4.1 Entity Relationship

```
┌─────────────────────┐         ┌──────────────────────────┐
│       users          │         │        products           │
├─────────────────────┤         ├──────────────────────────┤
│ id       INTEGER PK │───┐     │ id          INTEGER PK    │
│ name     VARCHAR     │   │     │ user_id     INTEGER FK    │
│ email    VARCHAR UQ  │   └────→│ name        VARCHAR       │
│ password VARCHAR     │         │ description TEXT nullable  │
│ created_at TIMESTAMP │         │ price       DECIMAL(10,2) │
│ updated_at TIMESTAMP │         │ created_at  TIMESTAMP     │
└─────────────────────┘         │ updated_at  TIMESTAMP     │
                                └──────────────────────────┘

Relationship: users (1) ── (N) products
```

### 4.2 Migrations

**Users Table:**
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->timestamps();
});
```

**Products Table:**
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->timestamps();

    $table->index('user_id');
    $table->index('name');
});
```

### 4.3 SQLite Notes

- Enable foreign keys in `config/database.php`: `'foreign_key_constraints' => true`
- SQLite stores `DECIMAL` as `REAL` — acceptable for this project.
- Use `:memory:` for fast test execution with `RefreshDatabase`.
- Zero setup — no database server needed.

---

## 5. Backend Planning

### 5.1 Endpoint List

#### Authentication

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/v1/auth/register` | Public | Register a new user |
| `POST` | `/api/v1/auth/login` | Public | Login and receive JWT |

#### Products

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/products` | Bearer | List products (paginated, searchable, sortable) |
| `POST` | `/api/v1/products` | Bearer | Create a new product |
| `GET` | `/api/v1/products/{id}` | Bearer | Get a single product |
| `PATCH` | `/api/v1/products/{id}` | Bearer | Partial update (owner only) |
| `DELETE` | `/api/v1/products/{id}` | Bearer | Delete product (owner only) |

#### System

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/api/v1/health` | Public | Health check |

### 5.2 Request/Response Examples

#### Register

```http
POST /api/v1/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**201 Created:**
```json
{
    "success": true,
    "message": "User registered successfully.",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2026-05-22T14:00:00.000000Z"
        },
        "access_token": "eyJ0eXAiOiJKV1Qi...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

**422 Validation Error:**
```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

#### Login

```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**200 OK:**
```json
{
    "success": true,
    "message": "Login successful.",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1Qi...",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

**401 Unauthorized:**
```json
{
    "success": false,
    "message": "Invalid credentials."
}
```

#### Create Product

```http
POST /api/v1/products
Content-Type: application/json
Authorization: Bearer eyJ0eXAiOiJKV1Qi...

{
    "name": "Wireless Keyboard",
    "description": "Ergonomic wireless keyboard.",
    "price": 49.99
}
```

**201 Created:**
```json
{
    "success": true,
    "message": "Product created successfully.",
    "data": {
        "id": 1,
        "name": "Wireless Keyboard",
        "description": "Ergonomic wireless keyboard.",
        "price": 49.99,
        "user_id": 1,
        "created_at": "2026-05-22T14:05:00.000000Z",
        "updated_at": "2026-05-22T14:05:00.000000Z"
    }
}
```

#### List Products (with query params)

```http
GET /api/v1/products?page=1&per_page=10&search=keyboard&sort_by=price&sort_order=asc
Authorization: Bearer eyJ0eXAiOiJKV1Qi...
```

**200 OK:**
```json
{
    "success": true,
    "message": "Products retrieved successfully.",
    "data": [
        {
            "id": 1,
            "name": "Wireless Keyboard",
            "description": "Ergonomic wireless keyboard.",
            "price": 49.99,
            "user_id": 1,
            "created_at": "2026-05-22T14:05:00.000000Z",
            "updated_at": "2026-05-22T14:05:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "total": 1
    }
}
```

#### Update Product (PATCH — Owner Only)

```http
PATCH /api/v1/products/1
Content-Type: application/json
Authorization: Bearer eyJ0eXAiOiJKV1Qi...

{
    "name": "Wireless Keyboard Pro",
    "price": 59.99
}
```

**200 OK (owner):** Returns updated product data.

**403 Forbidden (non-owner):**
```json
{
    "success": false,
    "message": "You are not authorized to update this product."
}
```

#### Delete Product (Owner Only)

```http
DELETE /api/v1/products/1
Authorization: Bearer eyJ0eXAiOiJKV1Qi...
```

**200 OK:** `{ "success": true, "message": "Product deleted successfully." }`

**403 Forbidden (non-owner):** `{ "success": false, "message": "..." }`

#### Health Check

```http
GET /api/v1/health
```

**200 OK:**
```json
{
    "success": true,
    "message": "API is running.",
    "data": {
        "status": "healthy",
        "version": "1.0.0",
        "timestamp": "2026-05-22T14:00:00.000000Z"
    }
}
```

### 5.3 API Response Format

All responses use a consistent envelope:

```json
// Success
{ "success": true, "message": "...", "data": { } }

// Success with pagination
{ "success": true, "message": "...", "data": [ ], "meta": { "current_page": 1, "last_page": 5, "per_page": 10, "total": 50 } }

// Error
{ "success": false, "message": "..." }

// Validation error
{ "success": false, "message": "Validation failed.", "errors": { "field": ["..."] } }
```

Implement via an `ApiResponse` trait used by all controllers.

### 5.4 Validation Rules

| Form Request | Controller Method | Rules |
|---|---|---|
| `RegisterRequest` | `AuthController@register` | `name`: required, string, max:255 |
| | | `email`: required, email, unique:users |
| | | `password`: required, string, min:8, confirmed |
| `LoginRequest` | `AuthController@login` | `email`: required, email |
| | | `password`: required, string |
| `StoreProductRequest` | `ProductController@store` | `name`: required, string, max:255 |
| | | `description`: nullable, string |
| | | `price`: required, numeric, min:0 |
| `UpdateProductRequest` | `ProductController@update` | `name`: sometimes, string, max:255 |
| | | `description`: nullable, string |
| | | `price`: sometimes, numeric, min:0 |

> `UpdateProductRequest` uses `sometimes` rules to support partial updates via `PATCH`.

### 5.5 Policy Authorization

```php
class ProductPolicy
{
    public function update(User $user, Product $product): bool
    {
        return $user->id === $product->user_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->id === $product->user_id;
    }
}
```

Used in controller: `$this->authorize('update', $product)` → auto-returns `403` on failure.

### 5.6 Route Configuration

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    // Public
    Route::get('/health', [HealthController::class, 'index']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:api')->group(function () {
        Route::apiResource('products', ProductController::class);
    });
});
```

> Note: `apiResource` generates `GET`, `POST`, `GET/{id}`, `PUT/{id}`, `PATCH/{id}`, `DELETE/{id}`. The update endpoint will respond to `PATCH`.

### 5.7 Error Handling

Handle key exceptions in Laravel's exception handler to return consistent JSON:

| Scenario | HTTP Code |
|---|---|
| Validation failure | `422` |
| Unauthenticated (no/invalid token) | `401` |
| Unauthorized (not owner) | `403` |
| Resource not found | `404` |
| Server error | `500` |

---

## 6. Blade Visualization Planning

> **Blade is a bonus feature — not the core system.**

### 6.1 Rules

- API is the source of truth — Blade **never** accesses the database directly.
- JWT stored in `localStorage`, sent as `Authorization: Bearer <token>` via `fetch()`.
- **No Laravel session auth** — no `Auth::check()`, no `@auth`, no CSRF tokens.
- Simple vanilla JavaScript, minimal CSS.

### 6.2 Pages

| Page | Web Route | Purpose |
|---|---|---|
| Auth Page | `/` | Login and register forms |
| Dashboard | `/dashboard` | Product list + CRUD actions |

These are web routes that return Blade views. No server-side data fetching.

### 6.3 Interaction Flow

```
User → / (Auth Page)
  ├── Login form → fetch POST /api/v1/auth/login
  │   ├── OK → store JWT in localStorage → redirect to /dashboard
  │   └── Fail → show error
  └── Register form → fetch POST /api/v1/auth/register
      ├── OK → store JWT → redirect to /dashboard
      └── Fail → show validation errors

User → /dashboard
  ├── Load → fetch GET /api/v1/products (with Bearer token)
  ├── Search → fetch GET /api/v1/products?search=...
  ├── Create → fetch POST /api/v1/products
  ├── Edit → fetch PATCH /api/v1/products/{id}
  ├── Delete → fetch DELETE /api/v1/products/{id}
  └── Logout → clear localStorage → redirect to /
```

---

## 7. Folder Structure

```
backend_div/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── AuthController.php
│   │   │           ├── ProductController.php
│   │   │           └── HealthController.php
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   ├── RegisterRequest.php
│   │   │   │   └── LoginRequest.php
│   │   │   └── Product/
│   │   │       ├── StoreProductRequest.php
│   │   │       └── UpdateProductRequest.php
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       └── ProductResource.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Product.php
│   ├── Policies/
│   │   └── ProductPolicy.php
│   └── Traits/
│       └── ApiResponse.php
├── config/
│   ├── auth.php
│   └── jwt.php
├── database/
│   ├── database.sqlite
│   ├── factories/
│   │   ├── UserFactory.php
│   │   └── ProductFactory.php
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   └── xxxx_create_products_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth.blade.php
│       └── dashboard.blade.php
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   ├── RegisterTest.php
│   │   │   └── LoginTest.php
│   │   ├── Product/
│   │   │   ├── ProductCrudTest.php
│   │   │   ├── ProductAuthorizationTest.php
│   │   │   └── ProductQueryTest.php
│   │   └── HealthTest.php
│   └── TestCase.php
├── .env
├── .env.testing
├── phpunit.xml
├── PROJECT_PLAN.md
└── README.md
```

---

## 8. Testing Strategy

> **Testing is the most important part of this technical assessment.** Each test directly validates a testcase requirement.

### 8.1 Environment

| Setting | Value |
|---|---|
| Framework | PHPUnit or Pest |
| Database | SQLite `:memory:` via `RefreshDatabase` |
| JWT | Real token generation (no mocking) |
| Config | `phpunit.xml` with `BCRYPT_ROUNDS=4` for speed |

### 8.2 Test Suite

```
tests/Feature/
├── Auth/
│   ├── RegisterTest.php             (4 tests)
│   └── LoginTest.php                (3 tests)
├── Product/
│   ├── ProductCrudTest.php          (6 tests)
│   ├── ProductAuthorizationTest.php (4 tests)
│   └── ProductQueryTest.php         (5 tests)
└── HealthTest.php                   (1 test)
                                     ──────────
                             Total:  ~23 tests
```

### 8.3 Detailed Test Plan

#### 8.3.1 Register Tests (`RegisterTest.php`)

| # | Test Name | Status | Key Assertions |
|---|---|---|---|
| 1 | `test_user_can_register_with_valid_data` | `201` | Response has `user`, `access_token`; user in DB |
| 2 | `test_registration_fails_without_required_fields` | `422` | Errors for `name`, `email`, `password` |
| 3 | `test_registration_fails_with_duplicate_email` | `422` | `errors.email` present |
| 4 | `test_registration_fails_with_short_password` | `422` | `errors.password` present |

**Example:**
```php
public function test_user_can_register_with_valid_data(): void
{
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success', 'message',
            'data' => ['user' => ['id', 'name', 'email'], 'access_token', 'token_type', 'expires_in'],
        ])
        ->assertJson(['success' => true]);

    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
}

public function test_registration_fails_with_duplicate_email(): void
{
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false])
        ->assertJsonValidationErrors(['email']);
}
```

#### 8.3.2 Login Tests (`LoginTest.php`)

| # | Test Name | Status | Key Assertions |
|---|---|---|---|
| 1 | `test_user_can_login_with_valid_credentials` | `200` | Has `access_token`, `token_type`, `expires_in` |
| 2 | `test_login_fails_with_invalid_credentials` | `401` | `success` is `false` |
| 3 | `test_login_returns_valid_jwt_that_works_on_protected_route` | `200` | Token can access `/api/v1/products` |

**Example:**
```php
public function test_login_returns_valid_jwt_that_works_on_protected_route(): void
{
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $token = $loginResponse->json('data.access_token');

    $this->getJson('/api/v1/products', [
        'Authorization' => "Bearer {$token}",
    ])->assertStatus(200);
}
```

#### 8.3.3 Product CRUD Tests (`ProductCrudTest.php`)

| # | Test Name | Method | Status | Key Assertions |
|---|---|---|---|---|
| 1 | `test_authenticated_user_can_create_product` | POST | `201` | Product in DB with correct `user_id` |
| 2 | `test_product_creation_fails_with_invalid_data` | POST | `422` | Validation errors |
| 3 | `test_unauthenticated_user_cannot_access_products` | GET | `401` | `success` is `false` |
| 4 | `test_authenticated_user_can_list_products` | GET | `200` | Has `data` array and `meta` |
| 5 | `test_authenticated_user_can_view_single_product` | GET | `200` | Matches product data |
| 6 | `test_viewing_nonexistent_product_returns_404` | GET | `404` | `success` is `false` |

**Example:**
```php
public function test_authenticated_user_can_create_product(): void
{
    $user = User::factory()->create();
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/products', [
            'name' => 'Test Product',
            'description' => 'A test product.',
            'price' => 29.99,
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => ['name' => 'Test Product', 'user_id' => $user->id],
        ]);

    $this->assertDatabaseHas('products', ['name' => 'Test Product', 'user_id' => $user->id]);
}
```

#### 8.3.4 Product Authorization Tests (`ProductAuthorizationTest.php`)

| # | Test Name | Method | Status | Key Assertions |
|---|---|---|---|---|
| 1 | `test_owner_can_update_own_product` | PATCH | `200` | Product updated in DB |
| 2 | `test_non_owner_cannot_update_product` | PATCH | `403` | DB unchanged |
| 3 | `test_owner_can_delete_own_product` | DELETE | `200` | Product removed from DB |
| 4 | `test_non_owner_cannot_delete_product` | DELETE | `403` | DB unchanged |

**Example:**
```php
public function test_non_owner_cannot_update_product(): void
{
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::factory()->create(['user_id' => $owner->id]);

    $token = auth('api')->login($otherUser);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/products/{$product->id}", [
            'name' => 'Hacked Name',
        ]);

    $response->assertStatus(403)->assertJson(['success' => false]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => $product->name, // unchanged
    ]);
}
```

#### 8.3.5 Product Query Tests (`ProductQueryTest.php`)

| # | Test Name | Query Param | Status | Key Assertions |
|---|---|---|---|---|
| 1 | `test_can_search_products_by_name` | `?search=keyboard` | `200` | Only matching products |
| 2 | `test_search_returns_empty_when_no_match` | `?search=zzzzz` | `200` | Empty `data`, `total` is 0 |
| 3 | `test_can_sort_products_by_price_asc` | `?sort_by=price&sort_order=asc` | `200` | Correct order |
| 4 | `test_products_are_paginated` | `?per_page=5` | `200` | `meta.per_page` is 5, max 5 items |
| 5 | `test_can_navigate_to_specific_page` | `?page=2` | `200` | `meta.current_page` is 2 |

#### 8.3.6 Health Test (`HealthTest.php`)

| # | Test Name | Status | Key Assertions |
|---|---|---|---|
| 1 | `test_health_endpoint_returns_healthy_status` | `200` | Has `status`, `version`, `timestamp` |

```php
public function test_health_endpoint_returns_healthy_status(): void
{
    $response = $this->getJson('/api/v1/health');

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'data' => ['status' => 'healthy']])
        ->assertJsonStructure(['success', 'message', 'data' => ['status', 'version', 'timestamp']]);
}
```

### 8.4 Running Tests

```bash
php artisan test                    # Run all tests
php artisan test --filter=LoginTest # Run specific file
php artisan test --verbose          # Verbose output
```

### 8.5 PHPUnit Config (`phpunit.xml`)

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="JWT_SECRET" value="testing-jwt-secret-key"/>
</php>
```

---

## 9. Development Phases

### Phase 1 — Setup

| Task | Command / Action |
|---|---|
| Create Laravel project | `composer create-project laravel/laravel backend_div` |
| Install JWT package | `composer require tymon/jwt-auth` |
| Publish JWT config | `php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"` |
| Generate JWT secret | `php artisan jwt:secret` |
| Configure SQLite | Update `.env`: `DB_CONNECTION=sqlite` |
| Configure auth guard | Set `api` guard to `jwt` driver in `config/auth.php` |
| Create `.env.testing` | SQLite `:memory:` |
| Run migrations | `php artisan migrate` |

### Phase 2 — Authentication

| Task |
|---|
| Implement `JWTSubject` interface on User model |
| Create `AuthController` (register, login) |
| Create `RegisterRequest`, `LoginRequest` |
| Create `UserResource` |
| Create `ApiResponse` trait |
| Define auth routes in `routes/api.php` |
| Write `RegisterTest`, `LoginTest` |

### Phase 3 — Product CRUD

| Task |
|---|
| Create Product model + migration |
| Create `ProductController` (index, store, show, update, destroy) |
| Create `StoreProductRequest`, `UpdateProductRequest` |
| Create `ProductResource`, `ProductFactory` |
| Implement pagination, search, sort in `index()` |
| Write `ProductCrudTest`, `ProductQueryTest` |

### Phase 4 — Authorization

| Task |
|---|
| Create `ProductPolicy` (update, delete) |
| Register policy in `AuthServiceProvider` |
| Add `$this->authorize()` calls in controller |
| Write `ProductAuthorizationTest` |

### Phase 5 — System & Polish

| Task |
|---|
| Create `HealthController` |
| Configure exception handler for JSON responses |
| Write `HealthTest` |
| Run full test suite — ensure all pass |

### Phase 6 — Blade Visualization (Bonus)

| Task |
|---|
| Create `layouts/app.blade.php` |
| Create `auth.blade.php` (login/register forms) |
| Create `dashboard.blade.php` (product list + CRUD) |
| Implement `fetch()` API calls with JWT Bearer |
| Add web routes (`/`, `/dashboard`) |

### Phase 7 — Final

| Task |
|---|
| Code cleanup |
| Run `php artisan test --verbose` |
| Write `README.md` |
| Optional: database seeder for demo data |

---

## 10. Technical Considerations

### 10.1 Why JWT?

- **Stateless** — No server-side session storage needed.
- **API-friendly** — Works with any HTTP client (Postman, mobile, fetch).
- **Simple testing** — Just attach `Authorization: Bearer <token>` header.
- **Industry standard** for API authentication.

### 10.2 Why SQLite?

- **Zero setup** — No database server to install.
- **Portable** — Single file, easy for evaluators to clone and run.
- **Fast tests** — In-memory (`:memory:`) database resets instantly.
- **Sufficient** for this project's scope.

### 10.3 Why Blade Is Only Visualization?

- **Assessment focus is backend** — Blade is a bonus, not the core deliverable.
- **API integrity** — Blade consuming the API proves the API works correctly.
- **Decoupled** — The Blade layer could be replaced with any frontend.

### 10.4 Security Basics

| Area | Approach |
|---|---|
| Password hashing | Bcrypt via `Hash::make()` |
| SQL injection | Prevented by Eloquent ORM |
| Mass assignment | `$fillable` on models |
| Input validation | Form Request classes |
| Authorization | Policy-based ownership checks |
| Token expiration | JWT TTL configured (default 60 min) |

---

## 11. README Planning

The `README.md` should enable an evaluator to set up and test the project from scratch.

### Planned Structure

```
# Product Management API

## About
## Tech Stack
## Requirements
## Installation (step-by-step)
## API Endpoints (table + examples)
## Query Parameters (search, sort, pagination)
## Authentication (JWT usage example)
## Running Tests
## Blade Demo (bonus)
## Project Structure
```

### Quality Criteria

- **Self-contained** — Evaluator needs only the README to get started.
- **Copy-pasteable** — All commands ready to run.
- **Clear** — Tables and code blocks for readability.
- **Honest** — State what's implemented and what's bonus.

---

## Quick Reference

| Item | Value |
|---|---|
| Framework | Laravel |
| Auth | `tymon/jwt-auth` |
| Database | SQLite |
| API Prefix | `/api/v1/` |
| Auth Routes | `/api/v1/auth/register`, `/api/v1/auth/login` |
| Product Routes | `/api/v1/products/*` |
| Health Route | `/api/v1/health` |
| Update Method | `PATCH` (partial update) |
| Validation | Form Request classes |
| Authorization | Laravel Policy |
| Response Format | `ApiResponse` trait |
| Testing | PHPUnit + `RefreshDatabase` + SQLite `:memory:` |
| Blade Auth | JWT Bearer via `fetch()` — **NOT** Laravel session |

---

*This document serves as the project planning reference for the backend technical assessment.*
