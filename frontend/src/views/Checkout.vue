<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { useCheckoutStore } from '@/stores/checkout'
import { useTokenStore } from '@/stores/token'
import { useCartStore } from '@/stores/cart'
import { useMoMoStore } from '@/stores/momo'
import { useNotify } from '@/composables/useNotify'
import { API_URL, API_URL_IMAGE } from '@/config/env'
import BaseLoading from '@/components/base/BaseLoading.vue'

const checkoutStore = useCheckoutStore()
const tokenStore = useTokenStore()
const cartStore = useCartStore()
const toast = useNotify()
const router = useRouter()

const showAddressModal = ref(false)
const couponCode = ref('')

onMounted(async () => {
    if (!tokenStore.user) {
        await tokenStore.getUser()
    }
    await cartStore.index()
    await checkoutStore.fetchCheckoutData()
    
    if (checkoutStore.cartItems.length === 0 && !checkoutStore.loading) {
        toast.warn('Giỏ hàng của bạn đang trống!')
        router.push('/cart')
    }
})

const applyCoupon = async () => {
    if (!couponCode.value.trim()) {
        toast.error('Vui lòng nhập mã giảm giá')
        return
    }

    const res = await checkoutStore.applyCoupon(couponCode.value)
    if (res.success) {
        couponCode.value = checkoutStore.cartSummary.coupon.code
        toast.success(res.message)
    } else {
        toast.error(res.message)
    }
}

const removeCoupon = () => {
    checkoutStore.removeCoupon()
    couponCode.value = ''
    toast.success('Đã bỏ mã giảm giá.')
}

const handlePlaceOrder = async () => {
    if (!checkoutStore.selectedAddressId) {
        toast.error('Vui lòng chọn địa chỉ giao hàng!')
        return
    }
    if (!checkoutStore.customerName || !checkoutStore.customerPhone || !checkoutStore.customerEmail) {
        toast.error('Vui lòng điền đầy đủ thông tin liên hệ!')
        return
    }

    const res = await checkoutStore.placeOrder()
    if (res.success) {
        cartStore.clearLocal() // Xóa giỏ hàng local
        
        if (checkoutStore.paymentMethod === 'vnpay') {
            toast.info('Đang chuyển hướng sang cổng thanh toán VNPay...')
            try {
                const payRes = await axios.post(`${API_URL}/vnpay/create-payment`, {
                    order_id: res.data.order_id
                }, {
                    headers: { Authorization: `Bearer ${tokenStore.token}` }
                })
                
                if (payRes.data.success && payRes.data.data.payment_url) {
                    window.location.href = payRes.data.data.payment_url
                } else {
                    toast.error(payRes.data.message || 'Không thể tạo liên kết thanh toán VNPay. Vui lòng thử lại.')
                    setTimeout(() => {
                        router.push({ name: 'UserOrders' })
                    }, 2000)
                }
            } catch (error) {
                console.error('Lỗi thanh toán VNPay:', error)
                toast.error(error.response?.data?.message || 'Lỗi khi tạo liên kết thanh toán VNPay.')
                setTimeout(() => {
                    router.push({ name: 'UserOrders' })
                }, 2000)
            }
        } else if (checkoutStore.paymentMethod === 'momo') {
            toast.info('Đang chuyển hướng sang cổng thanh toán MoMo...')
            try {
                const momoStore = useMoMoStore()
                const payRes = await momoStore.createPayment(res.data.order_id)
                
                if (payRes.success && payRes.data.payment_url) {
                    window.location.href = payRes.data.payment_url
                } else {
                    toast.error(payRes.message || 'Không thể tạo liên kết thanh toán MoMo. Vui lòng thử lại.')
                    setTimeout(() => {
                        router.push({ name: 'UserOrders' })
                    }, 2000)
                }
            } catch (error) {
                console.error('Lỗi thanh toán MoMo:', error)
                toast.error('Lỗi khi tạo liên kết thanh toán MoMo.')
                setTimeout(() => {
                    router.push({ name: 'UserOrders' })
                }, 2000)
            }
        } else {
            toast.success(res.message)
            router.push({ name: 'OrderSuccess', params: { code: res.data.order_code } })
        }
    } else {
        toast.error(res.message)
    }
}

const selectAddress = (addrId) => {
    checkoutStore.updateShippingFee(addrId)
    showAddressModal.value = false
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const getProductImage = (item) => {
    const filename = item.variant?.image || item.product?.thumbnail
    return filename ? `${API_URL_IMAGE}/${filename}` : '/placeholder.png'
}
</script>

<template>
    <div class="checkout-page bg-light min-vh-100 py-4">
        <div class="container">
            <!-- Simple Breadcrumb -->
            <nav class="breadcrumb-nav mb-4">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><router-link to="/">Trang chủ</router-link></li>
                    <li class="breadcrumb-item"><router-link to="/cart">Giỏ hàng</router-link></li>
                    <li class="breadcrumb-item active">Thanh toán</li>
                </ol>
            </nav>

            <div v-if="checkoutStore.loading" class="py-5">
                <BaseLoading text="Đang xử lý..." />
            </div>

            <div v-else class="row g-4">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <div class="checkout-card mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-black mb-0 d-flex align-items-center gap-2">
                                <i class="ph ph-truck text-dark"></i> THÔNG TIN GIAO HÀNG
                            </h6>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="label-custom">Họ tên người nhận</label>
                                <input type="text" v-model="checkoutStore.customerName" class="input-premium" placeholder="Họ và tên">
                            </div>
                            <div class="col-md-4">
                                <label class="label-custom">Số điện thoại</label>
                                <input type="text" v-model="checkoutStore.customerPhone" class="input-premium" placeholder="Số điện thoại">
                            </div>
                            <div class="col-md-4">
                                <label class="label-custom">Email liên hệ</label>
                                <input type="email" v-model="checkoutStore.customerEmail" class="input-premium" placeholder="Email">
                            </div>
                        </div>

                        <div class="divider my-4"></div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 text-muted x-small uppercase tracking-wider">ĐỊA CHỈ NHẬN HÀNG</h6>
                            <button @click="showAddressModal = true" class="btn-change">
                                <i class="ph ph-pencil-simple"></i> Thay đổi
                            </button>
                        </div>

                        <div v-if="checkoutStore.selectedAddress" class="selected-address-box">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-black text-dark small">{{ checkoutStore.selectedAddress.name }}</span>
                                    <span v-if="checkoutStore.selectedAddress.is_default" class="badge-default">Mặc định</span>
                                </div>
                                <span class="phone-text small">{{ checkoutStore.selectedAddress.phone }}</span>
                            </div>
                            <p class="address-text mb-0 x-small">
                                {{ checkoutStore.selectedAddress.address }}, {{ checkoutStore.selectedAddress.ward_name }}, {{ checkoutStore.selectedAddress.district_name }}, {{ checkoutStore.selectedAddress.province_name }}
                            </p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="checkout-card h-100">
                                <h6 class="fw-black mb-4 x-small uppercase">THANH TOÁN</h6>
                                <div class="payment-options">
                                    <label 
                                        class="payment-card" 
                                        :class="{ active: checkoutStore.paymentMethod === 'cod' }"
                                        @click="checkoutStore.paymentMethod = 'cod'"
                                    >
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="payment-icon"><i class="ph ph-wallet"></i></div>
                                            <div class="fw-bold x-small">Tiền mặt (COD)</div>
                                        </div>
                                        <i v-if="checkoutStore.paymentMethod === 'cod'" class="ph-fill ph-check-circle text-dark ms-auto"></i>
                                    </label>
                                    <label 
                                        class="payment-card" 
                                        :class="{ active: checkoutStore.paymentMethod === 'vnpay' }"
                                        @click="checkoutStore.paymentMethod = 'vnpay'"
                                    >
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="payment-icon"><i class="ph ph-bank"></i></div>
                                            <div class="fw-bold x-small">Thanh toán Online (VNPay)</div>
                                        </div>
                                        <i v-if="checkoutStore.paymentMethod === 'vnpay'" class="ph-fill ph-check-circle text-dark ms-auto"></i>
                                    </label>
                                    <label 
                                        class="payment-card" 
                                        :class="{ active: checkoutStore.paymentMethod === 'momo' }"
                                        @click="checkoutStore.paymentMethod = 'momo'"
                                    >
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="payment-icon" style="color: #a50064;"><i class="ph ph-wallet"></i></div>
                                            <div class="fw-bold x-small">Thanh toán qua ví MoMo</div>
                                        </div>
                                        <i v-if="checkoutStore.paymentMethod === 'momo'" class="ph-fill ph-check-circle text-dark ms-auto"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="checkout-card h-100">
                                <h6 class="fw-black mb-4 x-small uppercase">GHI CHÚ</h6>
                                <textarea v-model="checkoutStore.note" class="input-premium w-100 x-small" rows="4" placeholder="Lời nhắn..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <div class="summary-card sticky-top" style="top: 100px; z-index: 10;">
                        <h6 class="fw-black mb-4 text-center x-small uppercase">TÓM TẮT ĐƠN HÀNG</h6>
                        
                        <div class="item-list-scroll mb-4 pe-2">
                            <div v-for="item in checkoutStore.cartItems" :key="item.id" class="checkout-item mb-3">
                                <div class="item-img-container">
                                    <div class="item-img-box">
                                        <img :src="getProductImage(item)" class="object-fit-cover">
                                    </div>
                                    <span class="qty-badge">{{ item.quantity }}</span>
                                </div>
                                <div class="item-details flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-truncate-2 small">{{ item.product?.name }}</div>
                                    <div v-if="item.variant_name" class="variant-text mt-1">Phân loại: {{ item.variant_name }}</div>
                                    <div class="fw-black mt-1 small" :class="item.flash_sale ? 'text-danger' : 'text-primary'">
                                        {{ formatPrice(item.price) }}
                                        <span v-if="item.flash_sale" class="original-price ms-1">{{ formatPrice(item.original_price) }}</span>
                                    </div>
                                    <div v-if="item.flash_sale" class="flash-sale-label"><i class="ph-fill ph-lightning"></i> Flash Sale</div>
                                </div>
                            </div>
                        </div>

                        <div class="coupon-section mb-4">
                            <div class="input-group">
                                <input type="text" v-model="couponCode" @keyup.enter="applyCoupon" class="form-control input-premium border-end-0 py-2 x-small" placeholder="Mã giảm giá">
                                <button @click="applyCoupon" :disabled="checkoutStore.couponProcessing" class="btn btn-dark px-3 rounded-end-4 fw-bold x-small">
                                    {{ checkoutStore.couponProcessing ? 'Đang kiểm tra' : 'Áp dụng' }}
                                </button>
                            </div>
                            <div v-if="checkoutStore.cartSummary.coupon" class="applied-coupon mt-2">
                                <div>
                                    <strong>{{ checkoutStore.cartSummary.coupon.code }}</strong>
                                    <small>{{ checkoutStore.cartSummary.coupon.name }}</small>
                                </div>
                                <button type="button" @click="removeCoupon">Bỏ mã</button>
                            </div>
                        </div>

                        <div class="pricing-box">
                <div v-if="checkoutStore.cartSummary.applied_combos?.length" class="applied-combo mb-3">
                  <div class="combo-label"><i class="ph-fill ph-tag"></i> Combo đã áp dụng</div>
                  <div v-for="combo in checkoutStore.cartSummary.applied_combos" :key="combo.id" class="checkout-combo-line">
                    <strong>{{ combo.name }}</strong>
                    <small>-{{ formatPrice(combo.discount_amount) }}</small>
                  </div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted x-small">Tạm tính:</span>
                  <span class="fw-bold x-small">{{ formatPrice(checkoutStore.cartSummary.subtotal_before_combo ?? checkoutStore.cartSummary.total_price) }}</span>
                </div>
                <div v-if="checkoutStore.cartSummary.flash_sale_savings > 0" class="d-flex justify-content-between mb-2 text-danger">
                  <span class="fw-bold x-small">Tiết kiệm Flash Sale:</span>
                  <span class="fw-bold x-small">-{{ formatPrice(checkoutStore.cartSummary.flash_sale_savings) }}</span>
                </div>
                <div v-if="checkoutStore.cartSummary.combo_discount > 0" class="d-flex justify-content-between mb-2 combo-saving">
                  <span class="fw-bold x-small">Giảm giá Combo:</span>
                  <span class="fw-bold x-small">-{{ formatPrice(checkoutStore.cartSummary.combo_discount) }}</span>
                </div>
                <div v-if="checkoutStore.cartSummary.coupon_discount > 0" class="d-flex justify-content-between mb-2 coupon-saving">
                  <span class="fw-bold x-small">Mã giảm giá:</span>
                  <span class="fw-bold x-small">-{{ formatPrice(checkoutStore.cartSummary.coupon_discount) }}</span>
                </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted x-small">Phí vận chuyển:</span>
                                <span class="fw-bold text-success x-small">+{{ formatPrice(checkoutStore.shippingFee) }}</span>
                            </div>
                            <div class="divider my-3"></div>
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <span class="fw-black x-small uppercase">TỔNG CỘNG</span>
                                <span class="fw-black h5 mb-0 text-primary">{{ formatPrice(checkoutStore.totalAmount) }}</span>
                            </div>
                        </div>

                        <button 
                            class="btn btn-dark w-100 rounded-pill py-3 mt-4 fw-black shadow-lg small"
                            @click="handlePlaceOrder"
                            :disabled="checkoutStore.processing || !checkoutStore.selectedAddressId"
                        >
                            {{ checkoutStore.processing ? 'ĐANG XỬ LÝ...' : 'ĐẶT HÀNG NGAY' }}
                        </button>
                        
                        <div class="text-center mt-3">
                            <router-link to="/cart" class="text-dark text-decoration-underline x-small fw-bold">
                                <i class="ph ph-arrow-left me-1"></i> Quay lại giỏ hàng
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Modal -->
        <div v-if="showAddressModal" class="modal-overlay" @click.self="showAddressModal = false">
            <div class="modal-card animate__animated animate__zoomIn">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-black mb-0">SỔ ĐỊA CHỈ</h6>
                    <button @click="showAddressModal = false" class="btn-close-custom"><i class="ph ph-x"></i></button>
                </div>
                <div class="address-scroll-box pe-2">
                    <div v-for="addr in checkoutStore.addresses" :key="addr.id" 
                        class="address-item-option"
                        :class="{ active: checkoutStore.selectedAddressId === addr.id }"
                        @click="selectAddress(addr.id)"
                    >
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-black x-small">{{ addr.name }}</span>
                            <span v-if="addr.is_default" class="badge-default">Mặc định</span>
                        </div>
                        <div class="text-muted x-small mb-1">{{ addr.phone }}</div>
                        <div class="text-muted x-small">{{ addr.address }}, {{ addr.ward_name }}, {{ addr.district_name }}</div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-top text-center">
                    <router-link to="/user/addresses" class="btn btn-outline-dark rounded-pill btn-sm px-4 fw-bold">
                        Quản lý địa chỉ
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.breadcrumb-nav { font-size: 13px; font-weight: 500; }
.breadcrumb-item a { color: #64748b; text-decoration: none; transition: color 0.2s; }
.breadcrumb-item a:hover { color: #000; }
.breadcrumb-item.active { color: #000; font-weight: 800; }
.breadcrumb-item + .breadcrumb-item::before { 
    content: "/"; 
    padding: 0 10px; 
    color: #cbd5e1; 
}

.checkout-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    border: 1px solid rgba(0,0,0,0.05);
}

.summary-card {
    background: #ffffff;
    border-radius: 30px;
    padding: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.label-custom {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 6px;
    display: block;
    letter-spacing: 0.5px;
}

.input-premium {
    background: #f8fafc;
    border: 2px solid #f1f5f9;
    border-radius: 14px;
    padding: 10px 15px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.input-premium:focus {
    border-color: #000;
    background: #fff;
    outline: none;
    box-shadow: 0 0 0 4px rgba(0,0,0,0.05);
}

.divider { height: 1px; background: #f1f5f9; }

.selected-address-box {
    border: 3px solid #000;
    border-radius: 18px;
    padding: 15px;
    background: #fff;
    position: relative;
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}

.badge-default {
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 100px;
    text-transform: uppercase;
}

.phone-text { font-weight: 700; color: #000; }
.address-text { color: #64748b; line-height: 1.5; }

.btn-change {
    background: none;
    border: none;
    color: #000;
    font-size: 13px;
    font-weight: 800;
    display: flex; align-items: center; gap: 5px; padding: 0;
}

.payment-card {
    display: flex; align-items: center; padding: 15px;
    border: 2px solid #f1f5f9; border-radius: 14px; margin-bottom: 10px;
    cursor: pointer; transition: all 0.3s ease;
}

.payment-card.active { border-color: #000; background: #fff; }

.payment-icon {
    width: 36px; height: 36px; background: #f1f5f9; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
}

.checkout-item { display: flex; gap: 15px; align-items: center; }

.item-img-container {
    position: relative;
    flex-shrink: 0;
}

.item-img-box {
    width: 65px; height: 65px; border-radius: 12px;
    overflow: hidden; background: #f8fafc;
}

.item-img-box img { width: 100%; height: 100%; object-fit: cover; }

.qty-badge {
    position: absolute; 
    top: -6px; 
    right: -6px; 
    background: #000; 
    color: #fff;
    font-size: 11px; 
    font-weight: 900; 
    width: 22px; 
    height: 22px;
    display: flex; 
    align-items: center; 
    justify-content: center; 
    border-radius: 50%;
    border: 2px solid #fff; /* Thêm viền trắng để nổi bật */
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 2;
}

.variant-text { font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
.original-price { color: #94a3b8; font-size: 12px; font-weight: 600; text-decoration: line-through; }
.flash-sale-label { color: #dc2626; font-size: 11px; font-weight: 800; margin-top: 2px; text-transform: uppercase; }
.applied-combo {
    background: linear-gradient(120deg, #fff4ef, #ffe7dc);
    border: 1px solid #ffd0bf;
    border-radius: 12px;
    color: #892715;
    display: flex;
    flex-direction: column;
    gap: 3px;
    padding: 11px 12px;
}
.combo-label { color: #e24326; font-size: 11px; font-weight: 900; text-transform: uppercase; }
.applied-combo strong { font-size: 13px; }
.applied-combo small { color: #a5503e; font-size: 12px; font-weight: 700; }
.checkout-combo-line { display: flex; justify-content: space-between; gap: 8px; }
.combo-saving { color: #e24326; }
.coupon-saving { color: #16a34a; }
.applied-coupon {
    align-items: center;
    background: #effcf3;
    border: 1px dashed #86efac;
    border-radius: 12px;
    color: #166534;
    display: flex;
    justify-content: space-between;
    padding: 9px 12px;
}
.applied-coupon strong { display: block; font-size: 13px; }
.applied-coupon small { color: #4b8060; display: block; font-size: 12px; }
.applied-coupon button {
    background: none;
    border: none;
    color: #dc2626;
    font-size: 12px;
    font-weight: 800;
}

.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.7); z-index: 9999;
    display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px);
}

.modal-card { background: #fff; width: 90%; max-width: 450px; border-radius: 30px; padding: 25px; }

.address-item-option {
    padding: 12px; border: 2px solid #f1f5f9; border-radius: 15px; margin-bottom: 8px;
    cursor: pointer; position: relative;
}

.address-item-option.active { border-color: #000; border-width: 2px; }

.fw-black { font-weight: 900 !important; }
.uppercase { text-transform: uppercase; }
.tracking-wider { letter-spacing: 0.05em; }
.x-small { font-size: 13px; }
.max-vh-40 { max-height: 40vh; overflow-y: auto; }
.address-scroll-box { max-height: 50vh; overflow-y: auto; }

::-webkit-scrollbar { width: 3px; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

.btn-close-custom { background: none; border: none; font-size: 20px; }
</style>
