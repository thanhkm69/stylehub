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
        default: "form-control"
    },
    customAccept: {
        type: String,
        default: ""
    },
    error: {
        type: String,
        default: ""
    }
})

const emit = defineEmits(["change"])
</script>

<template>
    <BaseInputLabel :customId="customId" :labelContent="labelContent" />
    <input @change="emit('change', $event)" type="file" :id="customId" :accept="customAccept"
        :class="[customClassInput, error ? 'is-invalid' : '']">
    <BaseInputError :error="error" />
</template>

<style scoped></style>