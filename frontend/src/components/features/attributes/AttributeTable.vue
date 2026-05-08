<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean
})

const emit = defineEmits(["show", "update", "destroy", "openValues"])

</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên thuộc tính</th>
                    <th>Slug</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                    <th>Giá trị</th>
                </tr>
            </thead>

            <tbody>
                <!-- Loading -->
                <tr v-if="loadingData">
                    <td colspan="6" class="text-center" style="padding: 32px 0;">
                        <BaseLoading />
                    </td>
                </tr>

                <!-- Không có dữ liệu -->
                <tr v-else-if="!data || data.length === 0">
                    <td colspan="6" class="text-center" style="padding: 32px 0; color: var(--text-muted);">
                        Không có dữ liệu
                    </td>
                </tr>

                <!-- Có dữ liệu -->
                <tr v-else v-for="(item, index) in data" :key="item.id">
                    <td>
                        {{ (params.page - 1) * params.limit + index + 1 }}
                    </td>

                    <td>
                        <strong style="color: var(--text-main);">{{ item.name }}</strong>
                    </td>

                   

                    <td>
                        <small style="color: var(--text-muted); font-family: monospace; background: var(--background); padding: 4px 8px; border-radius: 4px;">{{ item.slug }}</small>
                    </td>

                    <td>
                        <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                            {{ item.status ? 'Hiển thị' : 'Ẩn' }}
                        </span>
                    </td>

                    <td>
                        <div class="action-group">
                            <BaseButton @click="emit('show', item)" customText="Xem" customClass="btn-action btn-view" />
                            <BaseButton @click="emit('update', item)" customText="Sửa" customClass="btn-action btn-edit" />
                            <BaseButton @click="emit('destroy', item.id)" customText="Xóa" customClass="btn-action btn-delete" />
                        </div>
                    </td>

                     <td>
                        <BaseButton @click="emit('openValues', item)" customText="Giá trị" 
                            customClass="btn-action btn-values" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.btn-values {
    background-color: var(--primary) !important;
    color: white !important;
    border-radius: 999px !important;
    padding: 4px 16px !important;
    font-size: 13px !important;
}

.btn-action {
    transition: all 0.2s ease;
    cursor: pointer;
    font-weight: 600;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    filter: brightness(1.1);
}

.btn-action:active {
    transform: translateY(0);
}
</style>
