<script setup>
import { computed } from 'vue'
import { API_URL_IMAGE } from '@/config/env'

const props = defineProps({
    dataForm: {
        type: Object,
        default: () => ({})
    },
    isShow: {
        type: Boolean,
        default: false
    },
    loadingSubmit: {
        type: Boolean,
        default: false
    },
    statusMap: {
        type: Array,
        default: () => []
    },
    paymentStatusMap: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['submit', 'close', 'update:isShow'])

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

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

const getProductImage = (item) => {
    return item.product_thumbnail ? `${API_URL_IMAGE}/${item.product_thumbnail}` : '/placeholder.png'
}
</script>

<template>
    <div v-if="isShow" class="modal-overlay" @click.self="emit('close')">
        <div class="modal-content custom-modal animate__animated animate__zoomIn">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="modal-title fw-black uppercase tracking-wider mb-0">Quản lý Đơn hàng</h5>
                    <span class="text-primary fw-bold small mt-1 d-block">{{ dataForm.order_code }}</span>
                </div>
                <button type="button" class="btn-close-custom" @click="emit('close')">
                    <i class="ph ph-x"></i>
                </button>
            </div>
            
            <div class="modal-body p-4 position-relative" :class="{ 'opacity-50 pointer-events-none': loadingSubmit }">
                <!-- Shipping Stepper -->
                <div class="shipping-stepper mb-5 px-4">
                    <div class="stepper-line">
                        <div class="progress-line" :style="{ width: (getActiveStep(dataForm.status) * 25) + '%' }"></div>
                    </div>
                    <div class="stepper-items">
                        <div v-for="(step, index) in steps" :key="step.key" 
                            class="step-item"
                            :class="{ 
                                active: getActiveStep(dataForm.status) >= index,
                                current: getActiveStep(dataForm.status) === index 
                            }"
                        >
                            <div class="step-icon-box">
                                <i :class="['ph-bold', step.icon]"></i>
                            </div>
                            <span class="step-label">{{ step.label }}</span>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Customer & Shipping Info -->
                    <div class="col-md-6">
                        <div class="info-card-premium h-100">
                            <h6 class="section-title-premium"><i class="ph-bold ph-user-focus"></i> Khách hàng</h6>
                            <div class="info-item-premium">
                                <label>Họ tên:</label>
                                <span>{{ dataForm.shipping_name }}</span>
                            </div>
                            <div class="info-item-premium">
                                <label>Điện thoại:</label>
                                <span>{{ dataForm.shipping_phone }}</span>
                            </div>
                            <div class="info-item-premium">
                                <label>Địa chỉ:</label>
                                <span class="small">{{ dataForm.shipping_full_address }}</span>
                            </div>
                            <div class="info-item-premium border-top mt-2 pt-2">
                                <label>Ghi chú:</label>
                                <span class="text-muted fw-normal">{{ dataForm.note || 'Không có ghi chú từ khách' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Action Area -->
                    <div class="col-md-6">
                        <div class="info-card-premium action-area h-100">
                            <h6 class="section-title-premium text-primary"><i class="ph-bold ph-pencil-line"></i> Xử lý đơn hàng</h6>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small uppercase">Cập nhật Trạng thái</label>
                                <select class="form-select-premium" v-model="dataForm.status">
                                    <option v-for="st in statusMap" :key="st.id" :value="st.id">{{ st.name }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small uppercase">Trạng thái thanh toán</label>
                                <select class="form-select-premium" v-model="dataForm.payment_status">
                                    <option v-for="ps in paymentStatusMap" :key="ps.id" :value="ps.id">{{ ps.name }}</option>
                                </select>
                            </div>

                            <div class="mb-3" v-if="dataForm.status === 'cancelled'">
                                <label class="form-label fw-bold small uppercase text-danger">Lý do hủy đơn</label>
                                <textarea class="form-control-premium" v-model="dataForm.cancelled_reason" rows="2" placeholder="Vui lòng nhập lý do hủy..."></textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold small uppercase">Ghi chú nội bộ</label>
                                <textarea class="form-control-premium" v-model="dataForm.admin_note" rows="2" placeholder="Ghi chú cho nội bộ shop..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="col-12">
                        <div class="items-card-premium">
                            <h6 class="section-title-premium"><i class="ph-bold ph-shopping-bag"></i> Danh sách sản phẩm</h6>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light-premium">
                                        <tr>
                                            <th style="width: 60px;">Ảnh</th>
                                            <th>Sản phẩm</th>
                                            <th class="text-center">Số lượng</th>
                                            <th class="text-end">Đơn giá</th>
                                            <th class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in dataForm.order_details" :key="item.id">
                                            <td>
                                                <div class="item-thumb-admin">
                                                    <img :src="getProductImage(item)" class="img-fluid rounded-2">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark">{{ item.product_name }}</span>
                                                    <span v-if="item.variant_name" class="variant-tag-admin mt-1">{{ item.variant_name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center fw-bold">{{ item.quantity }}</td>
                                            <td class="text-end text-muted small">{{ formatPrice(item.price) }}</td>
                                            <td class="text-end fw-black text-primary">{{ formatPrice(item.subtotal) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="summary-box-admin mt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small fw-bold uppercase">Tạm tính:</span>
                                    <span class="fw-bold">{{ formatPrice(dataForm.subtotal_amount) }}</span>
                                </div>
                                <div v-if="dataForm.discount_amount > 0" class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small fw-bold uppercase">Giảm giá:</span>
                                    <span class="fw-bold text-danger">-{{ formatPrice(dataForm.discount_amount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small fw-bold uppercase">Phí vận chuyển:</span>
                                    <span class="fw-bold text-success">+{{ formatPrice(dataForm.shipping_fee) }}</span>
                                </div>
                                <div class="border-top-dashed my-3"></div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-black uppercase tracking-wider small">TỔNG CỘNG:</span>
                                    <span class="fw-black h4 mb-0 text-primary">{{ formatPrice(dataForm.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-premium p-4 d-flex gap-3">
                <button type="button" class="btn btn-light rounded-pill px-4 py-2 fw-bold flex-grow-1" @click="emit('close')" :disabled="loadingSubmit">
                    HỦY BỎ
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-5 py-2 fw-black flex-grow-1 shadow-sm" 
                    :disabled="loadingSubmit" @click="emit('submit')">
                    <span v-if="loadingSubmit" class="loading-text"><i class="ph ph-spinner-gap ph-bold spinning"></i> ĐANG LƯU...</span>
                    <span v-else>LƯU THAY ĐỔI</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fw-black { font-weight: 900 !important; }
.uppercase { text-transform: uppercase; }
.tracking-wider { letter-spacing: 0.1em; }

.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.6); backdrop-filter: blur(10px);
    display: flex; align-items: center; justify-content: center; z-index: 1050; padding: 20px;
}

.custom-modal {
    background: var(--surface); color: var(--text-main); width: 100%; max-width: 900px; max-height: 95vh;
    border-radius: 30px; box-shadow: 0 30px 60px rgba(0,0,0,0.2);
    display: flex; flex-direction: column; overflow: hidden; border: 1px solid rgba(0,0,0,0.05);
}

.modal-header { padding: 25px 30px; border-bottom: 1px solid var(--border); }
.btn-close-custom { background: none; border: none; font-size: 24px; color: #64748b; transition: all 0.2s; }
.btn-close-custom:hover { color: var(--text-main); transform: rotate(90deg); }

.modal-body { overflow-y: auto; scrollbar-width: thin; }
.pointer-events-none { pointer-events: none; }

/* Item Thumbnail Admin */
.item-thumb-admin { width: 45px; height: 45px; overflow: hidden; background: var(--background); border: 1px solid var(--border); border-radius: 8px; }
.item-thumb-admin img { width: 100%; height: 100%; object-fit: cover; }

/* Shipping Stepper Admin */
.shipping-stepper { position: relative; }
.stepper-line { position: absolute; top: 15px; left: 40px; right: 40px; height: 2px; background: var(--border); z-index: 1; }
.progress-line { height: 100%; background: #10b981; transition: width 0.5s ease; }
.stepper-items { display: flex; justify-content: space-between; position: relative; z-index: 2; }
.step-item { display: flex; flex-direction: column; align-items: center; gap: 8px; width: 80px; }
.step-icon-box { width: 32px; height: 32px; background: var(--surface); border: 2px solid var(--border); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; color: var(--text-muted); transition: all 0.3s; }
.step-label { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; text-align: center; }
.step-item.active .step-icon-box { border-color: #10b981; color: #10b981; }
.step-item.active .step-label { color: #10b981; }
.step-item.current .step-icon-box { background: #10b981; color: #fff; box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2); }

.info-card-premium { background: var(--muted); padding: 24px; border-radius: 20px; border: 1px solid var(--border); }
.action-area { border: 1px solid var(--border); background: var(--surface); }

.section-title-premium { font-size: 14px; font-weight: 900; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; text-transform: uppercase; letter-spacing: 0.5px; }

.info-item-premium { display: flex; margin-bottom: 12px; font-size: 14px; }
.info-item-premium label { width: 120px; color: var(--text-muted); font-weight: 700; flex-shrink: 0; }
.info-item-premium span { flex: 1; color: var(--text-main); font-weight: 800; }

.form-select-premium, .form-control-premium {
    width: 100%; padding: 10px 15px; border-radius: 12px; border: 1px solid #e2e8f0;
    color: var(--text-main); font-size: 14px; font-weight: 600; background: var(--muted); transition: all 0.2s;
}
.form-select-premium:focus, .form-control-premium:focus { border-color: var(--ring); outline: none; background: var(--surface); box-shadow: 0 0 0 4px rgba(0,0,0,0.05); }

.items-card-premium :deep(.table) { --bs-table-bg: var(--surface); --bs-table-color: var(--text-main); --bs-table-hover-bg: var(--muted); --bs-table-hover-color: var(--text-main); color: var(--text-main); }
.items-card-premium :deep(.table > :not(caption) > * > *) { background: var(--surface); border-color: var(--border); color: var(--text-main); }
.items-card-premium :deep(.table-hover > tbody > tr:hover > *) { background: var(--muted); color: var(--text-main); }
.items-card-premium :deep(.text-dark) { color: var(--text-main) !important; }
.items-card-premium :deep(.text-muted) { color: var(--text-muted) !important; }
.table-light-premium th { background: var(--muted) !important; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted) !important; padding: 12px; }
.variant-tag-admin { background: var(--muted); color: var(--text-main); font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; display: inline-block; }

.summary-box-admin { padding: 20px; background: var(--muted); border: 1px solid var(--border); border-radius: 20px; color: var(--text-main); }
.summary-box-admin :deep(.text-muted) { color: var(--text-muted) !important; }
.border-top-dashed { border-top: 1px dashed var(--border); }

.modal-footer-premium { border-top: 1px solid var(--border); background: var(--surface); }

.spinning { animation: spin 1s linear infinite; display: inline-block; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

.loading-text { display: flex; align-items: center; justify-content: center; gap: 8px; }

::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
