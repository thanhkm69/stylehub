<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useBlogCategoryStore } from '@/stores/blogCategory'
import { swalConfirmDelete } from '@/composables/useSwal'

import BlogCategoryTable from './BlogCategoryTable.vue'
import BlogCategoryForm from './BlogCategoryForm.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'

import { useNotify } from '@/composables/useNotify'
const toast = useNotify()

// ================= STORE =================
const store = useBlogCategoryStore()

// ================= COMPUTED =================
const categories = computed(() => store.categories)
const totalPages = computed(() => store.pagination?.last_page || 1)
const totalCategories = computed(() => store.pagination?.total || 0)

// ================= STATE =================
const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const errors = ref({})

const params = ref({
    search: '',
    sort: 'created_at_desc',
    limit: 10,
    page: 1
})

const dataForm = ref({
    id: null,
    name: '',
    slug: '',
    description: ''
})

// ================= MAP =================
const sortMap = [
    { id: 'name_asc', name: "A -> Z" },
    { id: 'name_desc', name: "Z -> A" },
    { id: 'created_at_asc', name: "Cũ nhất" },
    { id: 'created_at_desc', name: "Mới nhất" },
]

const limitMap = [
    { id: 10, name: '10' },
    { id: 20, name: '20' },
    { id: 50, name: '50' },
]

// ================= METHODS =================
const loadData = async () => {
    loadingData.value = true
    await store.fetchCategories(params.value.page) // The current fetchCategories only takes page. For a full implementation, the store should accept params.
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        name: '',
        slug: '',
        description: ''
    }
    errors.value = {}
}

const closeForm = () => {
    isShow.value = false
    resetForm()
}

const openCreateForm = () => {
    resetForm()
    isShow.value = true
}

const validate = () => {
    errors.value = {}

    if (!dataForm.value.name?.trim()) {
        errors.value.name = 'Tên danh mục không được để trống'
    }

    if (!dataForm.value.slug?.trim()) {
        errors.value.slug = 'Slug không được để trống'
    }

    return Object.keys(errors.value).length === 0
}

const submit = async () => {
    errors.value = {}
    if (!validate()) return

    loadingSubmit.value = true

    let result
    try {
        if (dataForm.value.id) {
            result = await store.updateCategory(dataForm.value.id, dataForm.value)
        } else {
            result = await store.createCategory(dataForm.value)
        }

        if (result) {
            closeForm()
            // store already updates the list or we can reload
            await loadData()
        }
    } catch (error) {
        toast.error("Đã xảy ra lỗi");
    } finally {
        loadingSubmit.value = false
    }
}

const update = (item) => {
    dataForm.value = { ...item }
    isShow.value = true
}

const destroy = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa danh mục này không?')
    if (!result.isConfirmed) return
    
    const res = await store.deleteCategory(id)
    if (res) {
        await loadData()
    }
}

const search = () => {
    params.value.page = 1
    loadData()
}

const changePage = (page) => {
    params.value.page = page
    loadData()
}

// ================= WATCH =================
watch(
    () => ({ ...params.value }),
    () => {}, // store.fetchCategories needs to support full params to use this effectively
    { deep: true }
)

// ================= INIT =================
onMounted(loadData)
</script>

<template>
    <!-- List -->
    <BaseAdmin :total="totalCategories" :totalPages="totalPages" :currentPage="params.page" v-model:params="params"
        :sortMap="sortMap" :limitMap="limitMap" @search="search" @open="openCreateForm"
        @changePage="changePage">
        <template #table>
            <BlogCategoryTable :params="params" :loadingData="loadingData" :data="categories" @update="update"
                @destroy="destroy" />
        </template>
    </BaseAdmin>

    <!-- Form -->
    <BlogCategoryForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :errors="errors" @submit="submit" @close="closeForm" />
</template>
