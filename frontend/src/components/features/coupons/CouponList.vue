<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useCouponStore } from '@/stores/coupon'
import { swalConfirmDelete } from '@/composables/useSwal'
import { useNotify } from '@/composables/useNotify'

import CouponTable from './CouponTable.vue'
import CouponForm from './CouponForm.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
const props = defineProps({
    title: String,
    description: String
})

// ================= STORE =================
const store = useCouponStore()

// ================= NOTIFY =================
const toast = useNotify()

// ================= COMPUTED =================
const coupons = computed(() => store.coupons)
const totalPages = computed(() => store.pagination.last_page)
const totalCoupons = computed(() => store.pagination.total)

// ================= STATE =================
const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const isEdit = ref(false)
const errors = ref({})

const params = ref({
    search: '',
    sort: 'created_at_desc',
    status: '',
    discount_type: '',
    limit: 15,
    page: 1
})

const dataForm = ref({
    id: null,
    code: '',
    name: '',
    description: '',
    discount_type: 'percentage',
    discount_value: 0,
    max_discount_amount: null,
    min_order_value: 0,
    usage_limit: null,
    starts_at: '',
    expires_at: '',
    status: 1
})

// ================= MAP =================
const sortMap = [
    { id: 'created_at_desc', name: 'Mới nhất' },
    { id: 'created_at_asc', name: 'Cũ nhất' },
]

const filterMap = [
    { id: 1, name: 'Hoạt động' },
    { id: 0, name: 'Tạm ngưng' },
]

const limitMap = [
    { id: 15, name: '15' },
    { id: 30, name: '30' },
    { id: 50, name: '50' },
    { id: 100, name: '100' },
]

const statusMap = [
    { id: 1, name: 'Hoạt động' },
    { id: 0, name: 'Tạm ngưng' }
]

const discountTypes = [
    { id: 'percentage', name: 'Phần trăm (%)' },
    { id: 'fixed', name: 'Số tiền cố định (VNĐ)' }
]

// ================= METHODS =================
const loadData = async () => {
    loadingData.value = true
    await store.index(params.value)
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        code: '',
        name: '',
        description: '',
        discount_type: 'percentage',
        discount_value: 0,
        max_discount_amount: null,
        min_order_value: 0,
        usage_limit: null,
        starts_at: '',
        expires_at: '',
        status: 1
    }
    errors.value = {}
}

const closeForm = () => {
    isShow.value = false
    isEdit.value = false
    resetForm()
}

const openCreateForm = () => {
    resetForm()
    isShow.value = true
}

const validate = () => {
    errors.value = {}

    if (!dataForm.value.code?.trim()) errors.value.code = 'Mã giảm giá không được để trống'
    if (!dataForm.value.name?.trim()) errors.value.name = 'Tên không được để trống'
    if (!dataForm.value.discount_type) errors.value.discount_type = 'Loại giảm giá không được để trống'

    const val = parseFloat(dataForm.value.discount_value)
    if (isNaN(val) || val < 1) {
        errors.value.discount_value = 'Giá trị giảm phải ít nhất 1'
    } else if (dataForm.value.discount_type === 'percentage') {
        if (val > 100) {
            errors.value.discount_value = 'Phần trăm giảm phải từ 1 đến 100'
        }
    } else if (dataForm.value.discount_type === 'fixed') {
        if (val < 1) {
            errors.value.discount_value = 'Giá trị giảm cố định phải ít nhất 1đ'
        }
    }

    return Object.keys(errors.value).length === 0
}

const submit = async () => {
    errors.value = {}
    if (!validate()) return

    loadingSubmit.value = true

    const payload = { ...dataForm.value }

    // Khi giảm cố định → max_discount_amount = discount_value (tự động)
    if (payload.discount_type === 'fixed') {
        payload.max_discount_amount = payload.discount_value
    }

    let result
    if (dataForm.value.id) {
        result = await store.update(dataForm.value.id, payload)
    } else {
        result = await store.store(payload)
    }

    if (!result?.success) {
        toast.error(result?.message || 'Lỗi khi lưu dữ liệu')
        if (result?.errors) {
            errors.value = {
                code: result.errors.code?.[0] ?? '',
                name: result.errors.name?.[0] ?? '',
                discount_type: result.errors.discount_type?.[0] ?? '',
                discount_value: result.errors.discount_value?.[0] ?? '',
                max_discount_amount: result.errors.max_discount_amount?.[0] ?? '',
                min_order_value: result.errors.min_order_value?.[0] ?? '',
                usage_limit: result.errors.usage_limit?.[0] ?? '',
                starts_at: result.errors.starts_at?.[0] ?? '',
                expires_at: result.errors.expires_at?.[0] ?? '',
                status: result.errors.status?.[0] ?? '',
            }
        }
    } else {
        toast.success(result?.message || 'Thành công')
        closeForm()
        loadData()
    }
    loadingSubmit.value = false
}

const update = async (item) => {
    const data = await store.show(item.id)
    if (!data || !data.success) return

    const d = data.data
    dataForm.value = {
        ...d,
        starts_at: d.starts_at ? d.starts_at.replace(' ', 'T').substring(0, 16) : '',
        expires_at: d.expires_at ? d.expires_at.replace(' ', 'T').substring(0, 16) : '',
        status: d.status ? 1 : 0,
    }
    isEdit.value = true
    isShow.value = true
}

const destroy = async (id) => {
    const confirm = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa mã giảm giá này không?')
    if (!confirm.isConfirmed) return

    const result = await store.destroy(id)
    if (!result?.success) {
        toast.error(result?.message || 'Lỗi khi xóa dữ liệu')
        return
    }
    toast.success(result?.message || 'Xóa thành công')
    loadData()
}

const search = () => {
    params.value.page = 1
    loadData()
}

const changePage = (page) => {
    params.value.page = page
}

// ================= WATCH =================
watch(
    () => ({ ...params.value }),
    loadData,
    { deep: true }
)

// ================= INIT =================
onMounted(() => {
    loadData()
})
</script>

<template>
    <!-- List -->
    <BaseAdmin :title="props.title" :description="props.description" :total="totalCoupons" :totalPages="totalPages"
        :currentPage="params.page" v-model:params="params" :sortMap="sortMap" :filterMap="filterMap"
        :limitMap="limitMap" @search="search" @open="openCreateForm" @changePage="changePage">
        <template #table>
            <CouponTable :params="params" :loadingData="loadingData" :data="coupons" @update="update"
                @destroy="destroy" />
        </template>
    </BaseAdmin>

    <!-- Form -->
    <CouponForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :errors="errors" :statusMap="statusMap" :discountTypes="discountTypes" @submit="submit" @close="closeForm" />
</template>
