# Laravel JWT Product Management API

REST API yang kokoh dan aman untuk pengelolaan produk dengan autentikasi JWT (JSON Web Token), dibangun menggunakan Laravel 11 sebagai bagian dari technical assessment Divisi Backend Developer 180DC UNAIR.

---

## 🚀 Fitur Utama

- **Autentikasi JWT**: Sistem keamanan *stateless* menggunakan package `tymon/jwt-auth`. Pengguna mendapatkan token akses setelah registrasi atau login.
- **Pengelolaan Produk (CRUD)**: Hak akses penuh untuk membuat, membaca, memperbarui (secara parsial dengan `PATCH`), dan menghapus produk.
- **Kebijakan Otorisasi (Laravel Policies)**: Fitur keamanan ketat di mana pengguna hanya diizinkan untuk memperbarui atau menghapus produk yang mereka miliki sendiri (`ProductPolicy`).
- **Advanced Queries**: Mendukung pencarian dinamis berdasarkan nama produk, pengurutan fleksibel (berdasarkan harga, nama, atau tanggal), dan paginasi otomatis.
- **Standardisasi API Response**: Seluruh response (sukses maupun error seperti validasi 422, unauthenticated 401, dan unauthorized 403) menggunakan amplop JSON yang konsisten lewat `ApiResponse` Trait.
- **Automated Testing**: Dilengkapi dengan **23 skenario Feature Testing** menggunakan PHPUnit untuk menjamin keandalan sistem tanpa regresi.
- **Visualisasi Blade (Bonus)**: Demo UI ringan berbasis Laravel Blade + Vanilla JS `fetch()` untuk mendemonstrasikan konsumsi API secara *stateless* tanpa session.

---

## 🛠️ Stack Teknologi

- **Framework**: Laravel 11
- **Database**: SQLite (ringan, portabel, tanpa konfigurasi server tambahan)
- **Autentikasi**: `tymon/jwt-auth`
- **Unit & Feature Testing**: PHPUnit (dengan In-Memory SQLite `:memory:` untuk eksekusi super cepat)

---

## 📋 Kebutuhan Sistem

- PHP 8.2 atau versi lebih tinggi
- Composer (Dependency Manager untuk PHP)
- Node.js (Opsional, tidak wajib karena tidak menggunakan compiler frontend berat)

---

## ⚙️ Panduan Instalasi (Langkah Demi Langkah)

Ikuti langkah-langkah di bawah ini untuk menjalankan project di lokal komputer Anda dari awal:

1. **Clone repositori** ini ke komputer lokal Anda.
2. **Masuk ke direktori project** lalu instal seluruh dependensi PHP menggunakan Composer:
   ```bash
   composer install
   ```
3. **Salin file konfigurasi lingkungan**:
   ```bash
   cp .env.example .env
   ```
4. **Buat Application Key** bawaan Laravel:
   ```bash
   php artisan key:generate
   ```
5. **Buat JWT Secret Key** untuk menandatangani token keamanan JWT:
   ```bash
   php artisan jwt:secret
   ```
6. **Jalankan Migrasi Database** (pilih 'Yes' jika Laravel meminta pembuatan database SQLite secara otomatis):
   ```bash
   php artisan migrate
   ```
7. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Aplikasi Anda kini berjalan dan dapat diakses di: **`http://localhost:8000`**

---

## 🧪 Cara Menjalankan Automated Testing

Untuk menguji seluruh fungsionalitas backend secara otomatis menggunakan database in-memory (SQLite `:memory:`):

```bash
php artisan test
```

Command ini akan mengeksekusi 23 skenario pengujian yang mencakup proses register, login, CRUD, otorisasi kepemilikan produk, pencarian (*searching*), pengurutan (*sorting*), paginasi, dan cek kesehatan sistem (*health check*).

---

## 🌱 Data Demo / Seeder

Untuk mempermudah demonstrasi dan proses evaluasi, jalankan seeder untuk mengisi database secara otomatis dengan data sampel yang realistis:

```bash
php artisan migrate:fresh --seed
```

Seeder ini akan membuat:
1. **Dua Pengguna Demo**:
   - Akun Pembuat Produk (Owner): **`owner@example.com`** (Password: `password123`)
   - Akun Pengguna Lain: **`user@example.com`** (Password: `password123`)
2. **Beberapa Sampel Produk**: Data produk e-commerce yang realistis yang terhubung ke masing-masing pengguna di atas.

---

## 📖 Dokumentasi Endpoint API

Seluruh endpoint API memiliki prefix `/api/v1`.

### 🔐 1. Autentikasi (Public)

| Metode HTTP | Endpoint | Deskripsi | Parameter Request |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | Mendaftarkan akun pengguna baru | `name`, `email`, `password`, `password_confirmation` |
| `POST` | `/api/v1/auth/login` | Login dan mendapatkan Token JWT | `email`, `password` |

### 📦 2. Manajemen Produk (Memerlukan Header `Authorization: Bearer <token>`)

| Metode HTTP | Endpoint | Deskripsi | Query Parameters (Opsional) |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/products` | Menampilkan seluruh produk (dengan paginasi) | `search` (nama), `sort` (`price`/`name`/`created_at`), `direction` (`asc`/`desc`), `page` |
| `POST` | `/api/v1/products` | Membuat produk baru | `name`, `description`, `price` |
| `GET` | `/api/v1/products/{id}` | Menampilkan detail produk tertentu | - |
| `PATCH` | `/api/v1/products/{id}` | Memperbarui produk (Hanya Owner) | `name` (opsional), `description` (opsional), `price` (opsional) |
| `DELETE` | `/api/v1/products/{id}` | Menghapus produk (Hanya Owner) | - |

### 🏥 3. Sistem Kesehatan (Public)

| Metode HTTP | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| `GET` | `/api/v1/health` | Cek status kesehatan sistem API |

---

## 🖥️ Demo Visualisasi UI (Bonus)

Sebagai bukti bahwa API berjalan murni dan terstandarisasi dengan multi-client, Anda dapat melihat demo visualisasinya di browser dengan menavigasi ke: **`http://localhost:8000/`**

Halaman ini merupakan halaman Blade ringan yang berinteraksi langsung dengan API menggunakan request `fetch()` asinkronus dan menyimpan token di `localStorage` (sama sekali tidak menggunakan Laravel Session, Cookie, atau CSRF server-side bawaan Web Laravel biasa).
