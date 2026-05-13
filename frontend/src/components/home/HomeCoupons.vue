<template>
  <section class="home-coupons container py-5">
    <div class="section-header mb-4">
      <div>
        <h2 class="section-title">Mã giảm giá đang có</h2>
        <p style="color: var(--text-muted); margin-top: 8px;">Các khuyến mãi hấp dẫn dành cho bạn.</p>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="coupons.length > 0" class="coupons-grid">
      <div v-for="coupon in coupons" :key="coupon.id" class="coupon-card">
        <div class="coupon-content">
          <div class="coupon-header">
            <span class="coupon-code">{{ coupon.code }}</span>
            <span v-if="coupon.discount_type === 'percentage'" class="badge badge-discount">
              -{{ coupon.discount_value }}%
            </span>
            <span v-else class="badge badge-discount">
              -{{ formatPrice(coupon.discount_value) }}
            </span>
          </div>
          
          <h3 class="coupon-name">{{ coupon.name }}</h3>
          <p v-if="coupon.description" class="coupon-description">{{ coupon.description }}</p>
          
          <div class="coupon-details">
            <div class="detail-item">
              <span class="label">Áp dụng từ:</span>
              <span class="value">{{ formatDate(coupon.starts_at) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Hết hạn:</span>
              <span class="value">{{ formatDate(coupon.expires_at) }}</span>
            </div>
            <div v-if="coupon.min_order_value" class="detail-item">
              <span class="label">Tối thiểu:</span>
              <span class="value">{{ formatPrice(coupon.min_order_value) }}</span>
            </div>
          </div>

          <button class="btn btn-sm btn-primary w-100 mt-3" @click="copyCoupon(coupon.code)">
            <i class="ph ph-copy"></i> Sao chép mã
          </button>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-5">
      <p class="text-muted">Hiện không có mã giảm giá nào.</p>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useNotify } from '@/composables/useNotify'

const coupons = ref([])
const loading = ref(true)
const toast = useNotify()

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

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

const formatDate = (dateString) => {
  if (!dateString) return 'Không giới hạn'
  const date = new Date(dateString)
  return date.toLocaleDateString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit' })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
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
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.coupon-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
  transition: var(--transition);
}

.coupon-card:hover {
  box-shadow: var(--shadow-hover);
  transform: translateY(-4px);
}

.coupon-content {
  padding: 24px;
}

.coupon-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.coupon-code {
  font-family: 'Courier New', monospace;
  font-weight: 700;
  font-size: 16px;
  color: var(--primary);
}

.badge-discount {
  background: #ef4444;
  color: white;
  padding: 4px 12px;
  border-radius: var(--radius-full);
  font-size: 13px;
  font-weight: 600;
}

.coupon-name {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--text-main);
}

.coupon-description {
  font-size: 14px;
  color: var(--text-muted);
  margin-bottom: 16px;
  line-height: 1.5;
}

.coupon-details {
  background: var(--background);
  padding: 12px;
  border-radius: var(--radius-md);
  margin-bottom: 16px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-size: 13px;
}

.detail-item:last-child {
  margin-bottom: 0;
}

.detail-item .label {
  color: var(--text-muted);
  font-weight: 500;
}

.detail-item .value {
  color: var(--text-main);
  font-weight: 600;
}

@media (max-width: 768px) {
  .coupons-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
  }
}
</style>
