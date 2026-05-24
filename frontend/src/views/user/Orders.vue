<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import { API_URL, API_URL_IMAGE } from '@/config/env'
import { useTokenStore } from '@/stores/token'
import BaseLoading from '@/components/base/BaseLoading.vue'

const tokenStore = useTokenStore()
const orders = ref([])
const loading = ref(true)
const activeFilter = ref('all')

// Pagination state
const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0
})

// Modal state
const showModal = ref(false)
const selectedOrder = ref(null)

const filters = [
    { label: 'Tất cả', value: 'all' },
    { label: 'Chờ xác nhận', value: 'pending' },
    { label: 'Đã xác nhận', value: 'confirmed' },
    { label: 'Chuẩn bị hàng', value: 'processing' },
    { label: 'Đang giao hàng', value: 'shipping' },
    { label: 'Hoàn thành', value: 'delivered' },
    { label: 'Đã hủy', value: 'cancelled' }
]

const steps = [
    { key: 'pending', label: 'Chờ xác nhận', icon: 'ph-paper-plane-tilt' },
    { key: 'confirmed', label: 'Đã xác nhận', icon: 'ph-check-circle' },
    { key: 'processing', label: 'Chuẩn bị hàng', icon: 'ph-package' },
    { key: 'shipping', label: 'Đang giao', icon: 'ph-truck' },
    { key: 'delivered', label: 'Hoàn thành', icon: 'ph-house-line' }
]

const getActiveStep = (status) => {
    const map = { 'pending': 0, 'confirmed': 1, 'processing': 2, 'shipping': 3, 'delivered': 4 }
    return map[status] ?? -1
}

const fetchOrders = async (page = 1) => {
    loading.value = true
    try {
        const params = {
            page,
            per_page: 2
        }
        if (activeFilter.value !== 'all') {
            params.status = activeFilter.value
        }

        const res = await axios.get(`${API_URL}/orders`, {
            params,
            headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
            orders.value = res.data.data
            pagination.value = res.data.meta
        }
    } catch (error) {
        console.error('Lỗi lấy danh sách đơn hàng:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => fetchOrders())

const setFilter = (value) => {
    activeFilter.value = value
    pagination.value.current_page = 1
    fetchOrders(1)
}

const changePage = (page) => {
    if (page < 1 || page > pagination.value.last_page) return
    fetchOrders(page)
}

const openDetails = (order) => {
    selectedOrder.value = order
    showModal.value = true
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'pending': 'bg-warning-subtle text-warning',
        'confirmed': 'bg-info-subtle text-info',
        'processing': 'bg-primary-subtle text-primary',
        'shipping': 'bg-info-subtle text-info',
        'delivered': 'bg-success-subtle text-success',
        'cancelled': 'bg-danger-subtle text-danger',
        'refunded': 'bg-secondary-subtle text-secondary'
    }
    return classes[status] || 'bg-light text-dark'
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

const getProductImage = (item) => {
    const filename = item.product_variant?.image || item.product?.thumbnail
    return filename ? `${API_URL_IMAGE}/${filename}` : '/placeholder.png'
}
</script>

<template>
    <div class="user-orders py-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-black mb-0 uppercase tracking-wider">Đơn hàng của tôi</h6>
            <div class="text-muted xx-small fw-bold">{{ pagination.total }} đơn hàng</div>
        </div>

        <!-- Filters -->
        <div class="filter-tabs mb-3 pb-1 d-flex gap-2 overflow-x-auto">
            <button 
                v-for="filter in filters" 
                :key="filter.value"
                @click="setFilter(filter.value)"
                class="btn-filter"
                :class="{ active: activeFilter === filter.value }"
            >
                {{ filter.label }}
            </button>
        </div>

        <div v-if="loading" class="py-5 text-center">
            <BaseLoading text="Đang tải..." />
        </div>

        <div v-else-if="orders.length > 0" class="order-list">
            <div v-for="order in orders" :key="order.id" class="order-card mb-3 animate__animated animate__fadeIn">
                <!-- Card Header -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="fw-black text-primary xx-small mb-0">{{ order.order_code }}</div>
                        <div class="text-muted xxx-small fw-bold">{{ new Date(order.created_at).toLocaleDateString('vi-VN') }}</div>
                    </div>
                    <span class="badge-status" :class="getStatusBadgeClass(order.status)">
                        {{ getStatusLabel(order.status) }}
                    </span>
                </div>

                <div class="divider-light mb-2"></div>

                <!-- Product List in Main View -->
                <div class="order-items-preview mb-3">
                    <div v-for="item in (order.order_details || []).slice(0, 2)" :key="item.id" class="d-flex align-items-center gap-2 mb-2">
                        <div class="item-thumb-compact">
                            <img :src="getProductImage(item)" class="object-fit-cover rounded-2">
                            <span class="item-qty-sm">{{ item.quantity }}</span>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-bold xx-small text-truncate">{{ item.product_name }}</div>
                            <div v-if="item.variant_name" class="variant-tag-sm">{{ item.variant_name }}</div>
                        </div>
                        <div class="fw-black text-primary xx-small">{{ formatPrice(item.price) }}</div>
                    </div>
                    <div v-if="order.order_details?.length > 2" class="text-center py-1 bg-light rounded-2 xxx-small fw-bold text-muted">
                        + {{ order.order_details.length - 2 }} sản phẩm khác
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-dashed">
                    <div class="total-box">
                        <span class="text-muted xxx-small uppercase fw-bold d-block mb-0">Tổng cộng</span>
                        <span class="fw-black text-dark small mb-0">{{ formatPrice(order.total_amount) }}</span>
                    </div>
                    <button @click="openDetails(order)" class="btn btn-dark rounded-pill px-3 py-1 xx-small fw-black">
                        CHI TIẾT
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="d-flex justify-content-center gap-1 mt-4">
                <button @click="changePage(pagination.current_page - 1)" class="btn-page-sm" :disabled="pagination.current_page === 1">
                    <i class="ph ph-caret-left"></i>
                </button>
                <button v-for="p in pagination.last_page" :key="p" @click="changePage(p)" class="btn-page-sm" :class="{ active: p === pagination.current_page }">
                    {{ p }}
                </button>
                <button @click="changePage(pagination.current_page + 1)" class="btn-page-sm" :disabled="pagination.current_page === pagination.last_page">
                    <i class="ph ph-caret-right"></i>
                </button>
            </div>
        </div>

        <div v-else class="text-center py-5">
            <p class="text-muted xx-small">Không tìm thấy đơn hàng nào.</p>
        </div>

        <!-- Detail Modal -->
        <div v-if="showModal && selectedOrder" class="modal-overlay" @click.self="showModal = false">
            <div class="modal-card animate__animated animate__slideInUp">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-black mb-0 small uppercase tracking-wider">CHI TIẾT ĐƠN HÀNG</h6>
                    <button @click="showModal = false" class="btn-close-custom"><i class="ph ph-x"></i></button>
                </div>

                <div class="modal-body-scroll pe-2">
                    <!-- Shipping Stepper -->
                    <div class="shipping-stepper mb-5">
                        <div class="stepper-line">
                            <div class="progress-line" :style="{ width: (getActiveStep(selectedOrder.status) * 25) + '%' }"></div>
                        </div>
                        <div class="stepper-items">
                            <div v-for="(step, index) in steps" :key="step.key" 
                                class="step-item"
                                :class="{ 
                                    active: getActiveStep(selectedOrder.status) >= index,
                                    current: getActiveStep(selectedOrder.status) === index 
                                }"
                            >
                                <div class="step-icon-box">
                                    <i :class="['ph-bold', step.icon]"></i>
                                </div>
                                <span class="step-label">{{ step.label }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment & Order Status Info -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="info-box-status">
                                <span class="xxx-small uppercase fw-bold text-muted mb-1 d-block">Trạng thái đơn</span>
                                <span class="badge-status w-fit" :class="getStatusBadgeClass(selectedOrder.status)">
                                    {{ getStatusLabel(selectedOrder.status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box-status text-end">
                                <span class="xxx-small uppercase fw-bold text-muted mb-1 d-block">Phương thức thanh toán</span>
                                <span class="fw-black xx-small text-dark d-block mb-1">
                                    {{ 
                                        selectedOrder.payment_method === 'cod' ? 'TIỀN MẶT (COD)' : 
                                        selectedOrder.payment_method === 'momo' ? 'MOMO' : 
                                        selectedOrder.payment_method === 'vnpay' ? 'VNPAY' : selectedOrder.payment_method.toUpperCase()
                                    }}
                                </span>
                                <span class="badge-status w-fit ms-auto d-block" :class="selectedOrder.payment_status === 'paid' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'">
                                    {{ selectedOrder.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="mb-3">
                        <div v-for="item in selectedOrder.order_details" :key="item.id" class="item-detail-row d-flex gap-2 mb-2 pb-2 border-bottom border-light">
                            <div class="item-modal-thumb-sm">
                                <img :src="getProductImage(item)" class="rounded-2 object-fit-cover">
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold xx-small mb-0">{{ item.product_name }}</div>
                                <div v-if="item.variant_name" class="variant-tag-sm">{{ item.variant_name }}</div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="xxx-small fw-bold text-muted">Số lượng: {{ item.quantity }}</span>
                                    <span class="fw-black text-primary xx-small">{{ formatPrice(item.price) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Summary -->
                    <div class="pricing-card-modal p-3 rounded-4 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted xxx-small">Tạm tính:</span>
                            <span class="fw-bold xxx-small">{{ formatPrice(selectedOrder.subtotal_amount) }}</span>
                        </div>
                        <div v-if="selectedOrder.discount_amount > 0" class="d-flex justify-content-between mb-1">
                            <span class="text-muted xxx-small">Giảm giá:</span>
                            <span class="fw-bold text-danger xxx-small">-{{ formatPrice(selectedOrder.discount_amount) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted xxx-small">Phí vận chuyển:</span>
                            <span class="fw-bold text-success xxx-small">+{{ formatPrice(selectedOrder.shipping_fee) }}</span>
                        </div>
                        <div class="divider-dashed my-2"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-black uppercase xx-small tracking-wider">Tổng cộng</span>
                            <span class="fw-black small mb-0 text-primary">{{ formatPrice(selectedOrder.total_amount) }}</span>
                        </div>
                    </div>

                    <!-- Delivery Address -->
                    <div class="address-box-modal p-3 border rounded-4">
                        <h6 class="fw-black xxx-small uppercase tracking-wider text-muted mb-2">Địa chỉ nhận hàng</h6>
                        <div class="fw-bold xx-small mb-1">{{ selectedOrder.shipping_name }} | {{ selectedOrder.shipping_phone }}</div>
                        <div class="text-muted xxx-small">{{ selectedOrder.shipping_full_address }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fw-black { font-weight: 900 !important; }
.uppercase { text-transform: uppercase; }
.small { font-size: 13px; }
.xx-small { font-size: 11px; }
.xxx-small { font-size: 10px; }
.tracking-wider { letter-spacing: 0.1em; }

.btn-filter {
    background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 14px;
    border-radius: 100px; font-size: 10px; font-weight: 800;
    white-space: nowrap; transition: all 0.2s; color: #64748b;
}
.btn-filter.active { background: #000; border-color: #000; color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

.order-card {
    background: #ffffff; border-radius: 20px; padding: 15px;
    border: 1px solid #f1f5f9; box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.badge-status { font-size: 8px; font-weight: 900; padding: 3px 10px; border-radius: 100px; text-transform: uppercase; }
.w-fit { width: fit-content; }

.item-thumb-compact { width: 45px; height: 45px; position: relative; flex-shrink: 0; overflow: hidden; border-radius: 8px; }
.item-thumb-compact img { width: 100%; height: 100%; object-fit: cover; }

.item-qty-sm {
    position: absolute; top: -3px; right: -3px; background: #000; color: #fff;
    font-size: 8px; font-weight: 900; width: 15px; height: 15px;
    display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1.5px solid #fff; z-index: 10;
}

.variant-tag-sm { font-size: 8px; font-weight: 800; color: #64748b; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; display: inline-block; }

.btn-page-sm {
    width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e2e8f0;
    background: #fff; font-size: 11px; font-weight: 800; color: #000;
    display: flex; align-items: center; justify-content: center; transition: all 0.2s;
}
.btn-page-sm.active { background: #000; border-color: #000; color: #fff; }

/* Modal & Stepper */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.7); z-index: 9999;
    display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px);
}
.modal-card { background: #fff; width: 95%; max-width: 500px; border-radius: 30px; padding: 25px; max-height: 90vh; display: flex; flex-direction: column; }
.modal-body-scroll { overflow-y: auto; flex-grow: 1; }

.shipping-stepper { position: relative; padding: 0 5px; }
.stepper-line { position: absolute; top: 15px; left: 30px; right: 30px; height: 2px; background: #f1f5f9; z-index: 1; }
.progress-line { height: 100%; background: #10b981; transition: width 0.5s ease; }

.stepper-items { display: flex; justify-content: space-between; position: relative; z-index: 2; }
.step-item { display: flex; flex-direction: column; align-items: center; gap: 5px; width: 50px; }
.step-icon-box { width: 32px; height: 32px; background: #fff; border: 2px solid #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #cbd5e1; transition: all 0.3s; }
.step-label { font-size: 8px; font-weight: 800; color: #94a3b8; text-transform: uppercase; text-align: center; }

.step-item.active .step-icon-box { border-color: #10b981; color: #10b981; }
.step-item.current .step-icon-box { background: #10b981; color: #fff; }

.info-box-status { background: #f8fafc; padding: 10px 15px; border-radius: 15px; }

.pricing-card-modal { background: #f8fafc; }
.divider-dashed { border-top: 1px dashed #cbd5e1; }
.divider-light { height: 1px; background: #f1f5f9; }
.border-dashed { border-top-style: dashed !important; }

.item-modal-thumb-sm { width: 50px; height: 50px; flex-shrink: 0; overflow: hidden; border-radius: 8px; }
.item-modal-thumb-sm img { width: 100%; height: 100%; object-fit: cover; }

.btn-close-custom { background: none; border: none; font-size: 20px; color: #000; }

.bg-warning-subtle { background-color: #fff8e1; }
.bg-info-subtle { background-color: #e0f2f1; }
.bg-primary-subtle { background-color: #e3f2fd; }
.bg-success-subtle { background-color: #e8f5e9; }
.bg-danger-subtle { background-color: #ffebee; }
.bg-secondary-subtle { background-color: #f5f5f5; }

::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.filter-tabs::-webkit-scrollbar { display: none; }
</style>
