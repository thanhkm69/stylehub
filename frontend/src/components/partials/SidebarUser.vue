<script setup>
import { computed, onMounted } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useTokenStore } from '@/stores/token';
import { useProfileStore } from '@/stores/profile';
import { useNotify } from '@/composables/useNotify';

const route = useRoute();
const router = useRouter();
const tokenStore = useTokenStore();
const profileStore = useProfileStore();
const toast = useNotify();

const user = computed(() => tokenStore.user?.data);
const displayName = computed(() => profileStore.profile?.full_name || user.value?.name || 'Người dùng');
const displayEmail = computed(() => user.value?.email || profileStore.profile?.email || '');

const userMenu = [
    { name: 'Hồ sơ cá nhân', path: '/user', icon: 'ph-user' },
    { name: 'Đơn hàng của tôi', path: '/user/orders', icon: 'ph-shopping-bag' },
    { name: 'Sổ địa chỉ', path: '/user/addresses', icon: 'ph-map-pin' },
    { name: 'Sản phẩm yêu thích', path: '/user/wishlist', icon: 'ph-heart' },
    { name: 'Đổi mật khẩu', path: '/user/password', icon: 'ph-lock' },
];

const isActive = (path) => {
    if (path === '/user') return route.path === '/user';
    return route.path.startsWith(path);
};

const logout = async () => {
    const result = await tokenStore.logout();
    profileStore.clear();
    router.replace('/');

    if (result.success === true) {
        toast.success(result.message);
    } else {
        toast.error(result?.message || 'Lỗi khi đăng xuất');
    }
};

onMounted(() => {
    if (tokenStore.token && !profileStore.profile) {
        profileStore.me();
    }
});
</script>

<template>
    <aside class="sidebar-user">
        <div class="user-card">
            <div class="user-card-header">
                <div class="user-avatar-lg">
                    <i class="ph-fill ph-user"></i>
                </div>
                <div class="user-meta">
                    <h3 class="user-name">{{ displayName }}</h3>
                    <p class="user-email">{{ displayEmail }}</p>
                </div>
            </div>
            
            <nav class="user-nav">
                <ul class="user-nav-list">
                    <li v-for="item in userMenu" :key="item.path">
                        <RouterLink :to="item.path" class="user-nav-link" :class="{ active: isActive(item.path) }">
                            <i :class="['ph-bold', item.icon]"></i>
                            <span>{{ item.name }}</span>
                            <i class="ph ph-caret-right chevron"></i>
                        </RouterLink>
                    </li>
                    <li class="nav-divider"></li>
                    <li>
                        <button type="button" class="user-nav-link logout-btn" @click="logout">
                            <i class="ph-bold ph-sign-out"></i>
                            <span>Đăng xuất</span>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
</template>

<style scoped>
.sidebar-user {
    width: 100%;
}

.user-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.user-card-header {
    padding: 32px 24px;
    background: var(--background);
    border-bottom: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 16px;
}

.user-avatar-lg {
    width: 80px;
    height: 80px;
    background: var(--surface);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: var(--primary);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 4px solid var(--surface);
}

.user-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 4px;
}

.user-email {
    font-size: 13px;
    color: var(--text-muted);
    margin: 0;
}

.user-nav {
    padding: 16px;
}

.user-nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.user-nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    text-decoration: none;
    color: var(--text-muted);
    font-size: 14px;
    font-weight: 500;
    border-radius: var(--radius-md);
    transition: all 0.2s ease;
}

.user-nav-link i {
    font-size: 18px;
}

.user-nav-link .chevron {
    margin-left: auto;
    font-size: 12px;
    opacity: 0;
    transition: all 0.2s ease;
}

.user-nav-link:hover {
    background: var(--background);
    color: var(--text-main);
}

.user-nav-link:hover .chevron {
    opacity: 1;
    transform: translateX(4px);
}

.user-nav-link.active {
    background: color-mix(in oklch, #3b82f6 14%, var(--surface));
    color: var(--primary);
}

.user-nav-link.active .chevron {
    opacity: 1;
    color: var(--primary);
}

.nav-divider {
    height: 1px;
    background: var(--border);
    margin: 12px 16px;
}

.logout-btn:hover {
    background: #fef2f2;
    color: #ef4444;
}

.logout-btn {
    width: 100%;
    border: 0;
    background: transparent;
}
</style>
