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
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Slug</th>
                <th>Mô tả</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(item, index) in data" :key="index">
                <td>#{{ item.id }}</td>
                <td>
                    <div class="fw-medium text-dark">{{ item.name }}</div>
                </td>
                <td class="text-muted">{{ item.slug }}</td>
                <td class="text-muted max-w-xs truncate">{{ item.description }}</td>
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
                <td colspan="5" class="text-center py-5 text-muted">
                    <div class="empty-state">
                        <i class="ph-light ph-folder-dashed empty-icon"></i>
                        <p>Không tìm thấy danh mục nào</p>
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
