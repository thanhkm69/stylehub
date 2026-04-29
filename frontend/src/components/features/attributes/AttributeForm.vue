<script setup>
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';

const props = defineProps({
    errors: Object,
    statusMap: Array
})

const emit = defineEmits(["close", "submit"])

const isShow = defineModel("isShow")
const dataForm = defineModel("dataForm")
const loadingSubmit = defineModel("loadingSubmit")

</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow">
        <BaseForm @handleSubmit="emit('submit')">
            <template #input>
                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 24px; color: var(--text-main);">Thuộc tính</h3>
                    <BaseInputText labelContent="Tên thuộc tính" customId="name" v-model="dataForm.name" customPlaceholderInput="Nhập tên thuộc tính"
                        :error="errors.name" />

                    <BaseInputText labelContent="Slug" customId="slug" v-model="dataForm.slug" customPlaceholderInput="Nhập đường dẫn (tuỳ chọn)" :error="errors.slug" />

                    <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status" :values="statusMap" />
                </div>
            </template>

            <template #button>
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px;">
                    <BaseButton @click="emit('close')" customType="button" customText="Hủy" customClass="btn" style="background: var(--background); color: var(--text-main); border: 1px solid var(--border);" />
                    <BaseButton v-if="!loadingSubmit" customType="submit" customText="Lưu thuộc tính" customClass="btn btn-primary" :disabled="loadingSubmit" />
                    <div v-else style="display: flex; align-items: center; justify-content: center; padding: 0 24px;">
                        <BaseSpinner />
                    </div>
                </div>
            </template>
        </BaseForm>
    </BaseModal>
</template>
