<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useComboStore } from '@/stores/combo'
import { swalConfirmDelete } from '@/composables/useSwal'
import { useNotify } from '@/composables/useNotify'

import ComboTable from './ComboTable.vue'
import ComboForm from './ComboForm.vue'
import ComboItemModal from './ComboItemModal.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
const props = defineProps({
    title: String,
    description: String
})

const store = useComboStore()
const toast = useNotify()

const combos = computed(() => store.combos)
const totalPages = computed(() => store.pagination.last_page)
const totalItems = computed(() => store.pagination.total)

const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const isShowItem = ref(false)
const isEdit = ref(false)
const errors = ref({})

const selectedCombo = ref(null)

const params = ref({
    search: '',
    sort: 'created_at_desc',
    combo_type: '',
    status: '',
    limit: 15,
    page: 1,
})

const dataForm = ref({
    id: null, name: '', description: '', thumbnail: null, preview: '',
    combo_type: 'fixed_combo', discount_type: 'percentage', discount_value: 0,
    starts_at: '', ends_at: '', status: true, display: 0,
})

// ================= MAPS =================
const sortMap = [
    { id: 'created_at_desc', name: 'Mới nhất' },
    { id: 'created_at_asc', name: 'Cũ nhất' },
    { id: 'display_asc', name: 'Thứ tự tăng' },
    { id: 'display_desc', name: 'Thứ tự giảm' },
]

const filterMap = [
    { id: 'fixed_combo', name: 'Combo cố định' },
    { id: 'buy_get', name: 'Mua tặng' },
    { id: 'bundle', name: 'Gói sản phẩm' },
]

const limitMap = [
    { id: 15, name: '15' }, { id: 30, name: '30' },
    { id: 50, name: '50' }, { id: 100, name: '100' },
]

const comboTypes = [
    { id: 'fixed_combo', name: 'Combo cố định' },
    { id: 'buy_get', name: 'Mua tặng' },
    { id: 'bundle', name: 'Gói sản phẩm' },
]

const discountTypes = [
    { id: 'percentage', name: 'Phần trăm (%)' },
    { id: 'fixed_price', name: 'Giảm cố định (₫)' },
    { id: 'bundle_price', name: 'Giá gói (₫)' },
]

const statusMap = [
    { id: true, name: 'Hiện' },
    { id: false, name: 'Ẩn' },
]

// ================= METHODS =================
const loadData = async () => {
    loadingData.value = true
    await store.index(params.value)
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null, name: '', description: '', thumbnail: null, preview: '',
        combo_type: 'fixed_combo', discount_type: 'percentage', discount_value: 0,
        starts_at: '', ends_at: '', status: true, display: 0,
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
    if (!dataForm.value.name?.trim()) errors.value.name = 'Tên combo không được để trống'
    if (!dataForm.value.combo_type) errors.value.combo_type = 'Vui lòng chọn loại combo'
    if (!dataForm.value.discount_type) errors.value.discount_type = 'Vui lòng chọn loại giảm giá'

    const val = parseFloat(dataForm.value.discount_value)
    if (isNaN(val) || val < 0) {
        errors.value.discount_value = 'Giá trị giảm không hợp lệ'
    }

    if (dataForm.value.starts_at && dataForm.value.ends_at) {
        if (new Date(dataForm.value.ends_at) <= new Date(dataForm.value.starts_at)) {
            errors.value.ends_at = 'Ngày kết thúc phải sau ngày bắt đầu'
        }
    }

    if (dataForm.value.thumbnail instanceof File) {
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp']
        if (!validTypes.includes(dataForm.value.thumbnail.type))
            errors.value.thumbnail = 'Chỉ chấp nhận png, jpg, jpeg, webp'
        if (dataForm.value.thumbnail.size > 2 * 1024 * 1024)
            errors.value.thumbnail = 'Ảnh tối đa 2MB'
    }

    return Object.keys(errors.value).length === 0
}

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (!file) return
    dataForm.value.thumbnail = file
    dataForm.value.preview = URL.createObjectURL(file)
}

const submit = async () => {
    errors.value = {}
    if (!validate()) return

    loadingSubmit.value = true

    const formData = new FormData()
    formData.append('name', dataForm.value.name)
    formData.append('description', dataForm.value.description ?? '')
    formData.append('combo_type', dataForm.value.combo_type)
    formData.append('discount_type', dataForm.value.discount_type)
    formData.append('discount_value', dataForm.value.discount_value ?? 0)
    formData.append('starts_at', dataForm.value.starts_at ?? '')
    formData.append('ends_at', dataForm.value.ends_at ?? '')
    formData.append('status', dataForm.value.status ? 1 : 0)
    formData.append('display', dataForm.value.display ?? 0)
    if (dataForm.value.thumbnail instanceof File) {
        formData.append('thumbnail', dataForm.value.thumbnail)
    }

    const result = dataForm.value.id
        ? await store.update(dataForm.value.id, formData)
        : await store.store(formData)

    if (!result?.success) {
        toast.error(result?.message || 'Lỗi khi lưu dữ liệu')
        if (result?.errors) {
            errors.value = {
                name: result.errors.name?.[0] ?? '',
                thumbnail: result.errors.thumbnail?.[0] ?? '',
                combo_type: result.errors.combo_type?.[0] ?? '',
                discount_type: result.errors.discount_type?.[0] ?? '',
                discount_value: result.errors.discount_value?.[0] ?? '',
                starts_at: result.errors.starts_at?.[0] ?? '',
                ends_at: result.errors.ends_at?.[0] ?? '',
                status: result.errors.status?.[0] ?? '',
                display: result.errors.display?.[0] ?? '',
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
    if (!data?.success) return
    const d = data.data
    dataForm.value = {
        ...d,
        thumbnail: null,
        preview: d.thumbnail ? `http://localhost:8000/storage/${d.thumbnail}` : '',
        starts_at: d.starts_at ? d.starts_at.replace(' ', 'T').substring(0, 16) : '',
        ends_at: d.ends_at ? d.ends_at.replace(' ', 'T').substring(0, 16) : '',
    }
    isEdit.value = true
    isShow.value = true
}

const manageItems = (item) => {
    selectedCombo.value = item
    isShowItem.value = true
}

const destroy = async (id) => {
    const confirm = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa combo này không?')
    if (!confirm.isConfirmed) return
    const result = await store.destroy(id)
    if (!result?.success) {
        toast.error(result?.message || 'Lỗi khi xóa dữ liệu')
        return
    }
    toast.success(result?.message || 'Xóa thành công')
    loadData()
}

const search = () => { params.value.page = 1; loadData() }
const changePage = (page) => { params.value.page = page }

watch(() => ({ ...params.value }), loadData, { deep: true })
onMounted(() => loadData())
</script>

<template>
    <BaseAdmin :title="props.title" :description="props.description" :total="totalItems" :totalPages="totalPages"
        :currentPage="params.page" v-model:params="params" :sortMap="sortMap" :filterMap="filterMap"
        :limitMap="limitMap" @search="search" @open="openCreateForm" @changePage="changePage">
        <template #table>
            <ComboTable :params="params" :loadingData="loadingData" :data="combos" @update="update" @destroy="destroy"
                @manageItems="manageItems" />
        </template>
    </BaseAdmin>

    <ComboForm v-model:isShow="isShow" v-model:dataForm="dataForm" v-model:loadingSubmit="loadingSubmit"
        :errors="errors" :comboTypes="comboTypes" :discountTypes="discountTypes" :statusMap="statusMap" @submit="submit"
        @close="closeForm" @handleImageChange="handleImageChange" />

    <ComboItemModal v-if="selectedCombo" v-model:isShow="isShowItem" :combo="selectedCombo" @itemsChanged="loadData"
        @close="isShowItem = false" />
</template>
