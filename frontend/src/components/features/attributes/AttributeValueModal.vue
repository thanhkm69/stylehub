<script setup>
import { ref, computed, watch } from 'vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import { useAttributeValueStore } from '@/stores/attributeValue';
import { swalConfirmDelete } from '@/composables/useSwal';
import { useNotify } from '@/composables/useNotify';

const props = defineProps({
    attribute: Object
})

const emit = defineEmits(["close", "valuesChanged"])
const isShow = defineModel("isShow")

const store = useAttributeValueStore()
const toast = useNotify()

const loadingData = ref(false)
const loadingSubmit = ref(false)
const isEdit = ref(false)
const errors = ref({})

const dataForm = ref({
    id: null,
    attribute_id: null,
    value: '',
    slug: '',
    status: 1
})

const statusMap = [
    { id: 1, name: 'Hiện' },
    { id: 0, name: 'Ẩn' }
]

const values = computed(() => store.attributeValues)

const loadData = async () => {
    loadingData.value = true
    await store.index(props.attribute.id)
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        attribute_id: props.attribute?.id || null,
        value: '',
        slug: '',
        status: 1
    }
    isEdit.value = false
    errors.value = {}
}

const validate = () => {
    errors.value = {}

    if (!dataForm.value.value?.trim()) {
        errors.value.value = 'Giá trị không được để trống'
    }

    if (!dataForm.value.slug?.trim()) {
        errors.value.slug = 'Slug không được để trống'
    }

    return Object.keys(errors.value).length === 0
}

const submit = async () => {
    if (!validate()) return

    loadingSubmit.value = true
    dataForm.value.attribute_id = props.attribute.id

    const payload = {
        attribute_id: dataForm.value.attribute_id,
        value: dataForm.value.value,
        slug: dataForm.value.slug,
        status: dataForm.value.status
    }

    let result
    if (isEdit.value) {
        result = await store.update(dataForm.value.id, payload)
    } else {
        result = await store.store(payload)
    }

    if (!result?.status) {
        toast.error(result?.message || "Lỗi khi lưu dữ liệu");
        if (result?.errors) {
            errors.value = {
                value: result.errors.value?.[0] ?? "",
                slug: result.errors.slug?.[0] ?? "",
                status: result.errors.status?.[0] ?? "",
            }
        }
    } else {
        toast.success(result?.message || "Thành công");
        resetForm()
        await loadData()
        emit('valuesChanged')
    }
    loadingSubmit.value = false
}

const editValue = (item) => {
    dataForm.value = { ...item }
    isEdit.value = true
}

const destroyValue = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa giá trị này không ?')
    if (!result.isConfirmed) return
    await store.destroy(id)
    await loadData()
    emit('valuesChanged')
}

watch(() => isShow.value, (newVal) => {
    if (newVal) {
        resetForm()
        loadData()
    }
})

</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" customWidth="1000px">
        <div class="row">
            <!-- Trái: Danh sách giá trị -->
            <div class="col-md-7">
                <h5 class="mb-3">Thuộc tính: <span class="text-primary">{{ attribute?.name }}</span></h5>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>STT</th>
                                <th>Giá trị</th>
                                <th>Slug</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loadingData">
                                <td colspan="5" class="text-center">Đang tải...</td>
                            </tr>
                            <tr v-else-if="values.length === 0">
                                <td colspan="5" class="text-center">Chưa có giá trị nào</td>
                            </tr>
                            <tr v-else v-for="(item, index) in values" :key="item.id">
                                <td>{{ index + 1 }}</td>
                                <td><strong>{{ item.value }}</strong></td>
                                <td><small class="text-muted">{{ item.slug }}</small></td>
                                <td>
                                    <span :class="item.status ? 'badge bg-success' : 'badge bg-secondary'">
                                        {{ item.status ? 'Hiện' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td>
                                    <BaseButton @click="editValue(item)" customText="Sửa"
                                        customClass="btn-warning btn-sm me-1" />
                                    <BaseButton @click="destroyValue(item.id)" customText="Xóa"
                                        customClass="btn-danger btn-sm" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Phải: Form Thêm/Sửa -->
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-4 border-bottom pb-2">{{ isEdit ? 'Sửa giá trị' : 'Thêm giá trị mới' }}
                        </h5>
                        <BaseForm @handleSubmit="submit">
                            <template #input>
                                <BaseInputText labelContent="Giá trị" customId="val_value" v-model="dataForm.value"
                                    :error="errors.value" />
                                <BaseInputText labelContent="Slug" customId="val_slug" v-model="dataForm.slug"
                                    :error="errors.slug" />
                                <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status"
                                    :values="statusMap" />
                            </template>
                            <template #button>
                                <div class="d-flex gap-2">
                                    <BaseButton v-if="!loadingSubmit" customType="submit"
                                        :customText="isEdit ? 'Cập nhật' : 'Thêm'" :disabled="loadingSubmit"
                                        customClass="btn-primary flex-grow-1" />
                                    <BaseSpinner v-else />
                                    <BaseButton v-if="isEdit" @click="resetForm" customType="button" customText="Hủy"
                                        customClass="btn-secondary" />
                                </div>
                            </template>
                        </BaseForm>
                    </div>
                </div>
            </div>
        </div>
    </BaseModal>
</template>
