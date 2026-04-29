<script setup>
import BaseInputLabel from './BaseInputLabel.vue';
import BaseInputError from './BaseInputError.vue';
const props = defineProps({
    customId: {
        default: "",
        type: [String, Number],
    },
    labelContent: {
        default: "",
        type: String,
    },
    customClassInput: {
        type: String,
        default: "auth-input"
    },
    customMin: {
        default: null,
        type: Number
    },
    customMax: {
        default: null,
        type: Number
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
        <input v-model.number="value" type="number" :min="customMin" :max="customMax" :id="customId"
            :class="[customClassInput, error ? 'is-invalid' : '']">
        <BaseInputError :error="error" />
    </div>
</template>

<style scoped></style>