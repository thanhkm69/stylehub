<script setup>
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputEmail from '@/components/base/BaseInputEmail.vue';
import BaseInputPassword from '@/components/base/BaseInputPassword.vue';
import BaseOuth from '@/components/base/BaseOuth.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import { useNotify } from '@/composables/useNotify';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { ref } from 'vue';

const authStore = useAuthStore()
const toast = useNotify()
const router = useRouter()

const loading = ref(false)

const formData = ref({
    email: '',
    password: ''
})

const errors = ref({
    email: '',
    password: ''
})

const validate = () => {
    let isValid = true;
    let newErrors = {
        email: '',
        password: ''
    };

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!formData.value.email) {
        newErrors.email = 'Email bắt buộc phải nhập';
        isValid = false;
    } else if (!emailRegex.test(formData.value.email)) {
        newErrors.email = 'Email không đúng định dạng';
        isValid = false;
    } else if (formData.value.email.length > 255) {
        newErrors.email = 'Email không được vượt quá 255 ký tự';
        isValid = false;
    }

    if (!formData.value.password) {
        newErrors.password = 'Mật khẩu bắt buộc phải nhập';
        isValid = false;
    } else if (formData.value.password.length < 8) {
        newErrors.password = 'Mật khẩu phải ít nhất 8 ký tự';
        isValid = false;
    } else if (formData.value.password.length > 25) {
        newErrors.password = 'Mật khẩu không được vượt quá 25 ký tự';
        isValid = false;
    }

    errors.value = newErrors;
    return isValid;
};

const handleSubmit = async () => {
    if (!validate()) {
        return;
    }
    loading.value = true
    const result = await authStore.login(formData.value)
    if (result.success === true) {
        toast.success(result.message)
        router.push({ name: 'Home' })
    } else {
        toast.error(result?.message || "Lỗi khi đăng nhập")
        errors.value = {
            email: result?.errors?.email?.[0] ?? "",
            password: result?.errors?.password?.[0] ?? ""
        }
    }
    loading.value = false
}
</script>

<template>
    <BaseForm @handleSubmit="handleSubmit">
        <template #input>
            <BaseInputEmail label-content="Email" custom-id="email" customPlaceholderInput="Nhập email"
                v-model="formData.email" :error="errors.email" />
            <BaseInputPassword label-content="Mật khẩu" custom-id="password" customPlaceholderInput="Nhập mật khẩu"
                v-model="formData.password" :error="errors.password" />
        </template>
        <template #button>
            <BaseButton v-if="!loading" type="submit" customText="Đăng nhập" customClass="btn btn-primary" />
            <BaseSpinner v-else />
        </template>
    </BaseForm>

    <BaseOuth />

    <div class="login-extras">
        <RouterLink to="/forgot" class="forgot-link">
            Quên mật khẩu?
        </RouterLink>
    </div>
    <div class="auth-links">
        <RouterLink to="/register" class="auth-link">
            Chưa có tài khoản? <strong>Đăng ký ngay</strong>
        </RouterLink>
    </div>
</template>

<style scoped>
.login-extras {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1.25rem;
}

.forgot-link {
    font-size: 0.82rem;
    font-weight: 500;
}
</style>