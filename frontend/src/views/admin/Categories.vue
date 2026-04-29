<script setup>
import CategoryList from '@/components/features/categories/CategoryList.vue';
</script>

<template>
    <div class="admin-page-header">
        <h2 class="admin-page-title">Quản lý Danh mục</h2>
        <p class="admin-page-desc">Tạo và cấu hình các danh mục sản phẩm của cửa hàng.</p>
    </div>
    <CategoryList />
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