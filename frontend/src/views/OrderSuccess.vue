<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { API_URL, API_URL_IMAGE } from '@/config/env'
import { useTokenStore } from '@/stores/token'
import BaseLoading from '@/components/base/BaseLoading.vue'

const route = useRoute()
const tokenStore = useTokenStore()
const orderCode = route.params.code

const loading = ref(true)
const order = ref(null)

const fetchOrder = async () => {
    try {
        const res = await axios.get(`${API_URL}/orders/code/${orderCode}`, {
            headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
            order.value = res.data.data
        }
    } catch (error) {
        console.error('Lỗi lấy thông tin đơn hàng:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    fetchOrder()
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const getProductImage = (item) => {
    const filename = item.product_variant?.image || item.product?.thumbnail
    return filename ? `${API_URL_IMAGE}/${filename}` : '/placeholder.png'
}

const getStatusLabel = (status) => {
    const labels = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'processing': 'Đang chuẩn bị hàng',
        'shipping': 'Đang giao hàng',
        'delivered': 'Hoàn thành',
        'cancelled': 'Đã hủy',
        'refunded': 'Đã hoàn tiền'
    }
    return labels[status] || status
}
</script>

<template>
    <div class="order-success-page min-vh-100 bg-light py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div v-if="loading" class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <BaseLoading text="Đang tải..." />
                    </div>

                    <div v-else-if="order" class="card border-0 shadow-premium rounded-4 overflow-hidden p-3 p-md-4">
                        <div class="text-center mb-4">
                            <div class="success-icon-wrapper mb-3">
                                <div class="success-icon">
                                    <i class="ph-fill ph-check-circle"></i>
                                </div>
                                <div class="confetti-ring"></div>
                            </div>
                            <h4 class="fw-black mb-1">Đặt hàng thành công!</h4>
                            <p class="text-muted x-small">Cảm ơn bạn đã tin tưởng StyleHub.</p>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="info-card h-100">
                                    <div class="d-flex align-items-center justify-content-between bg-light rounded-3 p-2 px-3 border border-dashed border-dark-subtle">
                                        <div class="d-flex flex-column">
                                            <span class="text-muted xx-small uppercase fw-bold">Mã đơn hàng</span>
                                            <span class="fw-black text-primary small">{{ order.order_code }}</span>
                                        </div>
                                        <span class="badge-status">{{ getStatusLabel(order.status) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card h-100">
                                    <div class="bg-light rounded-3 p-2 px-3 border border-dashed border-dark-subtle h-100 d-flex flex-column justify-content-center">
                                        <span class="text-muted xx-small uppercase fw-bold">Ngày đặt</span>
                                        <span class="fw-bold small">{{ new Date(order.created_at).toLocaleString('vi-VN', { dateStyle: 'short', timeStyle: 'short' }) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="order-details-section mb-4">
                            <h6 class="fw-black xx-small uppercase tracking-wider text-muted mb-3">Sản phẩm của bạn</h6>
                            <div class="item-list max-vh-30 pe-1">
                                <div v-for="item in order.order_details" :key="item.id" class="item-row d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                                    <div class="item-img">
                                        <img :src="getProductImage(item)" class="object-fit-cover rounded-2">
                                    </div>
                                    <div class="item-info flex-grow-1 overflow-hidden">
                                        <div class="fw-bold x-small text-truncate">{{ item.product_name }}</div>
                                        <div v-if="item.variant_name" class="variant-text text-muted xx-small uppercase">{{ item.variant_name }}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-0">
                                            <span class="xx-small fw-bold">SL: {{ item.quantity }}</span>
                                            <span class="fw-black text-primary x-small">{{ formatPrice(item.price) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pricing-summary bg-light rounded-4 p-3 mb-4">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted xx-small">Tạm tính:</span>
                                <span class="fw-bold xx-small">{{ formatPrice(order.subtotal_amount) }}</span>
                            </div>
                            <div v-if="order.discount_amount > 0" class="d-flex justify-content-between mb-1">
                                <span class="text-muted xx-small">Giảm giá:</span>
                                <span class="fw-bold text-danger xx-small">-{{ formatPrice(order.discount_amount) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted xx-small">Phí vận chuyển:</span>
                                <span class="fw-bold text-success xx-small">+{{ formatPrice(order.shipping_fee) }}</span>
                            </div>
                            <div class="divider my-2"></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-black uppercase xx-small tracking-wider">Tổng thanh toán</span>
                                <span class="fw-black h5 mb-0 text-primary">{{ formatPrice(order.total_amount) }}</span>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-6">
                                <router-link to="/user/orders" class="btn btn-dark w-100 rounded-pill py-2 fw-black xx-small">
                                    LỊCH SỬ ĐƠN HÀNG
                                </router-link>
                            </div>
                            <div class="col-6">
                                <router-link to="/" class="btn btn-outline-dark w-100 rounded-pill py-2 fw-black xx-small">
                                    TIẾP TỤC MUA SẮM
                                </router-link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <i class="ph ph-warning-circle text-danger h1 mb-3"></i>
                        <h6 class="fw-bold">Không tìm thấy đơn hàng</h6>
                        <router-link to="/" class="btn btn-dark rounded-pill mt-3 px-4 xx-small">Quay về</router-link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fw-black { font-weight: 900 !important; }
.uppercase { text-transform: uppercase; }
.x-small { font-size: 13px; }
.xx-small { font-size: 11px; }
.tracking-wider { letter-spacing: 0.1em; }

.shadow-premium { box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
.rounded-4 { border-radius: 20px !important; }

.success-icon-wrapper {
    position: relative;
    width: 70px;
    height: 70px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.success-icon {
    font-size: 50px;
    color: #10b981;
    z-index: 2;
    animation: scaleUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.confetti-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border: 3px solid #10b981;
    border-radius: 50%;
    animation: ringExpand 1s ease-out forwards;
}

@keyframes scaleUp {
    from { transform: scale(0); }
    to { transform: scale(1); }
}

@keyframes ringExpand {
    from { transform: scale(0.5); opacity: 1; }
    to { transform: scale(1.5); opacity: 0; }
}

.badge-status {
    background: #000;
    color: #fff;
    font-size: 8px;
    font-weight: 900;
    padding: 3px 10px;
    border-radius: 100px;
    text-transform: uppercase;
}

.item-img {
    width: 50px;
    height: 50px;
    flex-shrink: 0;
}
.item-img img {
    width: 100%;
    height: 100%;
}

.divider { height: 1px; background: #e2e8f0; }

.max-vh-30 { max-height: 30vh; overflow-y: auto; }
::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

.border-dashed { border-style: dashed !important; }
</style>
