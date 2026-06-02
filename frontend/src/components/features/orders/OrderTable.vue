<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
    data: {
        type: Array,
        default: () => []
    },
    loadingData: {
        type: Boolean,
        default: false
    },
    params: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['update', 'destroy', 'show'])

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const getStatusClass = (status) => {
    const map = {
        'pending': 'bg-warning-subtle text-warning',
        'confirmed': 'bg-info-subtle text-info',
        'processing': 'bg-primary-subtle text-primary',
        'shipping': 'bg-secondary-subtle text-secondary',
        'delivered': 'bg-success-subtle text-success',
        'cancelled': 'bg-danger-subtle text-danger',
        'refunded': 'bg-dark-subtle text-dark'
    }
    return map[status] || 'bg-light text-dark'
}

const getStatusLabel = (status) => {
    const map = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'processing': 'Đang chuẩn bị hàng',
        'shipping': 'Đang giao hàng',
        'delivered': 'Hoàn thành',
        'cancelled': 'Đã hủy',
        'refunded': 'Đã hoàn tiền'
    }
    return map[status] || status
}

const getPaymentStatusClass = (status) => {
    const map = {
        'unpaid': 'text-danger',
        'paid': 'text-success',
        'refunded': 'text-dark'
    }
    return map[status] || ''
}
</script>

<template>
    <div class="table-responsive">
        <table class="table table-hover align-middle custom-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã đơn hàng</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loading -->
                <tr v-if="loadingData">
                    <td colspan="8" class="text-center" style="padding: 32px 0;">
                        <BaseLoading />
                    </td>
                </tr>

                <!-- Không có dữ liệu -->
                <tr v-else-if="data.length === 0">
                    <td colspan="8" class="text-center py-5 text-muted">Không tìm thấy đơn hàng nào</td>
                </tr>

                <!-- Có dữ liệu -->
                <tr v-else v-for="(item, index) in data" :key="item.id">
                    <td>{{ (params.page - 1) * params.limit + index + 1 }}</td>
                    <td>
                        <span class="fw-bold text-primary">{{ item.order_code }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">{{ item.shipping_name }}</span>
                            <small class="text-muted">{{ item.shipping_phone }}</small>
                        </div>
                    </td>
                    <td>
                        <span class="fw-bold">{{ formatPrice(item.total_amount) }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <small :class="getPaymentStatusClass(item.payment_status)" class="fw-bold text-uppercase">
                                {{ item.payment_status === 'paid' ? 'Đã thanh toán' : (item.payment_status === 'unpaid' ? 'Chưa thanh toán' : 'Hoàn tiền') }}
                            </small>
                            <small class="text-muted text-uppercase">{{ item.payment_method }}</small>
                        </div>
                    </td>
                    <td>
                        <span :class="['badge', getStatusClass(item.status)]" class="rounded-pill">
                            {{ getStatusLabel(item.status) }}
                        </span>
                    </td>
                    <td>
                        <small>{{ item.created_at }}</small>
                    </td>
                    <td class="text-end">
                        <div class="action-group justify-content-end">
                            <BaseButton @click="emit('update', item)" customText="Sửa" customClass="btn-action btn-edit" />
                            <BaseButton @click="emit('destroy', item.id)" customText="Xóa"
                                customClass="btn-action btn-delete" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.custom-table {
    --bs-table-bg: var(--surface);
    --bs-table-color: var(--text-main);
    --bs-table-hover-bg: var(--muted);
    --bs-table-hover-color: var(--text-main);
    margin-bottom: 0;
}

.custom-table thead th {
    background: var(--muted);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-muted);
    border-bottom: 2px solid var(--border);
    padding: 10px 12px;
}

.custom-table tbody td {
    padding: 10px 12px;
    font-size: 13px;
    color: var(--text-main);
    border-bottom: 1px solid var(--border);
}

.badge {
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
}

.action-group {
    display: flex;
    gap: 8px;
}

:deep(.btn-action) {
    min-width: 52px;
    padding: 10px 14px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
}

:deep(.btn-edit) {
    background: #eff6ff;
    color: #3b82f6;
}

:deep(.btn-edit:hover) {
    background: #dbeafe;
    color: #2563eb;
}

:deep(.btn-delete) {
    background: #fef2f2;
    color: #ef4444;
}

:deep(.btn-delete:hover) {
    background: #fee2e2;
    color: #dc2626;
}
</style>
