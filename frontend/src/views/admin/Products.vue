<script setup>
import ProductList from '@/components/features/products/ProductList.vue';
</script>

<template>
    <div class="admin-page-header">
        <h2 class="admin-page-title">Quản lý Sản phẩm</h2>
        <p class="admin-page-desc">Tạo và cấu hình các sản phẩm của hệ thống.</p>
    </div>
    <ProductList />
</template>

<style scoped>
.admin-page-header {
    margin-bottom: 24px;
}

.admin-page-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.admin-page-desc {
    color: var(--text-muted);
    font-size: 15px;
}
</style>
