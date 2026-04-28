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
                <BaseInputText labelContent="Tên thuộc tính" customId="name" v-model="dataForm.name"
                    :error="errors.name" />

                <BaseInputText labelContent="Slug" customId="slug" v-model="dataForm.slug" :error="errors.slug" />

                <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status" :values="statusMap" />
            </template>

            <template #button>
                <BaseButton v-if="!loadingSubmit" customType="submit" customText="Lưu" :disabled="loadingSubmit" />
                <BaseSpinner v-else />
            </template>
        </BaseForm>
    </BaseModal>
</template>
