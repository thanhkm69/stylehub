<script setup>
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMoMoStore } from '@/stores/momo'
import { useNotify } from '@/composables/useNotify'
import BaseLoading from '@/components/base/BaseLoading.vue'

const route = useRoute()
const router = useRouter()
const momoStore = useMoMoStore()
const toast = useNotify()

const orderId = route.params.orderId

onMounted(async () => {
    if (!orderId) {
        router.push({ name: 'UserOrders' })
        return
    }
    await momoStore.checkStatus(orderId)
})

const transaction = computed(() => momoStore.transaction)
const isLoading = computed(() => momoStore.loading)
const hasError = computed(() => !!momoStore.error)

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const handleRetryPayment = async () => {
    if (!orderId) return
    const res = await momoStore.createPayment(orderId)
    if (res.success && res.data.payment_url) {
        window.location.href = res.data.payment_url
    } else {
        toast.error(res.message || 'Lỗi tạo lại liên kết thanh toán MoMo.')
    }
}
</script>

<template>
    <div class="payment-status-page min-vh-100 py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-bottom p-4">
                            <h5 class="fw-black mb-0 text-center">Trạng Thái Thanh Toán MoMo</h5>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            
                            <!-- Loading State -->
                            <div v-if="isLoading" class="py-5 text-center">
                                <BaseLoading text="Đang kiểm tra trạng thái..." />
                            </div>

                            <!-- Error State -->
                            <div v-else-if="hasError" class="py-5 text-center">
                                <div class="text-danger mb-3">
                                    <i class="ph-fill ph-warning-circle" style="font-size: 48px;"></i>
                                </div>
                                <h6 class="fw-bold mb-2">Không thể lấy trạng thái</h6>
                                <p class="text-muted small">{{ momoStore.error }}</p>
                                <button @click="router.push({ name: 'UserOrders' })" class="btn btn-outline-dark rounded-pill mt-3 px-4">
                                    Quay lại danh sách đơn hàng
                                </button>
                            </div>

                            <!-- Success / Data State -->
                            <div v-else-if="transaction" class="transaction-details">
                                <div class="text-center mb-4">
                                    <div v-if="transaction.status === 'success'" class="icon-circle bg-success-subtle text-success mx-auto mb-3">
                                        <i class="ph-fill ph-check-circle" style="font-size: 48px;"></i>
                                    </div>
                                    <div v-else-if="transaction.status === 'pending'" class="icon-circle bg-warning-subtle text-warning mx-auto mb-3">
                                        <i class="ph-fill ph-clock" style="font-size: 48px;"></i>
                                    </div>
                                    <div v-else class="icon-circle bg-danger-subtle text-danger mx-auto mb-3">
                                        <i class="ph-fill ph-x-circle" style="font-size: 48px;"></i>
                                    </div>

                                    <h4 class="fw-black mb-1">
                                        {{ transaction.status === 'success' ? 'Thanh toán hoàn tất' : 
                                           transaction.status === 'pending' ? 'Chờ thanh toán' : 'Thanh toán thất bại' }}
                                    </h4>
                                    <p class="text-muted small mb-0">{{ transaction.message }}</p>
                                </div>

                                <div class="info-list bg-light p-4 rounded-4 mb-4">
                                    <div class="info-item d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-bold small">Mã đơn hàng:</span>
                                        <span class="text-dark fw-bold">{{ orderId }}</span>
                                    </div>
                                    <div class="info-item d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-bold small">Mã giao dịch (MoMo):</span>
                                        <span class="text-dark fw-bold">{{ transaction.transaction_id || '---' }}</span>
                                    </div>
                                    <div class="info-item d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-bold small">Mã yêu cầu (Request ID):</span>
                                        <span class="text-dark fw-bold small text-truncate ms-3" style="max-width: 150px;">{{ transaction.request_id || '---' }}</span>
                                    </div>
                                    <div class="info-item d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-bold small">Tổng tiền:</span>
                                        <span class="text-primary fw-black">{{ formatPrice(transaction.amount) }}</span>
                                    </div>
                                    <div class="info-item d-flex justify-content-between mb-3">
                                        <span class="text-muted fw-bold small">Thời gian:</span>
                                        <span class="text-dark fw-bold small">{{ transaction.paid_at || transaction.created_at || '---' }}</span>
                                    </div>
                                    <div class="info-item d-flex justify-content-between">
                                        <span class="text-muted fw-bold small">Trạng thái GD:</span>
                                        <span class="badge" 
                                            :class="{
                                                'bg-success': transaction.status === 'success',
                                                'bg-warning text-dark': transaction.status === 'pending',
                                                'bg-danger': transaction.status === 'failed'
                                            }">
                                            {{ transaction.status.toUpperCase() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-2">
                                    <button v-if="transaction.status === 'failed' || transaction.status === 'pending'" 
                                            @click="handleRetryPayment" 
                                            class="btn btn-dark rounded-pill py-2 fw-bold w-100 mb-2">
                                        Thanh toán lại
                                    </button>
                                    <router-link :to="{ name: 'UserOrders' }" class="btn btn-outline-dark rounded-pill py-2 fw-bold w-100">
                                        Quản lý đơn hàng
                                    </router-link>
                                </div>
                            </div>
                            
                            <!-- Empty State -->
                            <div v-else class="text-center py-4">
                                <p class="text-muted">Không tìm thấy thông tin giao dịch.</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.payment-status-page {
    background-color: #f8fafc;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-list {
    border: 1px dashed #cbd5e1;
}

.fw-black {
    font-weight: 900;
}
</style>
