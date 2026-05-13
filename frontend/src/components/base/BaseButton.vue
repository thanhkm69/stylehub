<script setup>
import { computed } from 'vue'
import BaseSpinner from './BaseSpinner.vue'

const props = defineProps({
    customText: {
        type: [String, Number],
        default: 'Nút ấn'
    },
    variant: {
        type: String,
        default: 'primary' // primary, secondary, outline, danger, etc.
    },
    size: {
        type: String,
        default: 'md' // sm, md, lg
    },
    customClass: {
        type: [String, Array, Object],
        default: ''
    },
    customType: {
        type: String,
        default: 'button'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    block: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(["click"])

const buttonClasses = computed(() => {
    return [
        'btn',
        `btn-${props.variant}`,
        `btn-${props.size}`,
        { 'btn-block': props.block },
        { 'is-loading': props.loading },
        props.customClass
    ]
})

const spinnerColor = computed(() => {
    if (props.variant === 'outline' || props.variant === 'secondary') return 'primary'
    return 'white'
})
</script>

<template>
    <button 
        :disabled="disabled || loading" 
        :type="customType" 
        @click="emit('click')" 
        :class="buttonClasses"
    >
        <template v-if="loading">
            <BaseSpinner :size="size === 'lg' ? 'md' : 'sm'" :color="spinnerColor" label="" />
        </template>
        <slot v-else>
            <span>{{ customText }}</span>
        </slot>
    </button>
</template>

<style scoped>
.btn-block {
    width: 100%;
}
.btn.is-loading {
    cursor: not-allowed;
    opacity: 0.8;
}
</style>