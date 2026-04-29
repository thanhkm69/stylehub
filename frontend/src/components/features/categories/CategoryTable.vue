<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import { API_URL_IMAGE } from '@/config/env'
import { computed } from 'vue'

const props = defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean
})

const emit = defineEmits(["show", "update", "destroy"])

const categorizedData = computed(() => {
    let rootIndex = 0

    return (props.data || []).map(item => {
        if (!item.parent_id) {
            rootIndex++
            return {
                ...item,
                stt: (props.params.page - 1) * props.params.limit + rootIndex,
                isParent: true
            }
        }

        return {
            ...item,
            stt: '', // Sub-items don't strictly need a new index or can inherit parent context
            isParent: false
        }
    })
})

</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th width="80">STT</th>
                    <th width="100">Hình ảnh</th>
                    <th>Tên danh mục</th>
                    <th>Mô tả</th>
                    <th width="100">Thứ tự</th>
                    <th width="120">Trạng thái</th>
                    <th width="200">Hành động</th>
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
                <tr v-else-if="!categorizedData || categorizedData.length === 0">
                    <td colspan="7" class="text-center" style="padding: 32px 0; color: var(--text-muted);">
                        Không có dữ liệu
                    </td>
                </tr>

                <!-- Có dữ liệu -->
                <tr v-else v-for="(item, index) in categorizedData" :key="item.id" :class="{ 'row-child': !item.isParent }">
                    <td class="text-center">
                        <span v-if="item.isParent" class="parent-stt">{{ item.stt }}</span>
                        <span v-else class="child-indicator"><i class="ph ph-dots-three-vertical"></i></span>
                    </td>
                    <td>
                        <div class="category-image-wrapper">
                            <img v-if="item.image" :src="`${API_URL_IMAGE}/${item.image}`" :alt="item.name">
                            <div v-else class="no-image"><i class="ph ph-image"></i></div>
                        </div>
                    </td>

                    <td :style="{ paddingLeft: (item.level * 32 + 12) + 'px' }">
                        <div class="name-container">
                            <span v-if="!item.isParent" class="tree-branch"></span>
                            <div>
                                <strong class="category-name">{{ item.name }}</strong>
                                <div class="category-slug">{{ item.slug }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="desc-cell">
                        {{ item.description || '---' }}
                    </td>
                    <td class="text-center">
                        <span class="display-order">{{ item.display }}</span>
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
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.row-child {
    background-color: #fafafa;
}

.parent-stt {
    font-weight: 700;
    color: var(--text-main);
}

.child-indicator {
    color: #cbd5e1;
    font-size: 18px;
}

.category-image-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 10px;
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

.name-container {
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.tree-branch {
    position: absolute;
    left: -20px;
    top: -12px;
    width: 16px;
    height: 24px;
    border-left: 2px solid #e2e8f0;
    border-bottom: 2px solid #e2e8f0;
    border-bottom-left-radius: 8px;
}

.category-name {
    color: var(--text-main);
    font-size: 15px;
}

.category-slug {
    font-size: 12px;
    color: var(--text-muted);
    font-family: 'JetBrains Mono', monospace;
    opacity: 0.8;
}

.desc-cell {
    max-width: 250px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: var(--text-muted);
    font-size: 14px;
}

.display-order {
    display: inline-flex;
    padding: 2px 8px;
    background: #f1f5f9;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
    color: #475569;
}

.text-center { text-align: center; }

:deep(.btn-action) {
    padding: 6px 12px;
}
</style>