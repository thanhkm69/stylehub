<script setup>
import { API_URL_IMAGE } from '@/config/env'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
    data: Array,
    loadingData: Boolean,
    params: Object
})

const emit = defineEmits(['showDetail', 'toggleStatus', 'destroy'])

const toggleStatus = (item) => {
    emit('toggleStatus', item)
}

const showDetail = (item) => {
    emit('showDetail', item)
}

const destroy = (id) => {
    emit('destroy', id)
}
</script>

<template>
    <div class="admin-table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-[80px]">ID</th>
                    <th class="w-[200px]">Khách hàng</th>
                    <th class="w-[250px]">Sản phẩm</th>
                    <th class="w-[150px]">Đánh giá</th>
                    <th class="w-[300px]">Bình luận</th>
                    <th class="w-[120px]">Trạng thái</th>
                    <th class="w-[200px]">Thao tác</th>
                </tr>
            </thead>

            <tbody>
                <!-- Loading -->
                <tr v-if="loadingData">
                    <td colspan="7" class="text-center" style="padding: 32px 0;">
                        <BaseLoading />
                    </td>
                </tr>

                <!-- Empty Data -->
                <tr v-else-if="!data || data.length === 0">
                    <td colspan="7" class="text-center empty-cell">
                        Không có đánh giá nào phù hợp
                    </td>
                </tr>

                <!-- Content -->
                <tr v-else v-for="item in data" :key="item.id">
                    <td>#{{ item.id }}</td>
                    <td>
                        <div class="customer-info">
                            <span class="customer-name">{{ item.user?.name || 'Ẩn danh' }}</span>
                            <span class="customer-id">UID: #{{ item.user?.id || '---' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="product-info">
                            <div class="product-thumb">
                                <img v-if="item.product?.thumbnail" :src="`${API_URL_IMAGE}/${item.product?.thumbnail}`" alt="product thumbnail" />
                                <div v-else class="no-thumb">★</div>
                            </div>
                            <div class="product-details">
                                <span class="product-name line-clamp-1" :title="item.product?.name">{{ item.product?.name || '---' }}</span>
                                <span class="order-id">Đơn hàng: #{{ item.order_id || '---' }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="rating-stars">
                            <span v-for="star in 5" :key="star" class="star" :class="{ 'filled': star <= item.rating }">★</span>
                        </div>
                    </td>
                    <td>
                        <div class="comment-cell">
                            <p class="comment-text line-clamp-2">{{ item.comment || 'Không có bình luận.' }}</p>
                            <div v-if="item.images && item.images.length > 0" class="review-images-preview">
                                <div v-for="(img, idx) in item.images.slice(0, 3)" :key="idx" class="img-preview-thumb">
                                    <img :src="`${API_URL_IMAGE}/${img}`" alt="review photo" />
                                </div>
                                <span v-if="item.images?.length > 3" class="more-images-badge">+{{ item.images?.length - 3 }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span v-if="item.status == 1" class="badge-status badge-active">Hiển thị</span>
                        <span v-else-if="item.status == 2" class="badge-status badge-pending">Chờ duyệt</span>
                        <span v-else class="badge-status badge-inactive">Đã ẩn</span>
                    </td>
                    <td>
                        <div class="action-group">
                            <BaseButton @click="showDetail(item)" customText="Chi tiết" customClass="btn-action btn-view" />
                            <BaseButton @click="toggleStatus(item)" :customText="item.status == 1 ? 'Ẩn' : 'Duyệt'" :customClass="['btn-action', item.status == 1 ? 'btn-deactivate' : 'btn-activate']" />
                            <BaseButton @click="destroy(item.id)" customText="Xóa" customClass="btn-action btn-delete" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.admin-table-wrapper {
    width: 100%;
}

.empty-cell {
    padding: 48px 0;
    color: var(--text-muted);
    font-size: 15px;
}

.customer-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.customer-name {
    font-weight: 600;
    color: var(--text-main);
}

.customer-id {
    font-size: 12px;
    color: var(--text-muted);
}

.product-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.product-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
    background: #f8fafc;
    flex-shrink: 0;
}

.product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-thumb {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 20px;
}

.product-details {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-name {
    font-weight: 600;
    color: var(--text-main);
    max-width: 180px;
}

.order-id {
    font-size: 12px;
    color: var(--text-muted);
}

.rating-stars {
    display: flex;
    align-items: center;
    gap: 2px;
}

.star {
    font-size: 18px;
    color: #e2e8f0;
}

.star.filled {
    color: #fbbf24;
}

.comment-cell {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.comment-text {
    font-size: 14px;
    color: var(--text-main);
    line-height: 1.5;
    max-width: 280px;
}

.review-images-preview {
    display: flex;
    align-items: center;
    gap: 6px;
}

.img-preview-thumb {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    overflow: hidden;
    border: 1px solid var(--border);
}

.img-preview-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.more-images-badge {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid var(--border);
}

.action-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

:deep(.btn-action) {
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
}

:deep(.btn-view) {
    background: #f1f5f9;
    color: #475569;
}

:deep(.btn-view:hover) {
    background: #e2e8f0;
}

:deep(.btn-activate) {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

:deep(.btn-activate:hover) {
    background: #d1fae5;
}

:deep(.btn-deactivate) {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
}

:deep(.btn-deactivate:hover) {
    background: #fef3c7;
}

:deep(.btn-delete) {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

:deep(.btn-delete:hover) {
    background: #fee2e2;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>
