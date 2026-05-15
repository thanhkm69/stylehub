<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useFlashSaleStore } from '@/stores/flashSale'
import { swalConfirmDelete } from '@/composables/useSwal'
import { useNotify } from '@/composables/useNotify'

import FlashSaleTable from './FlashSaleTable.vue'
import FlashSaleForm from './FlashSaleForm.vue'
import FlashSaleItemModal from './FlashSaleItemModal.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
import { API_URL_IMAGE } from '@/config/env'
const props = defineProps({
    title: String,
    description: String
})

// ================= STORE =================
const store = useFlashSaleStore()

// ================= NOTIFY =================
const toast = useNotify()

// ================= COMPUTED =================
const flashSales = computed(() => store.flashSales)
const totalPages = computed(() => store.pagination.last_page)
const totalItems = computed(() => store.pagination.total)

// ================= STATE =================
const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const isShowItemsModal = ref(false)
const isEdit = ref(false)
const errors = ref({})
const selectedFlashSale = ref(null)

const params = ref({
    search: '',
    sort: 'created_at_desc',
    status: '',
    limit: 15,
    page: 1
})

const dataForm = ref({
    id: null,
    name: '',
    description: '',
    thumbnail: null,
    preview: '',
    starts_at: '',
    ends_at: '',
    status: 'draft',
    display: 0,
})

// ================= MAP =================
const sortMap = [
    { id: 'created_at_desc', name: 'Mới nhất' },
    { id: 'created_at_asc', name: 'Cũ nhất' },
    { id: 'display_asc', name: 'Thứ tự tăng' },
    { id: 'display_desc', name: 'Thứ tự giảm' },
]

const filterMap = [
    { id: 'draft', name: 'Nháp' },
    { id: 'active', name: 'Đang chạy' },
    { id: 'ended', name: 'Đã kết thúc' },
    { id: 'cancelled', name: 'Đã hủy' },
]

const limitMap = [
    { id: 15, name: '15' },
    { id: 30, name: '30' },
    { id: 50, name: '50' },
    { id: 100, name: '100' },
]

const statusMap = [
    { id: 'draft', name: 'Nháp' },
    { id: 'active', name: 'Đang chạy' },
    { id: 'ended', name: 'Đã kết thúc' },
    { id: 'cancelled', name: 'Đã hủy' },
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
        name: '',
        description: '',
        thumbnail: null,
        preview: '',
        starts_at: '',
        ends_at: '',
        status: 'draft',
        display: 0,
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

    if (!dataForm.value.name?.trim()) errors.value.name = 'Tên Flash Sale không được để trống'
    if (!dataForm.value.starts_at) errors.value.starts_at = 'Ngày bắt đầu không được để trống'
    if (!dataForm.value.ends_at) errors.value.ends_at = 'Ngày kết thúc không được để trống'

    if (dataForm.value.starts_at && dataForm.value.ends_at) {
        if (new Date(dataForm.value.ends_at) <= new Date(dataForm.value.starts_at)) {
            errors.value.ends_at = 'Ngày kết thúc phải sau ngày bắt đầu'
        }
    }

    if (!dataForm.value.id && !dataForm.value.thumbnail) {
        // thumbnail optional for flash sale
    } else if (dataForm.value.thumbnail instanceof File) {
        const file = dataForm.value.thumbnail
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp']
        if (!validTypes.includes(file.type)) errors.value.thumbnail = 'Chỉ chấp nhận png, jpg, jpeg, webp'
        if (file.size > 2 * 1024 * 1024) errors.value.thumbnail = 'Ảnh tối đa 2MB'
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
    formData.append('starts_at', dataForm.value.starts_at)
    formData.append('ends_at', dataForm.value.ends_at)
    formData.append('status', dataForm.value.status)
    formData.append('display', dataForm.value.display ?? 0)
    if (dataForm.value.thumbnail instanceof File) {
        formData.append('thumbnail', dataForm.value.thumbnail)
    }

    let result
    if (dataForm.value.id) {
        result = await store.update(dataForm.value.id, formData)
    } else {
        result = await store.store(formData)
    }

    if (!result?.success) {
        toast.error(result?.message || 'Lỗi khi lưu dữ liệu')
        if (result?.errors) {
            errors.value = {
                name: result.errors.name?.[0] ?? '',
                thumbnail: result.errors.thumbnail?.[0] ?? '',
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
    if (!data || !data.success) return

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

const destroy = async (id) => {
    const confirm = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa flash sale này không?')
    if (!confirm.isConfirmed) return

    const result = await store.destroy(id)
    if (!result?.success) {
        toast.error(result?.message || 'Lỗi khi xóa dữ liệu')
        return
    }
    toast.success(result?.message || 'Xóa thành công')
    loadData()
}

const openItems = (item) => {
    selectedFlashSale.value = item
    isShowItemsModal.value = true
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
    <BaseAdmin :title="props.title" :description="props.description" :total="totalItems" :totalPages="totalPages"
        :currentPage="params.page" v-model:params="params" :sortMap="sortMap" :filterMap="filterMap"
        :limitMap="limitMap" @search="search" @open="openCreateForm" @changePage="changePage">
        <template #table>
            <FlashSaleTable :params="params" :loadingData="loadingData" :data="flashSales" @update="update"
                @destroy="destroy" @openItems="openItems" />
        </template>
    </BaseAdmin>

    <!-- Form -->
    <FlashSaleForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :errors="errors" :statusMap="statusMap" @submit="submit" @close="closeForm"
        @handleImageChange="handleImageChange" />

    <!-- Items Modal -->
    <FlashSaleItemModal v-model:isShow="isShowItemsModal" :flashSale="selectedFlashSale"
        @close="isShowItemsModal = false" />
</template>
