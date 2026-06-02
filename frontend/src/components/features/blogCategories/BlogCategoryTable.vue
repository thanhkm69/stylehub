<script setup>
import BaseButton from '@/components/base/BaseButton.vue';
import BaseLoading from '@/components/base/BaseLoading.vue';

const props = defineProps({
    params: Object,
    loadingData: Boolean,
    data: Array
})

const emit = defineEmits(["update", "destroy", "show"])

</script>

<template>
    <table class="admin-table">
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
            <tr v-if="loadingData">
                <td colspan="5" class="loading-cell">
                    <BaseLoading />
                </td>
            </tr>
            <tr v-else v-for="item in data" :key="item.id">
                <td>#{{ item.id }}</td>
                <td>
                    <div class="fw-medium text-dark">{{ item.name }}</div>
                </td>
                <td class="text-muted">{{ item.slug }}</td>
                <td class="text-muted max-w-xs truncate">{{ item.description }}</td>
                <td>
                    <div class="action-buttons justify-content-end">
                        <BaseButton @click="emit('update', item)" customText="Sửa" customClass="btn-action btn-edit" />
                        <BaseButton @click="emit('destroy', item.id)" customText="Xóa" customClass="btn-action btn-delete" />
                    </div>
                </td>
            </tr>
            <tr v-if="!loadingData && data.length === 0">
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
    background: var(--surface);
    color: var(--text-main);
}

.admin-table th {
    background: var(--muted);
    color: var(--text-muted);
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px;
    border-bottom: 2px solid var(--border);
    white-space: nowrap;
}

.admin-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
    background: var(--surface);
    color: var(--text-main);
}

.loading-cell {
    height: 320px;
    padding: 0 !important;
}

.admin-table tbody tr {
    transition: all 0.2s;
}

.admin-table tbody tr:hover {
    background: var(--muted);
}

.admin-table tbody tr:hover td {
    background: var(--muted);
}

.admin-table .text-dark {
    color: var(--text-main) !important;
}

.admin-table .text-muted {
    color: var(--text-muted) !important;
}

.action-buttons {
    display: flex;
    gap: 8px;
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
