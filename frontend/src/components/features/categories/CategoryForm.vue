<script setup>
import { computed } from 'vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseInputTextarea from '@/components/base/BaseInputTextarea.vue';
import BaseInputFile from '@/components/base/BaseInputFile.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import BaseInputNumber from '@/components/base/BaseInputNumber.vue';

const props = defineProps({
    categoryIds: {
        type: Array,
        default: () => []
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    statusMap: Array
})

const emit = defineEmits(["close", "submit", "handleImageChange"])

const isShow = defineModel("isShow")
const dataForm = defineModel("dataForm")
const loadingSubmit = defineModel("loadingSubmit")
const categories = defineModel("categories")

// Format categories for dropdown with tree indentation
const formattedCategories = computed(() => {
    return (categories.value || []).map(cat => ({
        ...cat,
        name: '  '.repeat(cat.level) + (cat.level > 0 ? '↳ ' : '') + cat.name
    }))
})

</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" :title="dataForm.id ? 'Cập nhật danh mục' : 'Thêm danh mục mới'" customWidth="850px">
        <BaseForm @handleSubmit="emit('submit')">
            <template #input>
                <div class="category-form-grid">
                    <!-- Cột trái: Thông tin chính -->
                    <div class="form-column">
                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-info"></i> Thông tin cơ bản</h4>
                            
                            <BaseInputSelect labelContent="Danh mục cha" :categoryIds="categoryIds" v-model="dataForm.parent_id"
                                :values="formattedCategories" placeholder="Chọn danh mục cha (không bắt buộc)" />

                            <BaseInputText labelContent="Tên danh mục" customId="name" v-model="dataForm.name" customPlaceholderInput="Ví dụ: Thời trang nam"
                                :error="errors.name" />

                            <BaseInputText labelContent="Slug (Đường dẫn)" customId="slug" v-model="dataForm.slug" customPlaceholderInput="thoi-trang-nam" :error="errors.slug" />
                        </div>

                        <div class="form-section no-border">
                            <h4 class="section-subtitle"><i class="ph-bold ph-sliders"></i> Cấu hình hiển thị</h4>
                            <div class="config-grid">
                                <BaseInputNumber labelContent="Thứ tự" v-model="dataForm.display" />
                                <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status" :values="statusMap" />
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Media & Mô tả -->
                    <div class="form-column secondary-column">
                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-image"></i> Hình ảnh đại diện</h4>
                            
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
                                    <BaseInputFile labelContent="" customId="image" :error="errors.image" customAccept="image/*"
                                        @change="(event) => emit('handleImageChange', event)" />
                                    <p class="upload-hint">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</p>
                                </div>
                            </div>
                        </div>

                        <div class="form-section no-border">
                            <h4 class="section-subtitle"><i class="ph-bold ph-text-align-left"></i> Mô tả chi tiết</h4>
                            <BaseInputTextarea labelContent="" customId="description" v-model="dataForm.description" customPlaceholderInput="Mô tả ngắn gọn về danh mục này để khách hàng dễ dàng tìm kiếm..." />
                        </div>
                    </div>
                </div>
            </template>

            <template #button>
                <div class="form-actions">
                    <BaseButton @click="emit('close')" customType="button" customText="Hủy bỏ" customClass="btn-cancel" />
                    <BaseButton v-if="!loadingSubmit" customType="submit" :customText="dataForm.id ? 'Lưu thay đổi' : 'Tạo danh mục'" customClass="btn btn-primary px-5" :disabled="loadingSubmit" />
                    <div v-else class="loading-submit">
                        <BaseSpinner size="sm" />
                    </div>
                </div>
            </template>
        </BaseForm>
    </BaseModal>
</template>

<style scoped>
.category-form-grid {
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

.image-placeholder i {
    font-size: 40px;
}

.preview-overlay {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(0, 0, 0, 0.5);
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
    .category-form-grid {
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