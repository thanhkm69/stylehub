<script setup>
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';

const props = defineProps({
    params: Object,
    loadingData: Boolean,
    data: Array
})

const emit = defineEmits(["update", "destroy", "show"])

</script>

<template>
    <div v-if="loadingData" class="text-center py-5">
        <BaseSpinner />
    </div>

    <table v-else class="admin-table">
        <thead>
            <tr>
                <th>Hình ảnh</th>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Trạng thái</th>
                <th>Ngày đăng</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data" :key="index">
                <td>
                    <div class="product-img-wrapper">
                        <img v-if="item.image" :src="item.image" alt="Thumbnail" />
                        <div v-else class="img-placeholder">
                            <i class="ph-light ph-image"></i>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="fw-medium text-dark max-w-xs truncate">{{ item.title }}</div>
                    <div class="text-muted text-xs mt-1">{{ item.slug }}</div>
                </td>
                <td>
                    <span class="category-badge">{{ item.blog_category?.name }}</span>
                </td>
                <td>
                    <span :class="['status-badge', item.status === 'published' ? 'active' : 'inactive']">
                        <i :class="item.status === 'published' ? 'ph-fill ph-check-circle' : 'ph-fill ph-x-circle'"></i>
                        {{ item.status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}
                    </span>
                </td>
                <td class="text-muted">
                    {{ new Date(item.created_at).toLocaleDateString('vi-VN') }}
                </td>
                <td>
                    <div class="action-buttons justify-content-end">
                        <BaseButton @click="emit('update', item)" customClass="btn-icon btn-light" customTitle="Sửa">
                            <i class="ph-bold ph-pencil-simple text-primary"></i>
                        </BaseButton>
                        <BaseButton @click="emit('destroy', item.id)" customClass="btn-icon btn-light" customTitle="Xóa">
                            <i class="ph-bold ph-trash text-danger"></i>
                        </BaseButton>
                    </div>
                </td>
            </tr>
            <tr v-if="data.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">
                    <div class="empty-state">
                        <i class="ph-light ph-folder-dashed empty-icon"></i>
                        <p>Không tìm thấy bài viết nào</p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<style scoped>
.admin-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.admin-table th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}

.admin-table td {
    padding: 16px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.admin-table tbody tr {
    transition: all 0.2s;
}

.admin-table tbody tr:hover {
    background: #f8fafc;
}

.product-img-wrapper {
    width: 60px;
    height: 45px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #ffffff;
}

.product-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #94a3b8;
}

.img-placeholder i {
    font-size: 20px;
}

.category-badge {
    background: #e0e7ff;
    color: #4338ca;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.active {
    background: #dcfce7;
    color: #166534;
}

.status-badge.inactive {
    background: #f1f5f9;
    color: #475569;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-icon {
    width: 36px;
    height: 36px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}

.btn-icon i {
    font-size: 18px;
}

.btn-icon:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-icon:hover .text-primary {
    color: var(--primary) !important;
}

.btn-icon:hover .text-danger {
    color: #ef4444 !important;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.empty-icon {
    font-size: 48px;
    color: #cbd5e1;
}

.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.max-w-xs {
    max-width: 20rem;
}
</style>
