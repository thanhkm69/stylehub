<script setup>
import PostCommentList from '@/components/features/postComments/PostCommentList.vue'
</script>

<template>
  <div class="admin-page-header">
    <h2 class="admin-page-title">Quản lý Bình luận bài viết</h2>
    <p class="admin-page-desc">Theo dõi, ẩn hiện và xử lý bình luận trong chuyên mục tin tức.</p>
  </div>
  <PostCommentList />
</template>

<style scoped>
.admin-page-header {
  margin-bottom: 24px;
}

.admin-page-title {
  color: var(--text-main);
  font-size: 24px;
  font-weight: 700;
  letter-spacing: -0.5px;
  margin-bottom: 8px;
}

.admin-page-desc {
  color: var(--text-muted);
  font-size: 15px;
}
</style>
