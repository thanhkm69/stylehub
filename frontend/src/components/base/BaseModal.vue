<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    isShow: Boolean,
    title: {
        type: String,
        default: ''
    },
    customWidth: {
        type: String,
        default: '500px'
    }
})

const emit = defineEmits(["close"])

// Prevent scrolling when modal is open
const handleEsc = (e) => {
    if (e.key === 'Escape' && props.isShow) {
        emit('close');
    }
};

onMounted(() => window.addEventListener('keydown', handleEsc));
onUnmounted(() => window.removeEventListener('keydown', handleEsc));

</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="isShow" class="modal-overlay" @click.self="emit('close')">
                <div class="modal-container" :style="{ maxWidth: customWidth }">
                    <!-- Close Button -->
                    <button class="btn-close-modal" @click="emit('close')" aria-label="Close">
                        <i class="ph-bold ph-x"></i>
                    </button>

                    <!-- Header (Optional) -->
                    <div v-if="title" class="modal-header">
                        <h3 class="modal-title">{{ title }}</h3>
                    </div>

                    <!-- Content -->
                    <div class="modal-body">
                        <slot></slot>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 24px;
}

.modal-container {
    background: #ffffff;
    width: 100%;
    position: relative;
    border-radius: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 48px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-close-modal {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: #f1f5f9;
    border: none;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 10;
}

.btn-close-modal:hover {
    background: #e2e8f0;
    color: #ef4444;
    transform: rotate(90deg);
}

.modal-header {
    padding: 28px 32px 12px;
}

.modal-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-main);
    margin: 0;
    letter-spacing: -0.5px;
}

.modal-body {
    padding: 20px 32px 32px;
    overflow-y: auto;
    flex: 1;
}

/* Scrollbar styling */
.modal-body::-webkit-scrollbar {
    width: 6px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

/* Transition Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-active .modal-container {
    animation: modal-scale-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-fade-leave-active .modal-container {
    animation: modal-scale-in 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) reverse;
}

@keyframes modal-scale-in {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@media (max-width: 576px) {
    .modal-overlay {
        padding: 0;
        align-items: flex-end;
    }

    .modal-container {
        border-radius: 24px 24px 0 0;
        max-height: 90vh;
    }

    .btn-close-modal {
        top: 16px;
        right: 16px;
    }
}
</style>