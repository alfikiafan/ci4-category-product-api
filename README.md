# REST API Demo - Category & Product

REST API CRUD sederhana untuk mengelola **Kategori** dan **Produk**, dibangun menggunakan **PHP (CodeIgniter 4)** dan **MySQL**, sebagai bagian dari technical test Developer.

## Tech Stack

- PHP 8.2
- CodeIgniter 4.7
- MySQL / MariaDB
- Composer

## Struktur Project

```
app/
├── Config/
│   └── Routes.php                  # Routing endpoint API
├── Controllers/
│   └── Api/
│       ├── BaseApiController.php   # Helper response JSON standar
│       ├── CategoryController.php  # CRUD Category
│       └── ProductController.php   # CRUD Product
├── Database/
│   ├── Migrations/                 # Skema tabel categories & products
│   └── Seeds/                      # Data dummy
└── Models/
    ├── CategoryModel.php
    └── ProductModel.php
```

## ERD (Entity Relationship Diagram)

```
┌──────────────────┐         ┌─────────────────────────┐
│    categories    │         │         products        │
├──────────────────┤         ├─────────────────────────┤
│ id (PK)          │1───────*│ category_id (FK)        │
│ name (unique)    │         │ id (PK)                 │
│ description      │         │ name                    │
│ created_at       │         │ sku (unique)            │
│ updated_at       │         │ price                   │
└──────────────────┘         │ stock                   │
                             │ description             │
                             │ created_at              │
                             │ updated_at              │
                             └─────────────────────────┘
```

Relasi: satu **Category** dapat memiliki banyak **Product** (one-to-many). Sebuah produk wajib memiliki `category_id` yang valid (FK constraint).

## Setup & Instalasi

### 1. Clone repository

```bash
git clone <repo-url>
cd rest-api-demo
```

### 2. Install dependency

```bash
composer install
```

### 3. Konfigurasi environment

Copy file `env` menjadi `.env`, lalu sesuaikan konfigurasi database:

```bash
cp env .env
```

Edit bagian berikut di `.env`:

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = rest_api_demo
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
```

Generate encryption key:

```bash
php spark key:generate
```

### 4. Buat database

```sql
CREATE DATABASE rest_api_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### 5. Jalankan migration & seeder

```bash
php spark migrate --all
php spark db:seed DatabaseSeeder
```

### 6. Jalankan server

```bash
php spark serve --port 8080
```

API akan tersedia di `http://localhost:8080/api`.

## Format Response

Semua response menggunakan format JSON konsisten:

**Success**

```json
{
    "status": "success",
    "message": "Categories retrieved successfully",
    "data": { ... }
}
```

**Error**

```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "name": "The name field is required."
    }
}
```

## API Endpoints

### Category

| Method | Endpoint              | Deskripsi                          |
|--------|-----------------------|-------------------------------------|
| GET    | `/api/categories`     | List kategori (pagination, search) |
| GET    | `/api/categories/{id}`| Detail kategori                    |
| POST   | `/api/categories`     | Tambah kategori                    |
| PUT    | `/api/categories/{id}`| Update kategori                    |
| DELETE | `/api/categories/{id}`| Hapus kategori                     |

**Query params untuk list:** `?search=keyword&per_page=10&page=1`

**Body (POST/PUT):**

```json
{
    "name": "Electronics",
    "description": "Electronic devices and accessories"
}
```

### Product

| Method | Endpoint            | Deskripsi                                    |
|--------|---------------------|------------------------------------------------|
| GET    | `/api/products`     | List produk (pagination, search, filter category) |
| GET    | `/api/products/{id}`| Detail produk                                  |
| POST   | `/api/products`     | Tambah produk                                  |
| PUT    | `/api/products/{id}`| Update produk                                  |
| DELETE | `/api/products/{id}`| Hapus produk                                   |

**Query params untuk list:** `?search=keyword&category_id=1&per_page=10&page=1`

**Body (POST/PUT):**

```json
{
    "category_id": 1,
    "name": "Wireless Mouse",
    "sku": "ELEC-001",
    "price": 125000,
    "stock": 50,
    "description": "2.4GHz wireless optical mouse"
}
```

## Validasi & Error Handling

- Field wajib, panjang minimum/maksimum, tipe data, dan keunikan (`name`, `sku`) divalidasi otomatis - error dikembalikan dengan status `422`.
- Request ke resource yang tidak ditemukan mengembalikan status `404`.
- Penghapusan kategori yang masih memiliki produk terkait akan ditolak dengan status `409` (menjaga integritas relasi).
- Endpoint yang tidak dikenal mengembalikan JSON `404` (tidak ada halaman HTML error).

## Catatan Desain & Pengembangan Lanjutan

Beberapa hal berikut sengaja tidak diimplementasikan pada demo ini, namun menjadi pertimbangan desain untuk pengembangan lanjutan:

- **API Versioning** - Endpoint saat ini belum menggunakan prefix versi (misal `/api/v1/categories`). Untuk production, sebaiknya seluruh route dikelompokkan di bawah versi (`/api/v1/...`) agar perubahan breaking di masa depan tidak mengganggu konsumen API yang masih memakai versi lama.
- **Autentikasi & Otorisasi** - API ini belum dilindungi autentikasi (API key/Bearer token) karena di luar scope technical test. Pada implementasi production, endpoint write (`POST`, `PUT`, `DELETE`) sebaiknya dilindungi dengan token-based authentication (misal JWT atau API key) dan otorisasi berbasis role.

## Dokumentasi API (Postman)

Import file [`docs/postman_collection.json`](docs/postman_collection.json) ke Postman. Collection sudah berisi seluruh endpoint di atas beserta contoh request body.

## Automated Testing

Unit & feature test (PHPUnit) tersedia untuk seluruh endpoint Category & Product, mencakup skenario sukses, validasi, not found, dan konflik FK. Test berjalan di database SQLite in-memory terpisah sehingga tidak memengaruhi database `rest_api_demo`.

```bash
composer test
# atau
vendor/bin/phpunit
```

## Testing Manual (contoh curl)

```bash
# List categories
curl http://localhost:8080/api/categories

# Create category
curl -X POST http://localhost:8080/api/categories \
  -H "Content-Type: application/json" \
  -d '{"name":"Electronics","description":"Electronic devices"}'

# Get product detail
curl http://localhost:8080/api/products/1

# Update product
curl -X PUT http://localhost:8080/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{"price":135000,"stock":40}'

# Delete product
curl -X DELETE http://localhost:8080/api/products/1
```
