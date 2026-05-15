<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { usePostStore } from '@/stores/post'
import { useBlogCategoryStore } from '@/stores/blogCategory'
import { swalConfirmDelete } from '@/composables/useSwal'

import PostTable from './PostTable.vue'
import PostForm from './PostForm.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'

import { useNotify } from '@/composables/useNotify'
const toast = useNotify()

// ================= STORE =================
const store = usePostStore()
const categoryStore = useBlogCategoryStore()

// ================= COMPUTED =================
const posts = computed(() => store.posts)
const totalPages = computed(() => store.pagination?.last_page || 1)
const totalPosts = computed(() => store.pagination?.total || 0)

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
const errors = ref({})

const params = ref({
    search: '',
    sort: 'created_at_desc',
    status: null,
    limit: 10,
    page: 1
})

const dataForm = ref({
    id: null,
    blog_category_id: '',
    title: '',
    slug: '',
    summary: '',
    content: '',
    status: 'draft',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    image: null,
    preview: ''
})

// ================= MAP =================
const sortMap = [
    { id: 'created_at_asc', name: "Cũ nhất" },
    { id: 'created_at_desc', name: "Mới nhất" },
]

const filterMap = [
    { id: 'published', name: 'Đã xuất bản' },
    { id: 'draft', name: 'Bản nháp' },
]

const limitMap = [
    { id: 10, name: '10' },
    { id: 20, name: '20' },
    { id: 50, name: '50' },
]

const statusMap = [
    { id: 'published', name: 'Đã xuất bản' },
    { id: 'draft', name: 'Bản nháp' }
]

// ================= METHODS =================
const loadData = async () => {
    loadingData.value = true
    await Promise.all([
        store.fetchPosts(params.value.page),
        categoryStore.categories.length === 0 ? categoryStore.fetchCategories() : Promise.resolve()
    ])
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        blog_category_id: '',
        title: '',
        slug: '',
        summary: '',
        content: '',
        status: 'draft',
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        image: null,
        preview: ''
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

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (!file) return

    dataForm.value.image = file
    dataForm.value.preview = URL.createObjectURL(file)
}

const validate = () => {
    errors.value = {}

    if (!dataForm.value.title?.trim()) errors.value.title = 'Tiêu đề không được để trống'
    if (!dataForm.value.slug?.trim()) errors.value.slug = 'Slug không được để trống'
    if (!dataForm.value.content?.trim()) errors.value.content = 'Nội dung không được để trống'
    if (!dataForm.value.blog_category_id) errors.value.blog_category_id = 'Vui lòng chọn danh mục'

    return Object.keys(errors.value).length === 0
}

const submit = async () => {
    errors.value = {}
    if (!validate()) return

    loadingSubmit.value = true

    const formData = new FormData()
    formData.append('blog_category_id', dataForm.value.blog_category_id);
    formData.append('title', dataForm.value.title);
    formData.append('slug', dataForm.value.slug);
    formData.append('summary', dataForm.value.summary || '');
    formData.append('content', dataForm.value.content);
    formData.append('status', dataForm.value.status);
    formData.append('meta_title', dataForm.value.meta_title || '');
    formData.append('meta_description', dataForm.value.meta_description || '');
    formData.append('meta_keywords', dataForm.value.meta_keywords || '');

    if (dataForm.value.status === 'published' && !dataForm.value.id) {
        formData.append('published_at', new Date().toISOString().slice(0, 19).replace('T', ' '));
    }

    if (dataForm.value.image instanceof File) {
        formData.append('image', dataForm.value.image)
    }

    let success;
    try {
        if (dataForm.value.id) {
            formData.append('_method', 'PUT'); // Laravel requirement
            success = await store.updatePost(dataForm.value.id, formData)
        } else {
            success = await store.createPost(formData)
        }

        if (success) {
            closeForm()
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
    if (item.image) {
        dataForm.value.preview = item.image
    }
    isShow.value = true
}

const destroy = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa bài viết này không?')
    if (!result.isConfirmed) return
    
    const res = await store.deletePost(id)
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
    () => {},
    { deep: true }
)

// ================= INIT =================
onMounted(loadData)
</script>

<template>
    <!-- List -->
    <BaseAdmin :total="totalPosts" :totalPages="totalPages" :currentPage="params.page" v-model:params="params"
        :sortMap="sortMap" :filterMap="filterMap" :limitMap="limitMap" @search="search" @open="openCreateForm"
        @changePage="changePage">
        <template #table>
            <PostTable :params="params" :loadingData="loadingData" :data="posts" @update="update"
                @destroy="destroy" />
        </template>
    </BaseAdmin>

    <!-- Form -->
    <PostForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :categories="categoriesMap" :errors="errors" :statusMap="statusMap" @submit="submit"
        @close="closeForm" @handleImageChange="handleImageChange" />
</template>
