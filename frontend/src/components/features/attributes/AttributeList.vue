<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAttributeStore } from '@/stores/attribute'
import { swalConfirmDelete } from '@/composables/useSwal'

import AttributeTable from './AttributeTable.vue'
import AttributeForm from './AttributeForm.vue'
import AttributeValueModal from './AttributeValueModal.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
import { useNotify } from '@/composables/useNotify'

// ================= STORE =================
const store = useAttributeStore()

// ================= NOTIFY =================
const toast = useNotify()

// ================= COMPUTED =================
const attributes = computed(() => store.attributes)
const totalPages = computed(() => store.pagination.last_page)
const totalAttributes = computed(() => store.pagination.total)

// ================= STATE =================
const loadingData = ref(false)
const loadingSubmit = ref(false)
const isShow = ref(false)
const isEdit = ref(false)
const isShowValuesModal = ref(false)
const selectedAttribute = ref(null)
const errors = ref({})

const params = ref({
    search: '',
    sort: 'created_at_desc',
    status: null,
    limit: 5,
    page: 1
})

const dataForm = ref({
    id: null,
    name: '',
    slug: '',
    status: 1
})

// ================= MAP =================
const sortMap = [
    { id: 'name_asc', name: "A -> Z" },
    { id: 'name_desc', name: "Z -> A" },
    { id: 'created_at_asc', name: "Cũ nhất" },
    { id: 'created_at_desc', name: "Mới nhất" },
]

const filterMap = [
    { id: 1, name: 'Thuộc tính hiện' },
    { id: 0, name: 'Thuộc tính ẩn' },
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
        name: '',
        slug: '',
        status: 1
    }
    errors.value = {}
}

const closeForm = () => {
    isShow.value = false
    isEdit.value = false
    resetForm()
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

    // ===== STATUS =====
    if (dataForm.value.status !== null) {
        if (![0, 1, true, false].includes(dataForm.value.status)) {
            errors.value.status = 'Không hợp lệ'
        }
    }

    return Object.keys(errors.value).length === 0
}

const submit = async () => {

    errors.value = {}

    if (!validate()) return

    loadingSubmit.value = true

    const payload = {
        name: dataForm.value.name,
        slug: dataForm.value.slug,
        status: dataForm.value.status
    }

    let result


    if (dataForm.value.id) {
        result = await store.update(dataForm.value.id, payload)
    } else {
        result = await store.store(payload)
    }

    if (!result?.success) {
        toast.error(result?.message || "Lỗi khi lưu dữ liệu");
        if (result?.errors) {
            errors.value = {
                name: result.errors.name?.[0] ?? "",
                slug: result.errors.slug?.[0] ?? "",
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
    if (!data) return
    dataForm.value = { ...item }
    isEdit.value = true
    isShow.value = true
}

const destroy = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa thuộc tính này không ?')
    if (!result.isConfirmed) return
    const res = await store.destroy(id)
    loadData()
}

const show = (item) => {
    dataForm.value = { ...item }
    isShow.value = true
}

const openValues = (item) => {
    selectedAttribute.value = item
    isShowValuesModal.value = true
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
    <BaseAdmin :total="totalAttributes" :totalPages="totalPages" :currentPage="params.page" v-model:params="params"
        :sortMap="sortMap" :filterMap="filterMap" :limitMap="limitMap" @search="search" @open="isShow = true"
        @changePage="changePage">
        <template #table>
            <AttributeTable :params="params" :loadingData="loadingData" :data="attributes" @update="update"
                @destroy="destroy" @show="show" @openValues="openValues" />
        </template>
    </BaseAdmin>

    <!-- Form -->
    <AttributeForm v-model:loadingSubmit="loadingSubmit" v-model:dataForm="dataForm" v-model:isShow="isShow"
        :errors="errors" :statusMap="statusMap" @submit="submit" @close="closeForm" />

    <!-- Attribute Values Modal -->
    <AttributeValueModal v-model:isShow="isShowValuesModal" :attribute="selectedAttribute" @valuesChanged="loadData"
        @close="isShowValuesModal = false" />
</template>