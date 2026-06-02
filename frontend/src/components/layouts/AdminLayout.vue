<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import SidebarAdmin from '@/components/partials/SidebarAdmin.vue';
import { RouterView, useRouter } from 'vue-router';
import { useChatStore } from '@/stores/chat';

const chatStore = useChatStore();
const router = useRouter();
const isSidebarOpen = ref(false);
const showNotifications = ref(false);

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
};

const goToChat = (conversationId = null, notifId = null) => {
    showNotifications.value = false;
    // Remove clicked notification from the list
    if (notifId) {
        chatStore.adminNotifications = chatStore.adminNotifications.filter(n => n.id !== notifId);
    }
    if (conversationId) {
        chatStore.activeAdminConversationId = conversationId;
    }
    router.push({ name: 'ChatManager' });
};

const formatNotifTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const now = new Date();
    const diffMs = now - date;
    const diffMin = Math.floor(diffMs / 60000);
    if (diffMin < 1) return 'Vừa xong';
    if (diffMin < 60) return `${diffMin} phút trước`;
    const diffHour = Math.floor(diffMin / 60);
    if (diffHour < 24) return `${diffHour} giờ trước`;
    return date.toLocaleDateString('vi-VN');
};

const closeNotifications = (e) => {
    if (!e.target.closest('.notification-wrapper')) {
        showNotifications.value = false;
    }
};

onMounted(() => {
    chatStore.fetchAdminUnreadCount();
    chatStore.listenForAdminNotifications();
    document.addEventListener('click', closeNotifications);
});

onUnmounted(() => {
    document.removeEventListener('click', closeNotifications);
});
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
                    <div class="topbar-actions">
                        <!-- Notification Bell -->
                        <div class="notification-wrapper">
                            <button class="icon-btn" @click.stop="toggleNotifications">
                                <i class="ph-bold ph-bell"></i>
                                <span v-if="chatStore.adminUnreadCount > 0" class="notif-badge">
                                    {{ chatStore.adminUnreadCount > 9 ? '9+' : chatStore.adminUnreadCount }}
                                </span>
                            </button>

                            <!-- Notification Dropdown -->
                            <Transition name="dropdown">
                                <div v-if="showNotifications" class="notif-dropdown">
                                    <div class="notif-header">
                                        <h4>Tin nhắn hỗ trợ</h4>
                                        <span v-if="chatStore.adminUnreadCount > 0" class="notif-count-label">
                                            {{ chatStore.adminUnreadCount }}
                                        </span>
                                    </div>

                                    <div class="notif-list">
                                        <div v-if="chatStore.adminNotifications.length === 0" class="notif-empty">
                                            <i class="ph ph-checks"></i>
                                            <p>Không có tin nhắn mới</p>
                                        </div>
                                        <div 
                                            v-for="notif in chatStore.adminNotifications" 
                                            :key="notif.id" 
                                            class="notif-item"
                                            @click="goToChat(notif.conversation_id, notif.id)"
                                        >
                                            <div class="notif-avatar-wrap">
                                                <div class="notif-avatar">
                                                    {{ notif.sender_name?.charAt(0).toUpperCase() || 'K' }}
                                                </div>
                                            </div>
                                            <div class="notif-body">
                                                <p class="notif-text">
                                                    <strong>{{ notif.sender_name }}</strong> đã gửi tin nhắn cho bạn
                                                </p>
                                                <p class="notif-preview">{{ notif.message }}</p>
                                                <p class="notif-time">{{ formatNotifTime(notif.created_at) }}</p>
                                            </div>
                                            <div class="notif-dot"></div>
                                        </div>
                                    </div>

                                    <button class="notif-view-all" @click="goToChat()">
                                        Xem tất cả trong Hỗ trợ trực tuyến
                                    </button>
                                </div>
                            </Transition>
                        </div>
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

/* Notification Badge */
.notif-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    background: #ef4444;
    color: white;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
    line-height: 1;
    animation: badge-pop 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes badge-pop {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Notification Wrapper */
.notification-wrapper {
    position: relative;
}

/* Notification Dropdown */
.notif-dropdown {
    position: absolute;
    top: calc(100% + 12px);
    right: -8px;
    width: 360px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12), 0 4px 12px rgba(0, 0, 0, 0.06);
    border: 1px solid var(--border);
    overflow: hidden;
    z-index: 1000;
}

.notif-dropdown::before {
    content: '';
    position: absolute;
    top: -6px;
    right: 18px;
    width: 12px;
    height: 12px;
    background: white;
    border: 1px solid var(--border);
    border-bottom: none;
    border-right: none;
    transform: rotate(45deg);
}

/* Dropdown transition */
.dropdown-enter-active {
    animation: dropdown-in 0.2s ease-out;
}
.dropdown-leave-active {
    animation: dropdown-in 0.15s ease-in reverse;
}
@keyframes dropdown-in {
    0% { opacity: 0; transform: translateY(-8px) scale(0.96); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}

.notif-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.notif-header h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--text-main);
}

.notif-count-label {
    min-width: 22px;
    height: 22px;
    padding: 0 7px;
    font-size: 12px;
    font-weight: 700;
    color: white;
    background: #ef4444;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.notif-list {
    max-height: 360px;
    overflow-y: auto;
}

.notif-empty {
    padding: 48px 20px;
    text-align: center;
    color: var(--text-muted);
}

.notif-empty i {
    font-size: 40px;
    margin-bottom: 12px;
    opacity: 0.4;
    display: block;
    color: #22c55e;
}

.notif-empty p {
    margin: 0;
    font-size: 14px;
}

.notif-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: background 0.15s;
    background: #eff6ff;
}

.notif-item:hover {
    background: #e0ecff;
}

.notif-avatar-wrap {
    flex-shrink: 0;
}

.notif-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 600;
}

.notif-body {
    flex: 1;
    min-width: 0;
}

.notif-text {
    margin: 0;
    font-size: 13px;
    color: var(--text-main);
    line-height: 1.4;
}

.notif-text strong {
    font-weight: 700;
}

.notif-preview {
    margin: 2px 0 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notif-time {
    margin: 2px 0 0;
    font-size: 12px;
    color: var(--primary);
    font-weight: 600;
}

.notif-dot {
    width: 12px;
    height: 12px;
    background: var(--primary);
    border-radius: 50%;
    flex-shrink: 0;
}

.notif-view-all {
    width: 100%;
    padding: 14px 20px;
    background: #f8fafc;
    border: none;
    border-top: 1px solid var(--border);
    color: var(--primary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.15s;
}

.notif-view-all:hover {
    background: #f1f5f9;
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

    .notif-dropdown {
        width: 300px;
        right: -40px;
    }
}
</style>