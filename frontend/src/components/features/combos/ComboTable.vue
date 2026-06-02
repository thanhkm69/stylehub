<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean,
})

const emit = defineEmits(['update', 'destroy', 'manageItems'])

const discountTypeLabel = {
    percentage:   '%',
    fixed_price:  '₫ giảm',
}

const formatCurrency = (val) =>
    new Intl.NumberFormat('vi-VN').format(val) + 'đ'
</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Combo</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Thứ tự</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loadingData">
                    <td colspan="7" class="text-center" style="padding: 32px 0;">
                        <BaseLoading />
                    </td>
                </tr>
                <tr v-else-if="!data || data.length === 0">
                    <td colspan="7" class="text-center" style="padding: 32px 0; color: var(--text-muted);">
                        Không có dữ liệu
                    </td>
                </tr>
                <tr v-else v-for="(item, index) in data" :key="item.id">
                    <td>{{ (params.page - 1) * params.limit + index + 1 }}</td>

                    <td>
                        <div class="combo-summary">
                            <img v-if="item.thumbnail"
                                :src="'http://localhost:8000/storage/' + item.thumbnail"
                                :alt="item.name"
                                class="combo-thumbnail" />
                            <div v-else class="combo-thumbnail combo-thumbnail-empty">N/A</div>
                            <div class="combo-summary-content">
                                <strong>{{ item.name }}</strong>
                                <span class="combo-discount">
                                    {{ item.discount_value }}
                                    <small>{{ discountTypeLabel[item.discount_type] }}</small>
                                </span>
                            </div>
                        </div>
                    </td>

                    <td style="font-size: 12px; color: var(--text-muted);">{{ item.starts_at ?? '—' }}</td>
                    <td style="font-size: 12px; color: var(--text-muted);">{{ item.ends_at ?? '—' }}</td>

                    <td>{{ item.display }}</td>

                    <td>
                        <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                            {{ item.status ? 'Hiện' : 'Ẩn' }}
                        </span>
                    </td>

                    <td>
                        <div class="action-group">
                            <BaseButton @click="emit('manageItems', item)" customText="Sản phẩm"
                                customClass="btn-action btn-manage"
                                style="background: #7c3aed; color: #ffffff; border: 1px solid #7c3aed;" />
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
.combo-summary {
    align-items: center;
    display: flex;
    gap: 14px;
    min-width: 245px;
}

.combo-thumbnail {
    border-radius: 8px;
    flex-shrink: 0;
    height: 58px;
    object-fit: cover;
    width: 58px;
}

.combo-thumbnail-empty {
    align-items: center;
    background: var(--background);
    color: var(--text-muted);
    display: flex;
    font-size: 11px;
    justify-content: center;
}

.combo-summary-content {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.combo-summary-content strong {
    color: var(--text-main);
}

.combo-discount {
    color: var(--danger);
    font-size: 13px;
    font-weight: 700;
}

.combo-discount small {
    color: var(--text-muted);
    font-size: 11px;
    font-weight: 500;
    margin-left: 3px;
}

:deep(.btn-manage:hover) {
    background: #6d28d9 !important;
    border-color: #6d28d9 !important;
    color: #ffffff !important;
}
</style>
