<script setup>
import BaseInputLabel from './BaseInputLabel.vue';
import BaseInputError from './BaseInputError.vue';

const props = defineProps({
    categoryIds: {
        type: Array,
        default: []
    },
    customId: {
        type: [String, Number],
        default: ""
    },
    labelContent: {
        type: String,
        default: ""
    },
    placeholder: {
        type: String,
        default: "Chọn"
    },
    values: {
        type: Array,
        default: []
    },
    isDisabled: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: ""
    },

})
const value = defineModel()

</script>

<template>
    <div class="auth-form-group">
        <BaseInputLabel :customId="customId" :labelContent="labelContent" />

        <select :id="customId" v-model="value" class="auth-input" :class="[error ? 'is-invalid' : '']">
            <option :disabled="isDisabled" :value="null">{{ placeholder }}</option>
            <option v-for="(item, index) in values" :disabled="categoryIds.includes(item.id)" :key="index" :value="item.id">
                {{ item.name }}
            </option>
        </select>

        <BaseInputError :error="error" />
    </div>
</template>

<style scoped></style>