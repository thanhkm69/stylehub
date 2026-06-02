<script setup>
import { watch, ref } from 'vue';
import { useSlug } from '@/composables/useSlug';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseInputTextarea from '@/components/base/BaseInputTextarea.vue';
import BaseInputFile from '@/components/base/BaseInputFile.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({})
    },
    statusMap: Array,
    categories: Array
})

const emit = defineEmits(["close", "submit", "handleImageChange"])

const isShow = defineModel("isShow")
const dataForm = defineModel("dataForm")
const loadingSubmit = defineModel("loadingSubmit")

const { generateSlug } = useSlug()

watch(
    () => dataForm.value.title,
    (newTitle) => {
        if (newTitle) {
            dataForm.value.slug = generateSlug(newTitle)
        } else if (!newTitle) {
            dataForm.value.slug = ''
        }
    }
)
</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" :title="dataForm.id ? 'Cập nhật bài viết' : 'Thêm bài viết mới'" customWidth="900px">
        <BaseForm @handleSubmit="emit('submit')">
            <template #input>
                <div class="product-form-grid">
                    <!-- Cột trái: Thông tin chính -->
                    <div class="form-column">
                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-info"></i> Thông tin cơ bản</h4>
                            
                            <BaseInputSelect labelContent="Danh mục" v-model="dataForm.blog_category_id"
                                :values="categories" placeholder="Chọn danh mục" :error="errors.blog_category_id" />

                            <BaseInputText labelContent="Tiêu đề" customId="title" v-model="dataForm.title" customPlaceholderInput="Nhập tiêu đề bài viết"
                                :error="errors.title" />

                            <BaseInputText labelContent="Slug (Đường dẫn)" customId="slug" v-model="dataForm.slug" customPlaceholderInput="tieu-de-bai-viet" :error="errors.slug" />
                            
                            <BaseInputTextarea labelContent="Tóm tắt" customId="summary" v-model="dataForm.summary" customPlaceholderInput="Đoạn tóm tắt ngắn gọn..." :error="errors.summary" />

                            <div class="quill-editor-container">
                                <label class="form-label fw-bold" style="font-size: 14px; font-weight: 500; margin-bottom: 8px; display: block; color: var(--text-main);">Nội dung</label>
                                <QuillEditor theme="snow" v-model:content="dataForm.content" contentType="html" style="height: 300px;" />
                                <div v-if="errors.content" class="error-message" style="color: #ef4444; font-size: 13px; margin-top: 4px;">{{ errors.content[0] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Hình ảnh, Cấu hình và SEO -->
                    <div class="form-column secondary-column">
                        <div class="form-section">
                            <h4 class="section-subtitle"><i class="ph-bold ph-image"></i> Ảnh bìa bài viết</h4>
                            
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
                            <h4 class="section-subtitle"><i class="ph-bold ph-sliders"></i> Cấu hình xuất bản</h4>
                            <div class="config-grid">
                                <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status" :values="statusMap" :error="errors.status" />
                            </div>
                        </div>

                        <div class="form-section no-border">
                            <h4 class="section-subtitle"><i class="ph-bold ph-magnifying-glass"></i> Cấu hình SEO</h4>
                            
                            <BaseInputText labelContent="Meta Title" customId="meta_title" v-model="dataForm.meta_title" customPlaceholderInput="Tiêu đề SEO..." :error="errors.meta_title" />

                            <BaseInputTextarea labelContent="Meta Description" customId="meta_description" v-model="dataForm.meta_description" customPlaceholderInput="Mô tả SEO..." :error="errors.meta_description" />

                            <BaseInputText labelContent="Meta Keywords" customId="meta_keywords" v-model="dataForm.meta_keywords" customPlaceholderInput="từ khóa 1, từ khóa 2..." :error="errors.meta_keywords" />
                        </div>
                    </div>
                </div>
            </template>

            <template #button>
                <div class="form-actions">
                    <BaseButton @click="emit('close')" customType="button" customText="Hủy bỏ" customClass="btn-cancel" />
                    <BaseButton v-if="!loadingSubmit" customType="submit" :customText="dataForm.id ? 'Lưu thay đổi' : 'Đăng bài viết'" customClass="btn btn-primary px-5" :disabled="loadingSubmit" />
                    <div v-else class="loading-submit">
                        <BaseSpinner size="sm" />
                    </div>
                </div>
            </template>
        </BaseForm>
    </BaseModal>
</template>

<style scoped>
.product-form-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
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
    border-left: 1px solid var(--border);
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
    background: var(--muted);
}

.image-preview-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-placeholder {
    width: 100%;
    height: 180px;
    border-radius: 16px;
    border: 2px dashed var(--border);
    background: var(--muted);
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
    grid-template-columns: 1fr;
    gap: 16px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    padding-top: 24px;
    margin-top: 8px;
    border-top: 1px solid var(--border);
}

.btn-cancel {
    background: var(--muted);
    color: var(--text-muted);
    font-weight: 600;
    padding: 10px 24px;
    border-radius: 12px;
}

.btn-cancel:hover {
    background: var(--accent);
}

.loading-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 40px;
}

@media (max-width: 849px) {
    .product-form-grid {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    
    .secondary-column {
        padding-left: 0;
        border-left: none;
        border-top: 1px solid var(--border);
        padding-top: 24px;
    }
}

.quill-editor-container {
    margin-top: 8px;
}

:deep(.ql-container) {
    background: var(--surface);
    border-color: var(--border);
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    color: var(--text-main);
    font-family: inherit;
    font-size: 15px;
}

:deep(.ql-toolbar) {
    border-color: var(--border);
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    background-color: var(--muted);
}

:deep(.ql-editor.ql-blank::before) {
    color: var(--text-muted);
}

:deep(.ql-snow .ql-stroke) {
    stroke: var(--text-muted);
}

:deep(.ql-snow .ql-fill),
:deep(.ql-snow .ql-stroke.ql-fill) {
    fill: var(--text-muted);
}

:deep(.ql-snow .ql-picker) {
    color: var(--text-muted);
}

:deep(.ql-snow .ql-picker-options) {
    background: var(--surface);
    border-color: var(--border);
}

:deep(.ql-snow button:hover .ql-stroke),
:deep(.ql-snow button.ql-active .ql-stroke) {
    stroke: var(--text-main);
}

:deep(.ql-snow button:hover .ql-fill),
:deep(.ql-snow button.ql-active .ql-fill) {
    fill: var(--text-main);
}
</style>
