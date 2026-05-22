<script setup>
import BaseInputLabel from './BaseInputLabel.vue';
import BaseInputError from './BaseInputError.vue';
const props = defineProps({
    labelContent: {
        type: String,
        default: ""
    },
    customId: {
        type: [String, Number],
        default: ""
    },
    customClassInput: {
        type: String,
        default: "auth-input"
    },
    customPlaceholderInput: {
        type: String,
        default: ""
    },
    customType: {
        type: String,
        default: 'text'
    },
    isRequired: {
        type: Boolean,
        default: false
    },
    error: {
        type: [String, Array],
        default: ""
    },
    disabled: {
        type: Boolean,
        default: false
    },
    readonly: {
        type: Boolean,
        default: false
    }
})

const value = defineModel();
</script>

<template>
    <div class="auth-form-group">
        <BaseInputLabel :customId="customId" :labelContent="labelContent" />
        <input :required="isRequired" :placeholder="customPlaceholderInput" v-model="value" :type="customType" :id="customId"
            :disabled="disabled" :readonly="readonly" :class="[customClassInput, error ? 'is-invalid' : '']">
        <BaseInputError :error="error" />
    </div>
</template>

<style scoped>
.auth-input:disabled,
.auth-input[readonly] {
    background-color: #f8fafc;
    color: #64748b;
    cursor: not-allowed;
    opacity: 1;
}
</style>
