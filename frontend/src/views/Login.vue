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
    <div class="auth-page">
        <div class="auth-card">
            
            <!-- Trái: Oauth -->
            <div class="auth-left">
                <div class="auth-header left-align hide-on-mobile">
                    <h2>Đăng nhập</h2>
                    <p>Chào mừng bạn trở lại với StyleHub</p>
                </div>
                
                <div class="oauth-title">Đăng nhập nhanh bằng</div>
                <BaseOuth />
                
                <div class="auth-links mt-auto hide-on-mobile">
                    <RouterLink to="/register" class="auth-link">
                        Chưa có tài khoản? <strong>Đăng ký ngay</strong>
                    </RouterLink>
                </div>
            </div>

            <!-- Phải: Form -->
            <div class="auth-right">
                <div class="auth-header mobile-only">
                    <h2>Đăng nhập</h2>
                    <p>Chào mừng bạn trở lại với StyleHub</p>
                </div>

                <div class="form-title">Đăng nhập bằng Email</div>

                <BaseForm @handleSubmit="handleSubmit">
                    <template #input>
                        <BaseInputEmail label-content="Email" custom-id="email" customPlaceholderInput="Nhập địa chỉ email"
                            v-model="formData.email" :error="errors.email" />
                        <BaseInputPassword label-content="Mật khẩu" custom-id="password" customPlaceholderInput="Nhập mật khẩu"
                            v-model="formData.password" :error="errors.password" />
                            
                        <div class="login-extras">
                            <RouterLink to="/forgot" class="forgot-link">
                                Quên mật khẩu?
                            </RouterLink>
                        </div>
                    </template>
                    <template #button>
                        <BaseButton v-if="!loading" type="submit" customText="Đăng nhập" customClass="btn btn-primary btn-full" />
                        <div class="spinner-wrapper" v-if="loading">
                            <BaseSpinner />
                        </div>
                    </template>
                </BaseForm>
                
                <div class="auth-links mt-auto mobile-only" style="margin-top: 32px">
                    <RouterLink to="/register" class="auth-link">
                        Chưa có tài khoản? <strong>Đăng ký ngay</strong>
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

.login-extras {
    display: flex;
    justify-content: flex-end;
    margin-top: -8px;
    margin-bottom: 24px;
}

.forgot-link {
    font-size: 14px;
    font-weight: 500;
    color: var(--primary);
    transition: var(--transition);
}

.forgot-link:hover {
    text-decoration: underline;
}

:deep(.btn-full) {
    width: 100%;
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