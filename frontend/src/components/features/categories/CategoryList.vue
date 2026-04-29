<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useCategoryStore } from '@/stores/category'
import { swalConfirmDelete } from '@/composables/useSwal'

import CategoryTable from './CategoryTable.vue'
import CategoryForm from './CategoryForm.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'

import { API_URL_IMAGE } from '@/config/env'

// ================= STORE =================
const store = useCategoryStore()
import { useNotify } from '@/composables/useNotify'
const toast = useNotify()

// ================= COMPUTED =================
const categories = computed(() => store.categories)
const totalPages = computed(() => store.pagination.last_page)
const totalCategories = computed(() => store.pagination.total)
const message = computed(() => store.message)

// ================= STATE =================
const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const isEdit = ref(false)
const errors = ref({})
const categoryIds = ref([])

const params = ref({
    search: '',
    sort: 'display_asc',
    status: null,
    limit: 5,
    page: 1
})

const dataForm = ref({
    id: null,
    parent_id: null,
    name: '',
    slug: '',
    description: '',
    image: '',
    preview: '',
    display: 0,
    status: 1
})

// ================= MAP =================
const sortMap = [
    { id: 'name_asc', name: "A -> Z" },
    { id: 'name_desc', name: "Z -> A" },
    { id: 'display_asc', name: "Thấp -> Cao" },
    { id: 'display_desc', name: "Cao -> Thấp" },
    { id: 'created_at_asc', name: "Cũ nhất" },
    { id: 'created_at_desc', name: "Mới nhất" },
]

const filterMap = [
    { id: 1, name: 'Danh mục hiện' },
    { id: 0, name: 'Danh mục ẩn' },
]

const limitMap = [
    { id: 5, name: '5' },
    { id: 10, name: '10' },
    { id: 20, name: '20' },
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
        parent_id: null,
        name: '',
        slug: '',
        description: '',
        image: '',
        preview: '',
        display: 0,
        status: 1
    }
    categoryIds.value = []
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

    // ===== NAME =====
    if (!dataForm.value.name?.trim()) {
        errors.value.name = 'Tên không được để trống'
    } else if (dataForm.value.name.length > 100) {
        errors.value.name = 'Tối đa 100 ký tự'
    }

    // ===== SLUG =====
    if (!dataForm.value.slug?.trim()) {
        errors.value.slug = 'Slug không được để trống'
    } else if (dataForm.value.slug.length > 150) {
        errors.value.slug = 'Tối đa 150 ký tự'
    }

    // ===== DISPLAY =====
    if (dataForm.value.display !== null) {
        if (isNaN(dataForm.value.display)) {
            errors.value.display = 'Phải là số'
        } else if (dataForm.value.display < 0) {
            errors.value.display = 'Phải >= 0'
        }
    }

    // ===== STATUS =====
    if (dataForm.value.status !== null) {
        if (![0, 1, true, false].includes(dataForm.value.status)) {
            errors.value.status = 'Không hợp lệ'
        }
    }

    // ===== IMAGE =====
    if (dataForm.value.image instanceof File) {
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
        formData.append(key, value ?? '')
    })

    let result
    try {
        if (dataForm.value.id) {
            result = await store.update(dataForm.value.id, formData)
        } else {
            result = await store.store(formData)
        }

        if (!result?.success) {
            toast.error(result?.message || "Lỗi khi lưu dữ liệu");
            if (result?.errors) {
                errors.value = {
                    name: result.errors.name?.[0] ?? "",
                    slug: result.errors.slug?.[0] ?? "",
                    status: result.errors.status?.[0] ?? "",
                    parent_id: result.errors.parent_id?.[0] ?? "",
                    description: result.errors.description?.[0] ?? "",
                    display: result.errors.display?.[0] ?? "",
                    image: result.errors.image?.[0] ?? "",
                }
            }
        } else {
            toast.success(result?.message || "Thành công");
            closeForm();
            loadData();
        }

    } catch (error) {
        toast.error("Đã xảy ra lỗi không xác định");
        console.error(error);
    } finally {
        loadingSubmit.value = false
    }
}

const update = async (item) => {
    const data = await store.show(item.id)
    if (!data) return
    categoryIds.value = data.ids
    dataForm.value = { ...item }
    if (dataForm.value.image) {
        dataForm.value.preview = `${API_URL_IMAGE}/${dataForm.value.image}`
    }
    isEdit.value = true
    isShow.value = true
}

const destroy = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa danh mục này không ?')
    if (!result.isConfirmed) return
    const res = await store.destroy(id)
    loadData()
}

const show = (item) => {
    dataForm.value = { ...item }
    isShow.value = true
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

    <!-- List -->
    <BaseAdmin :total="totalCategories" :totalPages="totalPages" :currentPage="params.page" v-model:params="params"
        :sortMap="sortMap" :filterMap="filterMap" :limitMap="limitMap" @search="search" @open="openCreateForm"
        @changePage="changePage">
        <template #table>
            <CategoryTable :params="params" :loadingData="loadingData" :data="categories" @update="update"
                @destroy="destroy" @show="show" />
        </template>
    </BaseAdmin>

    <!-- Form -->
    <CategoryForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :categories="categories" :errors="errors" :categoryIds="categoryIds" :statusMap="statusMap" @submit="submit"
        @close="closeForm" @handleImageChange="handleImageChange" />
</template>