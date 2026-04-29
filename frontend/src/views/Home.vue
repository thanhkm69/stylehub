<!-- ... existing code ... -->
<template>
    <main>
        <!-- Hero Section -->
        <section class="hero container">
            <div class="hero-inner">
                <div class="hero-content">
                    <h1>Định Hình<br>Phong Cách Của Bạn</h1>
                    <p>Khám phá bộ sưu tập mùa hè mới nhất với các thiết kế độc quyền, chất liệu cao cấp và form dáng chuẩn mực dành riêng cho bạn.</p>
                    <router-link to="/shop" class="btn btn-primary">
                        Mua sắm ngay <i class="ph ph-arrow-right" style="margin-left: 8px;"></i>
                    </router-link>
                </div>
                <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=2070&auto=format&fit=crop" alt="Thời trang StyleHub" class="hero-image">
            </div>
        </section>

        <!-- Featured Products -->
        <section class="products-section container">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Hàng mới về</h2>
                    <p style="color: var(--text-muted); margin-top: 8px;">Những mẫu thiết kế vừa cập bến StyleHub.</p>
                </div>
                <router-link to="/shop" class="view-all">Xem tất cả <i class="ph ph-caret-right"></i></router-link>
            </div>

            <div class="product-grid">
                
                <!-- Lưu ý: Dùng v-for ở thẻ này để render danh sách lấy từ API -->
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <span class="badge new">Mới</span>
                        <!-- Bind source ảnh: :src="product.image_url" -->
                        <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=1000&auto=format&fit=crop" alt="Áo thun Basic" class="product-image">
                        <!-- Thêm sự kiện @click="addToCart(product)" -->
                        <button class="add-to-cart-btn" title="Thêm vào giỏ">
                            <i class="ph ph-shopping-cart-simple"></i>
                        </button>
                    </div>
                    <div class="product-info">
                        <!-- Bind dữ liệu: {{ product.category_name }} -->
                        <div class="product-category">Áo Nữ</div>
                        <h3 class="product-name">Áo Thun Basic Cotton 100% Cổ Tròn Mùa Hè</h3>
                        <div class="product-price">250.000₫</div>
                    </div>
                </div>

                <!-- Product Mẫu 2 -->
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <span class="badge sale">-20%</span>
                        <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=1000&auto=format&fit=crop" alt="Áo thun Basic" class="product-image">
                        <button class="add-to-cart-btn" title="Thêm vào giỏ">
                            <i class="ph ph-shopping-cart-simple"></i>
                        </button>
                    </div>
                    <div class="product-info">
                        <div class="product-category">Áo Nam</div>
                        <h3 class="product-name">Áo Thun Nam Form Rộng Màu Đất</h3>
                        <div class="product-price">199.000₫ <span class="price-old">250.000₫</span></div>
                    </div>
                </div>

                <!-- Product Mẫu 3 -->
                <div class="product-card">
                    <div class="product-image-wrapper">
                        <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=1000&auto=format&fit=crop" alt="Áo thun Basic" class="product-image">
                        <button class="add-to-cart-btn" title="Thêm vào giỏ">
                            <i class="ph ph-shopping-cart-simple"></i>
                        </button>
                    </div>
                    <div class="product-info">
                        <div class="product-category">Áo Khoác</div>
                        <h3 class="product-name">Áo Khoác Da Bomber Cao Cấp</h3>
                        <div class="product-price">850.000₫</div>
                    </div>
                </div>

            </div>
        </section>
    </main>
</template>
<!-- ... existing code ... -->
<style scoped>
.hero {
    padding: 60px 0;
}

.hero-inner {
    background-color: var(--accent);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    overflow: hidden;
    padding: 60px;
    min-height: 500px;
    position: relative;
}

.hero-content {
    max-width: 500px;
    z-index: 2;
}

.hero-content h1 {
    font-size: 56px;
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 24px;
    letter-spacing: -1.5px;
}

.hero-content p {
    font-size: 18px;
    color: var(--text-muted);
    margin-bottom: 32px;
}

.hero-image {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 50%;
    object-fit: cover;
    border-radius: 0 var(--radius-lg) var(--radius-lg) 0;
}

.products-section {
    padding: 60px 0;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 32px;
}

.product-card {
    background: var(--surface);
    border-radius: var(--radius-md);
    padding: 16px;
    transition: var(--transition);
    border: 1px solid transparent;
}

.product-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-5px);
    border-color: var(--border);
}

.product-image-wrapper {
    position: relative;
    border-radius: var(--radius-md);
    overflow: hidden;
    aspect-ratio: 4/5;
    background: var(--accent);
    margin-bottom: 16px;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: var(--surface);
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-sm);
}

.badge.new { color: #10b981; }
.badge.sale { color: #ef4444; }

.add-to-cart-btn {
    position: absolute;
    bottom: 12px;
    right: 12px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--shadow-md);
    opacity: 0;
    transform: translateY(10px);
    transition: var(--transition);
    color: var(--primary);
}

.product-card:hover .add-to-cart-btn {
    opacity: 1;
    transform: translateY(0);
}

.add-to-cart-btn:hover {
    background: var(--primary);
    color: var(--surface);
}

.product-category {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 4px;
}

.product-name {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price {
    font-weight: 700;
    font-size: 18px;
}

.price-old {
    color: var(--text-muted);
    text-decoration: line-through;
    font-size: 14px;
    font-weight: 400;
    margin-left: 8px;
}

@media (max-width: 992px) {
    .hero-inner {
        flex-direction: column;
        padding: 40px;
    }
    .hero-image {
        position: relative;
        width: 100%;
        height: 300px;
        border-radius: var(--radius-md);
        margin-top: 32px;
    }
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 40px;
    }
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
    }
}
</style>
<!-- ... existing code ... -->