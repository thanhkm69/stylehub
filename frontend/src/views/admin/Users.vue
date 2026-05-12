<script setup>
import UserList from '@/components/features/users/UserList.vue';
</script>

<template>
    <div class="admin-page-header">
        <h2 class="admin-page-title">Quản lý Người dùng</h2>
        <p class="admin-page-desc">Quản lý tài khoản và phân quyền người dùng hệ thống.</p>
    </div>
    <UserList />
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
