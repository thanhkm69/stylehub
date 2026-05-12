<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean
})

const emit = defineEmits(["update", "destroy"])

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody>
                <!-- Loading -->
                <tr v-if="loadingData">
                    <td colspan="7" class="text-center" style="padding: 32px 0;">
                        <BaseLoading />
                    </td>
                </tr>

                <!-- Không có dữ liệu -->
                <tr v-else-if="!data || data.length === 0">
                    <td colspan="7" class="text-center" style="padding: 32px 0; color: var(--text-muted);">
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
                        <span style="color: var(--text-main);">{{ item.email }}</span>
                    </td>

                    <td>
                        <span :class="['badge-role', item.role?.toLowerCase() === 'admin' ? 'badge-admin' : 'badge-user']">
                            {{ item.role?.toLowerCase() === 'admin' ? 'Admin' : 'User' }}
                        </span>
                    </td>

                    <td>
                        <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                            {{ item.status ? 'Hoạt động' : 'Khóa' }}
                        </span>
                    </td>

                    <td>
                        <span style="color: var(--text-muted); font-size: 13px;">
                            {{ formatDate(item.created_at) }}
                        </span>
                    </td>

                    <td>
                        <div class="action-group">
                            <BaseButton @click="emit('update', item)" customText="Sửa"
                                customClass="btn-action btn-edit" />
                            <BaseButton @click="emit('destroy', item.id)" customText="Xóa"
                                customClass="btn-action btn-delete" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.badge-role {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-admin {
    background: #fef3c7;
    color: #d97706;
}

.badge-user {
    background: #e0e7ff;
    color: #4f46e5;
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
