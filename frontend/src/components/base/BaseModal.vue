<script setup>
import BaseButton from './BaseButton.vue'

const props = defineProps({
    isShow: Boolean
})

const emit = defineEmits(["close"])

</script>

<template>
    <div v-if="isShow" class="modal-overlay">
        <div class="modal-content">
            <BaseButton @click="emit('close')" customClass="btn-danger">X</BaseButton>
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
}

.modal-content {
    background: white;
    padding: 20px;
    width: 400px;
    margin: 100px auto;
}
</style>