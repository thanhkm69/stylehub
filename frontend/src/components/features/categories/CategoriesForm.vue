<script setup>
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseInputTextarea from '@/components/base/BaseInputTextarea.vue';
import BaseInputFile from '@/components/base/BaseInputFile.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import BaseInputNumber from '@/components/base/BaseInputNumber.vue';
import BaseImagePreview from '@/components/base/BaseImagePreview.vue';

const props = defineProps({
    categoryIds: Array,
    errors: Object,
    statusMap: Array
})

const emit = defineEmits(["close", "submit", "handleImageChange"])

const isShow = defineModel("isShow")
const dataForm = defineModel("dataForm")
const loadingSubmit = defineModel("loadingSubmit")
const categories = defineModel("categories")


</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow">
        <BaseForm @handleSubmit="emit('submit')">
            <template #input>

                <BaseInputSelect labelContent="Danh mục cha" :categoryIds="categoryIds" v-model="dataForm.parent_id"
                    :values="categories" />

                <BaseInputText labelContent="Tên danh mục" customId="name" v-model="dataForm.name"
                    :error="errors.name" />

                <BaseInputText labelContent="Slug" customId="slug" v-model="dataForm.slug" :error="errors.slug" />

                <BaseInputFile labelContent="Ảnh danh mục" customId="image" :error="errors.image" customAccept="image/*"
                    @change="(event) => emit('handleImageChange', event)" />

                <BaseImagePreview v-if="dataForm.preview" :preview="dataForm.preview" />

                <BaseInputTextarea labelContent="Mô tả" customId="description" v-model="dataForm.description" />

                <BaseInputNumber labelContent="Thứ tự hiển thị" v-model="dataForm.display" />

                <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status" :values="statusMap" />

            </template>

            <template #button>
                <BaseButton customType="submit" customText="Submit" :disabled="loadingSubmit" />
                <BaseSpinner v-if="loadingSubmit" />
            </template>
        </BaseForm>
    </BaseModal>
</template>