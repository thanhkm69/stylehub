# Hướng dẫn khởi tạo và chạy dự án StyleHub

Tài liệu này dùng **một quy trình chuẩn, hay dùng nhất** cho dự án:
- `backend`: Laravel 12
- `frontend`: Vue 3 + Vite
- chạy phát triển bằng **3 terminal riêng** (API, queue, frontend)

## 1) Yêu cầu môi trường

- PHP `>= 8.2`
- Composer mới nhất
- Node.js `^20.19.0` hoặc `>=22.12.0`
- npm
- MySQL/MariaDB

## 2) Khởi tạo lần đầu

### Bước 1: Cài backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Mở file `backend/.env` và cấu hình tối thiểu:
- `APP_URL=http://localhost:8000`
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `FRONT_END=http://localhost:5173`

Sau đó chạy migrate:

```bash
php artisan migrate
```

### Bước 2: Cài frontend

```bash
cd ../frontend
npm install
```

## 3) Cách chạy chuẩn khi phát triển (khuyên dùng)

Mở **3 terminal** tại thư mục gốc dự án:

### Terminal 1 - Chạy API Laravel

```bash
cd backend
php artisan serve
```

### Terminal 2 - Chạy queue worker

```bash
cd backend
php artisan queue:work
```

### Terminal 3 - Chạy frontend Vue

```bash
cd frontend
npm run dev
```

Sau khi chạy:
- Backend: `http://localhost:8000`
- Frontend: `http://localhost:5173`

## 4) Lệnh thường dùng

Trong `backend`:

```bash
php artisan test
php artisan route:list
php artisan migrate:status
```

Trong `frontend`:

```bash
npm run build
npm run preview
```

## 5) Lỗi thường gặp

- **Không kết nối được database**: kiểm tra lại `DB_*` trong `backend/.env`.
- **Lỗi CORS/API**: đảm bảo `FRONT_END` trong `backend/.env` đúng `http://localhost:5173`.
- **OTP/mail không gửi**: kiểm tra terminal queue có đang chạy không.
- **Frontend không cập nhật**: chạy lại `npm run dev` hoặc `npm run build`.
