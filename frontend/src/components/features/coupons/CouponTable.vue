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
</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã</th>
                    <th>Tên</th>
                    <th>Loại</th>
                    <th>Giảm giá</th>
                    <th>Đơn tối thiểu</th>
                    <th>Bắt đầu</th>
                    <th>Hết hạn</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody>
                <!-- Loading -->
                <tr v-if="loadingData">
                    <td colspan="10" class="text-center" style="padding: 32px 0;">
                        <BaseLoading />
                    </td>
                </tr>

                <!-- Không có dữ liệu -->
                <tr v-else-if="!data || data.length === 0">
                    <td colspan="10" class="text-center" style="padding: 32px 0; color: var(--text-muted);">
                        Không có dữ liệu
                    </td>
                </tr>

                <!-- Có dữ liệu -->
                <tr v-else v-for="(item, index) in data" :key="item.id">
                    <td>{{ (params.page - 1) * params.limit + index + 1 }}</td>

                    <td>
                        <strong style="color: var(--primary); font-family: monospace;">{{ item.code }}</strong>
                    </td>

                    <td>
                        <span style="color: var(--text-main);">{{ item.name }}</span>
                    </td>

                    <td>
                        <span :class="['badge-status', item.discount_type === 'percentage' ? 'badge-percent' : 'badge-fixed']">
                            {{ item.discount_type === 'percentage' ? '% Phần trăm' : '₫ Cố định' }}
                        </span>
                    </td>

                    <td>
                        <strong style="color: var(--danger);">{{ formatDiscount(item) }}</strong>
                    </td>

                    <td>{{ formatCurrency(item.min_order_value) }}</td>

                    <td style="font-size: 12px; color: var(--text-muted);">
                        {{ item.starts_at ?? '—' }}
                    </td>

                    <td style="font-size: 12px; color: var(--text-muted);">
                        {{ item.expires_at ?? '—' }}
                    </td>

                    <td>
                        <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                            {{ item.status ? 'Hoạt động' : 'Tạm ngưng' }}
                        </span>
                    </td>

                    <td>
                        <div class="action-group">
                            <BaseButton @click="emit('update', item)" customText="Sửa" customClass="btn-action btn-edit" />
                            <BaseButton @click="emit('destroy', item.id)" customText="Xóa" customClass="btn-action btn-delete" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.badge-percent {
    background-color: #e0f2fe;
    color: #0369a1;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 20px;
    font-weight: 600;
}
.badge-fixed {
    background-color: #fef3c7;
    color: #92400e;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 20px;
    font-weight: 600;
}
</style>
