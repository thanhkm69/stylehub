<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean,
})

const emit = defineEmits(['update', 'destroy', 'manageItems'])

const comboTypeLabel = {
    fixed_combo: { text: 'Combo cố định', cls: 'badge-fixed' },
    buy_get:     { text: 'Mua tặng',      cls: 'badge-buy' },
    bundle:      { text: 'Gói sản phẩm',  cls: 'badge-bundle' },
}

const discountTypeLabel = {
    percentage:   '%',
    fixed_price:  '₫ giảm',
    bundle_price: '₫ gói',
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
                    <th>Ảnh</th>
                    <th>Tên Combo</th>
                    <th>Loại combo</th>
                    <th>Giảm giá</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Thứ tự</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loadingData">
                    <td colspan="10" class="text-center" style="padding: 32px 0;">
                        <BaseLoading />
                    </td>
                </tr>
                <tr v-else-if="!data || data.length === 0">
                    <td colspan="10" class="text-center" style="padding: 32px 0; color: var(--text-muted);">
                        Không có dữ liệu
                    </td>
                </tr>
                <tr v-else v-for="(item, index) in data" :key="item.id">
                    <td>{{ (params.page - 1) * params.limit + index + 1 }}</td>

                    <td>
                        <img v-if="item.thumbnail"
                            :src="'http://localhost:8000/storage/' + item.thumbnail"
                            alt="thumbnail"
                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" />
                        <span v-else style="color: var(--text-muted); font-size: 12px;">N/A</span>
                    </td>

                    <td>
                        <strong style="color: var(--text-main);">{{ item.name }}</strong>
                    </td>

                    <td>
                        <span :class="['badge-type', comboTypeLabel[item.combo_type]?.cls]">
                            {{ comboTypeLabel[item.combo_type]?.text ?? item.combo_type }}
                        </span>
                    </td>

                    <td style="color: var(--danger); font-weight: 600;">
                        {{ item.discount_value }}
                        <span style="font-size: 11px; color: var(--text-muted);">
                            {{ discountTypeLabel[item.discount_type] }}
                        </span>
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
                                style="background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary-light);" />
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
.badge-type {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
}
.badge-fixed  { background: #e0f2fe; color: #0369a1; }
.badge-buy    { background: #fef3c7; color: #92400e; }
.badge-bundle { background: #f3e8ff; color: #7c3aed; }
</style>
