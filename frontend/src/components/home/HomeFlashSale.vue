<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { API_URL_IMAGE } from '@/config/env'

const formatCurrency = (value) => {
  if (!value) return '0 đ'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

const props = defineProps({
  flashSales: {
    type: Array,
    required: true
  }
})

const selectedSaleId = ref(null)
const sales = computed(() => props.flashSales || [])
const selectedSale = computed(() => {
  return sales.value.find((sale) => sale.id === selectedSaleId.value) || sales.value[0] || null
})
const items = computed(() => selectedSale.value?.items || [])

const timeRemaining = ref({
  days: 0,
  hours: 0,
  minutes: 0,
  seconds: 0,
  expired: false
})

let timerInterval = null

const calculateTimeRemaining = () => {
  if (!selectedSale.value?.ends_at) return

  const endTime = new Date(selectedSale.value.ends_at).getTime()
  const now = new Date().getTime()
  const distance = endTime - now

  if (distance < 0) {
    timeRemaining.value.expired = true
    clearInterval(timerInterval)
    return
  }

  timeRemaining.value = {
    days: Math.floor(distance / (1000 * 60 * 60 * 24)),
    hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
    minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)),
    seconds: Math.floor((distance % (1000 * 60)) / 1000),
    expired: false
  }
}

watch(
  sales,
  (currentSales) => {
    if (!currentSales.some((sale) => sale.id === selectedSaleId.value)) {
      selectedSaleId.value = currentSales[0]?.id ?? null
    }
  },
  { immediate: true }
)

watch(selectedSale, () => {
  calculateTimeRemaining()
})

onMounted(() => {
  calculateTimeRemaining()
  timerInterval = setInterval(calculateTimeRemaining, 1000)
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

const getProductImage = (product) => {
  return product.thumbnail ? `${API_URL_IMAGE}/${product.thumbnail}` : 'https://images.unsplash.com/photo-1572099606223-6e29045d7de3?q=80&w=2070&auto=format&fit=crop'
}

const getSaleImage = (sale) => {
  return sale.thumbnail ? `${API_URL_IMAGE}/${sale.thumbnail}` : ''
}

const getDiscountPercentage = (item) => {
  if (item.discount_type === 'percentage') {
    return `-${Number(item.discount_value)}%`
  }
  if (!Number(item.original_price)) return ''
  const percentage = Math.round(((item.original_price - item.sale_price) / item.original_price) * 100)
  return `-${percentage}%`
}
</script>

<template>
  <section v-if="sales.length" class="flash-sale-section container">
    <div class="flash-sale-tabs" role="tablist" aria-label="Flash Sale đang chạy">
      <button
        v-for="sale in sales"
        :key="sale.id"
        type="button"
        role="tab"
        class="flash-sale-tab"
        :class="{ active: sale.id === selectedSale?.id }"
        :aria-selected="sale.id === selectedSale?.id"
        @click="selectedSaleId = sale.id"
      >
        {{ sale.name }}
      </button>
    </div>

    <div v-if="selectedSale && !timeRemaining.expired" class="flash-sale-wrapper">
      <div class="sale-hero" :class="{ 'without-banner': !selectedSale.thumbnail }">
        <div v-if="selectedSale.thumbnail" class="sale-banner-frame">
          <img :src="getSaleImage(selectedSale)" aria-hidden="true" class="sale-banner-backdrop" />
          <img :src="getSaleImage(selectedSale)" :alt="selectedSale.name" class="sale-banner" />
        </div>

        <!-- Flash Sale Header -->
        <div class="flash-sale-header">
          <div class="title-group">
            <div class="fire-icon"><i class="ph-fill ph-lightning"></i></div>
            <div>
              <span class="sale-eyebrow">Ưu đãi đang diễn ra</span>
              <h2>FLASH SALE</h2>
              <p class="sale-name">{{ selectedSale.name }}</p>
            </div>
          </div>
          <p v-if="selectedSale.description" class="sale-description">{{ selectedSale.description }}</p>
          <div class="countdown">
            <span class="time-block">{{ String(timeRemaining.days).padStart(2, '0') }}<span class="label">Ngày</span></span>
            <span class="colon">:</span>
            <span class="time-block">{{ String(timeRemaining.hours).padStart(2, '0') }}<span class="label">Giờ</span></span>
            <span class="colon">:</span>
            <span class="time-block">{{ String(timeRemaining.minutes).padStart(2, '0') }}<span class="label">Phút</span></span>
            <span class="colon">:</span>
            <span class="time-block">{{ String(timeRemaining.seconds).padStart(2, '0') }}<span class="label">Giây</span></span>
          </div>
          <router-link to="/shop" class="view-all-btn">
            Mua ngay <i class="ph ph-arrow-right"></i>
          </router-link>
        </div>
      </div>

      <!-- Flash Sale Products Grid -->
      <div v-if="items.length" class="flash-sale-grid">
        <router-link
          v-for="item in items"
          :key="item.id"
          :to="{ name: 'ProductDetail', params: { slug: item.product.slug } }"
          class="flash-sale-card"
        >
          <div class="image-container">
            <img :src="getProductImage(item.product)" :alt="item.product.name" class="product-image" loading="lazy" />
            <div v-if="getDiscountPercentage(item)" class="discount-badge">{{ getDiscountPercentage(item) }}</div>
            <!-- Hot flame icon -->
            <div class="hot-badge"><i class="ph-fill ph-fire"></i> Hot</div>
          </div>
          <div class="product-info">
            <h3 class="product-name" :title="item.product.name">{{ item.product.name }}</h3>
            <div class="price-wrapper">
              <span class="sale-price">{{ formatCurrency(item.sale_price) }}</span>
              <span class="original-price">{{ formatCurrency(item.original_price) }}</span>
            </div>
          </div>
        </router-link>
      </div>
      <p v-else class="sale-empty">Chương trình chưa có sản phẩm áp dụng.</p>
    </div>
  </section>
</template>

<style scoped>
.flash-sale-section {
  padding: 0 0 60px;
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.flash-sale-tabs {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  padding: 4px 2px 18px;
  scrollbar-width: none;
}

.flash-sale-tabs::-webkit-scrollbar {
  display: none;
}

.flash-sale-tab {
  background: #fff7f5;
  border: 1px solid #fed7ce;
  border-radius: 999px;
  color: #9f2922;
  cursor: pointer;
  flex: none;
  font-size: 15px;
  font-weight: 700;
  padding: 12px 24px;
  transition: all 0.25s ease;
}

.flash-sale-tab:hover {
  border-color: #f04436;
  color: #dc2626;
}

.flash-sale-tab.active {
  background: linear-gradient(115deg, #ef2f27, #ff6332);
  border-color: transparent;
  box-shadow: 0 9px 22px rgba(229, 43, 33, 0.32);
  color: #fff;
}

.flash-sale-wrapper {
  background:
    radial-gradient(circle at 4% 3%, rgba(255, 140, 42, 0.36), transparent 28%),
    radial-gradient(circle at 97% 0%, rgba(255, 55, 33, 0.4), transparent 34%),
    linear-gradient(135deg, #290706 0%, #540b09 48%, #210605 100%);
  border-radius: var(--radius-xl);
  padding: 30px;
  box-shadow: 0 24px 55px -18px rgba(116, 14, 9, 0.7);
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(255, 109, 62, 0.35);
}

.sale-hero {
  display: grid;
  gap: 30px;
  grid-template-columns: minmax(320px, 1.05fr) minmax(330px, 0.95fr);
  margin-bottom: 30px;
}

.sale-hero.without-banner {
  grid-template-columns: 1fr;
}

.sale-hero.without-banner .flash-sale-header {
  align-items: center;
  text-align: center;
}

.sale-hero.without-banner .sale-description {
  margin-left: auto;
  margin-right: auto;
}

.sale-banner-frame {
  align-items: center;
  background: #220605;
  border: 1px solid rgba(255, 165, 110, 0.28);
  border-radius: var(--radius-lg);
  display: flex;
  height: clamp(285px, 29vw, 365px);
  justify-content: center;
  overflow: hidden;
  padding: 18px;
  position: relative;
}

.sale-banner-backdrop {
  filter: blur(28px) brightness(0.56) saturate(1.25);
  height: calc(100% + 60px);
  inset: -30px;
  object-fit: cover;
  opacity: 0.72;
  position: absolute;
  transform: scale(1.12);
  width: calc(100% + 60px);
}

.sale-banner {
  border-radius: calc(var(--radius-lg) - 7px);
  display: block;
  height: calc(100% - 2px);
  max-width: calc(100% - 2px);
  object-fit: contain;
  position: relative;
  width: auto;
  z-index: 1;
}

.flash-sale-wrapper::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 5px;
  background: linear-gradient(90deg, #ff3128, #ffb020, #ff3529, #ff922e);
  background-size: 200% auto;
  animation: shimmer 3s linear infinite;
}

@keyframes shimmer {
  to { background-position: 200% center; }
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

.flash-sale-header {
  display: flex;
  align-items: flex-start;
  flex-direction: column;
  justify-content: center;
  padding: 8px 6px;
}

.title-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.fire-icon {
  align-items: center;
  background: linear-gradient(145deg, #ffbd2e, #fb3225);
  border-radius: 18px;
  box-shadow: 0 10px 25px rgba(255, 66, 28, 0.46);
  color: #fff;
  display: flex;
  height: 58px;
  justify-content: center;
  width: 58px;
}

.fire-icon i {
  font-size: 34px;
  animation: pulse 2s infinite;
}

.sale-eyebrow {
  color: #ffb291;
  display: block;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1.2px;
  margin-bottom: 3px;
  text-transform: uppercase;
}

.title-group h2 {
  font-size: clamp(29px, 3vw, 38px);
  font-weight: 900;
  font-style: italic;
  color: #fff6ef;
  letter-spacing: 1px;
  text-transform: uppercase;
  background: linear-gradient(to right, #fff9f2, #ff936e);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin: 0;
}

.sale-name {
  color: #ffd4c4;
  font-size: 16px;
  font-weight: 700;
  margin: 3px 0 0;
}

.sale-description {
  color: #eab5a6;
  font-size: 14px;
  line-height: 1.55;
  margin: 20px 0 25px;
  max-width: 450px;
}

@keyframes pulse {
  0% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.5)); }
  50% { transform: scale(1.1); filter: drop-shadow(0 0 20px rgba(239, 68, 68, 0.8)); }
  100% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(239, 68, 68, 0.5)); }
}

.countdown {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-bottom: 31px;
}

.time-block {
  background: rgba(255, 69, 44, 0.2);
  border: 1px solid rgba(255, 111, 64, 0.46);
  color: #fff1eb;
  font-size: 23px;
  font-weight: 800;
  width: 60px;
  height: 62px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 14px;
  position: relative;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}

.time-block .label {
  position: absolute;
  bottom: -22px;
  font-size: 11px;
  color: #e9a995;
  font-weight: 600;
  text-transform: uppercase;
}

.colon {
  font-size: 24px;
  font-weight: 800;
  color: #ff7b51;
  animation: blink 1s step-start infinite;
}

@keyframes blink {
  50% { opacity: 0; }
}

.view-all-btn {
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  font-size: 15px;
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 13px 25px;
  background: linear-gradient(110deg, #ee2c23, #ff7135);
  border-radius: 100px;
  transition: all 0.3s ease;
  border: 0;
  box-shadow: 0 12px 26px rgba(235, 41, 28, 0.42);
}

.view-all-btn:hover {
  background: linear-gradient(110deg, #ff3c29, #ff8c3b);
  color: #fff;
  transform: translateX(4px);
}

.flash-sale-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 24px;
}

.flash-sale-card {
  background: rgba(70, 12, 10, 0.75);
  border-radius: var(--radius-lg);
  overflow: hidden;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(255, 121, 77, 0.22);
  position: relative;
  z-index: 1;
}

.flash-sale-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 28px -8px rgba(16, 2, 1, 0.75), 0 0 23px rgba(255, 62, 31, 0.32);
  border-color: rgba(255, 103, 53, 0.7);
}

.image-container {
  position: relative;
  aspect-ratio: 3/4;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.7s ease;
}

.flash-sale-card:hover .product-image {
  transform: scale(1.08);
}

.discount-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  background: linear-gradient(110deg, #f32620, #ff6830);
  color: white;
  padding: 6px 10px;
  border-radius: 8px;
  font-weight: 800;
  font-size: 14px;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
  z-index: 2;
}

.hot-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  color: #ffc13c;
  padding: 6px 12px;
  border-radius: 100px;
  font-weight: 700;
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
  border: 1px solid rgba(245, 158, 11, 0.3);
  z-index: 2;
}

.product-info {
  padding: 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: linear-gradient(to bottom, rgba(125, 23, 16, 0.18), rgba(22, 3, 3, 0.3));
}

.product-name {
  color: #f3f4f6;
  font-size: 16px;
  font-weight: 600;
  line-height: 1.4;
  margin: 0 0 16px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.price-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.sale-price {
  color: #ff6652;
  font-size: 20px;
  font-weight: 800;
  text-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

.original-price {
  color: #6b7280;
  font-size: 14px;
  text-decoration: line-through;
  font-weight: 500;
}

.sale-empty {
  border: 1px dashed rgba(255, 255, 255, 0.15);
  border-radius: var(--radius-lg);
  color: #d1d5db;
  margin: 0;
  padding: 36px 20px;
  text-align: center;
}

@media (max-width: 992px) {
  .sale-hero {
    grid-template-columns: 1fr;
  }

  .flash-sale-header {
    padding-top: 0;
  }
}

@media (max-width: 768px) {
  .flash-sale-wrapper {
    padding: 18px;
    border-radius: var(--radius-lg);
  }
  .sale-hero {
    gap: 22px;
    margin-bottom: 24px;
  }
  .sale-banner-frame {
    height: 245px;
    padding: 10px;
  }
  .title-group h2 {
    font-size: 24px;
  }
  .fire-icon {
    height: 48px;
    width: 48px;
  }
  .fire-icon i {
    font-size: 28px;
  }
  .time-block {
    width: 48px;
    height: 53px;
    font-size: 19px;
  }
  .countdown {
    gap: 5px;
  }
  .flash-sale-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
  }
  .product-info {
    padding: 16px;
  }
  .sale-price {
    font-size: 18px;
  }
}
</style>
