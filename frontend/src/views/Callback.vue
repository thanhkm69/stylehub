<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useTokenStore } from '@/stores/token';
import BaseLoading from '@/components/base/BaseLoading.vue';

const route = useRoute()
const router = useRouter()
const tokenStore = useTokenStore()
const token = route.query.token

const status = ref('loading'); // loading, success, error

onMounted(async () => {
    try {
        if (!token) {
            status.value = 'error';
            return;
        }

        // Simulate a small delay for premium feel
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        tokenStore.token = token;
        status.value = 'success';

        // Redirect after success animation
        setTimeout(() => {
            router.push({ name: 'Home' });
        }, 2000);

    } catch (err) {
        status.value = 'error';
    }
})
</script>

<template>
    <div class="auth-callback-page">
        <div class="callback-card">
            <!-- State: Loading -->
            <div v-if="status === 'loading'" class="state-content">
                <BaseLoading text="Đang xác thực danh tính..." />
            </div>

            <!-- State: Success -->
            <div v-if="status === 'success'" class="state-content success-state">
                <div class="success-animation">
                    <div class="check-icon">
                        <i class="ph-fill ph-check-circle"></i>
                    </div>
                    <div class="confetti"></div>
                </div>
                <h2 class="status-title">Xác thực thành công!</h2>
                <p class="status-desc">Chào mừng bạn quay trở lại với <strong>StyleHub</strong>.</p>
                <div class="redirect-hint">
                    <div class="dot-flashing"></div>
                    <span>Đang chuyển hướng về trang chủ</span>
                </div>
            </div>

            <!-- State: Error -->
            <div v-if="status === 'error'" class="state-content error-state">
                <div class="error-icon">
                    <i class="ph-fill ph-warning-circle"></i>
                </div>
                <h2 class="status-title">Xác thực thất bại</h2>
                <p class="status-desc">Đã có lỗi xảy ra trong quá trình xác thực. Vui lòng thử lại sau.</p>
                <button @click="router.push('/login')" class="retry-btn">Quay lại Đăng nhập</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-callback-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    padding: 24px;
}

.callback-card {
    background: #ffffff;
    width: 100%;
    max-width: 480px;
    padding: 60px 40px;
    border-radius: 32px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
    text-align: center;
    border: 1px solid #f1f5f9;
}

.state-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
}

/* Success State Animations */
.success-icon {
    font-size: 80px;
    color: #22c55e;
    animation: bounce-in 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.status-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-main);
    margin: 0;
    letter-spacing: -0.5px;
}

.status-desc {
    color: var(--text-muted);
    font-size: 15px;
    margin: 0;
}

.status-desc strong {
    color: var(--primary);
}

.redirect-hint {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 12px;
    padding: 10px 20px;
    background: #f0fdf4;
    border-radius: 12px;
    color: #166534;
    font-size: 13px;
    font-weight: 600;
}

/* Dot Flashing Animation */
.dot-flashing {
    position: relative;
    width: 6px;
    height: 6px;
    border-radius: 5px;
    background-color: #22c55e;
    color: #22c55e;
    animation: dot-flashing 1s infinite linear alternate;
    animation-delay: 0.5s;
}
.dot-flashing::before, .dot-flashing::after {
    content: "";
    display: inline-block;
    position: absolute;
    top: 0;
}
.dot-flashing::before {
    left: -12px;
    width: 6px;
    height: 6px;
    border-radius: 5px;
    background-color: #22c55e;
    color: #22c55e;
    animation: dot-flashing 1s infinite alternate;
    animation-delay: 0s;
}
.dot-flashing::after {
    left: 12px;
    width: 6px;
    height: 6px;
    border-radius: 5px;
    background-color: #22c55e;
    color: #22c55e;
    animation: dot-flashing 1s infinite alternate;
    animation-delay: 1s;
}

@keyframes dot-flashing {
    0% { background-color: #22c55e; }
    50%, 100% { background-color: rgba(34, 197, 94, 0.2); }
}

/* Error State */
.error-icon {
    font-size: 80px;
    color: #ef4444;
}

.retry-btn {
    margin-top: 12px;
    padding: 12px 24px;
    background: var(--text-main);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.retry-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* General Animations */
@keyframes bounce-in {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}

.check-icon {
    font-size: 72px;
    color: #22c55e;
    display: flex;
    align-items: center;
    justify-content: center;
    filter: drop-shadow(0 10px 15px rgba(34, 197, 94, 0.2));
    animation: check-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes check-pop {
    from { transform: scale(0) rotate(-45deg); opacity: 0; }
    to { transform: scale(1) rotate(0); opacity: 1; }
}
</style>