<script setup>
import { ref } from 'vue';
import SidebarAdmin from '@/components/partials/SidebarAdmin.vue';
import { RouterView } from 'vue-router';

const isSidebarOpen = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};
</script>

<template>
    <div class="admin-layout" :class="{ 'sidebar-open': isSidebarOpen }">
        <div class="sidebar-overlay" @click="isSidebarOpen = false"></div>
        <SidebarAdmin />
        <main class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="menu-toggle d-lg-none" @click="toggleSidebar">
                        <i class="ph-bold" :class="isSidebarOpen ? 'ph-x' : 'ph-list'"></i>
                    </button>
                    <h1 class="page-title d-none d-sm-block">Hệ thống quản trị</h1>
                </div>
                <div class="topbar-right">
                    <div class="topbar-actions hide-on-mobile">
                        <button class="icon-btn">
                            <i class="ph-bold ph-bell"></i>
                            <span class="badge"></span>
                        </button>
                    </div>
                    <div class="user-profile">
                        <div class="user-info d-none d-md-block text-end">
                            <p class="user-name">Admin</p>
                            <p class="user-role">Quản trị viên</p>
                        </div>
                        <div class="user-avatar">
                            <i class="ph-bold ph-user"></i>
                        </div>
                    </div>
                </div>
            </header>

            <div class="admin-content">
                <RouterView />
            </div>
        </main>
    </div>
</template>

<style scoped>
.admin-layout {
    display: flex;
    min-height: 100vh;
    background: #f8fafc;
}

.sidebar-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    z-index: 999;
}

.admin-main {
    flex: 1;
    margin-left: 280px;
    display: flex;
    flex-direction: column;
    min-width: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.admin-topbar {
    height: 80px;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
    padding: 0 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 998;
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.menu-toggle {
    width: 40px;
    height: 40px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--text-main);
    cursor: pointer;
    transition: var(--transition);
}

.menu-toggle:hover {
    background: #f1f5f9;
}

.page-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-main);
    margin: 0;
    letter-spacing: -0.5px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 24px;
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.icon-btn {
    width: 40px;
    height: 40px;
    background: transparent;
    border: none;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #64748b;
    cursor: pointer;
    position: relative;
    transition: var(--transition);
}

.icon-btn:hover {
    background: #f1f5f9;
    color: var(--text-main);
}

.icon-btn .badge {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    background: #ef4444;
    border: 2px solid #fff;
    border-radius: 50%;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-left: 24px;
    border-left: 1px solid var(--border);
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-main);
    margin: 0;
}

.user-role {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.admin-content {
    padding: 32px;
    flex: 1;
}

@media (max-width: 992px) {
    .admin-main {
        margin-left: 0;
    }

    .admin-topbar {
        padding: 0 20px;
    }

    .sidebar-open .sidebar-overlay {
        display: block;
    }

    .sidebar-open :deep(.sidebar-admin) {
        transform: translateX(0);
    }
}

@media (max-width: 576px) {
    .hide-on-mobile {
        display: none;
    }

    .user-profile {
        padding-left: 0;
        border-left: none;
    }
}
</style>