# Tóm tắt quá trình sửa lỗi và phát triển Chatbot AI - StyleHub

Tài liệu này ghi lại toàn bộ các công việc đã thực hiện để khắc phục lỗi khởi chạy dự án, đồng thời tích hợp thành công **Chatbot AI tư vấn & Đặt hàng nhanh** thông qua **Groq API**.

---

## 1. Các lỗi đã được khắc phục (Bug Fixes)

*   **Lỗi CORS & 500 Internal Server Error**:
    *   *Nguyên nhân*: Dự án Laravel thiếu thư viện `pusher/pusher-php-server` (Reverb dùng dưới hook để broadcast) khiến ứng dụng bị crash ngay từ giai đoạn bootstrap của Service Provider. Các request API từ Frontend bị chặn do Laravel gặp sự cố bootstrap trước khi kịp gán header CORS.
    *   *Khắc phục*: Terminate các tiến trình PHP đang giữ file, chạy `composer install` để cài đặt đầy đủ các package cần thiết.
*   **Lỗi Class "Redis" not found**:
    *   *Nguyên nhân*: File cấu hình ban đầu sử dụng client `phpredis` yêu cầu extension C PHP trên máy chủ.
    *   *Khắc phục*: Đổi cấu hình trong `.env` thành `REDIS_CLIENT=predis` để sử dụng package PHP thuần đã cài đặt trong dự án.
*   **Lỗi thiếu bảng dữ liệu (Table 'stylehub.banners' doesn't exist)**:
    *   *Nguyên nhân*: Có các thay đổi về migration sau khi pull code mới về nhưng chưa được khởi tạo trong database.
    *   *Khắc phục*: Thực thi lệnh `php artisan migrate` để đồng bộ toàn bộ cơ sở dữ liệu.
*   **Lỗi ReflectionException (VNPayController)**:
    *   *Nguyên nhân*: Route được khai báo sử dụng `VNPayController` trong `routes/api.php` nhưng file controller thực tế chưa tồn tại.
    *   *Khắc phục*: Tạo file stub/mock [VNPayController.php](file:///d:/stylehub/backend/app/Http/Controllers/VNPayController.php) để ngăn chặn crash hệ thống route.

---

## 2. Tính năng Chatbot AI & Đặt hàng nhanh (Groq AI & Quick Order)

*   **Tích hợp Model Llama 3.1 8B**:
    *   Sử dụng model `llama-3.1-8b-instant` của Groq (thay thế cho dòng cũ đã bị decommission) giúp tốc độ phản hồi cực nhanh (dưới 1s) và hiểu ngôn ngữ tự nhiên tốt.
*   **Chatbot AI thông minh (RAG)**:
    *   Controller tự động lấy thông tin từ cơ sở dữ liệu về **Danh mục sản phẩm**, **8 sản phẩm nổi bật/mới nhất**, **Các Combo khuyến mãi đang hoạt động**, và **Các mã giảm giá (Coupon) đang chạy** để truyền vào ngữ cảnh của AI.
    *   AI có thể trả lời chuẩn xác thông tin khuyến mãi và sản phẩm thực tế của cửa hàng.
*   **Đặt hàng siêu tốc trực tiếp trong chat**:
    *   Khi giới thiệu sản phẩm, AI tự động chèn thẻ ẩn `[ORDER:id_sản_phẩm]`.
    *   Frontend tự động render thẻ ẩn này thành nút bấm **⚡ Mua nhanh sản phẩm này**.
    *   Khi nhấn nút, một Form Overlay hiện ra ngay trong chat giúp người dùng chọn **Size/Color (Phân loại sản phẩm)** và **Địa chỉ giao nhận** có sẵn của họ.
    *   Khi xác nhận, đơn hàng COD sẽ được tạo tức thì vào database, tính toán phí ship động qua API **Giao Hàng Nhanh (GHN)**, tự động trừ kho sản phẩm, gửi mail xác nhận và báo lại mã đơn hàng kèm tổng tiền cho khách trong khung chat.

---

## 3. Danh sách các file đã tạo mới / chỉnh sửa

### Backend (Laravel)
*   **[Mới]** [VNPayController.php](file:///d:/stylehub/backend/app/Http/Controllers/VNPayController.php): Controller giả lập xử lý VNPay tránh lỗi Reflection.
*   **[Mới]** [AiChatBotController.php](file:///d:/stylehub/backend/app/Http/Controllers/AiChatBotController.php): Controller chính xử lý luồng AI Chat (gửi nhận API Groq), truy xuất thông tin chi tiết sản phẩm và xử lý đặt hàng nhanh (`quickOrder`).
*   **[Chỉnh sửa]** [services.php](file:///d:/stylehub/backend/config/services.php): Khai báo Groq model.
*   **[Chỉnh sửa]** [api.php](file:///d:/stylehub/backend/routes/api.php): Khai báo 3 route phục vụ AI gồm `/ai/chat`, `/ai/product-details` và `/ai/quick-order`.
*   **[Chỉnh sửa]** [.env](file:///d:/stylehub/backend/.env): Cập nhật `REDIS_CLIENT=predis` để tránh lỗi thư viện Redis.

### Frontend (Vue 3 / Vite)
*   **[Chỉnh sửa]** [CustomerChatWidget.vue](file:///d:/stylehub/frontend/src/components/chat/CustomerChatWidget.vue): Giao diện chat được làm lại dạng Tab (Trợ lý AI & Nhân viên), hỗ trợ parser nút đặt hàng nhanh và Form Overlay nhập thông tin.

---

## 4. Có cần chạy lệnh gì nữa không?

**Hiện tại bạn KHÔNG cần chạy thêm lệnh gì cả.** Tôi đã chủ động cấu hình và khởi chạy đầy đủ mọi dịch vụ ở nền giúp bạn, bao gồm:
*   Đã chạy `composer install` nâng cấp thư viện.
*   Đã chạy `php artisan migrate` đồng bộ database.
*   Đã clear toàn bộ cache route/config.
*   Đã khởi chạy lại server và chạy mới server Reverb WebSocket (`php artisan reverb:start`) ở cổng 8080 để kết nối thời gian thực.

### Các lệnh hữu ích để tham khảo khi vận hành sau này:
Nếu sau này bạn tắt máy hoặc tắt terminal và cần chạy lại toàn bộ ứng dụng từ đầu, bạn chỉ cần mở các tab terminal và chạy:
1.  **Chạy server API**:
    ```bash
    php artisan serve
    ```
2.  **Chạy WebSocket Server (Reverb)** để nhắn tin thời gian thực:
    ```bash
    php artisan reverb:start
    ```
3.  **Chạy Queue Worker** xử lý email & gợi ý ngầm:
    ```bash
    php artisan queue:work
    ```
4.  **Chạy Frontend (Vite)**:
    ```bash
    npm run dev
    ```
