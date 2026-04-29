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
        <div class="attr-val-container">
            <!-- Trái: Danh sách giá trị -->
            <div class="attr-val-list">
                <h3 class="modal-title">Thuộc tính: <span style="color: var(--primary);">{{ attribute?.name }}</span></h3>
                
                <div class="admin-table-wrapper" style="max-height: 500px; overflow-y: auto;">
                    <table class="table">
                        <thead>
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
                                <td colspan="5" class="text-center" style="padding: 24px;">
                                    <BaseLoading />
                                </td>
                            </tr>
                            <tr v-else-if="values.length === 0">
                                <td colspan="5" class="text-center" style="padding: 24px; color: var(--text-muted);">
                                    Chưa có giá trị nào
                                </td>
                            </tr>
                            <tr v-else v-for="(item, index) in values" :key="item.id">
                                <td>{{ index + 1 }}</td>
                                <td><strong style="color: var(--text-main);">{{ item.value }}</strong></td>
                                <td>
                                    <small style="color: var(--text-muted); font-family: monospace; background: var(--background); padding: 4px 8px; border-radius: 4px;">{{ item.slug }}</small>
                                </td>
                                <td>
                                    <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                                        {{ item.status ? 'Hiện' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <BaseButton @click="editValue(item)" customText="Sửa"
                                            customClass="btn-action btn-edit" />
                                        <BaseButton @click="destroyValue(item.id)" customText="Xóa"
                                            customClass="btn-action btn-delete" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Phải: Form Thêm/Sửa -->
            <div class="attr-val-form">
                <div class="form-card">
                    <h4 class="form-title">{{ isEdit ? 'Sửa giá trị' : 'Thêm giá trị mới' }}</h4>
                    <BaseForm @handleSubmit="submit">
                        <template #input>
                            <BaseInputText labelContent="Giá trị" customId="val_value" v-model="dataForm.value" customPlaceholderInput="Nhập giá trị"
                                :error="errors.value" />
                            <BaseInputText labelContent="Slug" customId="val_slug" v-model="dataForm.slug" customPlaceholderInput="Nhập đường dẫn"
                                :error="errors.slug" />
                            <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status"
                                :values="statusMap" />
                        </template>
                        <template #button>
                            <div class="form-actions">
                                <BaseButton v-if="!loadingSubmit" customType="submit"
                                    :customText="isEdit ? 'Cập nhật' : 'Thêm'" :disabled="loadingSubmit"
                                    customClass="btn btn-primary" style="flex: 1;" />
                                <div v-else style="flex: 1; display: flex; justify-content: center; padding: 8px 0;">
                                    <BaseSpinner />
                                </div>
                                <BaseButton v-if="isEdit" @click="resetForm" customType="button" customText="Hủy"
                                    customClass="btn" style="background: var(--background); color: var(--text-main); border: 1px solid var(--border);" />
                            </div>
                        </template>
                    </BaseForm>
                </div>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.attr-val-container {
    display: flex;
    gap: 32px;
}

.attr-val-list {
    flex: 6;
}

.attr-val-form {
    flex: 4;
}

.modal-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 24px;
    color: var(--text-main);
}

.form-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    padding: 24px;
}

.form-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
    color: var(--text-main);
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

@media (max-width: 768px) {
    .attr-val-container {
        flex-direction: column;
    }
}
</style>
