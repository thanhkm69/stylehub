<script setup>
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseInputPassword from '@/components/base/BaseInputPassword.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({})
    },
    statusMap: Array,
    roleMap: Array
})

const emit = defineEmits(["close", "submit"])

const isShow = defineModel("isShow")
const dataForm = defineModel("dataForm")
const loadingSubmit = defineModel("loadingSubmit")

</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow"
        :title="dataForm.id ? 'Cập nhật người dùng' : 'Thêm người dùng mới'" customWidth="600px">
        <BaseForm @handleSubmit="emit('submit')">
            <template #input>
                <div class="user-form-grid">
                    <div class="form-section">
                        <h4 class="section-subtitle"><i class="ph-bold ph-user"></i> Thông tin cơ bản</h4>

                        <BaseInputText labelContent="Họ tên" customId="name" v-model="dataForm.name"
                            customPlaceholderInput="Ví dụ: Nguyễn Văn A" :error="errors.name" />

                        <BaseInputText labelContent="Email" customId="email" v-model="dataForm.email"
                            customPlaceholderInput="Ví dụ: user@email.com" :error="errors.email" />

                        <!-- Chỉ hiển thị khi chỉnh sửa, mật khẩu mới sẽ gửi qua email -->
                        <template v-if="dataForm.id">
                            <BaseInputPassword labelContent="Mật khẩu mới (tuỳ chọn)" customId="password"
                                v-model="dataForm.password"
                                customPlaceholderInput="Để trống nếu không đổi mật khẩu" :error="errors.password" />
                            <p class="password-hint">
                                <i class="ph ph-info"></i> Nếu nhập mật khẩu mới, hệ thống sẽ gửi email thông báo cho người dùng.
                            </p>
                        </template>

                        <!-- Thông báo khi thêm mới -->
                        <div v-else class="password-auto-note">
                            <i class="ph-bold ph-envelope-simple"></i>
                            <span>Mật khẩu sẽ được tạo tự động và gửi về email người dùng.</span>
                        </div>
                    </div>

                    <div class="form-section no-border">
                        <h4 class="section-subtitle"><i class="ph-bold ph-sliders"></i> Cấu hình</h4>
                        <div class="config-grid">
                            <BaseInputSelect labelContent="Vai trò" v-model="dataForm.role" :values="roleMap"
                                placeholder="Chọn vai trò" :error="errors.role" />
                            <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status" :values="statusMap"
                                :error="errors.status" />
                        </div>
                    </div>
                </div>
            </template>

            <template #button>
                <div class="form-actions">
                    <BaseButton @click="emit('close')" customType="button" customText="Hủy bỏ"
                        customClass="btn-cancel" />
                    <BaseButton v-if="!loadingSubmit" customType="submit"
                        :customText="dataForm.id ? 'Lưu thay đổi' : 'Tạo người dùng'"
                        customClass="btn btn-primary px-5" :disabled="loadingSubmit" />
                    <div v-else class="loading-submit">
                        <BaseSpinner size="sm" />
                    </div>
                </div>
            </template>
        </BaseForm>
    </BaseModal>
</template>

<style scoped>
.user-form-grid {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 8px 0;
}

.form-section {
    padding-bottom: 8px;
}

.section-subtitle {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-subtitle i {
    color: var(--primary);
    font-size: 18px;
}

.password-hint {
    font-size: 12px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
}

.password-auto-note {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    color: #1d4ed8;
    font-size: 13px;
    font-weight: 500;
    margin-top: 4px;
}

.password-auto-note i {
    font-size: 20px;
    flex-shrink: 0;
}

.config-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    padding-top: 24px;
    margin-top: 8px;
    border-top: 1px solid #f1f5f9;
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
    font-weight: 600;
    padding: 10px 24px;
    border-radius: 12px;
}

.btn-cancel:hover {
    background: #e2e8f0;
}

.loading-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 40px;
}

@media (max-width: 600px) {
    .config-grid {
        grid-template-columns: 1fr;
    }
}
</style>
