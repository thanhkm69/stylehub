<script setup>
import BaseModal from '@/components/base/BaseModal.vue'
import BaseForm from '@/components/base/BaseForm.vue'
import BaseInputText from '@/components/base/BaseInputText.vue'
import BaseInputSelect from '@/components/base/BaseInputSelect.vue'
import BaseInputTextarea from '@/components/base/BaseInputTextarea.vue'
import BaseInputFile from '@/components/base/BaseInputFile.vue'
import BaseInputNumber from '@/components/base/BaseInputNumber.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseSpinner from '@/components/base/BaseSpinner.vue'

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({})
    },
    statusMap: Array,
})

const emit = defineEmits(["close", "submit", "handleImageChange"])

const isShow = defineModel("isShow")
const dataForm = defineModel("dataForm")
const loadingSubmit = defineModel("loadingSubmit")
</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow"
        :title="dataForm.id ? 'Cập nhật Flash Sale' : 'Thêm Flash Sale mới'" customWidth="860px">
        <BaseForm @handleSubmit="emit('submit')">
            <template #input>
                <div class="flash-sale-form-grid">
                    <!-- Cột trái: Thông tin chính -->
                    <div class="form-column">
                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-lightning"></i> Thông tin cơ bản</h4>

                            <BaseInputText labelContent="Tên Flash Sale" customId="name" v-model="dataForm.name"
                                customPlaceholderInput="Ví dụ: Flash Sale 12.12" :error="errors.name" />

                            <BaseInputTextarea labelContent="Mô tả" customId="description" v-model="dataForm.description"
                                customPlaceholderInput="Mô tả chương trình flash sale..." />
                        </div>

                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-calendar"></i> Thời gian diễn ra</h4>

                            <div class="form-group">
                                <label class="form-label">Ngày bắt đầu <span class="required">*</span></label>
                                <input type="datetime-local" class="form-control" v-model="dataForm.starts_at">
                                <span v-if="errors.starts_at" class="form-error">{{ errors.starts_at }}</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Ngày kết thúc <span class="required">*</span></label>
                                <input type="datetime-local" class="form-control" v-model="dataForm.ends_at">
                                <span v-if="errors.ends_at" class="form-error">{{ errors.ends_at }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Media & Cấu hình -->
                    <div class="form-column secondary-column">
                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-image"></i> Ảnh đại diện</h4>

                            <div class="image-upload-wrapper">
                                <div v-if="dataForm.preview" class="image-preview-large">
                                    <img :src="dataForm.preview" alt="Preview" />
                                    <div class="preview-overlay">Xem trước</div>
                                </div>
                                <div v-else class="image-placeholder">
                                    <i class="ph ph-image-square"></i>
                                    <p>Chưa có ảnh</p>
                                </div>

                                <div class="upload-input-container">
                                    <BaseInputFile labelContent="" customId="thumbnail" :error="errors.thumbnail"
                                        customAccept="image/*"
                                        @change="(event) => emit('handleImageChange', event)" />
                                    <p class="upload-hint">Định dạng: JPG, PNG, WEBP. Tối đa 5MB.</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-section no-border">
                            <h4 class="section-subtitle"><i class="ph-bold ph-sliders"></i> Cấu hình hiển thị</h4>
                            <div class="config-grid">
                                <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status"
                                    :values="statusMap" :error="errors.status" />
                                <BaseInputNumber labelContent="Thứ tự hiển thị" v-model="dataForm.display"
                                    :error="errors.display" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #button>
                <div class="form-actions">
                    <BaseButton @click="emit('close')" customType="button" customText="Hủy bỏ" customClass="btn-cancel" />
                    <BaseButton v-if="!loadingSubmit" customType="submit"
                        :customText="dataForm.id ? 'Lưu thay đổi' : 'Tạo Flash Sale'"
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
.flash-sale-form-grid {
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
.required { color: var(--danger); }
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
.image-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.image-preview-large {
    width: 100%;
    height: 180px;
    border-radius: 16px;
    overflow: hidden;
    border: 2px dashed var(--border);
    position: relative;
    background: #f8fafc;
}
.image-preview-large img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.image-placeholder {
    width: 100%;
    height: 180px;
    border-radius: 16px;
    border: 2px dashed var(--border);
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    gap: 8px;
}
.image-placeholder i { font-size: 40px; }
.preview-overlay {
    position: absolute;
    top: 12px; right: 12px;
    background: rgba(0,0,0,0.5);
    color: white;
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 20px;
    backdrop-filter: blur(4px);
}
.upload-hint {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 8px;
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
.btn-cancel:hover { background: #e2e8f0; }
.loading-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 40px;
}
@media (max-width: 849px) {
    .flash-sale-form-grid {
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
