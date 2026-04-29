<script setup>
import BaseForm from '@/components/base/BaseForm.vue'
import BaseInputEmail from '@/components/base/BaseInputEmail.vue'
import BaseInputPassword from '@/components/base/BaseInputPassword.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseSpinner from '@/components/base/BaseSpinner.vue'
import BaseOTP from '@/components/base/BaseOTP.vue'
import { ref } from 'vue'
import { useForgotStore } from '@/stores/forgot'
import { useToast } from 'vue-toastification'
import { useRouter } from 'vue-router'

const forgotStore = useForgotStore()
const toast = useToast()
const router = useRouter()

const step = ref(1)
const loading = ref(false)

const expiresAt = parseInt(import.meta.env.VITE_EXPIRES_AT || 5) * 60
const otpRef = ref(null)

const form = ref({
    email: '',
    otp: '',
    newPassword: '',
    newPassword_confirmation: '',
})

const error = ref({
    email: '',
    otp: '',
    newPassword: '',
    newPassword_confirmation: '',
})

// ===== SEND OTP =====
const sendOtp = async () => {
    loading.value = true
    const res = await forgotStore.forgot(form.value)

    if (res.success) {
        step.value = 2
        toast.success(res.message)
    } else {
        error.value.email = res.errors?.email?.[0] ?? ''
        toast.error(res.message || "Đã có lỗi xảy ra")
    }

    loading.value = false
}

// ===== RESEND OTP =====
const sendResendOtp = async () => {
    loading.value = true

    const res = await forgotStore.forgot(form.value)

    if (res.success) {
        toast.success(res.message)
        otpRef.value?.startCountdown()
    } else {
        error.value.email = res.errors?.email?.[0] ?? ''
        toast.error(res.message || "Đã có lỗi xảy ra")
    }

    loading.value = false
}

// ===== VERIFY OTP =====
const verifyOtp = async () => {
    loading.value = true

    const res = await forgotStore.verifyOtp(form.value)

    if (res.success) {
        step.value = 3
        toast.success(res.message)
    } else {
        error.value.otp = res.errors?.otp?.[0] ?? ''
        toast.error(res.message || "Đã có lỗi xảy ra")
    }

    loading.value = false
}

// ===== RESET PASSWORD =====
const resetPassword = async () => {
    loading.value = true

    const res = await forgotStore.resetPassword(form.value)

    if (res.success) {
        step.value = 1
        toast.success(res.message)
        router.push({ name: 'Login' })
    } else {
        error.value.newPassword = res.errors?.newPassword?.[0] ?? ''
        error.value.newPassword_confirmation =
            res.errors?.newPassword_confirmation?.[0] ?? ''
        error.value.otp = res.errors?.otp?.[0] ?? ''
        toast.error(res.message || "Đã có lỗi xảy ra")
    }

    loading.value = false
}

</script>

<template>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Quên mật khẩu</h2>
                <p v-if="step === 1">Nhập email để nhận mã OTP khôi phục</p>
                <p v-if="step === 2">Nhập mã OTP đã được gửi đến email của bạn</p>
                <p v-if="step === 3">Tạo mật khẩu mới cho tài khoản của bạn</p>
            </div>

            <!-- STEP 1: NHẬP EMAIL -->
            <BaseForm v-if="step === 1" @handleSubmit="sendOtp">
                <template #input>
                    <BaseInputEmail v-model="form.email" customId="email" customPlaceholderInput="Nhập địa chỉ email"
                        :isRequired="true" :error="error.email" />
                </template>
                <template #button>
                    <BaseButton v-if="!loading" customText="Gửi OTP" customClass="btn btn-primary btn-full" customType="submit" />
                    <div class="spinner-wrapper" v-else>
                        <BaseSpinner />
                    </div>
                </template>
            </BaseForm>

            <!-- STEP 2: NHẬP OTP -->
            <div v-if="step === 2">
                <BaseForm @handleSubmit="verifyOtp">
                    <template #input>
                        <BaseOTP ref="otpRef" v-model="form.otp" :time="expiresAt" :error="error.otp" @resend="sendResendOtp" />
                    </template>
                    <template #button>
                        <BaseButton v-if="!loading" customText="Xác nhận OTP" customClass="btn btn-primary btn-full" customType="submit" />
                        <div class="spinner-wrapper" v-else>
                            <BaseSpinner />
                        </div>
                    </template>
                </BaseForm>
            </div>

            <!-- STEP 3: RESET PASSWORD -->
            <BaseForm v-if="step === 3" @handleSubmit="resetPassword">
                <template #input>
                    <BaseInputPassword v-model="form.newPassword" customId="newPassword"
                        customPlaceholderInput="Nhập mật khẩu mới" :isRequired="true"
                        :error="error.newPassword" />
                    <BaseInputPassword v-model="form.newPassword_confirmation" customId="newPassword_confirmation"
                        customPlaceholderInput="Xác nhận mật khẩu mới" :isRequired="true"
                        :error="error.newPassword_confirmation" />
                </template>
                <template #button>
                    <BaseButton v-if="!loading" customText="Đặt lại mật khẩu" customClass="btn btn-primary btn-full" customType="submit" />
                    <div class="spinner-wrapper" v-else>
                        <BaseSpinner />
                    </div>
                </template>
            </BaseForm>

            <div class="auth-links">
                <RouterLink to="/login" class="auth-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -3px; margin-right: 4px">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    Quay lại đăng nhập
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 400px);
    padding: 60px 24px;
    background-color: var(--background);
}

.auth-card {
    background: var(--surface);
    width: 100%;
    max-width: 480px;
    padding: 48px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border);
}

.auth-header {
    text-align: center;
    margin-bottom: 32px;
}

.auth-header h2 {
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -0.5px;
    color: var(--text-main);
    margin-bottom: 8px;
}

.auth-header p {
    color: var(--text-muted);
    font-size: 15px;
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

.auth-links {
    text-align: center;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
}

.auth-link {
    color: var(--text-muted);
    font-size: 15px;
    font-weight: 500;
    transition: var(--transition);
}

.auth-link:hover {
    color: var(--primary);
    text-decoration: underline;
}

@media (max-width: 576px) {
    .auth-card {
        padding: 32px 24px;
        border-radius: var(--radius-md);
    }
}
</style>