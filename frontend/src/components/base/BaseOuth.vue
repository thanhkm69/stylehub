<script setup>
import { API_URL } from '@/config/env'
</script>
<template>
    <div class="social-login">
        <a :href="`${API_URL}/google`" class="social-btn">
            <i class="ph-fill ph-google-logo" style="color: #DB4437;"></i> Google
        </a>
        <a :href="`${API_URL}/facebook`" class="social-btn">
            <i class="ph-fill ph-facebook-logo" style="color: #1877F2;"></i> Facebook
        </a>
        <a :href="`${API_URL}/github`" class="social-btn">
            <i class="ph-fill ph-github-logo"></i> Github
        </a>
    </div>
</template>

<style scoped>
.social-login {
    display: flex;
    flex-direction: column;
    gap: 16px;
    justify-content: center;
    width: 100%;
}

.social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    background: var(--surface);
    color: var(--text-main);
    font-size: 15px;
    font-weight: 500;
    transition: var(--transition);
}

.social-btn:hover {
    background: var(--background);
    border-color: var(--text-muted);
}

.social-btn i {
    font-size: 24px;
}
</style>