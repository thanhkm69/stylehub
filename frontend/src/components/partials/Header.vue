<script setup>
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { useTokenStore } from '@/stores/token';
import { useNotify } from '@/composables/useNotify';

const tokenStore = useTokenStore()
const toast = useNotify()

const isAuth = computed(() => tokenStore.token)
const user = computed(() => tokenStore.user)

const loading = ref(false)

const logout = async () => {
    loading.value = true
    const result = await tokenStore.logout()
    if (result.success === true) {
        toast.success(result.message)
        localStorage.removeItem('token')
    } else {
        toast.error(result?.message || "Lỗi khi đăng xuất")
    }
    loading.value = false
}

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

onMounted(async () => {
    await tokenStore.getUser()
})
</script>

<template>
    <header class="site-header">
        <div class="container">
            <!-- Logo -->
            <router-link to="/" class="nav-logo" @click="closeMobileMenu">
                <i class="ph-fill ph-storefront"></i> StyleHub
            </router-link>

            <!-- Menu Desktop -->
            <nav class="nav-links">
                <router-link to="/" exact-active-class="active">Trang chủ</router-link>
                <router-link to="/shop" active-class="active">Sản phẩm</router-link>
                <a href="#">Bộ sưu tập</a>
                <a href="#">Về chúng tôi</a>
                <a href="#">Blog</a>
            </nav>

            <!-- Actions & Auth -->
            <div class="nav-actions">
                <div class="action-icons">
                    <i class="ph ph-magnifying-glass" title="Tìm kiếm"></i>
                    <div class="cart-icon-wrapper">
                        <i class="ph ph-shopping-cart" title="Giỏ hàng"></i>
                        <span class="cart-badge">3</span>
                    </div>
                </div>

                <div class="divider hide-on-tablet"></div>

                <!-- Auth Logic: Chưa đăng nhập -->
                <div v-if="!isAuth" class="auth-group hide-on-tablet">
                    <RouterLink to="/login" class="auth-link">Đăng nhập</RouterLink>
                    <RouterLink to="/register" class="auth-btn">Đăng ký</RouterLink>
                </div>

                <!-- Auth Logic: Đã đăng nhập -->
                <div v-else class="user-group">
                    <div class="user-dropdown-wrapper">
                        <div class="user-info-trigger">
                            <div class="avatar-circle">
                                <i class="ph-fill ph-user"></i>
                            </div>
                            <span class="user-name hide-on-mobile">{{ user?.data?.name }}</span>
                            <i class="ph ph-caret-down dropdown-arrow"></i>
                        </div>

                        <div class="user-dropdown-menu">
                            <div class="dropdown-header">
                                <p class="user-display-name">{{ user?.data?.name }}</p>
                                <p class="user-email">{{ user?.data?.email }}</p>
                                <span :class="['role-badge', user?.data?.role === 'Admin' ? 'Admin' : 'User']">
                                    {{ user?.data?.role === 'Admin' ? 'Quản trị viên' : 'Thành viên' }}
                                </span>
                            </div>

                            <div class="dropdown-divider"></div>

                            <div class="dropdown-links">
                                <RouterLink v-if="user?.data?.role === 'Admin'" to="/admin" class="dropdown-item">
                                    <i class="ph-bold ph-layout"></i>
                                    <span>Trang quản trị</span>
                                </RouterLink>

                                <RouterLink to="/user" class="dropdown-item">
                                    <i class="ph-bold ph-user-circle-gear"></i>
                                    <span>Thông tin tài khoản</span>
                                </RouterLink>

                                <RouterLink to="/user/orders" class="dropdown-item">
                                    <i class="ph-bold ph-shopping-bag"></i>
                                    <span>Đơn hàng của tôi</span>
                                </RouterLink>
                            </div>

                            <div class="dropdown-divider"></div>

                            <button @click="logout" class="dropdown-item logout-btn" :disabled="loading">
                                <template v-if="!loading">
                                    <i class="ph-bold ph-sign-out"></i>
                                    <span>Đăng xuất</span>
                                </template>
                                <BaseSpinner v-else size="sm" color="gray" label="" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Hamburger Toggle -->
                <button class="mobile-menu-btn" @click="toggleMobileMenu" :class="{ active: isMobileMenuOpen }">
                    <i class="ph-bold" :class="isMobileMenuOpen ? 'ph-x' : 'ph-list'"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="isMobileMenuOpen" class="mobile-menu-overlay" @click="closeMobileMenu"></div>
            </Transition>

            <Transition name="slide">
                <div v-if="isMobileMenuOpen" class="mobile-menu-content">
                    <div class="mobile-menu-header">
                        <span class="mobile-brand">Style<span>Hub</span></span>
                        <button class="close-btn" @click="closeMobileMenu">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>

                    <div class="mobile-nav-list">
                        <RouterLink to="/" class="mobile-nav-item" @click="closeMobileMenu">
                            <i class="ph-bold ph-house"></i> <span>Trang chủ</span>
                        </RouterLink>
                        <RouterLink to="/shop" class="mobile-nav-item" @click="closeMobileMenu">
                            <i class="ph-bold ph-shopping-bag"></i> <span>Sản phẩm</span>
                        </RouterLink>
                        <a href="#" class="mobile-nav-item">
                            <i class="ph-bold ph-sketch-logo"></i> <span>Bộ sưu tập</span>
                        </a>
                        <a href="#" class="mobile-nav-item">
                            <i class="ph-bold ph-info"></i> <span>Về chúng tôi</span>
                        </a>
                        <a href="#" class="mobile-nav-item">
                            <i class="ph-bold ph-newspaper"></i> <span>Blog</span>
                        </a>
                    </div>

                    <div v-if="!isAuth" class="mobile-auth-section">
                        <RouterLink to="/login" class="mobile-btn login" @click="closeMobileMenu">Đăng nhập</RouterLink>
                        <RouterLink to="/register" class="mobile-btn register" @click="closeMobileMenu">Đăng ký
                        </RouterLink>
                    </div>

                    <div class="mobile-menu-footer">
                        <div class="social-links">
                            <i class="ph-fill ph-facebook-logo"></i>
                            <i class="ph-fill ph-instagram-logo"></i>
                            <i class="ph-fill ph-tiktok-logo"></i>
                        </div>
                        <p>© 2024 StyleHub Fashion</p>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </header>
</template>

<style scoped>
.site-header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
    padding: 14px 0;
}

.site-header .container {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nav-logo {
    font-size: 24px;
    font-weight: 800;
    letter-spacing: -1px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-main);
}

.nav-logo i {
    color: var(--primary);
}

.nav-links {
    display: flex;
    gap: 32px;
}

.nav-links a {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-muted);
    transition: var(--transition);
}

.nav-links a:hover,
.nav-links a.active {
    color: var(--primary);
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}

.action-icons {
    display: flex;
    align-items: center;
    gap: 16px;
}

.action-icons>i,
.cart-icon-wrapper i {
    font-size: 24px;
    color: var(--text-main);
    transition: var(--transition);
    cursor: pointer;
}

.action-icons>i:hover,
.cart-icon-wrapper:hover i {
    color: var(--primary);
    transform: translateY(-2px);
}

.cart-icon-wrapper {
    position: relative;
}

.cart-badge {
    position: absolute;
    top: -6px;
    right: -10px;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 800;
    height: 18px;
    min-width: 18px;
    padding: 0 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    border: 2px solid white;
}

.divider {
    width: 1px;
    height: 24px;
    background-color: var(--border);
}

/* Auth Styles */
.auth-group {
    display: flex;
    align-items: center;
    gap: 16px;
}

.auth-link {
    font-weight: 600;
    color: var(--text-main);
    font-size: 14px;
    transition: var(--transition);
}

.auth-link:hover {
    color: var(--primary);
}

.auth-btn {
    background: var(--text-main);
    color: white;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition);
}

.auth-btn:hover {
    background: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.2);
}

/* User Dropdown Styles */
.user-group {
    position: relative;
}

.user-dropdown-wrapper {
    position: relative;
    padding: 4px 0;
}

.user-info-trigger {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 6px 12px;
    border-radius: 12px;
    transition: var(--transition);
    background: #f8fafc;
    border: 1px solid var(--border);
}

.user-info-trigger:hover {
    background: #f1f5f9;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.user-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main);
    max-width: 100px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-arrow {
    font-size: 14px;
    color: var(--text-muted);
    transition: transform 0.3s ease;
}

.user-dropdown-wrapper:hover .dropdown-arrow {
    transform: rotate(180deg);
}

.user-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    width: 240px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    padding: 12px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    margin-top: 8px;
    z-index: 1001;
}

.user-dropdown-wrapper:hover .user-dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 8px 12px 12px;
}

.user-display-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-main);
    margin: 0;
}

.user-email {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 8px;
    word-break: break-all;
}

.role-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}

.role-badge.Admin {
    background: #fef2f2;
    color: #ef4444;
}

.role-badge.User {
    background: #eff6ff;
    color: #3b82f6;
}

.dropdown-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 8px 0;
}

.dropdown-links {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.dropdown-item i {
    font-size: 20px;
}

.dropdown-item:hover {
    background: #f8fafc;
    color: var(--primary);
}

.logout-btn {
    color: #ef4444;
}

.logout-btn:hover {
    background: #fef2f2;
    color: #ef4444;
}

/* Mobile Navigation Styles */
.mobile-menu-btn {
    display: none;
    width: 40px;
    height: 40px;
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 10px;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--text-main);
    cursor: pointer;
    transition: var(--transition);
}

.mobile-menu-btn:hover {
    background: #f1f5f9;
    color: var(--primary);
}

.mobile-menu-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.4);
    backdrop-filter: blur(4px);
    z-index: 10000;
}

.mobile-menu-content {
    position: fixed;
    top: 0;
    right: 0;
    width: 85%;
    max-width: 320px;
    height: 100vh;
    background: #ffffff;
    z-index: 10001;
    display: flex;
    flex-direction: column;
    box-shadow: -10px 0 25px rgba(0, 0, 0, 0.1);
}

.mobile-menu-header {
    padding: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
}

.mobile-brand {
    font-size: 20px;
    font-weight: 800;
}

.mobile-brand span {
    color: var(--primary);
}

.close-btn {
    width: 36px;
    height: 36px;
    background: #f1f5f9;
    border: none;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #64748b;
}

.mobile-nav-list {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mobile-nav-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 16px;
    border-radius: 12px;
    color: #475569;
    font-size: 16px;
    font-weight: 600;
    transition: var(--transition);
}

.mobile-nav-item i {
    font-size: 22px;
    color: var(--text-muted);
}

.mobile-nav-item:hover,
.mobile-nav-item.router-link-active {
    background: #f0fdf4;
    color: var(--primary);
}

.mobile-nav-item:hover i,
.mobile-nav-item.router-link-active i {
    color: var(--primary);
}

.mobile-auth-section {
    margin-top: auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #f8fafc;
}

.mobile-btn {
    padding: 14px;
    border-radius: 12px;
    text-align: center;
    font-weight: 700;
    font-size: 15px;
}

.mobile-btn.login {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text-main);
}

.mobile-btn.register {
    background: var(--primary);
    color: white;
}

.mobile-menu-footer {
    padding: 24px;
    text-align: center;
    border-top: 1px solid #f1f5f9;
}

.social-links {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 12px;
}

.social-links i {
    font-size: 24px;
    color: #64748b;
}

.mobile-menu-footer p {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}

@media (max-width: 992px) {

    .nav-links,
    .hide-on-tablet {
        display: none !important;
    }

    .mobile-menu-btn {
        display: flex;
    }
}
</style>