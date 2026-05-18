<script setup>
import { computed } from 'vue'
import BaseModal from '@/components/base/BaseModal.vue'
import BaseInputText from '@/components/base/BaseInputText.vue'
import BaseInputNumber from '@/components/base/BaseInputNumber.vue'
import BaseInputSelect from '@/components/base/BaseInputSelect.vue'
import BaseInputFile from '@/components/base/BaseInputFile.vue'

const props = defineProps({
    dataForm: Object,
    isShow: Boolean,
    errors: Object,
    loadingSubmit: Boolean,
    statusMap: Array
})

const emit = defineEmits([
    'update:isShow',
    'update:dataForm',
    'update:loadingSubmit',
    'submit',
    'close',
    'handleImageChange'
])

const isShowComputed = computed({
    get: () => props.isShow,
    set: (value) => emit('update:isShow', value)
})

const dataFormComputed = computed({
    get: () => props.dataForm,
    set: (value) => emit('update:dataForm', value)
})

const handleSubmit = () => {
    emit('submit')
}
</script>

<template>
    <BaseModal v-model:isShow="isShowComputed" :title="dataFormComputed.id ? 'Cập nhật Banner' : 'Thêm Banner mới'"
        customWidth="800px" @close="emit('close')">
        <div class="p-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Left Column -->
                    <div class="space-y-6">
                        <BaseInputText labelContent="Tiêu đề" customPlaceholderInput="Nhập tiêu đề banner"
                            v-model="dataFormComputed.title" :error="errors?.title" />

                        <BaseInputText labelContent="Đường dẫn (Link)" customPlaceholderInput="https://example.com"
                            v-model="dataFormComputed.link" :error="errors?.link" />

                        <div class="grid grid-cols-2 gap-4">
                            <BaseInputNumber labelContent="Vị trí (Thứ tự)" :customMin="0"
                                v-model="dataFormComputed.position" :error="errors?.position" />

                            <BaseInputSelect labelContent="Trạng thái" v-model="dataFormComputed.status"
                                :options="statusMap" :error="errors?.status" />
                        </div>
                    </div>

                    <!-- Right Column (Image) -->
                    <div class="space-y-4 form-section">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Hình ảnh <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="image-upload-wrapper">
                            <div v-if="dataFormComputed.preview" class="image-preview-large">
                                <img :src="dataFormComputed.preview" alt="Preview" />
                                <div class="preview-overlay">Xem trước</div>
                            </div>
                            <div v-else class="image-placeholder">
                                <i class="ph ph-image-square"></i>
                                <p>Chưa có ảnh</p>
                            </div>
                            
                            <div class="upload-input-container">
                                <BaseInputFile labelContent="" customId="image" :error="errors?.image" customAccept="image/*"
                                    @change="(event) => emit('handleImageChange', event)" />
                                <p class="upload-hint">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex justify-end gap-3 p-4 border-t border-gray-100 bg-gray-50/50 rounded-b-2xl">
            <button type="button" @click="emit('close')"
                class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 hover:text-gray-900 transition-all focus:outline-none focus:ring-2 focus:ring-gray-200">
                Hủy bỏ
            </button>
            <button type="button" @click="handleSubmit" :disabled="props.loadingSubmit"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-xl hover:bg-blue-700 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <i v-if="props.loadingSubmit" class="ph ph-spinner animate-spin text-lg"></i>
                <i v-else class="ph ph-floppy-disk text-lg"></i>
                {{ props.loadingSubmit ? 'Đang xử lý...' : 'Lưu Banner' }}
            </button>
        </div>
    </BaseModal>
</template>

<style scoped>
.form-section {
    padding-bottom: 8px;
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
</style>
