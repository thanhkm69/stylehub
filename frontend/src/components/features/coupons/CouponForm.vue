<script setup>
import BaseModal from '@/components/base/BaseModal.vue'
import BaseForm from '@/components/base/BaseForm.vue'
import BaseInputText from '@/components/base/BaseInputText.vue'
import BaseInputSelect from '@/components/base/BaseInputSelect.vue'
import BaseInputTextarea from '@/components/base/BaseInputTextarea.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseSpinner from '@/components/base/BaseSpinner.vue'
import BaseInputNumber from '@/components/base/BaseInputNumber.vue'

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({})
    },
    statusMap: Array,
    discountTypes: Array,
})

const emit = defineEmits(["close", "submit"])

const isShow = defineModel("isShow")
const dataForm = defineModel("dataForm")
const loadingSubmit = defineModel("loadingSubmit")
</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" :title="dataForm.id ? 'Cập nhật mã giảm giá' : 'Thêm mã giảm giá mới'" customWidth="860px">
        <BaseForm @handleSubmit="emit('submit')">
            <template #input>
                <div class="coupon-form-grid">
                    <!-- Cột trái: Thông tin chính -->
                    <div class="form-column">
                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-ticket"></i> Thông tin mã giảm giá</h4>

                            <BaseInputText labelContent="Mã giảm giá" customId="code" v-model="dataForm.code"
                                customPlaceholderInput="Ví dụ: SUMMER2024" :error="errors.code" />

                            <BaseInputText labelContent="Tên mã" customId="name" v-model="dataForm.name"
                                customPlaceholderInput="Ví dụ: Khuyến mãi mùa hè" :error="errors.name" />

                            <div class="two-col-grid">
                                <BaseInputSelect labelContent="Loại giảm giá" v-model="dataForm.discount_type"
                                    :values="discountTypes" :error="errors.discount_type" />
                                <BaseInputNumber labelContent="Giá trị giảm" v-model="dataForm.discount_value"
                                    :error="errors.discount_value" />
                            </div>

                            <div class="two-col-grid">
                                <BaseInputNumber labelContent="Giảm tối đa (VNĐ)" v-model="dataForm.max_discount_amount"
                                    :error="errors.max_discount_amount" />
                                <BaseInputNumber labelContent="Đơn tối thiểu (VNĐ)" v-model="dataForm.min_order_value"
                                    :error="errors.min_order_value" />
                            </div>

                            <div class="two-col-grid">
                                <BaseInputNumber labelContent="Tổng lượt dùng" v-model="dataForm.usage_limit"
                                    :error="errors.usage_limit" />
                                <BaseInputNumber labelContent="Lượt/người" v-model="dataForm.usage_limit_per_user"
                                    :error="errors.usage_limit_per_user" />
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Thời gian & Cấu hình -->
                    <div class="form-column secondary-column">
                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-calendar"></i> Thời gian hiệu lực</h4>

                            <div class="form-group">
                                <label class="form-label">Ngày bắt đầu</label>
                                <input type="datetime-local" class="form-control" v-model="dataForm.starts_at">
                                <span v-if="errors.starts_at" class="form-error">{{ errors.starts_at }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Ngày kết thúc</label>
                                <input type="datetime-local" class="form-control" v-model="dataForm.expires_at">
                                <span v-if="errors.expires_at" class="form-error">{{ errors.expires_at }}</span>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-sliders"></i> Cấu hình</h4>
                            <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status"
                                :values="statusMap" :error="errors.status" />
                        </div>

                        <div class="form-section no-border">
                            <h4 class="section-subtitle"><i class="ph-bold ph-text-align-left"></i> Mô tả</h4>
                            <BaseInputTextarea labelContent="" customId="description" v-model="dataForm.description"
                                customPlaceholderInput="Mô tả điều kiện áp dụng..." />
                        </div>
                    </div>
                </div>
            </template>

            <template #button>
                <div class="form-actions">
                    <BaseButton @click="emit('close')" customType="button" customText="Hủy bỏ" customClass="btn-cancel" />
                    <BaseButton v-if="!loadingSubmit" customType="submit"
                        :customText="dataForm.id ? 'Lưu thay đổi' : 'Tạo mã giảm giá'"
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
.coupon-form-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 32px;
    padding: 8px 0;
}

.form-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.secondary-column {
    padding-left: 32px;
    border-left: 1px solid #f1f5f9;
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

.two-col-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 14px;
    color: var(--text-main);
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background-color: var(--background);
    color: var(--text-main);
    font-size: 14px;
    box-sizing: border-box;
    transition: border-color 0.2s;
}

.form-control:focus {
    border-color: var(--primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-error {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: var(--danger);
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

@media (max-width: 849px) {
    .coupon-form-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .secondary-column {
        padding-left: 0;
        border-left: none;
        border-top: 1px solid #f1f5f9;
        padding-top: 24px;
    }
}
</style>
