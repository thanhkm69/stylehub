<script setup>
import { computed } from 'vue'
import { API_URL_IMAGE } from '@/config/env'
import WishlistButton from './WishlistButton.vue'

const props = defineProps({
  product: { type: Object, required: true },
  showBadge: { type: Boolean, default: false },
})

const productImage = computed(() => {
  if (props.product.thumbnail) {
    return `${API_URL_IMAGE}/${props.product.thumbnail}`
  }
  if (props.product.images?.length) {
    return `${API_URL_IMAGE}/${props.product.images[0].image}`
  }
  return 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=1000&auto=format&fit=crop'
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN').format(price) + '₫'
}

const salePercent = computed(() => {
  if (!props.product.sale_price || !props.product.original_price) return 0
  return Math.round((1 - props.product.sale_price / props.product.original_price) * 100)
})
</script>

<template>
  <router-link :to="`/products/${product.slug}`" class="product-card">
    <div class="product-image-wrapper">
      <span v-if="showBadge" class="badge new">Mới</span>
      <span v-if="product.sale_price" class="badge sale">-{{ salePercent }}%</span>
      <WishlistButton :product-id="product.id" :class="['card-wishlist-btn', { 'has-sale': product.sale_price }]" />
      <img :src="productImage" :alt="product.name" class="product-image" />
      <div class="product-actions">
        <button class="action-btn cart-btn" title="Thêm vào giỏ" @click.prevent>
          <i class="ph ph-shopping-cart-simple"></i>
        </button>
      </div>
    </div>
    <div class="product-info">
      <div class="product-category">{{ product.category?.name || 'Chưa phân loại' }}</div>
      <h3 class="product-name">{{ product.name }}</h3>
      <div v-if="product.sale_price" class="product-pricing">
        <span v-if="product.sale_price_from_variant" class="price-from">Từ</span>
        <span class="product-price sale-price">{{ formatPrice(product.sale_price) }}</span>
        <span class="original-price">{{ formatPrice(product.original_price) }}</span>
      </div>
      <div v-else class="product-price">{{ formatPrice(product.price) }}</div>
    </div>
  </router-link>
</template>

<style scoped>
.product-card {
  display: block;
  background: var(--surface);
  border-radius: var(--radius-md);
  padding: 16px;
  transition: var(--transition);
  border: 1px solid transparent;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
}

.product-card:hover {
  box-shadow: var(--shadow-hover);
  transform: translateY(-5px);
  border-color: var(--border);
}

.product-image-wrapper {
  position: relative;
  border-radius: var(--radius-md);
  overflow: hidden;
  aspect-ratio: 4/5;
  background: var(--accent);
  margin-bottom: 16px;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .product-image {
  transform: scale(1.05);
}

.badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: var(--surface);
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 600;
  border-radius: var(--radius-full);
  box-shadow: var(--shadow-sm);
  z-index: 2;
}
.badge.new { color: #10b981; }

.badge.sale {
  background: #ef3026;
  color: #fff;
  left: auto;
  right: 12px;
}

.product-actions {
  position: absolute;
  bottom: 12px;
  right: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.action-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--surface);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-md);
  opacity: 0;
  transform: translateY(10px);
  transition: var(--transition);
  color: var(--text-main);
  font-size: 18px;
  border: none;
  cursor: pointer;
}

.product-card:hover .action-btn {
  opacity: 1;
  transform: translateY(0);
}

.action-btn:nth-child(2) {
  transition-delay: 0.05s;
}

.card-wishlist-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 3;
  opacity: 0;
  transform: translateY(-5px);
  transition: all 0.3s ease;
}

.product-card:hover .card-wishlist-btn {
  opacity: 1;
  transform: translateY(0);
}

.cart-btn:hover {
  background: var(--primary);
  color: var(--surface);
}

.product-category {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 4px;
}

.product-name {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 8px;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-price {
  font-weight: 700;
  font-size: 18px;
}

.card-wishlist-btn.has-sale {
  top: 54px;
}

.product-pricing {
  align-items: baseline;
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
}

.sale-price {
  color: #e62920;
}

.price-from {
  color: #e62920;
  font-size: 12px;
  font-weight: 700;
}

.original-price {
  color: var(--text-muted);
  font-size: 13px;
  text-decoration: line-through;
}
</style>
