# Warehouse Management API

A **Laravel 12** API for managing products and orders in a warehouse system. This project provides endpoints to:

- Retrieve products with **cursor-based pagination**, optimized for large datasets  
- Filter products **in stock** (quantity > 0)  
- **Increment quantities** of existing products
- **Create orders** with multiple products while deducting quantities atomically  
- Handle errors with **custom exceptions** and clear API responses  

The project follows **best practices**:

- **Service layer** for business logic  
- **Repository pattern** for database access  
- **Form Requests** for input validation with custom error messages  
- **Transaction-safe operations** to maintain data consistency  
- **Unit and feature tests** using Mockery for reliable testing  

---

## Requirements

- WSL (if running in Windows)
- Docker

---

## Installation
```bash
# Clone repository
git clone https://github.com/yourusername/warehouse-api.git
cd warehouse-api

# Start docker
./vendor/bin/sail up -d

# Access API
http://localhost
```

## Enviroment Setup
```bash
# Copy example environment file
cp .env.example .env

# Run migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

---

## API Endoints

### Products

#### 1. Get All Products

GET /api/products/all

**Query Parameters:**

- `per_page` (optional) – number of items per page (1–100)
- `cursor` (optional) - cursor for pagination

**Example Request:**

GET /api/products/all?per_page=2&cursor=eyJpZCI6MiwiX3BvaW50c1RvTmV4dEl0ZW1zIjp0cnVlfQ

**Example Response:**

```json
{
    "data": [
        {
            "id": 3,
            "name": "vel quasi",
            "description": "Eius iste at tempore quia recusandae eligendi aut.",
            "price": "453.26",
            "quantity": 14,
            "created_at": "2026-01-15T19:46:43.000000Z",
            "updated_at": "2026-01-15T22:29:47.000000Z"
        },
        {
            "id": 4,
            "name": "enim hic",
            "description": "Nihil accusamus accusantium corrupti non perferendis iure nostrum accusamus exercitationem.",
            "price": "135.52",
            "quantity": 1,
            "created_at": "2026-01-15T19:46:43.000000Z",
            "updated_at": "2026-01-15T19:48:35.000000Z"
        }
    ],
    "meta": {
        "per_page": 2,
        "next_cursor": "eyJpZCI6NCwiX3BvaW50c1RvTmV4dEl0ZW1zIjp0cnVlfQ",
        "prev_cursor": "eyJpZCI6MywiX3BvaW50c1RvTmV4dEl0ZW1zIjpmYWxzZX0"
    }
}
```

#### 2. Get Products In Stock

GET /api/products/in-stock

- Returns only products with `quantity > 0`

**Query Parameters:**

- `per_page` (optional) – number of items per page (1–100)
- `cursor` (optional) - cursor for pagination

**Example Request:**

GET /api/products/in-stock?per_page=2

**Example Response:**

```json
{
    "data": [
        {
            "id": 2,
            "name": "aspernatur quisquam",
            "description": "Enim voluptas cupiditate incidunt eum nam ipsum a.",
            "price": "88.52",
            "quantity": 13,
            "created_at": "2026-01-15T19:46:43.000000Z",
            "updated_at": "2026-01-15T21:08:40.000000Z"
        },
        {
            "id": 3,
            "name": "vel quasi",
            "description": "Eius iste at tempore quia recusandae eligendi aut.",
            "price": "453.26",
            "quantity": 8,
            "created_at": "2026-01-15T19:46:43.000000Z",
            "updated_at": "2026-01-15T21:08:40.000000Z"
        }
    ],
    "meta": {
        "per_page": 2,
        "next_cursor": "eyJpZCI6MiwiX3BvaW50c1RvTmV4dEl0ZW1zIjp0cnVlfQ",
        "prev_cursor": null
    }
}
```

#### 3. Update product quantity

POST /api/products/add-quantity

**Query Parameters:**

- `products` - array of products to add
    - `id` (required) – existing product ID to increment quantity
    - `quantity` (required) – number to add

**Example Request:**

POST /api/products/add-quantity

```json
{
    "products": [
        {
            "id": 1,
            "quantity": 10
        },
        {
            "id": 2,
            "quantity": 1
        }
    ]
}
```

**Example Response:**

```json
{
    "message": "Products updated successfully",
    "data": [
        {
            "id": 1,
            "name": "aspernatur quisquam",
            "description": "Enim voluptas cupiditate incidunt eum nam ipsum a.",
            "price": "88.52",
            "quantity": 23,
            "created_at": "2026-01-15T19:46:43.000000Z",
            "updated_at": "2026-01-15T22:19:21.000000Z"
        },
        {
            "id": 2,
            "name": "dicta dolore",
            "description": "Veritatis unde voluptatem voluptatum eos molestias officia corrupti.",
            "price": "196.24",
            "quantity": 14,
            "created_at": "2026-01-15T19:46:43.000000Z",
            "updated_at": "2026-01-15T22:19:21.000000Z"
        }
    ]
}
```

### Orders

#### 1. Create Order

POST /api/create-order

**Query Parameters:**

- `products` - array of products to add
    - `id` (required) – existing product ID to increment quantity
    - `quantity` (required) – number to add

**Example Request:**

POST /api/create-order

```json
{
    "products": [
        {
            "id": 1,
            "quantity": 2
        },
        {
            "id": 2,
            "quantity": 1
        }
    ]
}
```

**Example Response:**

```json
{
    "message": "Order created successfully",
    "order": {
        "total_price": "481.00",
        "updated_at": "2026-01-15T22:27:05.000000Z",
        "created_at": "2026-01-15T22:27:05.000000Z",
        "id": 8,
        "order_products": [
            {
                "id": 15,
                "price": "88.52",
                "quantity": 1,
                "order_id": 8,
                "product_id": 1,
                "created_at": "2026-01-15T22:27:05.000000Z",
                "updated_at": "2026-01-15T22:27:05.000000Z"
            },
            {
                "id": 16,
                "price": "196.24",
                "quantity": 2,
                "order_id": 8,
                "product_id": 2,
                "created_at": "2026-01-15T22:27:05.000000Z",
                "updated_at": "2026-01-15T22:27:05.000000Z"
            }
        ]
    }
}
```
---

## Testing

- Includes tests for services

```bash
# Run tests
./vendor/bin/sail artisan test
```

