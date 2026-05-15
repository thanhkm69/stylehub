<script setup>
import PostList from '@/components/features/posts/PostList.vue';
</script>

<template>
    <div class="admin-page-header">
        <h2 class="admin-page-title">Quản lý Bài viết Blog</h2>
        <p class="admin-page-desc">Tạo và quản lý các bài viết tin tức, tối ưu SEO.</p>
    </div>
    <PostList />
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
