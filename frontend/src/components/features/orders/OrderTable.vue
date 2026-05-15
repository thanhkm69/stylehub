<script setup>
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
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" @click="emit('update', item)" title="Cập nhật trạng thái">
                                <i class="ph ph-note-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" :disabled="item.status !== 'cancelled'" 
                                @click="emit('destroy', item.id)" title="Xóa đơn hàng">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.custom-table thead th {
    background: #f8fafc;
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
</style>
