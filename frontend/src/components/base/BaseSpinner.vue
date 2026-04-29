<script setup>
defineProps({
    size: {
        type: String,
        default: 'md' // sm, md, lg
    },
    color: {
        type: String,
        default: 'primary' // primary, white, gray
    },
    label: {
        type: String,
        default: 'Đang xử lý...'
    }
})
</script>

<template>
    <div class="spinner-container" :class="[`size-${size}`, `color-${color}`]">
        <div class="spinner-ring">
            <div></div><div></div><div></div><div></div>
        </div>
        <span v-if="label" class="spinner-label">{{ label }}</span>
    </div>
</template>

<style scoped>
.spinner-container {
    display: inline-flex;
    align-items: center;
    gap: 12px;
}

.spinner-ring {
    display: inline-block;
    position: relative;
    width: 24px;
    height: 24px;
}

.size-sm .spinner-ring { width: 16px; height: 16px; }
.size-md .spinner-ring { width: 24px; height: 24px; }
.size-lg .spinner-ring { width: 40px; height: 40px; }

.spinner-ring div {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 100%;
    height: 100%;
    border: 2px solid currentColor;
    border-radius: 50%;
    animation: spinner-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-color: currentColor transparent transparent transparent;
}

.spinner-ring div:nth-child(1) { animation-delay: -0.45s; }
.spinner-ring div:nth-child(2) { animation-delay: -0.3s; }
.spinner-ring div:nth-child(3) { animation-delay: -0.15s; }

@keyframes spinner-ring {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Colors */
.color-primary { color: var(--primary); }
.color-white { color: #ffffff; }
.color-gray { color: #94a3b8; }

.spinner-label {
    font-size: 14px;
    font-weight: 500;
    color: inherit;
}

/* Size specific tweaks */
.size-sm .spinner-ring div { border-width: 1.5px; }
.size-lg .spinner-ring div { border-width: 3px; }
.size-sm .spinner-label { font-size: 13px; }
</style>