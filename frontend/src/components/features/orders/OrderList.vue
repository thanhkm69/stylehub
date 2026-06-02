<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useAdminOrderStore } from '@/stores/adminOrder'
import { swalConfirmDelete } from '@/composables/useSwal'
import { useNotify } from '@/composables/useNotify'

import OrderTable from './OrderTable.vue'
import OrderForm from './OrderForm.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
import BaseInputSelect from '@/components/base/BaseInputSelect.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
const props = defineProps({
    title: String,
    description: String
})

// ================= STORE =================
const store = useAdminOrderStore()

// ================= NOTIFY =================
const toast = useNotify()

// ================= COMPUTED =================
const orders = computed(() => store.orders)
const totalPages = computed(() => store.pagination.last_page)
const totalOrders = computed(() => store.pagination.total)

// ================= STATE =================
const loadingData = ref(false)
const loadingSubmit = ref(false)
const loadingDetail = ref(false) // Trạng thái tải chi tiết riêng biệt
const isShow = ref(false)

const params = ref({
    search: '',
    status: '',
    payment_status: '',
    limit: 15,
    page: 1,
})

const dataForm = ref({})

// ================= MAPS =================
const statusMap = [
    { id: 'pending', name: 'Chờ xác nhận' },
    { id: 'confirmed', name: 'Đã xác nhận' },
    { id: 'processing', name: 'Đang chuẩn bị hàng' },
    { id: 'shipping', name: 'Đang giao hàng' },
    { id: 'delivered', name: 'Hoàn thành' },
    { id: 'cancelled', name: 'Đã hủy' },
    { id: 'refunded', name: 'Đã hoàn tiền' }
]

const paymentStatusMap = [
    { id: 'unpaid', name: 'Chưa thanh toán' },
    { id: 'paid', name: 'Đã thanh toán' },
    { id: 'refunded', name: 'Đã hoàn tiền' }
]

const limitMap = [
    { id: 15, name: '15' },
    { id: 30, name: '30' },
    { id: 50, name: '50' }
]

// ================= METHODS =================
const loadData = async (silent = false) => {
    if (!silent) loadingData.value = true
    try {
        await store.index(params.value)
    } finally {
        loadingData.value = false
    }
}

const update = async (item) => {
    loadingDetail.value = true // Chỉ hiện loading nhỏ hoặc overlay nếu cần, không làm mất bảng
    try {
        const result = await store.show(item.id)
        if (result?.success) {
            dataForm.value = { ...result.data }
            isShow.value = true
        } else {
            toast.error(result?.message || 'Không thể tải chi tiết đơn hàng')
        }
    } finally {
        loadingDetail.value = false
    }
}

const submit = async () => {
    loadingSubmit.value = true
    try {
        const payload = {
            status: dataForm.value.status,
            payment_status: dataForm.value.payment_status,
            cancelled_reason: dataForm.value.cancelled_reason,
            admin_note: dataForm.value.admin_note
        }

        const result = await store.update(dataForm.value.id, payload)
        if (result?.success) {
            toast.success('Cập nhật đơn hàng thành công')
            isShow.value = false
            loadData(true) // Tải ngầm, không hiện logo
        } else {
            toast.error(result?.message || 'Có lỗi xảy ra')
        }
    } catch (error) {
        toast.error('Lỗi kết nối máy chủ')
    } finally {
        loadingSubmit.value = false
    }
}

const destroy = async (id) => {
    const confirm = await swalConfirmDelete('Xác nhận', 'Bạn có chắc chắn muốn xóa đơn hàng này? Thao tác này không thể hoàn tác.')
    if (!confirm.isConfirmed) return

    const result = await store.destroy(id)
    if (result?.success) {
        toast.success('Xóa đơn hàng thành công')
        loadData()
    } else {
        toast.error(result?.message || 'Không thể xóa đơn hàng này')
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

const closeForm = () => {
    isShow.value = false
}

// ================= WATCH =================
watch(
    () => ({ status: params.value.status, payment_status: params.value.payment_status, limit: params.value.limit }),
    () => {
        params.value.page = 1
        loadData()
    }
)

// ================= INIT =================
onMounted(() => {
    loadData()
})
</script>

<template>
    <div class="order-list-container">
        <!-- Sử dụng BaseLoading (Logo SH) thay cho vòng xoay cũ -->
        <div v-if="loadingDetail" class="detail-loading-overlay">
            <BaseLoading text="Đang lấy chi tiết..." />
        </div>

        <BaseAdmin :title="props.title" :description="props.description" :total="totalOrders" :totalPages="totalPages"
            :currentPage="params.page" v-model:params="params" :limitMap="limitMap" @search="search"
            @changePage="changePage" :hideOpenBtn="true">
            <template #filters>
                <BaseInputSelect v-model="params.status" customId="status-filter" :values="statusMap"
                    placeholder="Trạng thái đơn" />
                <BaseInputSelect v-model="params.payment_status" customId="payment-filter" :values="paymentStatusMap"
                    placeholder="Thanh toán" />
            </template>

            <template #table>
                <OrderTable :data="orders" :loadingData="loadingData" :params="params" @update="update"
                    @destroy="destroy" />
            </template>
        </BaseAdmin>

        <!-- Detail & Edit Form -->
        <OrderForm v-model:isShow="isShow" :dataForm="dataForm" :loadingSubmit="loadingSubmit" :statusMap="statusMap"
            :paymentStatusMap="paymentStatusMap" @submit="submit" @close="closeForm" />
    </div>
</template>

<style scoped>
.order-list-container {
    position: relative;
}

.detail-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(255, 255, 255, 0.8);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}

:global(.dark) .detail-loading-overlay {
    background: rgba(9, 9, 11, 0.9);
}

.order-list-container :deep(.toolbar-right) {
    gap: 8px;
}

.order-list-container :deep(.auth-input) {
    min-width: 140px !important;
}
</style>
