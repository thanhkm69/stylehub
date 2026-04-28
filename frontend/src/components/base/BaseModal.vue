<script setup>
import BaseButton from './BaseButton.vue'

const props = defineProps({
    isShow: Boolean,
    customWidth: {
        type: String,
        default: '400px'
    }
})

const emit = defineEmits(["close"])

</script>

<template>
    <div v-if="isShow" class="modal-overlay">
        <div class="modal-content" :style="{ width: customWidth }">
            <BaseButton @click="emit('close')" customClass="btn btn-danger float-end mb-3" customText="X" />
            <slot></slot>
        </div>
    </div>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1050;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 50px;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-height: 90vh;
    overflow-y: auto;
}
</style>