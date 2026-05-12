<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import { API_URL_IMAGE } from '@/config/env'

const props = defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean
})

const emit = defineEmits(["update", "destroy", "openItems"])

const statusLabel = {
    draft:     { text: 'Nháp',       cls: 'badge-draft' },
    active:    { text: 'Đang chạy',  cls: 'badge-active' },
    ended:     { text: 'Đã kết thúc',cls: 'badge-ended' },
    cancelled: { text: 'Đã hủy',    cls: 'badge-cancelled' },
}
</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Ảnh</th>
                    <th>Tên Flash Sale</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Thứ tự</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
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
                <tr v-else-if="!data || data.length === 0">
                    <td colspan="8" class="text-center" style="padding: 32px 0; color: var(--text-muted);">
                        Không có dữ liệu
                    </td>
                </tr>

                <!-- Có dữ liệu -->
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

                    <td style="font-size: 13px; color: var(--text-muted);">{{ item.starts_at ?? '—' }}</td>
                    <td style="font-size: 13px; color: var(--text-muted);">{{ item.ends_at ?? '—' }}</td>

                    <td>{{ item.display }}</td>

                    <td>
                        <span :class="['badge-status', statusLabel[item.status]?.cls]">
                            {{ statusLabel[item.status]?.text ?? item.status }}
                        </span>
                    </td>

                    <td>
                        <div class="action-group">
                            <BaseButton @click="emit('openItems', item)" customText="Sản phẩm"
                                customClass="btn-action btn-view" style="background-color: #8b5cf6; color: white; border: none;" />
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
.badge-draft     { background: #f1f5f9; color: #64748b; font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.badge-active    { background: #dcfce7; color: #166534; font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.badge-ended     { background: #fef3c7; color: #92400e; font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
.badge-cancelled { background: #fee2e2; color: #991b1b; font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
</style>
