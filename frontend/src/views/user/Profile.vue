<script setup>
import { useTokenStore } from '@/stores/token';
import { computed, ref } from 'vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';

const tokenStore = useTokenStore();
const user = computed(() => tokenStore.user?.data);

const loading = ref(false);
const form = ref({
    name: user.value?.name || '',
    email: user.value?.email || '',
    phone: user.value?.phone || '',
    address: user.value?.address || ''
});

const handleUpdate = () => {
    loading.value = true;
    // Update logic here
    setTimeout(() => {
        loading.value = false;
    }, 1000);
};
</script>

<template>
    <div class="profile-content">
        <div class="content-header">
            <h2 class="content-title">Hồ sơ cá nhân</h2>
            <p class="content-subtitle">Quản lý thông tin cá nhân của bạn để bảo mật tài khoản</p>
        </div>

        <form @submit.prevent="handleUpdate" class="profile-form">
            <div class="form-grid">
                <div class="form-group">
                    <BaseInputText 
                        labelContent="Họ và tên" 
                        v-model="form.name" 
                        customPlaceholderInput="Nhập họ và tên"
                    />
                </div>
                
                <div class="form-group">
                    <BaseInputText 
                        labelContent="Địa chỉ Email" 
                        v-model="form.email" 
                        :disabled="true"
                        customPlaceholderInput="email@example.com"
                    />
                    <small class="form-hint">Email không thể thay đổi vì lý do bảo mật.</small>
                </div>

                <div class="form-group">
                    <BaseInputText 
                        labelContent="Số điện thoại" 
                        v-model="form.phone" 
                        customPlaceholderInput="0123 456 789"
                    />
                </div>

                <div class="form-group">
                    <BaseInputText 
                        labelContent="Địa chỉ mặc định" 
                        v-model="form.address" 
                        customPlaceholderInput="Số nhà, tên đường, quận/huyện..."
                    />
                </div>
            </div>

            <div class="form-footer">
                <BaseButton 
                    customType="submit" 
                    customText="Lưu thay đổi" 
                    customClass="btn btn-primary px-5" 
                    :disabled="loading"
                />
            </div>
        </form>
    </div>
</template>

<style scoped>
.profile-content {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.content-header {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 24px;
}

.content-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-main);
    margin: 0 0 8px 0;
    letter-spacing: -0.5px;
}

.content-subtitle {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-hint {
    font-size: 12px;
    color: #94a3b8;
    font-style: italic;
}

.form-footer {
    margin-top: 16px;
    display: flex;
    justify-content: flex-start;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>