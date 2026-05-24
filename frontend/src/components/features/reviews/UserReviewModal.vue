<script setup>
import { ref, watch } from 'vue'
import { useReviewStore } from '@/stores/review'
import { useNotify } from '@/composables/useNotify'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import { API_URL, API_URL_IMAGE } from '@/config/env'

const props = defineProps({
    isShow: Boolean,
    orderId: [Number, String],
    items: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['update:isShow', 'close', 'success'])

const reviewStore = useReviewStore()
const toast = useNotify()

const reviewsData = ref([])
const loadingSubmit = ref(false)

const getProductImage = (item) => {
    const filename = item.product_variant?.image || item.product?.thumbnail
    return filename ? `${API_URL_IMAGE}/${filename}` : '/placeholder.png'
}

watch(() => props.items, (newItems) => {
    // Reset previous previews
    reviewsData.value.forEach(review => {
        review.previews.forEach(p => URL.revokeObjectURL(p))
    })

    if (newItems && newItems.length > 0) {
        reviewsData.value = newItems.map(item => ({
            product_id: item.product_id,
            product_name: item.product_name,
            product_image: getProductImage(item),
            rating: 5,
            comment: '',
            images: [],
            previews: []
        }))
    } else {
        reviewsData.value = []
    }
}, { deep: true, immediate: true })

const setRating = (index, ratingValue) => {
    reviewsData.value[index].rating = ratingValue
}

const handleFileChange = (e, index) => {
    const files = Array.from(e.target.files)
    const review = reviewsData.value[index]

    if (review.images.length + files.length > 5) {
        toast.error('Bạn chỉ có thể tải lên tối đa 5 hình ảnh.')
        return
    }

    files.forEach(file => {
        if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type)) {
            toast.error('Chỉ chấp nhận file ảnh định dạng JPG, PNG, WEBP.')
            return
        }
        if (file.size > 2 * 1024 * 1024) {
            toast.error('Kích thước mỗi ảnh tối đa là 2MB.')
            return
        }

        review.images.push(file)
        review.previews.push(URL.createObjectURL(file))
    })
}

const removeImage = (reviewIndex, imgIdx) => {
    const review = reviewsData.value[reviewIndex]
    review.images.splice(imgIdx, 1)
    URL.revokeObjectURL(review.previews[imgIdx])
    review.previews.splice(imgIdx, 1)
}

const submitReview = async () => {
    // Validate all
    for (const review of reviewsData.value) {
        if (!review.rating) {
            toast.error(`Vui lòng chọn số sao đánh giá cho sản phẩm ${review.product_name}.`)
            return
        }
    }

    loadingSubmit.value = true
    try {
        const promises = reviewsData.value.map(async (review) => {
            const formData = new FormData()
            formData.append('product_id', review.product_id)
            formData.append('order_id', props.orderId)
            formData.append('rating', review.rating)
            formData.append('comment', review.comment)

            review.images.forEach(file => {
                formData.append('images[]', file)
            })

            return reviewStore.store(formData)
        })

        const results = await Promise.all(promises)
        const allSuccess = results.every(res => res?.success)

        if (allSuccess) {
            toast.success('Đánh giá sản phẩm thành công!')
            emit('success')
            closeModal()
        } else {
            const failed = results.find(res => !res?.success)
            toast.error(failed?.message || 'Có lỗi xảy ra khi gửi đánh giá. Vui lòng kiểm tra lại.')
        }
    } catch (e) {
        toast.error('Lỗi khi kết nối đến máy chủ.')
    } finally {
        loadingSubmit.value = false
    }
}

const closeModal = () => {
    reviewsData.value.forEach(review => {
        review.previews.forEach(p => URL.revokeObjectURL(p))
    })
    reviewsData.value = []
    emit('update:isShow', false)
    emit('close')
}
</script>

<template>
    <div v-if="isShow" class="user-review-overlay" @click.self="closeModal">
        <div class="user-review-card animate__animated animate__fadeInUp">
            <div class="modal-header">
                <h3 class="modal-title">Đánh giá sản phẩm</h3>
                <button @click="closeModal" class="btn-close"><i class="ph ph-x"></i></button>
            </div>

            <div class="modal-body">
                <div v-for="(review, index) in reviewsData" :key="review.product_id" class="review-item-container p-3 mb-3 border rounded-4 bg-light">
                    <!-- Product Header Info -->
                    <div class="product-header-box mb-3">
                        <div class="product-thumb">
                            <img v-if="review.product_image" :src="review.product_image" alt="Product Thumbnail" />
                            <div v-else class="no-image">★</div>
                        </div>
                        <div class="product-info">
                            <h4 class="product-name">{{ review.product_name }}</h4>
                            <span class="order-tag">Mã đơn hàng: #{{ orderId }}</span>
                        </div>
                    </div>

                    <!-- Rating Stars Selection -->
                    <div class="rating-selection-section mb-3">
                        <label class="section-label">Độ hài lòng của bạn</label>
                        <div class="stars-row">
                            <span v-for="star in 5" :key="star" @click="setRating(index, star)" class="star-btn" :class="{ 'filled': star <= review.rating }">★</span>
                        </div>
                        <span class="rating-text">
                            {{ 
                                review.rating === 1 ? 'Rất không hài lòng' : 
                                review.rating === 2 ? 'Không hài lòng' : 
                                review.rating === 3 ? 'Bình thường' : 
                                review.rating === 4 ? 'Hài lòng' : 'Cực kỳ hài lòng' 
                            }}
                        </span>
                    </div>

                    <!-- Text Area Comment -->
                    <div class="comment-input-section mb-3">
                        <label class="section-label">Chia sẻ cảm nhận về sản phẩm</label>
                        <textarea v-model="review.comment" placeholder="Sản phẩm có chất lượng như thế nào? Phom dáng, chất liệu vải ra sao?..." class="comment-textarea"></textarea>
                    </div>

                    <!-- Multi-image Upload & Previews -->
                    <div class="image-upload-section">
                        <label class="section-label">Hình ảnh thực tế (Tối đa 5 hình ảnh)</label>
                        <div class="upload-grid">
                            <!-- Custom File Upload Button -->
                            <label class="upload-btn-card">
                                <input type="file" multiple accept="image/*" @change="handleFileChange($event, index)" class="hidden-file-input" />
                                <i class="ph-bold ph-camera"></i>
                                <span>Thêm ảnh</span>
                            </label>

                            <!-- Previews cards -->
                            <div v-for="(preview, idx) in review.previews" :key="idx" class="preview-card">
                                <img :src="preview" alt="Upload Preview" />
                                <button @click="removeImage(index, idx)" type="button" class="btn-remove-preview" title="Xóa ảnh này">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer actions -->
            <div class="modal-footer">
                <button @click="closeModal" class="btn-action-outline">Hủy bỏ</button>
                <BaseButton v-if="!loadingSubmit" @click="submitReview" customText="Gửi đánh giá" customClass="btn-action-primary" />
                <div v-else class="loading-submit">
                    <BaseLoading size="sm" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.user-review-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(6px);
}

.user-review-card {
    background: #ffffff;
    width: 95%;
    max-width: 600px;
    border-radius: 24px;
    padding: 24px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 16px;
}

.modal-title {
    font-size: 18px;
    font-weight: 900;
    color: var(--text-main);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #64748b;
    cursor: pointer;
    transition: color 0.2s;
}

.btn-close:hover {
    color: var(--text-main);
}

.modal-body {
    overflow-y: auto;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding-right: 4px;
}

.review-item-container {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
}

.product-header-box {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 12px;
}

.product-thumb {
    width: 56px;
    height: 56px;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
    border: 1px solid var(--border);
    flex-shrink: 0;
}

.product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main);
    line-height: 1.4;
}

.order-tag {
    font-size: 11px;
    color: var(--text-muted);
}

.section-label {
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    color: var(--text-muted);
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: block;
}

.rating-selection-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
}

.stars-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

.star-btn {
    font-size: 32px;
    color: #e2e8f0;
    cursor: pointer;
    transition: transform 0.2s, color 0.2s;
}

.star-btn:hover {
    transform: scale(1.15);
}

.star-btn.filled {
    color: #fbbf24;
}

.rating-text {
    font-size: 12px;
    font-weight: 800;
    color: var(--primary);
}

.comment-textarea {
    width: 100%;
    height: 100px;
    border-radius: 12px;
    border: 1px solid var(--border);
    padding: 12px;
    font-size: 14px;
    color: var(--text-main);
    resize: none;
    outline: none;
    transition: border-color 0.2s;
}

.comment-textarea:focus {
    border-color: var(--primary);
}

.upload-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.upload-btn-card {
    height: 90px;
    border: 2px dashed var(--border);
    background: #ffffff;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    color: #64748b;
    transition: background 0.2s, border-color 0.2s;
}

.upload-btn-card:hover {
    background: #f1f5f9;
    border-color: var(--primary);
}

.upload-btn-card i {
    font-size: 24px;
}

.upload-btn-card span {
    font-size: 11px;
    font-weight: 700;
}

.hidden-file-input {
    display: none;
}

.preview-card {
    height: 90px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border);
    position: relative;
    background: #ffffff;
}

.preview-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.btn-remove-preview {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    cursor: pointer;
}

.btn-remove-preview:hover {
    background: #ef4444;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    border-top: 1px solid #f1f5f9;
    padding-top: 16px;
    margin-top: 16px;
}

.btn-action-outline {
    background: #f1f5f9;
    color: #64748b;
    font-weight: 800;
    border: none;
    padding: 10px 20px;
    border-radius: 100px;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-action-outline:hover {
    background: #e2e8f0;
}

:deep(.btn-action-primary) {
    padding: 10px 24px;
    border-radius: 100px;
    font-size: 13px;
    font-weight: 800;
}

.loading-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 30px;
}

::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
</style>
