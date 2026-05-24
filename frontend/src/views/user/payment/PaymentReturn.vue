<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMoMoStore } from '@/stores/momo'

const route = useRoute()
const router = useRouter()
const momoStore = useMoMoStore()

const isSuccess = ref(false)
const orderCode = ref('')
const message = ref('')
const statusQuery = ref('')

onMounted(async () => {
    // Lấy query từ URL do MoMoController redirect về
    // URL format: ?status=success&order_code=ORD-123&message=...
    statusQuery.value = route.query.status || 'failed'
    orderCode.value = route.query.order_code || ''
    message.value = route.query.message || 'Giao dịch không thành công hoặc lỗi không xác định.'
    
    isSuccess.value = statusQuery.value === 'success'

    if (isSuccess.value && orderCode.value) {
        // Chuyển hướng ngay sang trang OrderSuccess để hiển thị giao diện đặt hàng thành công giống COD
        router.replace({ name: 'OrderSuccess', params: { code: orderCode.value } })
    }
})
</script>

<template>
    <div class="payment-return-page min-vh-100 d-flex align-items-center justify-content-center bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="card-body p-5 text-center">
                            
                            <!-- Success State -->
                            <div v-if="isSuccess" class="success-state animate__animated animate__fadeIn">
                                <div class="icon-circle bg-success-subtle text-success mx-auto mb-4">
                                    <i class="ph-fill ph-check-circle" style="font-size: 64px;"></i>
                                </div>
                                <h3 class="fw-black mb-3 text-dark">Thanh toán thành công!</h3>
                                <p class="text-muted mb-4">
                                    Cảm ơn bạn đã mua sắm tại StyleHub. Đơn hàng của bạn đã được thanh toán qua MoMo.
                                </p>
                                
                                <div class="order-info-box p-3 bg-light rounded-3 mb-4 text-start">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small fw-bold">Mã đơn hàng:</span>
                                        <span class="text-dark fw-bold">{{ orderCode }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted small fw-bold">Trạng thái:</span>
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    </div>
                                </div>

                                <div class="d-flex gap-3 justify-content-center mt-4">
                                    <router-link to="/" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                                        Về trang chủ
                                    </router-link>
                                    <router-link :to="{ name: 'UserOrders' }" class="btn btn-dark rounded-pill px-4 fw-bold">
                                        Xem đơn hàng
                                    </router-link>
                                </div>
                            </div>

                            <!-- Failed State -->
                            <div v-else class="failed-state animate__animated animate__fadeIn">
                                <div class="icon-circle bg-danger-subtle text-danger mx-auto mb-4">
                                    <i class="ph-fill ph-x-circle" style="font-size: 64px;"></i>
                                </div>
                                <h3 class="fw-black mb-3 text-dark">Thanh toán thất bại</h3>
                                <p class="text-danger fw-medium mb-4">
                                    {{ message }}
                                </p>

                                <div class="order-info-box p-3 bg-light rounded-3 mb-4 text-start">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small fw-bold">Mã đơn hàng:</span>
                                        <span class="text-dark fw-bold">{{ orderCode }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted small fw-bold">Trạng thái:</span>
                                        <span class="badge bg-danger">Thất bại</span>
                                    </div>
                                </div>

                                <div class="d-flex gap-3 justify-content-center mt-4">
                                    <router-link to="/" class="btn btn-outline-dark rounded-pill px-4 fw-bold">
                                        Về trang chủ
                                    </router-link>
                                    <router-link :to="{ name: 'UserOrders' }" class="btn btn-dark rounded-pill px-4 fw-bold">
                                        Quản lý đơn hàng
                                    </router-link>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.payment-return-page {
    background-color: #f8fafc;
}

.card {
    transition: all 0.3s ease;
}

.icon-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.order-info-box {
    border: 1px dashed #cbd5e1;
}

.fw-black {
    font-weight: 900;
}
</style>
