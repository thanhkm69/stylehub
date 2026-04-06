<script setup>
const props = defineProps({
    customText: {
        type: String,
        default: 'Nút ấn'
    },
    customClass: {
        type: [String, Array],
        default: 'btn-primary'
    },
    customType: {
        type: String,
        default: 'button'
    }
})

const emit = defineEmits(["click"])

</script>

<template>
    <button :type="customType" @click="emit('click')" :class="['btn', customClass]">{{ customText }}</button>
</template>