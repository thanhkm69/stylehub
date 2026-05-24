<script setup>
import { ref, computed, watch } from 'vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseInputNumber from '@/components/base/BaseInputNumber.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import BaseLoading from '@/components/base/BaseLoading.vue';
import { useComboItemStore } from '@/stores/comboItem';
import { useProductStore } from '@/stores/product';
import { useProductVariantStore } from '@/stores/productVariant';
import { swalConfirmDelete } from '@/composables/useSwal';
import { useNotify } from '@/composables/useNotify';

const props = defineProps({
    combo: Object
})

const emit = defineEmits(["close", "itemsChanged"])
const isShow = defineModel("isShow")

const store = useComboItemStore()
const productStore = useProductStore()
const variantStore = useProductVariantStore()
const toast = useNotify()

const loadingData = ref(false)
const loadingSubmit = ref(false)
const isEdit = ref(false)
const errors = ref({})

const dataForm = ref({
    id: null,
    combo_id: null,
    product_id: null,
    product_variant_id: null,
    quantity: 1,
})

const productOptions = computed(() =>
    productStore.products.map(p => ({ id: p.id, name: p.name }))
)

const variantOptions = computed(() => {
    if (!dataForm.value.product_id) return []
    return variantStore.productVariants.map(v => ({ id: v.id, name: v.sku }))
})

const values = computed(() => store.comboItems)

// ================= METHODS =================
const loadData = async () => {
    loadingData.value = true
    await store.index(props.combo.id)
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        combo_id: props.combo?.id || null,
        product_id: null,
        product_variant_id: null,
        quantity: 1,
    }
    isEdit.value = false
    errors.value = {}
}

const validate = () => {
    errors.value = {}
    if (!dataForm.value.product_id)    errors.value.product_id = 'Vui lòng chọn sản phẩm'
    if (dataForm.value.quantity < 1)   errors.value.quantity = 'Số lượng tối thiểu là 1'
    return Object.keys(errors.value).length === 0
}

const submit = async () => {
    if (!validate()) return

    loadingSubmit.value = true
    dataForm.value.combo_id = props.combo.id

    let result
    if (isEdit.value) {
        result = await store.update(dataForm.value.id, dataForm.value)
    } else {
        result = await store.store(dataForm.value)
    }

    if (!result?.success) {
        toast.error(result?.message || 'Lỗi khi lưu dữ liệu')
        if (result?.errors) {
            errors.value = {
                product_id:         result.errors.product_id?.[0] ?? '',
                product_variant_id: result.errors.product_variant_id?.[0] ?? '',
                quantity:           result.errors.quantity?.[0] ?? '',
            }
        }
    } else {
        toast.success(result?.message || 'Thành công')
        resetForm()
        await loadData()
        emit('itemsChanged')
    }
    loadingSubmit.value = false
}

const editValue = (item) => {
    dataForm.value = { ...item }
    isEdit.value = true

    // Load variants when editing
    if (item.product_id) {
        variantStore.index({ product_id: item.product_id })
    }
}

const destroyValue = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa sản phẩm này khỏi combo?')
    if (!result.isConfirmed) return
    await store.destroy(id)
    await loadData()
    emit('itemsChanged')
}

// Auto-load variants when product changes
watch(() => dataForm.value.product_id, async (newId) => {
    dataForm.value.product_variant_id = null
    if (newId) await variantStore.index({ product_id: newId })
})

watch([() => isShow.value, () => props.combo?.id], async ([newVal, comboId]) => {
    if (newVal && comboId) {
        resetForm()
        await Promise.all([
            loadData(),
            productStore.index(),
        ])
    }
}, { immediate: true })

</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" customWidth="1100px">
        <div class="ci-container">
            <!-- Trái: Danh sách -->
            <div class="ci-list">
                <h3 class="modal-title">
                    Sản phẩm trong Combo:
                    <span style="color: var(--primary);">{{ combo?.name }}</span>
                </h3>

                <div class="admin-table-wrapper" style="max-height: 600px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Sản phẩm</th>
                                <th>Biến thể</th>
                                <th>SL</th>
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
                                    Chưa có sản phẩm nào trong combo
                                </td>
                            </tr>
                            <tr v-else v-for="(item, index) in values" :key="item.id">
                                <td>{{ index + 1 }}</td>
                                <td>
                                    <strong style="color: var(--text-main); font-size: 13px;">
                                        {{ item.product?.name ?? '—' }}
                                    </strong>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);">
                                    {{ item.product_variant?.sku ?? 'Tất cả' }}
                                </td>
                                <td>{{ item.quantity }}</td>
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
            <div class="ci-form">
                <div class="form-card">
                    <h4 class="form-title">{{ isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' }}</h4>
                    <BaseForm @handleSubmit="submit">
                        <template #input>
                            <BaseInputSelect labelContent="Sản phẩm" v-model="dataForm.product_id"
                                :values="productOptions" :error="errors.product_id" />

                            <BaseInputSelect labelContent="Biến thể (tùy chọn)" v-model="dataForm.product_variant_id"
                                :values="[{ id: null, name: '— Tất cả biến thể —' }, ...variantOptions]"
                                :error="errors.product_variant_id" />

                            <BaseInputNumber labelContent="Số lượng" v-model="dataForm.quantity"
                                :error="errors.quantity" />
                        </template>

                        <template #button>
                            <div class="form-actions">
                                <BaseButton v-if="!loadingSubmit" customType="submit"
                                    :customText="isEdit ? 'Cập nhật' : 'Thêm'"
                                    customClass="btn btn-primary" style="flex: 1;" />
                                <div v-else style="flex: 1; display: flex; justify-content: center; padding: 8px 0;">
                                    <BaseSpinner />
                                </div>
                                <BaseButton v-if="isEdit" @click="resetForm" customType="button" customText="Hủy"
                                    customClass="btn"
                                    style="background: var(--background); color: var(--text-main); border: 1px solid var(--border);" />
                            </div>
                        </template>
                    </BaseForm>
                </div>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.ci-container {
    display: flex;
    gap: 32px;
}
.ci-list { flex: 6; }
.ci-form { flex: 4; }

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
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--border);
    color: var(--text-main);
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

@media (max-width: 768px) {
    .ci-container { flex-direction: column; }
}
</style>
