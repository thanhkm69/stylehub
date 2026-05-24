<script setup>
import { computed } from 'vue'
import BaseModal from '@/components/base/BaseModal.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import { API_URL_IMAGE } from '@/config/env'

const props = defineProps({
    review: Object,
    isShow: Boolean
})

const emit = defineEmits(['update:isShow', 'close', 'toggleStatus'])

const isShowComputed = computed({
    get: () => props.isShow,
    set: (value) => emit('update:isShow', value)
})
</script>

<template>
    <BaseModal v-model:isShow="isShowComputed" title="Chi tiết đánh giá sản phẩm" customWidth="800px" @close="emit('close')">
        <div v-if="review" class="review-detail-content">
            <!-- Top Section: Customer & Product Summary -->
            <div class="detail-header-grid">
                <!-- Customer Details -->
                <div class="info-card">
                    <div class="card-icon"><i class="ph ph-user"></i></div>
                    <div class="card-body">
                        <h3>Khách hàng</h3>
                        <p class="main-info">{{ review.user?.name || 'Ẩn danh' }}</p>
                        <p class="sub-info">ID tài khoản: #{{ review.user?.id || '---' }}</p>
                    </div>
                </div>

                <!-- Order and Date Details -->
                <div class="info-card">
                    <div class="card-icon"><i class="ph ph-shopping-cart"></i></div>
                    <div class="card-body">
                        <h3>Đơn hàng & Thời gian</h3>
                        <p class="main-info">Đơn hàng #{{ review.order_id || '---' }}</p>
                        <p class="sub-info">Ngày đánh giá: {{ review.created_at || '---' }}</p>
                    </div>
                </div>
            </div>

            <!-- Middle Section: Product Details -->
            <div class="product-detail-section">
                <h4>Sản phẩm được đánh giá</h4>
                <div class="product-horizontal-card">
                    <div class="product-image">
                        <img v-if="review.product?.thumbnail" :src="`${API_URL_IMAGE}/${review.product?.thumbnail}`" alt="Product Thumbnail" />
                        <div v-else class="no-image">★</div>
                    </div>
                    <div class="product-metadata">
                        <h5>{{ review.product?.name || '---' }}</h5>
                        <p class="product-slug">Slug: {{ review.product?.slug || '---' }}</p>
                        <p class="product-id">ID sản phẩm: #{{ review.product?.id || '---' }}</p>
                    </div>
                </div>
            </div>

            <!-- Rating & Comment Section -->
            <div class="rating-comment-section">
                <div class="rating-bar-row">
                    <div class="rating-label">Điểm đánh giá:</div>
                    <div class="rating-stars">
                        <span v-for="star in 5" :key="star" class="star" :class="{ 'filled': star <= review.rating }">★</span>
                        <span class="rating-number font-semibold">({{ review.rating }}/5 sao)</span>
                    </div>
                </div>

                <div class="comment-display">
                    <label class="comment-label">Nội dung bình luận:</label>
                    <div class="comment-body">
                        {{ review.comment || 'Khách hàng không để lại bình luận.' }}
                    </div>
                </div>
            </div>

            <!-- Uploaded Images Section -->
            <div v-if="review.images && review.images.length > 0" class="uploaded-images-section">
                <h4>Hình ảnh phản hồi ({{ review.images.length }})</h4>
                <div class="images-grid">
                    <div v-for="(img, idx) in review.images" :key="idx" class="image-gallery-card">
                        <a :href="`${API_URL_IMAGE}/${img}`" target="_blank" title="Xem ảnh kích thước đầy đủ">
                            <img :src="`${API_URL_IMAGE}/${img}`" alt="Feedback Photo" />
                            <div class="overlay"><i class="ph ph-magnifying-glass-plus"></i> Phóng to</div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Section -->
            <div class="status-summary-row">
                <div class="status-indicator">
                    <span class="status-label">Trạng thái hiển thị:</span>
                    <span :class="['badge-status', review.status ? 'badge-active' : 'badge-inactive']">
                        {{ review.status ? 'Hiển thị công khai' : 'Đang ẩn' }}
                    </span>
                </div>
                <div class="status-actions">
                    <BaseButton @click="emit('toggleStatus', review)" :customText="review.status ? 'Ẩn đánh giá này' : 'Hiển thị đánh giá'" :customClass="['btn-action', review.status ? 'btn-deactivate' : 'btn-activate']" />
                </div>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.review-detail-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.detail-header-grid {
    display: grid;
    grid-template-cols: 1fr;
    gap: 16px;
}

@media (min-width: 640px) {
    .detail-header-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.info-card {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.card-icon {
    font-size: 28px;
    color: var(--primary);
    background: rgba(var(--primary-rgb), 0.1);
    width: 52px;
    height: 52px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-body h3 {
    font-size: 13px;
    color: var(--text-muted);
    font-weight: 500;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.main-info {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-main);
}

.sub-info {
    font-size: 12px;
    color: var(--text-muted);
}

.product-detail-section h4, 
.uploaded-images-section h4 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 12px;
}

.product-horizontal-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px;
}

.product-image {
    width: 64px;
    height: 64px;
    border-radius: 8px;
    border: 1px solid var(--border);
    overflow: hidden;
    background: #fff;
    flex-shrink: 0;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 24px;
}

.product-metadata h5 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-main);
    margin-bottom: 4px;
}

.product-slug {
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 2px;
}

.product-id {
    font-size: 12px;
    color: var(--text-muted);
}

.rating-comment-section {
    background: #f8fafc;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.rating-bar-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 12px;
}

.rating-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-main);
}

.rating-stars {
    display: flex;
    align-items: center;
    gap: 4px;
}

.star {
    font-size: 20px;
    color: #e2e8f0;
}

.star.filled {
    color: #fbbf24;
}

.rating-number {
    font-size: 14px;
    color: var(--text-muted);
    margin-left: 8px;
}

.comment-display {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.comment-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-main);
}

.comment-body {
    font-size: 14px;
    color: var(--text-main);
    line-height: 1.6;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 12px;
    min-height: 80px;
    white-space: pre-wrap;
}

.uploaded-images-section {
    width: 100%;
}

.images-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

@media (min-width: 480px) {
    .images-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 640px) {
    .images-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.image-gallery-card {
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
    position: relative;
    background: #000;
}

.image-gallery-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.image-gallery-card:hover img {
    transform: scale(1.05);
    opacity: 0.8;
}

.image-gallery-card .overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    font-size: 11px;
    text-align: center;
    padding: 4px 0;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-gallery-card:hover .overlay {
    opacity: 1;
}

.status-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    border-top: 1px solid var(--border);
    padding-top: 16px;
    margin-top: 8px;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-label {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-main);
}

:deep(.btn-action) {
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
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
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

:deep(.btn-deactivate:hover) {
    background: #fee2e2;
}
</style>
