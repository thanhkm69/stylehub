<script setup>
defineProps({
    fullscreen: {
        type: Boolean,
        default: false
    },
    text: {
        type: String,
        default: 'Đang tải dữ liệu...'
    }
})
</script>

<template>
    <div :class="['loading-wrapper', { 'is-fullscreen': fullscreen }]">
        <div class="loader-content">
            <div class="loader-animation">
                <div class="logo-symbol">S<span>H</span></div>
                <div class="pulse-ring"></div>
            </div>
            <div class="loader-info">
                <span class="brand-text">STYLEHUB</span>
                <div class="progress-line">
                    <div class="line-fill"></div>
                </div>
                <p class="loading-text">{{ text }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.loading-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px;
    width: 100%;
}

.loading-wrapper.is-fullscreen {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    z-index: 9999;
    padding: 0;
}

.loader-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

.loader-animation {
    position: relative;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-symbol {
    font-size: 28px;
    font-weight: 900;
    color: var(--text-main);
    z-index: 2;
    letter-spacing: -2px;
}

.logo-symbol span {
    color: var(--primary);
}

.pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 3px solid var(--primary);
    border-radius: 50%;
    animation: pulse-ring 1.5s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
}

@keyframes pulse-ring {
    0% { transform: scale(0.6); opacity: 1; }
    80%, 100% { transform: scale(1.2); opacity: 0; }
}

.loader-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.brand-text {
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 4px;
    color: var(--text-main);
    opacity: 0.8;
}

.progress-line {
    width: 120px;
    height: 2px;
    background: #f1f5f9;
    border-radius: 2px;
    overflow: hidden;
}

.line-fill {
    height: 100%;
    width: 40%;
    background: var(--primary);
    animation: line-move 1.5s infinite ease-in-out;
}

@keyframes line-move {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(250%); }
}

.loading-text {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-muted);
    margin: 0;
}

/* Dark mode support if needed */
@media (prefers-color-scheme: dark) {
    .loading-wrapper.is-fullscreen {
        background: rgba(15, 23, 42, 0.9);
    }
}
</style>