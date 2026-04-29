<script setup>
import DefaultLayout from '@/components/layouts/DefaultLayout.vue';
import SidebarUser from '@/components/partials/SidebarUser.vue';
import { RouterView } from 'vue-router';
</script>

<template>
    <main class="user-layout-page">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4 col-xl-3">
                    <SidebarUser />
                </div>
                <div class="col-lg-8 col-xl-9">
                    <div class="user-content-card">
                        <RouterView />
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<style scoped>
.user-layout-page {
    background: #f8fafc;
    min-height: calc(100vh - 80px); /* Adjust based on header height */
}

.user-content-card {
    background: #ffffff;
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    padding: 32px;
    min-height: 500px;
    box-shadow: var(--shadow-sm);
}

@media (max-width: 991px) {
    .user-content-card {
        padding: 24px;
    }
}
</style>