<script setup>
import { API_URL_IMAGE } from '@/config/env'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
    data: Array,
    loadingData: Boolean,
    params: Object
})

const emit = defineEmits(['update', 'destroy'])
</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hình ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Đường dẫn</th>
                    <th>Vị trí</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
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
                <tr v-else v-for="item in data" :key="item.id">
                    <td>#{{ item.id }}</td>
                    <td class="w-[200px]">
                        <div class="category-image-wrapper">
                            <img v-if="item.image" :src="`${API_URL_IMAGE}/${item.image}`" alt="Banner Image"
                                class="w-full h-full object-cover rounded shadow-sm border border-border" />
                            <div v-else class="no-image"><i class="ph ph-image"></i></div>
                        </div>
                    </td>
                    <td>
                        <strong style="color: var(--text-main);" class="line-clamp-2 max-w-[200px]">
                            {{ item.title || '---' }}
                        </strong>
                    </td>
                    <td>
                        <a v-if="item.link" :href="item.link" target="_blank" class="text-blue-500 hover:underline line-clamp-1 max-w-[150px]">
                            {{ item.link }}
                        </a>
                        <span v-else class="text-gray-400">---</span>
                    </td>
                    <td>
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-md font-medium text-sm border border-gray-200">
                            {{ item.position }}
                        </span>
                    </td>
                    <td>
                        <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                            {{ item.status ? 'Hiển thị' : 'Ẩn' }}
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
.category-image-wrapper {
    width: 100px;
    height: 50px;
    border-radius: 8px;
    border: 1px solid var(--border);
    overflow: hidden;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    color: #cbd5e1;
    font-size: 20px;
}

.text-center { text-align: center; }

:deep(.btn-action) {
    min-width: 52px;
    padding: 10px 14px;
}
</style>

