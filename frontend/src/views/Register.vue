<script setup>

import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputPassword from '@/components/base/BaseInputPassword.vue';
import BaseInputEmail from '@/components/base/BaseInputEmail.vue';
import BaseOuth from '@/components/base/BaseOuth.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseOTP from '@/components/base/BaseOTP.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import { useNotify } from '@/composables/useNotify'
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import { ref } from 'vue';

const authStore = useAuthStore()
const toast = useNotify()
const router = useRouter()

const loading = ref(false)

const expiresAt = parseInt(import.meta.env.VITE_EXPIRES_AT || 5) * 60
const otpRef = ref(null)

const formData = ref({
    name: '',
    email: '',
    password: '',
    password_confirm: '',
    otp: ''
})

const errors = ref({
    name: '',
    email: '',
    password: '',
    password_confirm: '',
    otp: ''
})

const step = ref(1)

const validate = () => {
    let isValid = true;
    let newErrors = {
        name: '',
        email: '',
        password: '',
        password_confirm: '',
        otp: ''
    };

    if (!formData.value.name) {
        newErrors.name = 'Tên bắt buộc phải nhập';
        isValid = false;
    } else if (formData.value.name.length > 255) {
        newErrors.name = 'Tên không được vượt quá 255 ký tự';
        isValid = false;
    }

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

    if (!formData.value.password_confirm) {
        newErrors.password_confirm = 'Xác nhận mật khẩu bắt buộc phải nhập';
        isValid = false;
    } else if (formData.value.password_confirm.length < 8) {
        newErrors.password_confirm = 'Xác nhận mật khẩu phải ít nhất 8 ký tự';
        isValid = false;
    } else if (formData.value.password_confirm.length > 25) {
        newErrors.password_confirm = 'Xác nhận mật khẩu không được vượt quá 25 ký tự';
        isValid = false;
    } else if (formData.value.password_confirm !== formData.value.password) {
        newErrors.password_confirm = 'Xác nhận mật khẩu không khớp';
        isValid = false;
    }

    if (step.value === 2) {
        if (!formData.value.otp) {
            newErrors.otp = 'Mã OTP bắt buộc phải nhập';
            isValid = false;
        } else if (formData.value.otp.length !== 6) {
            newErrors.otp = 'Mã OTP phải có 6 ký tự';
            isValid = false;
        }
    }

    errors.value = newErrors;
    return isValid;
};

const verify = async () => {
    if (!validate()) {
        return;
    }
    loading.value = true
    const isResend = step.value === 2;
    const result = await authStore.verify({ email: formData.value.email })
    if (result.success === true) {
        toast.success(result.message)
        if (!isResend) {
            step.value = 2
        } else {
            otpRef.value?.startCountdown()
        }
    } else {
        toast.error(result?.message || "Lỗi khi gửi OTP để xác nhận tài khoản")
        errors.value.email = result?.errors?.email?.[0] ?? ""
    }
    loading.value = false
}

const handleSubmit = async () => {
    if (!validate() || step.value === 1) {
        return;
    }
    loading.value = true
    const result = await authStore.register(formData.value)
    if (result.success === true) {
        toast.success(result.message)
        router.push({ name: 'Home' })
    } else {
        toast.error(result?.message || "Lỗi khi đăng ký")
        errors.value = {
            name: result?.errors?.name?.[0] ?? "",
            email: result?.errors?.email?.[0] ?? "",
            password: result?.errors?.password?.[0] ?? "",
            password_confirm: result?.errors?.password_confirm?.[0] ?? "",
            otp: result?.errors?.otp?.[0] ?? ""
        }
    }
    loading.value = false
}
</script>

<template>
   <BaseForm @handleSubmit="handleSubmit">
        <template #input>
            <BaseInputText label-content="Tên" custom-id="name" customPlaceholderInput="Nhập tên"
                v-model="formData.name" :error="errors.name" />
            <BaseInputEmail label-content="Email" custom-id="email" customPlaceholderInput="Nhập email"
                v-model="formData.email" :error="errors.email" />
            <BaseInputPassword label-content="Mật khẩu" custom-id="password" customPlaceholderInput="Nhập mật khẩu"
                v-model="formData.password" :error="errors.password" />
            <BaseInputPassword label-content="Xác nhận mật khẩu" custom-id="password_confirm"
                customPlaceholderInput="Nhập lại mật khẩu" v-model="formData.password_confirm"
                :error="errors.password_confirm" />
            <BaseOTP v-if="step === 2 && !loading" ref="otpRef" v-model="formData.otp" :time="expiresAt"
                :error="errors.otp" @resend="verify" />
        </template>
        <template #button>
            <BaseButton v-if="step === 1 && !loading" customType="button" customText="Đăng ký" customClass="btn btn-primary"
                @click="verify" />
            <BaseButton v-if="step === 2 && !loading" customType="submit" customText="Xác nhận"
                customClass="btn btn-primary" />
            <BaseSpinner v-if="loading" />
        </template>
    </BaseForm>
    <BaseOuth />

    <div class="auth-links">
        <RouterLink to="/login" class="auth-link">
            Đã có tài khoản? <strong>Đăng nhập</strong>
        </RouterLink>
    </div>
</template>

<style scoped>
</style>