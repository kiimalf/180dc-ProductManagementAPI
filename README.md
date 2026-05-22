# Laravel JWT Product Management API

A robust REST API for product management with JWT authentication, built as a technical assessment backend project.

## Features

- **JWT Authentication**: Secure stateless authentication using `tymon/jwt-auth`.
- **Product Management**: Full CRUD operations for products.
- **Authorization**: Users can only update or delete their own products (Laravel Policies).
- **Advanced Queries**: Pagination, search by name, and sorting by price/name/date.
- **Standardized Responses**: Consistent JSON response format for success and errors.
- **Automated Testing**: Comprehensive Feature testing using PHPUnit.
- **Lightweight UI**: A simple Blade + Vanilla JS interface to demo the API (`/` and `/dashboard`).

## Tech Stack

- **Framework**: Laravel 11
- **Database**: SQLite
- **Authentication**: `tymon/jwt-auth`
- **Testing**: PHPUnit

## Requirements

- PHP 8.2+
- Composer
- Node.js (Optional, not required since we don't use frontend build tools here)

## Installation

1. **Clone the repository** (if not already done).
2. **Install dependencies**:
   ```bash
   composer install
   ```
3. **Copy `.env`**:
   ```bash
   cp .env.example .env
   ```
4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```
5. **Generate JWT Secret**:
   ```bash
   php artisan jwt:secret
   ```
6. **Run Migrations**:
   ```bash
   php artisan migrate
   ```
   *(Say yes if it asks to create the SQLite database)*
7. **Run the local server**:
   ```bash
   php artisan serve
   ```

## Running Tests

To run the automated test suite (uses an in-memory SQLite database):

```bash
php artisan test
```

## Seeder / Demo Data

Untuk mempermudah testing dan demo, Anda dapat menjalankan seeder untuk mengisi database dengan sample data.

Contoh command:
```bash
php artisan migrate:fresh --seed
```

Seeder akan membuat:
- 2 User (`owner@example.com` dan `user@example.com`) dengan password `password123`.
- Beberapa sample produk dengan data e-commerce yang realistis terhubung ke user tersebut.

## API Documentation

All API endpoints start with `/api/v1`.

### Authentication

- `POST /api/v1/auth/register` - Register a new user.
- `POST /api/v1/auth/login` - Login and get a JWT token.

### Products

**Requires `Authorization: Bearer <token>` header.**

- `GET /api/v1/products` - List all products (with pagination).
  - Query parameters: `search`, `sort` (`price`, `name`, `created_at`), `direction` (`asc`, `desc`), `page`.
- `POST /api/v1/products` - Create a new product.
- `GET /api/v1/products/{id}` - Get a specific product.
- `PATCH /api/v1/products/{id}` - Update a product (only owner).
- `DELETE /api/v1/products/{id}` - Delete a product (only owner).

### System

- `GET /api/v1/health` - Check API health status.

## Demo UI

You can view a basic demo of the API in action by navigating to `http://localhost:8000/` in your browser. This will render a Blade view that uses vanilla JavaScript `fetch()` calls to interact with the API endpoints.
