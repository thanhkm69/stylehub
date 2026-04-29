<template>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <router-link to="/" class="nav-logo" style="margin-bottom: 20px;">
                        <i class="ph-fill ph-storefront"></i> StyleHub
                    </router-link>
                    <p>Khám phá phong cách thời trang dẫn đầu xu hướng cùng StyleHub. Chất lượng tạo nên thương hiệu.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="ph-fill ph-facebook-logo"></i></a>
                        <a href="#"><i class="ph-fill ph-instagram-logo"></i></a>
                        <a href="#"><i class="ph-fill ph-twitter-logo"></i></a>
                        <a href="#"><i class="ph-fill ph-tiktok-logo"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Cửa Hàng</h3>
                    <div class="footer-links">
                        <router-link to="/shop">Sản phẩm nam</router-link>
                        <router-link to="/shop">Sản phẩm nữ</router-link>
                        <a href="#">Giảm giá</a>
                        <a href="#">Bộ sưu tập mới</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Hỗ Trợ</h3>
                    <div class="footer-links">
                        <a href="#">Chính sách đổi trả</a>
                        <a href="#">Câu hỏi thường gặp</a>
                        <a href="#">Kiểm tra đơn hàng</a>
                        <a href="#">Liên hệ</a>
                    </div>
                </div>

                <div class="footer-col">
                    <h3>Liên Hệ</h3>
                    <div class="footer-links">
                        <a href="#"><i class="ph ph-map-pin"></i> 123 Đường Fashion, TP.HCM</a>
                        <a href="#"><i class="ph ph-phone"></i> 0123 456 789</a>
                        <a href="#"><i class="ph ph-envelope"></i> hotro@stylehub.com</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 StyleHub. Mọi quyền được bảo lưu.</p>
                <div style="display: flex; gap: 16px;">
                    <a href="#">Điều khoản bảo mật</a>
                    <a href="#">Điều khoản sử dụng</a>
                </div>
            </div>
        </div>
    </footer>
</template>
<style scoped>
.footer {
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 80px 0 40px;
    margin-top: 60px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 40px;
    margin-bottom: 60px;
}

.footer-col h3 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
}

.footer-col p {
    color: var(--text-muted);
    margin-bottom: 24px;
    max-width: 300px;
}

.footer-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-links a {
    color: var(--text-muted);
    transition: var(--transition);
}

.footer-links a:hover {
    color: var(--primary);
    padding-left: 4px;
}

.social-links {
    display: flex;
    gap: 16px;
}

.social-links a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--accent);
    color: var(--text-main);
    transition: var(--transition);
}

.social-links a:hover {
    background: var(--primary);
    color: var(--surface);
}

.footer-bottom {
    padding-top: 32px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: var(--text-muted);
    font-size: 14px;
}

@media (max-width: 992px) {
    .footer-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr;
    }

    .footer-bottom {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
}
</style>
