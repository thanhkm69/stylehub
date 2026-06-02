<script setup>
import { ref, computed, watch } from 'vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseInputNumber from '@/components/base/BaseInputNumber.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import BaseLoading from '@/components/base/BaseLoading.vue';
import { useFlashSaleItemStore } from '@/stores/flashSaleItem';
import { useProductStore } from '@/stores/product';
import { useProductVariantStore } from '@/stores/productVariant';
import { swalConfirmDelete } from '@/composables/useSwal';
import { useNotify } from '@/composables/useNotify';

const props = defineProps({
    flashSale: Object
})

const emit = defineEmits(["close", "itemsChanged"])
const isShow = defineModel("isShow")

const store = useFlashSaleItemStore()
const productStore = useProductStore()
const variantStore = useProductVariantStore()
const toast = useNotify()

const loadingData = ref(false)
const loadingSubmit = ref(false)
const isEdit = ref(false)
const errors = ref({})

const dataForm = ref({
    id: null,
    flash_sale_id: null,
    product_id: null,
    product_variant_id: null,
    discount_type: 'percentage',
    discount_value: 0,
    original_price: 0,
    sale_price: 0,
    status: 1,
    display: 0,
})

// ================= MAP =================
const discountTypes = [
    { id: 'percentage',  name: 'Phần trăm (%)' },
    { id: 'fixed_price', name: 'Giá cố định (VNĐ)' },
]

const statusMap = [
    { id: 1, name: 'Hiện' },
    { id: 0, name: 'Ẩn' }
]

const productOptions = computed(() =>
    productStore.products.map(p => ({ id: p.id, name: p.name }))
)

const variantOptions = computed(() => {
    if (!dataForm.value.product_id) return []
    return variantStore.productVariants.map(v => ({ id: v.id, name: v.sku }))
})

const values = computed(() => store.flashSaleItems)

// ================= AUTO PRICE CALCULATION =================

// Giá gốc: lấy từ biến thể (nếu có) hoặc sản phẩm
const computedOriginalPrice = computed(() => {
    if (dataForm.value.product_variant_id) {
        const variant = variantStore.productVariants.find(
            v => v.id == dataForm.value.product_variant_id
        )
        return variant?.price ?? 0
    }
    if (dataForm.value.product_id) {
        const product = productStore.products.find(
            p => p.id == dataForm.value.product_id
        )
        return product?.price ?? 0
    }
    return 0
})

// Giá sale: tự tính từ loại giảm + giá trị giảm
const computedSalePrice = computed(() => {
    const original = computedOriginalPrice.value
    const discountVal = parseFloat(dataForm.value.discount_value) || 0

    if (dataForm.value.discount_type === 'percentage') {
        const pct = Math.min(discountVal, 100)
        return Math.max(0, original - (original * pct / 100))
    }
    // fixed_price: giảm 1 khoản cố định
    return Math.max(0, original - discountVal)
})

// ================= METHODS =================
const loadData = async () => {
    loadingData.value = true
    await store.index(props.flashSale.id)
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        flash_sale_id: props.flashSale?.id || null,
        product_id: null,
        product_variant_id: null,
        discount_type: 'percentage',
        discount_value: 0,
        original_price: 0,
        sale_price: 0,
        status: 1,
        display: 0,
    }
    isEdit.value = false
    errors.value = {}
}

const validate = () => {
    errors.value = {}
    if (!dataForm.value.product_id)    errors.value.product_id = 'Vui lòng chọn sản phẩm'
    if (!dataForm.value.discount_type) errors.value.discount_type = 'Vui lòng chọn loại giảm giá'
    if (computedOriginalPrice.value <= 0) errors.value.product_id = 'Sản phẩm không có giá hợp lệ'

    const val = parseFloat(dataForm.value.discount_value)
    if (isNaN(val) || val < 1) {
        errors.value.discount_value = 'Giá trị giảm phải ít nhất 1'
    } else if (dataForm.value.discount_type === 'percentage') {
        if (val > 100) {
            errors.value.discount_value = 'Phần trăm giảm phải từ 1 đến 100'
        }
    } else if (dataForm.value.discount_type === 'fixed_price') {
        const maxPrice = computedOriginalPrice.value
        if (maxPrice > 0 && val > maxPrice) {
            const formatted = new Intl.NumberFormat('vi-VN').format(maxPrice)
            errors.value.discount_value = `Giá giảm cố định không được vượt quá giá sản phẩm (${formatted}đ)`
        }
    }

    return Object.keys(errors.value).length === 0
}

const submit = async () => {
    if (!validate()) return

    loadingSubmit.value = true
    dataForm.value.flash_sale_id = props.flashSale.id

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
                discount_type:      result.errors.discount_type?.[0] ?? '',
                discount_value:     result.errors.discount_value?.[0] ?? '',
                original_price:     result.errors.original_price?.[0] ?? '',
                sale_price:         result.errors.sale_price?.[0] ?? '',
                status:             result.errors.status?.[0] ?? '',
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
        variantStore.index({ product_id: item.product_id})
    }
}

const destroyValue = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa sản phẩm này khỏi flash sale?')
    if (!result.isConfirmed) return
    await store.destroy(id)
    await loadData()
    emit('itemsChanged')
}

// Auto-load variants when product changes
watch(() => dataForm.value.product_id, async (newId) => {
    dataForm.value.product_variant_id = null
    if (newId) await variantStore.index({ product_id: newId})
})

watch(() => isShow.value, async (newVal) => {
    if (newVal) {
        resetForm()
        await Promise.all([
            loadData(),
            productStore.index(),
        ])
    }
})

const formatCurrency = (val) =>
    new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" customWidth="1180px">
        <div class="fsi-container">
            <!-- Trái: Danh sách -->
            <div class="fsi-list">
                <h3 class="modal-title">
                    Sản phẩm Flash Sale:
                    <span style="color: var(--primary);">{{ flashSale?.name }}</span>
                </h3>

                <div class="admin-table-wrapper fsi-table-wrapper">
                    <table class="table fsi-table">
                        <colgroup>
                            <col class="col-index">
                            <col class="col-product">
                            <col class="col-discount">
                            <col class="col-price">
                            <col class="col-status">
                            <col class="col-actions">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="cell-center">STT</th>
                                <th>Sản phẩm</th>
                                <th>Mức giảm</th>
                                <th>Giá bán</th>
                                <th class="cell-center">Trạng thái</th>
                                <th class="cell-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loadingData">
                                <td colspan="6" class="text-center" style="padding: 24px;">
                                    <BaseLoading />
                                </td>
                            </tr>
                            <tr v-else-if="values.length === 0">
                                <td colspan="6" class="text-center" style="padding: 24px; color: var(--text-muted);">
                                    Chưa có sản phẩm nào trong flash sale
                                </td>
                            </tr>
                            <tr v-else v-for="(item, index) in values" :key="item.id">
                                <td class="cell-center row-index">{{ index + 1 }}</td>
                                <td>
                                    <div class="product-cell">
                                        <strong class="product-name" :title="item.product?.name ?? '-'">
                                            {{ item.product?.name ?? '—' }}
                                        </strong>
                                        <span class="product-variant">
                                            Biến thể: {{ item.product_variant?.sku ?? 'Tất cả' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="discount-cell">
                                        <span :class="['badge-type', item.discount_type === 'percentage' ? 'badge-pct' : 'badge-fix']">
                                            {{ item.discount_type === 'percentage' ? '%' : '₫' }}
                                        </span>
                                        <span class="discount-value">
                                            {{ item.discount_type === 'percentage' ? `${item.discount_value}%` : formatCurrency(item.discount_value) }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="price-cell">
                                        <span class="price-old">
                                            {{ formatCurrency(item.original_price) }}
                                        </span>
                                        <span class="price-current">
                                            {{ formatCurrency(item.sale_price) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="cell-center">
                                    <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                                        {{ item.status ? 'Hiện' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td class="cell-center">
                                    <div class="action-group action-group-compact">
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
            <div class="fsi-form">
                <div class="form-card">
                    <h4 class="form-title">{{ isEdit ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' }}</h4>
                    <BaseForm @handleSubmit="submit">
                        <template #input>
                            <BaseInputSelect labelContent="Sản phẩm" v-model="dataForm.product_id"
                                :values="productOptions" :error="errors.product_id" />

                            <BaseInputSelect labelContent="Biến thể (tùy chọn)" v-model="dataForm.product_variant_id"
                                :values="[{ id: null, name: '— Tất cả biến thể —' }, ...variantOptions]"
                                :error="errors.product_variant_id" />

                            <BaseInputSelect labelContent="Loại giảm giá" v-model="dataForm.discount_type"
                                :values="discountTypes" :error="errors.discount_type" />

                            <BaseInputNumber labelContent="Giá trị giảm" v-model="dataForm.discount_value"
                                :error="errors.discount_value" />

                            <!-- Giá gốc & Giá sale: chỉ hiển thị, hệ thống tự tính -->
                            <div class="price-preview">
                                <div class="price-row">
                                    <span class="price-label">Giá gốc</span>
                                    <span class="price-value original">
                                        {{ computedOriginalPrice > 0 ? formatCurrency(computedOriginalPrice) : '—' }}
                                    </span>
                                </div>
                                <div class="price-row">
                                    <span class="price-label">Giá sale</span>
                                    <span class="price-value sale">
                                        {{ computedOriginalPrice > 0 ? formatCurrency(computedSalePrice) : '—' }}
                                    </span>
                                </div>
                                <div v-if="computedOriginalPrice > 0 && dataForm.discount_type === 'percentage'" class="price-saved">
                                    Tiết kiệm {{ dataForm.discount_value }}%
                                    ({{ formatCurrency(computedOriginalPrice - computedSalePrice) }})
                                </div>
                                <div v-else-if="computedOriginalPrice > 0 && dataForm.discount_type === 'fixed_price'" class="price-saved">
                                    Tiết kiệm {{ formatCurrency(computedOriginalPrice - computedSalePrice) }}
                                </div>
                                <p v-if="!dataForm.product_id" class="price-hint">Chọn sản phẩm để xem giá</p>
                            </div>

                            <BaseInputNumber labelContent="Thứ tự hiển thị" v-model="dataForm.display" />

                            <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status"
                                :values="statusMap" :error="errors.status" />
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
.fsi-container {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 24px;
    align-items: flex-start;
}
.fsi-list,
.fsi-form {
    min-width: 0;
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

.fsi-table-wrapper {
    max-height: 600px;
    overflow: auto;
}

.fsi-table {
    min-width: 760px;
    table-layout: fixed;
}

.fsi-table :is(th, td) {
    padding: 12px 14px;
}

.fsi-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    white-space: nowrap;
}

.fsi-table th:nth-child(1),
.fsi-table td:nth-child(1),
.fsi-table th:nth-child(5),
.fsi-table td:nth-child(5),
.fsi-table th:nth-child(6),
.fsi-table td:nth-child(6) {
    text-align: center;
}

.col-index { width: 56px; }
.col-product { width: 250px; }
.col-discount { width: 138px; }
.col-price { width: 150px; }
.col-status { width: 104px; }
.col-actions { width: 118px; }

.cell-center {
    text-align: center;
}

.row-index {
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 600;
}

.product-cell {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 4px;
}

.product-name {
    display: block;
    overflow: hidden;
    color: var(--text-main);
    font-size: 13px;
    font-weight: 700;
    line-height: 1.35;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.product-variant {
    display: block;
    overflow: hidden;
    color: var(--text-muted);
    font-size: 12px;
    line-height: 1.35;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.discount-cell {
    display: inline-flex;
    max-width: 100%;
    align-items: center;
    gap: 6px;
}

.discount-value {
    overflow: hidden;
    color: #dc2626;
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.price-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
    line-height: 1.3;
}

.price-old {
    color: var(--text-muted);
    font-size: 12px;
    text-decoration: line-through;
}

.price-current {
    color: var(--primary);
    font-size: 13px;
    font-weight: 700;
}

.action-group-compact {
    justify-content: center;
    gap: 6px;
}

.action-group-compact :deep(.btn-action) {
    min-width: 52px;
    padding: 10px 14px;
    font-size: 14px;
    line-height: 1.2;
}

.badge-type {
    display: inline-flex;
    min-width: 26px;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-full);
    padding: 3px 7px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
}
.badge-pct  { background: #e0f2fe; color: #0369a1; }
.badge-fix  { background: #fef3c7; color: #92400e; }

/* Price preview panel */
.price-preview {
    background: color-mix(in oklch, var(--surface) 78%, var(--background));
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price-label {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
}

.price-value {
    font-size: 15px;
    font-weight: 700;
}

.price-value.original {
    color: var(--text-main);
    text-decoration: line-through;
    font-weight: 500;
    font-size: 13px;
}

.price-value.sale {
    color: #dc2626;
    font-size: 18px;
}

.price-saved {
    font-size: 12px;
    color: #22c55e;
    background: color-mix(in oklch, #16a34a 14%, var(--surface));
    border: 1px solid color-mix(in oklch, #22c55e 55%, var(--border));
    border-radius: 8px;
    padding: 6px 10px;
    text-align: center;
    font-weight: 600;
}

.price-hint {
    font-size: 12px;
    color: var(--text-muted);
    text-align: center;
    margin: 0;
}

@media (max-width: 768px) {
    .fsi-container {
        grid-template-columns: 1fr;
    }

    .fsi-table {
        min-width: 720px;
    }
}
</style>
