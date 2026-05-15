<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean
})

const emit = defineEmits(["show", "update", "destroy", "openImages", "openVariants"])

</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Sản phẩm</th>
                    <th>Giá</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                    <th>ẢnhSP</th>
                    <th>Biến thể</th>
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
                    <td>
                        {{ (params.page - 1) * params.limit + index + 1 }}
                    </td>

                    <td>
                        <div style="display: flex; align-items: center; gap: 12px; text-align: left;">
                            <img v-if="item.thumbnail" :src="'http://localhost:8000/storage/' + item.thumbnail"
                                alt="thumbnail" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; flex-shrink: 0; border: 1px solid var(--border-color, #eee);" />
                            <div v-else style="width: 60px; height: 60px; background-color: var(--bg-soft, #f3f4f6); border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--border-color, #eee);">
                                <span style="color: var(--text-muted); font-size: 12px;">N/A</span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <strong style="color: var(--text-main); font-size: 14px; line-height: 1.4; white-space: normal;">{{ item.name }}</strong>
                                <span style="color: var(--text-muted); font-size: 12px;">{{ item.slug }}</span>
                                <span style="font-size: 11px; color: var(--primary, #3b82f6); background-color: var(--primary-light, rgba(59, 130, 246, 0.1)); padding: 2px 8px; border-radius: 4px; width: fit-content; font-weight: 500;">
                                    {{ item.category?.name || 'Không có danh mục' }}
                                </span>
                            </div>
                        </div>
                    </td>

                    <td>
                        <span style="color: var(--text-main);">{{ new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(item.price) }}</span>
                    </td>

                    <td>
                        <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                            {{ item.status ? 'Hiển thị' : 'Ẩn' }}
                        </span>
                    </td>

                    <td>
                        <div class="action-group">

                            <BaseButton @click="emit('show', item)" customText="Xem"
                                customClass="btn-action btn-view" />
                            <BaseButton @click="emit('update', item)" customText="Sửa"
                                customClass="btn-action btn-edit" />
                            <BaseButton @click="emit('destroy', item.id)" customText="Xóa"
                                customClass="btn-action btn-delete" />
                        </div>
                    </td>
                    <td>
                        <BaseButton @click="emit('openImages', item)" customText="ẢnhSP"
                            customClass="btn-action btn-view" style="background-color: var(--primary); color: white;" />
                    </td>
                    <td>
                        <BaseButton @click="emit('openVariants', item)" customText="Biến thể"
                            customClass="btn-action btn-view" style="background-color: #f59e0b; color: white;" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
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
