<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { useTokenStore } from '@/stores/token'
import { useProfileStore } from '@/stores/profile'
import { useNotify } from '@/composables/useNotify'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseInputText from '@/components/base/BaseInputText.vue'
import BaseInputSelect from '@/components/base/BaseInputSelect.vue'
import BaseInputTextarea from '@/components/base/BaseInputTextarea.vue'

const tokenStore = useTokenStore()
const profileStore = useProfileStore()
const notify = useNotify()
const user = computed(() => tokenStore.user?.data)
const accountEmail = computed(() => user.value?.email || '')

const loading = ref(false)
const submitting = ref(false)
const errors = ref({})

const form = ref({
  full_name: '',
  email: '',
  phone: '',
  gender: null,
  date_of_birth: '',
  hobbies: '',
  occupation: '',
})

const genderOptions = [
  { id: 'male', name: 'Nam' },
  { id: 'female', name: 'Nữ' },
  { id: 'other', name: 'Khác' },
]

const initForm = () => {
  form.value = {
    full_name: user.value?.name || '',
    email: accountEmail.value,
    phone: '',
    gender: null,
    date_of_birth: '',
    hobbies: '',
    occupation: '',
  }
}

const loadProfile = async () => {
  loading.value = true
  initForm()

  const result = await profileStore.me()
  if (result?.success && result?.data) {
    form.value = {
      full_name: result.data.full_name || form.value.full_name,
      email: accountEmail.value || result.data.email || form.value.email,
      phone: result.data.phone || '',
      gender: result.data.gender,
      date_of_birth: result.data.date_of_birth || '',
      hobbies: result.data.hobbies || '',
      occupation: result.data.occupation || '',
    }
  } else if (!result?.success) {
    notify.error(result?.message || 'Không thể tải hồ sơ cá nhân')
  }

  loading.value = false
}

const handleUpdate = async () => {
  submitting.value = true
  errors.value = {}

  const result = await profileStore.updateCurrent({
    full_name: form.value.full_name,
    phone: form.value.phone,
    gender: form.value.gender,
    date_of_birth: form.value.date_of_birth,
    hobbies: form.value.hobbies,
    occupation: form.value.occupation,
  })

  if (!result?.success) {
    notify.error(result?.message || 'Cập nhật hồ sơ thất bại')
    if (result?.errors) {
      errors.value = result.errors
    }
  } else {
    notify.success(result?.message || 'Cập nhật hồ sơ thành công')
  }

  submitting.value = false
}

watch(user, (value) => {
  if (value) {
    form.value.full_name = form.value.full_name || value.name || ''
    form.value.email = value.email || ''
  }
})

onMounted(loadProfile)
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
            v-model="form.full_name"
            customPlaceholderInput="Nhập họ và tên"
            :error="errors.full_name"
          />
        </div>

        <div class="form-group">
          <BaseInputText
            labelContent="Địa chỉ Email"
            v-model="form.email"
            :disabled="true"
            customPlaceholderInput="email@example.com"
            :error="errors.email"
          />
          <small class="form-hint">Email không thể thay đổi vì lý do bảo mật.</small>
        </div>

        <div class="form-group">
          <BaseInputText
            labelContent="Số điện thoại"
            v-model="form.phone"
            customPlaceholderInput="0123 456 789"
            :error="errors.phone"
          />
        </div>

        <div class="form-group">
          <BaseInputSelect
            labelContent="Giới tính"
            customId="gender"
            v-model="form.gender"
            :values="genderOptions"
            placeholder="Chọn giới tính"
            :error="errors.gender"
          />
        </div>

        <div class="form-group">
          <BaseInputText
            labelContent="Ngày sinh"
            v-model="form.date_of_birth"
            customType="date"
            :error="errors.date_of_birth"
          />
        </div>

        <div class="form-group">
          <BaseInputTextarea
            labelContent="Sở thích"
            customId="hobbies"
            v-model="form.hobbies"
            customPlaceholderInput="Nhập sở thích của bạn"
            :error="errors.hobbies"
          />
        </div>

        <div class="form-group">
          <BaseInputText
            labelContent="Nghề nghiệp"
            v-model="form.occupation"
            customPlaceholderInput="Nhập nghề nghiệp"
            :error="errors.occupation"
          />
        </div>
      </div>

      <div class="form-footer">
        <BaseButton
          customType="submit"
          customText="Lưu thay đổi"
          customClass="btn btn-primary px-5"
          :disabled="submitting"
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

.content-header {
    border-bottom: 1px solid var(--border);
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

.profile-form,
.password-form {
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

.password-card {
    background-color: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 28px;
}

.password-card-header {
    margin-bottom: 24px;
}

.password-card-title {
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 700;
}

.password-card-desc {
    margin: 0;
    color: var(--text-muted);
    font-size: 14px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
