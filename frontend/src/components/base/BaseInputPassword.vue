<script setup>
import BaseInputLabel from './BaseInputLabel.vue';
import BaseInputError from './BaseInputError.vue';
import { ref } from 'vue';

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
const showPassword = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
}
</script>

<template>
    <div class="auth-form-group">
        <BaseInputLabel :customId="customId" :labelContent="labelContent" />
        <div class="auth-password-wrapper">
            <input :required="isRequired" :placeholder="customPlaceholderInput" v-model="value"
                :type="showPassword ? 'text' : 'password'" :id="customId"
                :class="[customClassInput, error ? 'is-invalid' : '']">
            <button type="button" class="auth-password-toggle" @click="togglePassword" tabindex="-1"
                :title="showPassword ? 'Ẩn mật khẩu' : 'Hiển thị mật khẩu'">
                <!-- Eye icon (show) -->
                <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <!-- Eye-off icon (hide) -->
                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                    <line x1="1" y1="1" x2="23" y2="23" />
                </svg>
            </button>
        </div>
        <BaseInputError :error="error" />
    </div>
</template>

<style scoped></style>