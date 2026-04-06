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
        default: "form-control"
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
    <BaseInputLabel :customId="customId" :labelContent="labelContent" />
    <input :required="isRequired" :placeholder="customPlaceholderInput" v-model="value" type="text" :id="customId"
        :class="[customClassInput, error ? 'is-invalid' : '']">
    <BaseInputError :error="error" />
</template>

<style scoped></style>