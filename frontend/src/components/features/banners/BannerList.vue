<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useBannerStore } from '@/stores/banner'
import { swalConfirmDelete } from '@/composables/useSwal'
import { useNotify } from '@/composables/useNotify'
import { API_URL_IMAGE } from '@/config/env'

import BannerTable from './BannerTable.vue'
import BannerForm from './BannerForm.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'

const props = defineProps({
    title: String,
    description: String
})

// ================= STORE =================
const store = useBannerStore()
const toast = useNotify()

// ================= COMPUTED =================
const banners = computed(() => store.banners)
const totalPages = computed(() => store.pagination.last_page)
const totalBanners = computed(() => store.pagination.total)

// ================= STATE =================
const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const isEdit = ref(false)
const errors = ref({})

const params = ref({
    search: '',
    sort: 'position_asc',
    status: '',
    limit: 15,
    page: 1
})

const dataForm = ref({
    id: null,
    title: '',
    image: '',
    link: '',
    position: 0,
    status: 1,
    preview: ''
})

// ================= MAP =================
const sortMap = [
    { id: 'position_asc', name: "Vị trí tăng dần" },
    { id: 'position_desc', name: "Vị trí giảm dần" },
    { id: 'created_at_desc', name: "Mới nhất" },
    { id: 'created_at_asc', name: "Cũ nhất" },
]

const filterMap = [
    { id: 1, name: 'Hiện' },
    { id: 0, name: 'Ẩn' },
]

const limitMap = [
    { id: 15, name: '15' },
    { id: 30, name: '30' },
    { id: 50, name: '50' },
]

const statusMap = [
    { id: 1, name: 'Hiện' },
    { id: 0, name: 'Ẩn' }
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
        title: '',
        image: '',
        link: '',
        position: 0,
        status: 1,
        preview: ''
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

    // ===== IMAGE =====
    if (!dataForm.value.id && !dataForm.value.image) {
        errors.value.image = 'Hình ảnh không được để trống'
    } else if (dataForm.value.image instanceof File) {
        const file = dataForm.value.image
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp']
        if (!validTypes.includes(file.type)) {
            errors.value.image = 'Chỉ chấp nhận png, jpg, jpeg, webp'
        }
        if (file.size > 2 * 1024 * 1024) {
            errors.value.image = 'Ảnh tối đa 2MB'
        }
    }

    return Object.keys(errors.value).length === 0
}

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (!file) return

    dataForm.value.image = file
    dataForm.value.preview = URL.createObjectURL(file)
}

const submit = async () => {
    errors.value = {}
    if (!validate()) return

    loadingSubmit.value = true
    const formData = new FormData()

    Object.entries(dataForm.value).forEach(([key, value]) => {
        if (key === 'image' && !(value instanceof File)) return
        if (key === 'preview') return
        formData.append(key, value ?? '')
    })

    if (isEdit.value) {
        formData.append('_method', 'PUT')
    }

    let result
    try {
        if (dataForm.value.id) {
            result = await store.update(dataForm.value.id, formData)
        } else {
            result = await store.store(formData)
        }

        if (!result?.success) {
            toast.error(result?.message || "Lỗi khi lưu dữ liệu")
            if (result?.errors) {
                errors.value = {
                    title: result.errors.title?.[0] ?? "",
                    link: result.errors.link?.[0] ?? "",
                    status: result.errors.status?.[0] ?? "",
                    position: result.errors.position?.[0] ?? "",
                    image: result.errors.image?.[0] ?? "",
                }
            }
        } else {
            toast.success(result?.message || "Thành công")
            closeForm()
            loadData()
        }
    } catch (error) {
        toast.error("Đã xảy ra lỗi không xác định")
        console.error(error)
    } finally {
        loadingSubmit.value = false
    }
}

const update = async (item) => {
    const data = await store.show(item.id)
    if (!data) return
    
    dataForm.value = { ...data.data }
    if (dataForm.value.image) {
        dataForm.value.preview = `${API_URL_IMAGE}/${dataForm.value.image}`
    }
    isEdit.value = true
    isShow.value = true
}

const destroy = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa banner này không ?')
    if (!result.isConfirmed) return
    const res = await store.destroy(id)
    if (res?.success) {
        toast.success(res.message)
    }
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
onMounted(loadData)
</script>

<template>
    <BaseAdmin :title="props.title" :description="props.description" :total="totalBanners" :totalPages="totalPages"
        :currentPage="params.page" v-model:params="params" :sortMap="sortMap" :filterMap="filterMap" :limitMap="limitMap"
        @search="search" @open="openCreateForm" @changePage="changePage">
        
        <template #table>
            <BannerTable :params="params" :loadingData="loadingData" :data="banners" @update="update"
                @destroy="destroy" />
        </template>
    </BaseAdmin>

    <!-- Form -->
    <BannerForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :errors="errors" :statusMap="statusMap" @submit="submit" @close="closeForm"
        @handleImageChange="handleImageChange" />
</template>
