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
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(["click"])

</script>

<template>
    <button :disabled="disabled" :type="customType" @click="emit('click')" class="btn" :class="customClass">
        <span>{{ customText }}</span>
    </button>
</template>