<script setup>
import { watch } from 'vue';
import { useSlug } from '@/composables/useSlug';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputTextarea from '@/components/base/BaseInputTextarea.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(["close", "submit"])

const isShow = defineModel("isShow")
const dataForm = defineModel("dataForm")
const loadingSubmit = defineModel("loadingSubmit")

const { generateSlug } = useSlug()

watch(
    () => dataForm.value.name,
    (newName) => {
        if (newName) {
            dataForm.value.slug = generateSlug(newName)
        } else if (!newName) {
            dataForm.value.slug = ''
        }
    }
)
</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" :title="dataForm.id ? 'Cập nhật danh mục' : 'Thêm danh mục mới'" customWidth="600px">
        <BaseForm @handleSubmit="emit('submit')">
            <template #input>
                <div class="form-column">
                    <div class="form-section">
                        <BaseInputText labelContent="Tên danh mục" customId="name" v-model="dataForm.name" customPlaceholderInput="Ví dụ: Xu hướng thời trang"
                            :error="errors.name" />

                        <BaseInputText labelContent="Slug (Đường dẫn)" customId="slug" v-model="dataForm.slug" customPlaceholderInput="xu-huong-thoi-trang" :error="errors.slug" />
                        
                        <BaseInputTextarea labelContent="Mô tả" customId="description" v-model="dataForm.description" customPlaceholderInput="Mô tả về danh mục..." :error="errors.description" />
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
.form-column {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 8px 0;
}

.form-section {
    display: flex;
    flex-direction: column;
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
</style>
