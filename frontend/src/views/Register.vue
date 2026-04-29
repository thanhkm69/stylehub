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
    <div class="auth-page">
        <div class="auth-card">
            
            <!-- Trái: Oauth -->
            <div class="auth-left">
                <div class="auth-header left-align hide-on-mobile">
                    <h2>Đăng ký tài khoản</h2>
                    <p>Khám phá thế giới thời trang cùng StyleHub</p>
                </div>
                
                <div class="oauth-title">Đăng ký nhanh bằng</div>
                <BaseOuth />
                
                <div class="auth-links mt-auto hide-on-mobile">
                    <RouterLink to="/login" class="auth-link">
                        Đã có tài khoản? <strong>Đăng nhập ngay</strong>
                    </RouterLink>
                </div>
            </div>

            <!-- Phải: Form -->
            <div class="auth-right">
                <div class="auth-header mobile-only">
                    <h2>Đăng ký tài khoản</h2>
                    <p>Khám phá thế giới thời trang cùng StyleHub</p>
                </div>

                <div class="form-title">Đăng ký bằng Email</div>

                <BaseForm @handleSubmit="handleSubmit">
                    <template #input>
                        <BaseInputText label-content="Tên" custom-id="name" customPlaceholderInput="Nhập họ và tên"
                            v-model="formData.name" :error="errors.name" />
                        <BaseInputEmail label-content="Email" custom-id="email" customPlaceholderInput="Nhập địa chỉ email"
                            v-model="formData.email" :error="errors.email" />
                        <BaseInputPassword label-content="Mật khẩu" custom-id="password" customPlaceholderInput="Tạo mật khẩu"
                            v-model="formData.password" :error="errors.password" />
                        <BaseInputPassword label-content="Xác nhận mật khẩu" custom-id="password_confirm"
                            customPlaceholderInput="Nhập lại mật khẩu" v-model="formData.password_confirm"
                            :error="errors.password_confirm" />
                        <BaseOTP v-if="step === 2 && !loading" ref="otpRef" v-model="formData.otp" :time="expiresAt"
                            :error="errors.otp" @resend="verify" />
                    </template>
                    <template #button>
                        <BaseButton v-if="step === 1 && !loading" customType="button" customText="Đăng ký" customClass="btn btn-primary btn-full"
                            @click="verify" />
                        <BaseButton v-if="step === 2 && !loading" customType="submit" customText="Xác nhận"
                            customClass="btn btn-primary btn-full" />
                        <div class="spinner-wrapper" v-if="loading">
                            <BaseSpinner />
                        </div>
                    </template>
                </BaseForm>
                
                <div class="auth-links mt-auto mobile-only" style="margin-top: 32px">
                    <RouterLink to="/login" class="auth-link">
                        Đã có tài khoản? <strong>Đăng nhập ngay</strong>
                    </RouterLink>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.auth-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 200px);
    padding: 60px 24px;
    background-color: var(--background);
}

.auth-card {
    background: var(--surface);
    width: 100%;
    max-width: 900px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
    display: flex;
    overflow: hidden;
}

.auth-left {
    flex: 1;
    padding: 48px;
    background-color: var(--accent);
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border);
}

.auth-right {
    flex: 1.2;
    padding: 48px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-header {
    margin-bottom: 32px;
}

.left-align {
    text-align: left;
}

.auth-header h2 {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -0.5px;
    color: var(--text-main);
    margin-bottom: 8px;
}

.auth-header p {
    color: var(--text-muted);
    font-size: 15px;
}

.oauth-title, .form-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

:deep(.btn-full) {
    width: 100%;
    margin-top: 8px;
}

.spinner-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 16px;
}

.mt-auto {
    margin-top: auto;
    padding-top: 32px;
}

.auth-links {
    text-align: center;
}

.auth-link {
    color: var(--text-muted);
    font-size: 15px;
    transition: var(--transition);
}

.auth-link strong {
    color: var(--primary);
    font-weight: 600;
}

.auth-link:hover strong {
    text-decoration: underline;
}

.mobile-only {
    display: none;
}

@media (max-width: 768px) {
    .auth-card {
        flex-direction: column;
        max-width: 480px;
    }
    
    .auth-left {
        border-right: none;
        border-bottom: 1px solid var(--border);
        padding: 32px 24px;
    }
    
    .auth-right {
        padding: 32px 24px;
    }
    
    .hide-on-mobile {
        display: none !important;
    }
    
    .mobile-only {
        display: block;
        text-align: center;
    }
}
</style>