<template>
  <section class="home-coupons container py-5">
    <div class="section-header mb-4" style="display: flex; justify-content: space-between; align-items: flex-end;">
      <div>
        <h2 class="section-title">Mã giảm giá dành cho bạn</h2>
        <p style="color: var(--text-muted); margin-top: 8px; margin-bottom: 0;">Săn ngay các khuyến mãi hấp dẫn để mua sắm tiết kiệm hơn.
        </p>
      </div>
      <div v-if="coupons.length >= 6" class="slider-controls">
        <button class="slider-btn" @click="scrollSlider(-1)"><i class="ph ph-caret-left"></i></button>
        <button class="slider-btn" @click="scrollSlider(1)"><i class="ph ph-caret-right"></i></button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="coupons.length > 0" class="coupons-wrapper">
      <div :class="coupons.length >= 6 ? 'coupons-slider' : 'coupons-grid'" ref="sliderRef">
        <div v-for="coupon in coupons" :key="coupon.id" class="coupon-ticket">

        <!-- Left Side: Value -->
        <div class="ticket-left">
          <span class="discount-label">GIẢM</span>
          <span v-if="coupon.discount_type === 'percentage'" class="discount-value">
            {{ parseFloat(coupon.discount_value) }}%
          </span>
          <span v-else class="discount-value">
            {{ formatPriceShort(coupon.discount_value) }}
          </span>
        </div>

        <!-- Right Side: Info -->
        <div class="ticket-right">
          <div class="ticket-header">
            <h3 class="ticket-name" :title="coupon.name">{{ coupon.name }}</h3>
            <button class="btn-copy" @click="copyCoupon(coupon.code)" title="Sao chép">
              {{ coupon.code }} <i class="ph ph-copy"></i>
            </button>
          </div>

          <p v-if="coupon.description && coupon.description.trim() !== ''" class="ticket-desc"
            :title="coupon.description">
            {{ coupon.description }}
          </p>
          <p v-else class="ticket-desc">Giảm giá trực tiếp vào đơn hàng của bạn.</p>

          <div class="ticket-meta">
            <span v-if="coupon.min_order_value" title="Đơn tối thiểu">
              <i class="ph ph-shopping-bag"></i> Từ {{ formatPriceShort(coupon.min_order_value) }}
            </span>
            <span title="Thời gian áp dụng">
              <i class="ph ph-clock"></i>
              <template v-if="coupon.starts_at && coupon.expires_at">
                {{ formatDateShort(coupon.starts_at) }} - {{ formatDateShort(coupon.expires_at) }}
              </template>
              <template v-else-if="coupon.starts_at">
                Từ {{ formatDateShort(coupon.starts_at) }}
              </template>
              <template v-else-if="coupon.expires_at">
                HSD: {{ formatDateShort(coupon.expires_at) }}
              </template>
              <template v-else>
                Không thời hạn
              </template>
            </span>
            <span title="Số lượng">
              <i class="ph ph-ticket"></i> {{ coupon.usage_limit ? coupon.usage_limit + ' lượt' : 'Vô hạn' }}
            </span>
          </div>
        </div>

      </div>
    </div>
  </div>

    <div v-else class="empty-state">
      <i class="ph ph-ticket text-muted" style="font-size: 48px; margin-bottom: 12px;"></i>
      <p class="text-muted mb-0">Hiện chưa có mã giảm giá nào. Vui lòng quay lại sau!</p>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useNotify } from '@/composables/useNotify'

const coupons = ref([])
const loading = ref(true)
const sliderRef = ref(null)
const toast = useNotify()

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const scrollSlider = (direction) => {
  if (!sliderRef.value) return
  const scrollAmount = 340 // Chiều rộng thẻ + gap
  sliderRef.value.scrollBy({
    left: direction * scrollAmount,
    behavior: 'smooth'
  })
}

const fetchCoupons = async () => {
  try {
    loading.value = true
    const response = await axios.get(`${API_BASE}/coupons/active`)
    if (response.data.success) {
      coupons.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching coupons:', error)
  } finally {
    loading.value = false
  }
}

const formatDateShort = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', { month: '2-digit', day: '2-digit', year: 'numeric' })
}

const formatPriceShort = (price) => {
  if (price >= 1000000) {
    return (price / 1000000) + 'M'
  } else if (price >= 1000) {
    return (price / 1000) + 'K'
  }
  return price + 'đ'
}

const copyCoupon = (code) => {
  navigator.clipboard.writeText(code)
  toast.success(`Đã sao chép mã: ${code}`)
}

onMounted(() => {
  fetchCoupons()
})
</script>

<style scoped>
.home-coupons {
  background: var(--background);
}

.coupons-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.coupons-wrapper {
  position: relative;
}

.coupons-slider {
  display: flex;
  overflow-x: auto;
  gap: 20px;
  padding-bottom: 20px;
  margin-bottom: -20px;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.coupons-slider::-webkit-scrollbar {
  display: none;
}

.coupons-slider .coupon-ticket {
  flex: 0 0 320px;
  scroll-snap-align: start;
}

.slider-controls {
  display: flex;
  gap: 12px;
}

.slider-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--surface);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: var(--text-main);
}

.slider-btn:hover {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.slider-btn i {
  font-size: 16px;
}

/* TICKET DESIGN */
.coupon-ticket {
  display: grid;
  grid-template-columns: 90px 1fr;
  min-height: 120px;
  position: relative;
  filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.05));
  transition: all 0.3s ease;
  cursor: pointer;
  border-radius: 12px;
  background: linear-gradient(to right, var(--primary) 90px, var(--surface) 90px);
  -webkit-mask: 
    radial-gradient(circle at 90px 0, transparent 12px, black 12.5px) top / 100% 55% no-repeat,
    radial-gradient(circle at 90px 100%, transparent 12px, black 12.5px) bottom / 100% 55% no-repeat;
  mask: 
    radial-gradient(circle at 90px 0, transparent 12px, black 12.5px) top / 100% 55% no-repeat,
    radial-gradient(circle at 90px 100%, transparent 12px, black 12.5px) bottom / 100% 55% no-repeat;
}

.coupon-ticket::before {
  content: '';
  position: absolute;
  left: 90px;
  top: 16px;
  bottom: 16px;
  border-left: 2px dashed #cbd5e1;
  z-index: 1;
}

.coupon-ticket:hover {
  transform: translateY(-4px);
  filter: drop-shadow(0 8px 20px rgba(59, 130, 246, 0.15));
}

/* Left part of the ticket */
.ticket-left {
  color: white;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 16px 8px;
  text-align: center;
}

.discount-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  opacity: 0.9;
  margin-bottom: 4px;
}

.discount-value {
  font-size: 22px;
  font-weight: 800;
  line-height: 1;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Right part of the ticket */
.ticket-right {
  padding: 16px;
  display: flex;
  flex-direction: column;
  position: relative;
  min-width: 0;
  /* For text truncation */
}

/* Header: Name and Copy Button */
.ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 6px;
  gap: 12px;
}

.ticket-name {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

.btn-copy {
  background: #eff6ff;
  color: var(--primary);
  border: 1px dashed #bfdbfe;
  padding: 4px 8px;
  border-radius: 6px;
  font-family: 'Courier New', monospace;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-copy:hover {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

/* Description */
.ticket-desc {
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 10px;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex-shrink: 0;
}

/* Meta info */
.ticket-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 12px;
  font-size: 11px;
  color: var(--text-muted);
  margin-top: auto;
}

.ticket-meta span {
  display: flex;
  align-items: center;
  gap: 4px;
  background: var(--background);
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 500;
  border: 1px solid #f1f5f9;
  white-space: nowrap;
}

.ticket-meta i {
  font-size: 14px;
  color: var(--primary);
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  background: var(--surface);
  border-radius: 12px;
  border: 1px dashed var(--border);
}

@media (max-width: 768px) {
  .coupons-grid {
    grid-template-columns: 1fr;
  }
}
</style>
