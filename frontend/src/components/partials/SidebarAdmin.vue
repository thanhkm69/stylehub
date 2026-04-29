<script setup>
import { RouterLink, useRoute } from 'vue-router';
const route = useRoute();

const menuItems = [
    { name: 'Dashboard', path: '/admin', icon: 'ph-house-line' },
    { name: 'Danh mục', path: '/admin/categories', icon: 'ph-squares-four' },
    { name: 'Thuộc tính', path: '/admin/attributes', icon: 'ph-list-bullets' },
    { name: 'Sản phẩm', path: '/admin/products', icon: 'ph-package' },
    { name: 'Đơn hàng', path: '/admin/orders', icon: 'ph-shopping-cart' },
    { name: 'Khách hàng', path: '/admin/users', icon: 'ph-users' },
];

const isActive = (path) => {
    if (path === '/admin') {
        return route.path === '/admin';
    }
    return route.path.startsWith(path);
};
</script>

<template>
    <aside class="sidebar-admin">
        <div class="sidebar-header">
            <RouterLink to="/" class="brand">
                <img src="/logo.png" alt="Logo" class="logo" v-if="false"> <!-- Placeholder for logo -->
                <i class="ph-fill ph-storefront"></i>
                <span class="brand-text">Style<span>Hub</span></span>
            </RouterLink>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <p class="section-title">Menu Chính</p>
                <ul class="nav-list">
                    <li v-for="item in menuItems" :key="item.path">
                        <RouterLink :to="item.path" class="nav-link" :class="{ active: isActive(item.path) }">
                            <i :class="['ph-bold', item.icon]"></i>
                            <span>{{ item.name }}</span>
                        </RouterLink>
                    </li>
                </ul>
            </div>

            <div class="nav-section mt-auto">
                <p class="section-title">Hệ thống</p>
                <ul class="nav-list">
                    <li>
                        <RouterLink to="/admin/settings" class="nav-link">
                            <i class="ph-bold ph-gear"></i>
                            <span>Cài đặt</span>
                        </RouterLink>
                    </li>
                    <li>
                        <a href="#" class="nav-link logout-link">
                            <i class="ph-bold ph-sign-out"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>
</template>

<style scoped>
.sidebar-admin {
    width: 280px;
    height: 100vh;
    background: #ffffff;
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    transition: var(--transition);
}

.sidebar-header {
    padding: 32px 24px;
    display: flex;
    align-items: center;
}

.brand {
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-text {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -1px;
}

.brand i {
    font-size: 24px;
    color: var(--primary);
}

.brand-text span {
    color: var(--primary);
}

.sidebar-nav {
    flex: 1;
    padding: 0 16px 24px;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.nav-section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-muted);
    letter-spacing: 1px;
    padding: 0 12px;
    margin-bottom: 16px;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    text-decoration: none;
    color: #64748b;
    font-size: 15px;
    font-weight: 500;
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}

.nav-link i {
    font-size: 20px;
}

.nav-link:hover {
    background: #f8fafc;
    color: var(--text-main);
}

.nav-link.active {
    background: #eff6ff;
    color: var(--primary);
}

.logout-link:hover {
    background: #fef2f2;
    color: #ef4444;
}

.mt-auto {
    margin-top: auto;
}

/* Scrollbar styling */
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

@media (max-width: 992px) {
    .sidebar-admin {
        transform: translateX(-100%);
    }
}
</style>