<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProductStore } from '@/stores/product'
import { useCategoryStore } from '@/stores/category'
import { swalConfirmDelete } from '@/composables/useSwal'

import ProductTable from './ProductTable.vue'
import ProductForm from './ProductForm.vue'
import ProductImageModal from './ProductImageModal.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
import { useNotify } from '@/composables/useNotify'
import { API_URL_IMAGE } from '@/config/env'

// ================= STORE =================
const store = useProductStore()
const categoryStore = useCategoryStore()

// ================= ROUTER =================
const router = useRouter()

// ================= NOTIFY =================
const toast = useNotify()

// ================= COMPUTED =================
const products = computed(() => store.products)
const totalPages = computed(() => store.pagination.last_page)
const totalProducts = computed(() => store.pagination.total)

const categoriesMap = computed(() => {
    return categoryStore.categories.map(c => ({
        id: c.id,
        name: c.name
    }))
})

// ================= STATE =================
const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const isShowImagesModal = ref(false)
const isEdit = ref(false)
const errors = ref({})
const selectedProduct = ref(null)

const params = ref({
    search: '',
    sort: 'created_at_desc',
    status: null,
    limit: 15,
    page: 1
})

const dataForm = ref({
    id: null,
    category_id: null,
    thumbnail: null,
    preview: '',
    name: '',
    slug: '',
    price: 0,
    description: '',
    status: 1
})

// ================= MAP =================
const sortMap = [
    { id: 'price_asc', name: "Giá tăng dần" },
    { id: 'price_desc', name: "Giá giảm dần" },
    { id: 'created_at_asc', name: "Cũ nhất" },
    { id: 'created_at_desc', name: "Mới nhất" },
]

const filterMap = [
    { id: 1, name: 'Hiển thị' },
    { id: 0, name: 'Đã ẩn' },
]

const limitMap = [
    { id: 15, name: '15' },
    { id: 30, name: '30' },
    { id: 50, name: '50' },
    { id: 100, name: '100' },
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
        category_id: null,
        thumbnail: null,
        preview: '',
        name: '',
        slug: '',
        price: 0,
        description: '',
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

    if (!dataForm.value.category_id) errors.value.category_id = 'Danh mục không được để trống'
    if (!dataForm.value.name?.trim()) errors.value.name = 'Tên không được để trống'
    if (!dataForm.value.slug?.trim()) errors.value.slug = 'Slug không được để trống'
    if (dataForm.value.price === null || dataForm.value.price === '') errors.value.price = 'Giá không được để trống'
    else if (Number(dataForm.value.price) < 0) errors.value.price = 'Giá không hợp lệ'

    if (!dataForm.value.id && !dataForm.value.thumbnail) {
        errors.value.thumbnail = 'Ảnh đại diện không được để trống'
    } else if (dataForm.value.thumbnail instanceof File) {
        const file = dataForm.value.thumbnail
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp']

        if (!validTypes.includes(file.type)) {
            errors.value.thumbnail = 'Chỉ chấp nhận png, jpg, jpeg, webp'
        }

        if (file.size > 2 * 1024 * 1024) {
            errors.value.thumbnail = 'Ảnh tối đa 2MB'
        }
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
    formData.append('slug', dataForm.value.slug)
    formData.append('category_id', dataForm.value.category_id)
    formData.append('price', dataForm.value.price)
    formData.append('status', dataForm.value.status)
    if (dataForm.value.description) {
        formData.append('description', dataForm.value.description)
    }
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
        toast.error(result?.message || "Lỗi khi lưu dữ liệu");
        if (result?.errors) {
            errors.value = {
                name: result.errors.name?.[0] ?? "",
                slug: result.errors.slug?.[0] ?? "",
                category_id: result.errors.category_id?.[0] ?? "",
                price: result.errors.price?.[0] ?? "",
                thumbnail: result.errors.thumbnail?.[0] ?? "",
                status: result.errors.status?.[0] ?? "",
            }
        }
    } else {
        toast.success(result?.message || "Thành công");
        closeForm();
        loadData();
    }
    loadingSubmit.value = false
}

const update = async (item) => {
    const data = await store.show(item.id)
    if (!data || !data.success) return
    dataForm.value = { ...data.data }
    if (dataForm.value.thumbnail) {
        dataForm.value.preview = `${API_URL_IMAGE}/${dataForm.value.thumbnail}`
    }
    isEdit.value = true
    isShow.value = true
}

const destroy = async (id) => {
    const confirm = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa sản phẩm này không ?')
    if (!confirm.isConfirmed) return

    const result = await store.destroy(id)
    if (!result?.success) {
        toast.error(result?.message || "Lỗi khi xóa dữ liệu")
        return
    }
    toast.success(result?.message || "Xóa dữ liệu thành công")
    loadData()
}

const show = (item) => {
    dataForm.value = { ...item, thumbnail: null }
    if (dataForm.value.thumbnail) {
        dataForm.value.preview = `${API_URL_IMAGE}/${dataForm.value.thumbnail}`
    }
    isShow.value = true
}

const openImages = (item) => {
    selectedProduct.value = item
    isShowImagesModal.value = true
}

const openVariants = (item) => {
    router.push({ name: 'ProductVariants', params: { id: item.id } })
}

const handleImagesChanged = (type) => {
    if (selectedProduct.value) {
        if (type === 'add') {
            selectedProduct.value.images_count = (selectedProduct.value.images_count || 0) + 1
        } else if (type === 'delete') {
            selectedProduct.value.images_count = Math.max(0, (selectedProduct.value.images_count || 0) - 1)
        }
    }
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
    if (categoryStore.categories.length === 0) {
        categoryStore.index({ limit: 100 })
    }
})
</script>

<template>
    <!-- List -->
    <BaseAdmin :total="totalProducts" :totalPages="totalPages" :currentPage="params.page" v-model:params="params"
        :sortMap="sortMap" :filterMap="filterMap" :limitMap="limitMap" @search="search" @open="openCreateForm"
        @changePage="changePage">
        <template #table>
            <ProductTable :params="params" :loadingData="loadingData" :data="products" @update="update"
                @destroy="destroy" @show="show" @openImages="openImages" @openVariants="openVariants" />
        </template>
    </BaseAdmin>

    <!-- Form -->
    <ProductForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :errors="errors" :statusMap="statusMap" :categories="categoriesMap" @submit="submit" @close="closeForm"
        @handleImageChange="handleImageChange" />

    <!-- Product Images Modal -->
    <ProductImageModal v-model:isShow="isShowImagesModal" :product="selectedProduct" @imagesChanged="handleImagesChanged"
        @close="isShowImagesModal = false" />
</template>
