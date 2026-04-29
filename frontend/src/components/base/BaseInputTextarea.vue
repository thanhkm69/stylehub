<script setup>
import BaseInputLabel from './BaseInputLabel.vue';
import BaseInputError from './BaseInputError.vue';
const props = defineProps({
    customId: {
        type: [String, Number],
        default: ""
    },
    labelContent: {
        type: String,
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
    isRequired: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: ""
    }
})

const value = defineModel();
</script>

<template>
    <div class="auth-form-group">
        <BaseInputLabel :customId="customId" :labelContent="labelContent" />
        <textarea :required="isRequired" :placeholder="customPlaceholderInput" v-model="value" :id="customId"
            :class="[customClassInput, error ? 'is-invalid' : '']" rows="4"></textarea>
        <BaseInputError :error="error" />
    </div>
</template>

<style scoped></style>