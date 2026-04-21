<script setup>
const props = defineProps({
    error: {
        type: [String, Array],
        default: ""
    }
})

</script>

<template>
    <div v-if="error" class="auth-error-text">
        <svg viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd" />
        </svg>
        <span>{{ error }}</span>
    </div>
</template>

<style scoped>
.auth-error-text {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 8px;
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.auth-error-text svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}
</style>