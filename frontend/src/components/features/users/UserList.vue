<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useUserStore } from '@/stores/user'
import { swalConfirmDelete } from '@/composables/useSwal'
import { useNotify } from '@/composables/useNotify'

import UserTable from './UserTable.vue'
import UserForm from './UserForm.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
const store = useUserStore()
const toast = useNotify()
const users = computed(() => store.users)
const totalPages = computed(() => store.pagination.last_page)
const totalUsers = computed(() => store.pagination.total)
const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const isEdit = ref(false)
const errors = ref({})

const params = ref({
    search: '',
    sort: 'created_at_desc',
    status: null,
    limit: 15,
    page: 1
})

const dataForm = ref({
    id: null,
    name: '',
    email: '',
    password: '',
    role: 'user',
    status: 1
})

const sortMap = [
    { id: 'created_at_asc', name: "Cũ nhất" },
    { id: 'created_at_desc', name: "Mới nhất" },
]

const filterMap = [
    { id: 1, name: 'Hoạt động' },
    { id: 0, name: 'Đã khóa' },
]

const limitMap = [
    { id: 15, name: '15' },
    { id: 30, name: '30' },
    { id: 50, name: '50' },
    { id: 100, name: '100' },
]

const statusMap = [
    { id: 1, name: 'Hoạt động' },
    { id: 0, name: 'Khóa' }
]

const roleMap = [
    { id: 'Admin', name: 'Admin' },
    { id: 'user', name: 'User' }
]

const loadData = async () => {
    loadingData.value = true
    await store.index(params.value)
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        name: '',
        email: '',
        password: '',
        role: 'user',
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

    if (!dataForm.value.name?.trim()) errors.value.name = 'Họ tên không được để trống'
    if (!dataForm.value.email?.trim()) errors.value.email = 'Email không được để trống'

    if (dataForm.value.id && dataForm.value.password && dataForm.value.password.length < 8) {
        errors.value.password = 'Mật khẩu phải có ít nhất 8 ký tự'
    }

    return Object.keys(errors.value).length === 0
}

const submit = async () => {
    errors.value = {}
    if (!validate()) return

    loadingSubmit.value = true

    let result
    if (dataForm.value.id) {
        result = await store.update(dataForm.value.id, dataForm.value)
    } else {
        result = await store.store(dataForm.value)
    }

    if (!result?.success) {
        toast.error(result?.message || "Lỗi khi lưu dữ liệu")
        if (result?.errors) {
            errors.value = {
                name: result.errors.name?.[0] ?? "",
                email: result.errors.email?.[0] ?? "",
                password: result.errors.password?.[0] ?? "",
                role: result.errors.role?.[0] ?? "",
                status: result.errors.status?.[0] ?? "",
            }
        }
    } else {
        toast.success(result?.message || "Thành công")
        closeForm()
        loadData()
    }
    loadingSubmit.value = false
}

const update = async (item) => {
    const data = await store.show(item.id)
    if (!data || !data.success) return
    dataForm.value = {
        ...data.data,
        password: '',
    }
    isEdit.value = true
    isShow.value = true
}

const destroy = async (id) => {
    const confirm = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa người dùng này không?')
    if (!confirm.isConfirmed) return

    const result = await store.destroy(id)
    if (!result?.success) {
        toast.error(result?.message || "Lỗi khi xóa dữ liệu")
        return
    }
    toast.success(result?.message || "Xóa dữ liệu thành công")
    loadData()
}

const search = () => {
    params.value.page = 1
    loadData()
}

const changePage = (page) => {
    params.value.page = page
}

watch(
    () => ({ ...params.value }),
    loadData,
    { deep: true }
)

onMounted(() => {
    loadData()
})
</script>

<template>
    <BaseAdmin :total="totalUsers" :totalPages="totalPages" :currentPage="params.page" v-model:params="params"
        :sortMap="sortMap" :filterMap="filterMap" :limitMap="limitMap" @search="search" @open="openCreateForm"
        @changePage="changePage">
        <template #table>
            <UserTable :params="params" :loadingData="loadingData" :data="users" @update="update"
                @destroy="destroy" />
        </template>
    </BaseAdmin>
    <UserForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :errors="errors" :statusMap="statusMap" :roleMap="roleMap" @submit="submit" @close="closeForm" />
</template>
