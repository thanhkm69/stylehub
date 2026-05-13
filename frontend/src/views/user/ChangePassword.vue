<script setup>
import { useNotify } from '@/composables/useNotify';
import { useUserStore } from '@/stores/user';
import { ref } from 'vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseInputPassword from '@/components/base/BaseInputPassword.vue';

const userStore = useUserStore();
const toast = useNotify();
const passwordLoading = ref(false);
const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  newPassword_confirmation: '',
});
const errors = ref({
  currentPassword: '',
  newPassword: '',
  newPassword_confirmation: '',
});

const handleChangePassword = async () => {
  passwordLoading.value = true;
  errors.value = {
    currentPassword: '',
    newPassword: '',
    newPassword_confirmation: '',
  };

  const res = await userStore.changePassword(passwordForm.value);

  if (res.success === true) {
    toast.success(res.message || 'Đổi mật khẩu thành công.');
    passwordForm.value.currentPassword = '';
    passwordForm.value.newPassword = '';
    passwordForm.value.newPassword_confirmation = '';
  } else {
    const responseErrors = res.errors || {};
    errors.value.currentPassword = responseErrors.currentPassword?.[0] || '';
    errors.value.newPassword = responseErrors.newPassword?.[0] || '';
    errors.value.newPassword_confirmation = responseErrors.newPassword_confirmation?.[0] || '';
    toast.error(res.message || 'Không thể đổi mật khẩu. Vui lòng thử lại.');
  }

  passwordLoading.value = false;
};
</script>

<template>
  <div class="profile-content">
    <div class="content-header">
      <h2 class="content-title">Đổi mật khẩu</h2>
      <p class="content-subtitle">Nhập mật khẩu hiện tại và mật khẩu mới để cập nhật.</p>
    </div>

    <form @submit.prevent="handleChangePassword" class="password-form">
      <div class="form-grid">
        <div class="form-group">
          <BaseInputPassword
            labelContent="Mật khẩu hiện tại"
            customId="currentPassword"
            customPlaceholderInput="Nhập mật khẩu hiện tại"
            v-model="passwordForm.currentPassword"
            :error="errors.currentPassword"
          />
        </div>

        <div class="form-group">
          <BaseInputPassword
            labelContent="Mật khẩu mới"
            customId="newPassword"
            customPlaceholderInput="Nhập mật khẩu mới"
            v-model="passwordForm.newPassword"
            :error="errors.newPassword"
          />
        </div>

        <div class="form-group">
          <BaseInputPassword
            labelContent="Xác nhận mật khẩu mới"
            customId="newPassword_confirmation"
            customPlaceholderInput="Nhập lại mật khẩu mới"
            v-model="passwordForm.newPassword_confirmation"
            :error="errors.newPassword_confirmation"
          />
        </div>
      </div>

      <div class="form-footer">
        <BaseButton
          customType="submit"
          customText="Lưu mật khẩu mới"
          customClass="btn btn-primary px-5"
          :disabled="passwordLoading"
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

.password-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
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
