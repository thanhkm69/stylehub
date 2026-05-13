<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import BaseLoading from '@/components/base/BaseLoading.vue';
import { useProductVariantValueStore } from '@/stores/productVariantValue';
import { useAttributeValueStore } from '@/stores/attributeValue';
import { swalConfirmDelete } from '@/composables/useSwal';
import { useNotify } from '@/composables/useNotify';

const props = defineProps({
    variant: Object
})

const emit = defineEmits(["close", "variantValuesChanged"])
const isShow = defineModel("isShow")

const store = useProductVariantValueStore()
const attributeValueStore = useAttributeValueStore()
const toast = useNotify()

const loadingData = ref(false)
const loadingSubmit = ref(false)
const isEdit = ref(false)
const errors = ref({})

const dataForm = ref({
    id: null,
    product_variant_id: null,
    attribute_value_id: null,
})

const values = computed(() => store.productVariantValues)

// Lấy danh sách tất cả các giá trị thuộc tính để đưa vào select
const attributeValueOptions = computed(() => {
    return attributeValueStore.attributeValues.map(item => ({
        id: item.id,
        name: `${item.attribute?.name || 'Thuộc tính'} - ${item.value}`
    }))
})

const loadData = async () => {
    loadingData.value = true
    await store.index(props.variant.id)
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        product_variant_id: props.variant?.id || null,
        attribute_value_id: null,
    }
    isEdit.value = false
    errors.value = {}
}

const validate = () => {
    errors.value = {}

    if (!dataForm.value.attribute_value_id) {
        errors.value.attribute_value_id = 'Vui lòng chọn giá trị thuộc tính'
    }

    return Object.keys(errors.value).length === 0
}

const submit = async () => {
    if (!validate()) return

    loadingSubmit.value = true
    dataForm.value.product_variant_id = props.variant.id

    let result
    if (isEdit.value) {
        result = await store.update(dataForm.value.id, dataForm.value)
    } else {
        result = await store.store(dataForm.value)
    }

    if (!result?.success) {
        toast.error(result?.message || "Lỗi khi lưu dữ liệu");
        if (result?.errors) {
            errors.value = {
                attribute_value_id: result.errors.attribute_value_id?.[0] ?? "",
            }
        }
    } else {
        toast.success(result?.message || "Thành công");
        resetForm()
        await loadData()
        emit('variantValuesChanged', 'add')
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
    const res = await store.destroy(id)
    if (!res?.success) {
        toast.error(res?.message || "Lỗi khi xóa dữ liệu");
    } else {
        toast.success(res?.message || "Thành công");
        await loadData()
        emit('variantValuesChanged', 'delete')
    }
}

watch(() => isShow.value, async (newVal) => {
    if (newVal) {
        resetForm()
        await attributeValueStore.index() // Load full attribute values
        loadData()
    }
})

</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" customWidth="1100px">
        <div class="prod-var-val-container">
            <!-- Trái: Danh sách giá trị thuộc tính -->
            <div class="prod-var-val-list">
                <h3 class="modal-title">Thuộc tính của SKU: <span style="color: var(--primary);">{{ variant?.sku }}</span></h3>
                
                <div class="admin-table-wrapper" style="max-height: 500px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Loại thuộc tính</th>
                                <th>Giá trị</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loadingData">
                                <td colspan="4" class="text-center" style="padding: 48px;">
                                    <BaseSpinner size="lg" label="Đang tải dữ liệu..." />
                                </td>
                            </tr>
                            <tr v-else-if="values.length === 0">
                                <td colspan="4" class="text-center" style="padding: 24px; color: var(--text-muted);">
                                    Chưa có thuộc tính nào
                                </td>
                            </tr>
                            <tr v-else v-for="(item, index) in values" :key="item.id">
                                <td>{{ index + 1 }}</td>
                                <td><strong>{{ item.attribute_value?.attribute?.name || 'N/A' }}</strong></td>
                                <td>
                                    <span class="badge-status badge-active">{{ item.attribute_value?.value || 'N/A' }}</span>
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
            <div class="prod-var-val-form">
                <div class="form-card">
                    <h4 class="form-title">{{ isEdit ? 'Sửa thuộc tính' : 'Thêm thuộc tính mới' }}</h4>
                    <BaseForm @handleSubmit="submit">
                        <template #input>
                            <BaseInputSelect 
                                labelContent="Chọn giá trị thuộc tính" 
                                v-model="dataForm.attribute_value_id"
                                :values="attributeValueOptions" 
                                :error="errors.attribute_value_id" 
                            />
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
.prod-var-val-container {
    display: flex;
    gap: 24px;
}

.prod-var-val-list {
    flex: 6;
}

.prod-var-val-form {
    flex: 4;
}

.modal-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
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

.btn-action {
    transition: all 0.2s ease;
    cursor: pointer;
    font-weight: 600;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    filter: brightness(1.1);
}

.btn-action:active {
    transform: translateY(0);
}

.table tbody tr {
    animation: fadeIn 0.4s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .prod-var-val-container {
        flex-direction: column;
    }
}
</style>
