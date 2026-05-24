<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean
})

const emit = defineEmits(["show", "update", "destroy"])

const formatCurrency = (value) =>
    new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)

const formatDiscount = (item) => {
    if (item.discount_type === 'percentage') return `${item.discount_value}%`
    return formatCurrency(item.discount_value)
}

const formatDate = (dateString) => {
    if (!dateString) return 'Không giới hạn';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString; // fallback
    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}
</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 60px;">STT</th>
                    <th>Thông tin mã</th>
                    <th>Chi tiết giảm giá</th>
                    <th>Thời gian áp dụng</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-center" style="width: 120px;">Trạng thái</th>
                    <th class="text-center" style="width: 150px;">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                <!-- Loading -->
                <tr v-if="loadingData">
                    <td colspan="7" class="text-center" style="padding: 48px 0;">
                        <BaseLoading />
                    </td>
                </tr>

                <!-- Không có dữ liệu -->
                <tr v-else-if="!data || data.length === 0">
                    <td colspan="7" class="text-center empty-state">
                        <i class="ph ph-ticket-slash"></i>
                        <p>Không có dữ liệu mã giảm giá</p>
                    </td>
                </tr>

                <!-- Có dữ liệu -->
                <tr v-else v-for="(item, index) in data" :key="item.id">
                    <td class="text-center text-muted">
                        {{ (params.page - 1) * params.limit + index + 1 }}
                    </td>

                    <td>
                        <div class="coupon-info">
                            <span class="coupon-code">{{ item.code }}</span>
                            <span class="coupon-name">{{ item.name }}</span>
                        </div>
                    </td>

                    <td>
                        <div class="discount-detail">
                            <div class="discount-main">
                                <span class="discount-value">{{ formatDiscount(item) }}</span>
                                <span :class="['badge-type', item.discount_type === 'percentage' ? 'type-percent' : 'type-fixed']">
                                    {{ item.discount_type === 'percentage' ? 'Phần trăm' : 'Cố định' }}
                                </span>
                            </div>
                            <div class="discount-condition">
                                Đơn tối thiểu: <strong>{{ formatCurrency(item.min_order_value) }}</strong>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="time-range">
                            <div class="time-item">
                                <i class="ph ph-calendar-plus text-success"></i>
                                <span>{{ formatDate(item.starts_at) }}</span>
                            </div>
                            <div class="time-item">
                                <i class="ph ph-calendar-x text-danger"></i>
                                <span :class="{'text-danger font-medium': !item.expires_at}">
                                    {{ formatDate(item.expires_at) }}
                                </span>
                            </div>
                        </div>
                    </td>

                    <td class="text-center font-medium">
                        {{ item.usage_limit ? item.usage_limit : 'Không giới hạn' }}
                    </td>

                    <td class="text-center">
                        <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                            <span class="status-dot"></span>
                            {{ item.status ? 'Hoạt động' : 'Tạm ngưng' }}
                        </span>
                    </td>

                    <td>
                        <div class="action-group">
                            <BaseButton @click="emit('update', item)" customClass="btn-action btn-edit" title="Chỉnh sửa">
                                <i class="ph ph-pencil-simple"></i>
                            </BaseButton>
                            <BaseButton @click="emit('destroy', item.id)" customClass="btn-action btn-delete" title="Xóa">
                                <i class="ph ph-trash"></i>
                            </BaseButton>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.admin-table-wrapper {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border: 1px solid var(--border-color, #e2e8f0);
    overflow: hidden;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 600;
    padding: 16px;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

.table td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 14px;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.text-center {
    text-align: center !important;
}

.text-muted {
    color: #64748b;
}

.text-success {
    color: #10b981;
}

.text-danger {
    color: #ef4444;
}

.font-medium {
    font-weight: 500;
}

.empty-state {
    padding: 48px 0;
    color: #94a3b8;
}

.empty-state i {
    font-size: 32px;
    margin-bottom: 8px;
    color: #cbd5e1;
}

/* Coupon Info */
.coupon-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.coupon-code {
    font-family: 'JetBrains Mono', 'Courier New', monospace;
    font-size: 13px;
    font-weight: 700;
    color: var(--primary, #0f172a);
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-block;
    width: fit-content;
    letter-spacing: 0.5px;
    border: 1px dashed #cbd5e1;
}

.coupon-name {
    font-size: 13px;
    color: #475569;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-weight: 500;
}

/* Discount Detail */
.discount-detail {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.discount-main {
    display: flex;
    align-items: center;
    gap: 8px;
}

.discount-value {
    font-size: 15px;
    font-weight: 700;
    color: #ef4444;
}

.badge-type {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.type-percent {
    background-color: #e0f2fe;
    color: #0369a1;
}

.type-fixed {
    background-color: #fef3c7;
    color: #92400e;
}

.discount-condition {
    font-size: 12px;
    color: #64748b;
}

.discount-condition strong {
    color: #334155;
    font-weight: 600;
}

/* Time Range */
.time-range {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.time-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #475569;
}

.time-item i {
    font-size: 16px;
}

.time-item span {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
}

/* Status Badge */
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.badge-active {
    background-color: #dcfce7;
    color: #166534;
}

.badge-active .status-dot {
    background-color: #22c55e;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
}

.badge-inactive {
    background-color: #f1f5f9;
    color: #475569;
}

.badge-inactive .status-dot {
    background-color: #94a3b8;
}

/* Actions */
.action-group {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

:deep(.btn-action) {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    background: transparent;
}

:deep(.btn-action i) {
    font-size: 16px;
}

:deep(.btn-edit) {
    color: #3b82f6;
    background: #eff6ff;
}

:deep(.btn-edit:hover) {
    background: #dbeafe;
    color: #2563eb;
}

:deep(.btn-delete) {
    color: #ef4444;
    background: #fef2f2;
}

:deep(.btn-delete:hover) {
    background: #fee2e2;
    color: #dc2626;
}
</style>
