<script setup>
import { onMounted, computed, ref } from 'vue'
import { useCartStore } from '@/stores/cart'
import { API_URL_IMAGE } from '@/config/env'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import { useNotify } from '@/composables/useNotify'
import { swalConfirmDelete } from '@/composables/useSwal'

const cartStore = useCartStore()
const toast = useNotify()

onMounted(() => {
  cartStore.index()
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price)
}

const comboImage = (item) => {
  return item.thumbnail ? `${API_URL_IMAGE}/${item.thumbnail}` : '/placeholder.png'
}

const handleUpdateQuantity = async (item, delta) => {
  const newQty = item.quantity + delta
  const maxStock = item.stock ?? 0
  
  if (newQty > maxStock) {
    toast.error(`Số lượng tồn kho không đủ (Chỉ còn ${maxStock} sản phẩm)`)
    return
  }
  
  updateItemQuantity(item, newQty)
}

const handleManualInput = (item, event) => {
  const originalQty = item.quantity
  let newQty = parseInt(event.target.value)
  const maxStock = item.stock ?? 0

  if (isNaN(newQty) || newQty < 1) newQty = 1
  
  if (newQty > maxStock) {
    toast.error(`Số lượng tồn kho không đủ (Chỉ còn ${maxStock} sản phẩm)`)
    
    // Force DOM reset to the original quantity
    setTimeout(() => {
      event.target.value = originalQty
    }, 0)
    
    isAdjusting.value = true
    if (adjustTimeout) clearTimeout(adjustTimeout)
    adjustTimeout = setTimeout(() => {
      isAdjusting.value = false
    }, 500)
    
    return // Stop here, do not update backend
  }
  
  if (newQty > 99) newQty = 99
  
  if (newQty !== originalQty) {
    event.target.value = newQty
    updateItemQuantity(item, newQty)
  }
}

const updateItemQuantity = async (item, newQty) => {
  if (newQty === item.quantity) return
  if (newQty < 1) return
  
  const res = await cartStore.update(item.id, newQty)
  if (!res.success) {
    toast.error(res.message)
    // Revert quantity in UI if failed
    cartStore.index() 
  }
}

const handleRemoveItem = async (cartId) => {
  const result = await swalConfirmDelete(
    'Xóa sản phẩm?',
    'Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?'
  )
  
  if (result.isConfirmed) {
    const res = await cartStore.destroy(cartId)
    if (res.success) {
      toast.success('Đã xóa sản phẩm khỏi giỏ hàng')
    } else {
      toast.error(res.message)
    }
  }
}

const handleClearCart = async () => {
  const result = await swalConfirmDelete(
    'Làm trống giỏ hàng?',
    'Tất cả sản phẩm sẽ bị xóa khỏi giỏ hàng của bạn.'
  )
  
  if (result.isConfirmed) {
    const res = await cartStore.clear()
    if (res.success) {
      toast.success('Đã làm trống giỏ hàng')
    }
  }
}

import { useRouter } from 'vue-router'
const router = useRouter()
const isAdjusting = ref(false)
let adjustTimeout = null

const proceedToCheckout = () => {
  if (isAdjusting.value) {
    return // Prevent checkout if we just auto-adjusted quantity
  }
  router.push('/checkout')
}
</script>

<template>
  <div class="cart-page container">
    <header class="cart-header">
      <h1 class="page-title">Giỏ hàng của bạn</h1>
      <span class="count-badge" v-if="cartStore.summary.total_items > 0">
        {{ cartStore.summary.total_items }} sản phẩm
      </span>
    </header>

    <BaseLoading v-if="cartStore.loading && !cartStore.items.length" text="Đang tải giỏ hàng..." />

    <div v-else-if="cartStore.items.length === 0" class="empty-cart">
      <div class="empty-content">
        <i class="ph ph-shopping-cart"></i>
        <h2>Giỏ hàng của bạn đang trống</h2>
        <p>Có vẻ như bạn chưa chọn được món đồ nào ưng ý. Hãy ghé thăm cửa hàng của chúng tôi nhé!</p>
        <router-link to="/shop">
          <BaseButton variant="primary" size="lg">Tiếp tục mua sắm</BaseButton>
        </router-link>
      </div>
    </div>

    <div v-else class="cart-content">
      <div class="cart-items-section">
        <div class="items-header">
          <span>Sản phẩm</span>
          <span>Số lượng</span>
          <span>Tạm tính</span>
          <span></span>
        </div>

        <section v-if="cartStore.summary.applied_combos?.length" class="combo-detail-area">
          <article
            v-for="combo in cartStore.summary.applied_combos"
            :key="combo.id"
            class="combo-detail-card"
          >
            <div class="combo-detail-head">
              <div>
                <h3>{{ combo.name }}</h3>
                <p>Áp dụng <strong>{{ combo.quantity }} bộ</strong></p>
              </div>
              <div class="combo-benefit">
                <span>Giảm combo này</span>
                <strong>-{{ formatPrice(combo.discount_amount) }}</strong>
              </div>
            </div>

            <div class="combo-products">
              <router-link
                v-for="comboItem in combo.items"
                :key="comboItem.id"
                :to="{ name: 'ProductDetail', params: { slug: comboItem.product_slug } }"
                class="combo-product"
              >
                <img :src="comboImage(comboItem)" :alt="comboItem.product_name" />
                <div>
                  <strong>{{ comboItem.product_name }}</strong>
                  <small>
                    <template v-if="comboItem.variant_sku">{{ comboItem.variant_sku }} · </template>
                    Số lượng: {{ comboItem.quantity }}
                  </small>
                </div>
                <i class="ph ph-caret-right detail-arrow"></i>
              </router-link>
            </div>
          </article>
        </section>

        <div class="items-list">
          <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
            <div class="product-col">
              <div class="product-image">
                <img :src="item.variant?.image ? API_URL_IMAGE + '/' + item.variant.image : API_URL_IMAGE + '/' + item.product.thumbnail" :alt="item.product.name">
              </div>
              <div class="product-info">
                <router-link :to="'/products/' + item.product.slug" class="product-name">
                  {{ item.product.name }}
                </router-link>
                <div v-if="item.variant" class="product-variant">
                  <span v-for="val in item.variant.attribute_values" :key="val.slug">
                    {{ val.attribute.name }}: {{ val.value }}
                  </span>
                </div>
                <div v-if="item.flash_sale" class="item-flash-sale">
                  <i class="ph-fill ph-lightning"></i> {{ item.flash_sale.name }}
                </div>
                <div class="unit-price" :class="{ sale: item.flash_sale }">
                  {{ formatPrice(item.price) }}
                  <span v-if="item.flash_sale" class="unit-original-price">{{ formatPrice(item.original_price) }}</span>
                </div>
              </div>
            </div>

            <div class="quantity-col">
              <div class="quantity-selector">
                <button @click="handleUpdateQuantity(item, -1)" :disabled="item.quantity <= 1">
                  <i class="ph ph-minus"></i>
                </button>
                <input 
                  type="number" 
                  :value="item.quantity" 
                  @change="handleManualInput(item, $event)"
                  @keyup.enter="handleManualInput(item, $event)"
                  min="1"
                  :max="item.stock && item.stock < 99 ? item.stock : 99"
                >
                <button 
                  @click="handleUpdateQuantity(item, 1)" 
                  :disabled="parseInt(item.quantity) >= parseInt(item.stock || 0) || parseInt(item.quantity) >= 99"
                >
                  <i class="ph ph-plus"></i>
                </button>
              </div>
            </div>

            <div class="subtotal-col">
              {{ formatPrice(item.subtotal) }}
            </div>

            <div class="action-col">
              <button @click="handleRemoveItem(item.id)" class="btn-remove" title="Xóa khỏi giỏ hàng">
                <i class="ph ph-trash"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="cart-actions">
          <button @click="handleClearCart" class="btn-clear">
            <i class="ph ph-trash"></i> Làm trống giỏ hàng
          </button>
          <router-link to="/shop" class="btn-continue">
            <i class="ph ph-arrow-left"></i> Tiếp tục mua sắm
          </router-link>
        </div>
      </div>

      <aside class="cart-summary-section">
        <div class="summary-card">
          <h3 class="summary-title">Tóm tắt đơn hàng</h3>

          <div v-if="cartStore.summary.applied_combos?.length" class="combo-applied">
            <div class="combo-applied-title">
              <i class="ph-fill ph-tag"></i>
              {{ cartStore.summary.applied_combos.length }} combo đã áp dụng
            </div>
            <span v-for="combo in cartStore.summary.applied_combos" :key="combo.id">
              {{ combo.name }}: -{{ formatPrice(combo.discount_amount) }}
            </span>
          </div>
          
          <div class="summary-row">
            <span>Tạm tính</span>
            <span>{{ formatPrice(cartStore.summary.subtotal_before_combo ?? cartStore.summary.total_price) }}</span>
          </div>

          <div v-if="cartStore.summary.flash_sale_savings > 0" class="summary-row saving">
            <span>Tiết kiệm Flash Sale</span>
            <span>-{{ formatPrice(cartStore.summary.flash_sale_savings) }}</span>
          </div>

          <div v-if="cartStore.summary.combo_discount > 0" class="summary-row combo-saving">
            <span>Giảm giá Combo</span>
            <span>-{{ formatPrice(cartStore.summary.combo_discount) }}</span>
          </div>
          
          <div class="summary-row">
            <span>Phí vận chuyển</span>
            <span class="free">Miễn phí</span>
          </div>

          <div class="summary-divider"></div>

          <div class="summary-row total">
            <span>Tổng cộng</span>
            <span class="total-price">{{ formatPrice(cartStore.summary.total_price) }}</span>
          </div>

          <p class="vat-note">(Đã bao gồm thuế VAT nếu có)</p>

          <BaseButton variant="primary" size="lg" block class="btn-checkout" @click="proceedToCheckout">
            Thanh toán
          </BaseButton>

          <div class="payment-methods">
            <p>Chúng tôi chấp nhận:</p>
            <div class="icons">
              <i class="ph ph-credit-card"></i>
              <i class="ph ph-money"></i>
              <i class="ph ph-bank"></i>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<style scoped>
.cart-page {
  padding-top: 40px;
  padding-bottom: 80px;
  min-height: 70vh;
}

.cart-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 32px;
}

.page-title {
  font-size: 32px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0;
}

.count-badge {
  background: var(--accent);
  color: #C8883A;
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
}

/* ── Content Layout ── */
.cart-content {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: 40px;
  align-items: start;
}

/* ── Items Section ── */
.cart-items-section {
  background: white;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.items-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 50px;
  padding: 20px 24px;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  font-size: 14px;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.items-list {
  display: flex;
  flex-direction: column;
}

.combo-detail-area {
  background: linear-gradient(135deg, #fff9f6, #fff1ea);
  border-bottom: 1px solid #f5dacc;
  padding: 22px 24px;
}

.combo-detail-card {
  background: #fff;
  border: 1px solid #f3ddd4;
  border-radius: 18px;
  margin-bottom: 13px;
  padding: 16px;
}

.combo-detail-card:last-child {
  margin-bottom: 0;
}

.combo-detail-head {
  align-items: center;
  display: flex;
  gap: 18px;
  justify-content: space-between;
  margin-bottom: 13px;
}

.combo-detail-head h3 {
  color: #491a10;
  font-size: 20px;
  font-weight: 800;
  line-height: 1.25;
  margin: 0 0 4px;
}

.combo-detail-head p {
  color: #a0523d;
  font-size: 13px;
  margin: 0;
}

.combo-benefit {
  background: #fff;
  border: 1px solid #ffd4c5;
  border-radius: 14px;
  flex-shrink: 0;
  padding: 11px 15px;
  text-align: right;
}

.combo-benefit span {
  color: #9f5c48;
  display: block;
  font-size: 11px;
  font-weight: 700;
}

.combo-benefit strong {
  color: #e12f20;
  display: block;
  font-size: 20px;
  font-weight: 850;
}

.combo-products {
  display: grid;
  gap: 10px;
  grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
}

.combo-product {
  align-items: center;
  background: #fff;
  border: 1px solid #f0ddd6;
  border-radius: 14px;
  display: flex;
  gap: 11px;
  min-width: 0;
  padding: 9px;
  position: relative;
  text-decoration: none;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.combo-product:hover {
  box-shadow: 0 7px 17px rgba(97, 37, 20, 0.1);
  transform: translateY(-2px);
}

.combo-product img {
  border-radius: 9px;
  flex-shrink: 0;
  height: 55px;
  object-fit: cover;
  width: 55px;
}

.combo-product div {
  display: flex;
  flex-direction: column;
  min-width: 0;
  padding-right: 15px;
}

.combo-product strong {
  color: var(--text-main);
  font-size: 13px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.combo-product small {
  color: var(--text-muted);
  font-size: 11px;
}

.detail-arrow {
  color: #cfb0a5;
  font-size: 14px;
  position: absolute;
  right: 9px;
}

.cart-item {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 50px;
  padding: 24px;
  border-bottom: 1px solid var(--border);
  align-items: center;
  transition: var(--transition);
}

.cart-item:hover {
  background: #f8fafc;
}

.cart-item:last-child {
  border-bottom: none;
}

/* Product Column */
.product-col {
  display: flex;
  gap: 16px;
}

.product-image {
  width: 90px;
  height: 90px;
  border-radius: var(--radius-md);
  overflow: hidden;
  flex-shrink: 0;
  background: var(--accent);
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.product-name {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-main);
  text-decoration: none;
  transition: var(--transition);
}

.product-name:hover {
  color: var(--primary);
}

.product-variant {
  font-size: 13px;
  color: var(--text-muted);
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.product-variant span {
  background: var(--surface);
  padding: 2px 8px;
  border-radius: 4px;
  border: 1px solid var(--border);
}

.unit-price {
  font-weight: 600;
  color: var(--primary);
  font-size: 14px;
}

.unit-price.sale {
  color: #e62920;
}

.unit-original-price {
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 500;
  margin-left: 7px;
  text-decoration: line-through;
}

.item-flash-sale {
  align-items: center;
  color: #e62920;
  display: flex;
  font-size: 11px;
  font-weight: 700;
  gap: 4px;
}

/* Quantity Column */
.quantity-selector {
  display: flex;
  align-items: center;
  border: 1px solid var(--border);
  border-radius: 8px;
  width: fit-content;
  background: white;
  overflow: hidden;
}

.quantity-selector button {
  width: 32px;
  height: 36px;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-main);
  transition: var(--transition);
}

.quantity-selector button:hover:not(:disabled) {
  background: var(--surface);
  color: var(--primary);
}

.quantity-selector button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.quantity-selector input {
  width: 40px;
  border: none;
  text-align: center;
  font-weight: 700;
  font-size: 14px;
  background: transparent;
}

/* Price/Action columns */
.subtotal-col {
  font-weight: 700;
  color: var(--text-main);
  font-size: 16px;
}

.btn-remove {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: none;
  background: transparent;
  color: #94a3b8;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
}

.btn-remove:hover {
  background: #fee2e2;
  color: #ef4444;
}

/* Cart Actions */
.cart-actions {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--surface);
}

.btn-clear {
  background: transparent;
  border: none;
  color: #ef4444;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: var(--transition);
}

.btn-clear:hover {
  opacity: 0.7;
}

.btn-continue {
  text-decoration: none;
  color: var(--text-main);
  font-weight: 600;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: var(--transition);
}

.btn-continue:hover {
  color: var(--primary);
}

/* ── Summary Section ── */
.summary-card {
  background: white;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border);
  padding: 32px;
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 100px;
}

.summary-title {
  font-size: 20px;
  font-weight: 800;
  margin-bottom: 24px;
  color: var(--text-main);
}

.combo-applied {
  background: linear-gradient(120deg, #fff4ef, #ffe8de);
  border: 1px solid #ffd1c2;
  border-radius: 14px;
  color: #a93620;
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 22px;
  padding: 13px 14px;
}

.combo-applied-title {
  align-items: center;
  color: #e24326;
  display: flex;
  font-size: 11px;
  font-weight: 800;
  gap: 5px;
  text-transform: uppercase;
}

.combo-applied strong {
  color: #7f2414;
  font-size: 14px;
}

.combo-applied span {
  font-size: 12px;
  font-weight: 600;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 16px;
  font-size: 15px;
  color: var(--text-muted);
}

.summary-row.total {
  margin-top: 24px;
  font-size: 18px;
  font-weight: 800;
  color: var(--text-main);
}

.summary-row.saving {
  color: #dc2626;
  font-weight: 700;
}

.summary-row.combo-saving {
  color: #e24326;
  font-weight: 700;
}

.total-price {
  color: var(--primary);
  font-size: 24px;
}

.free {
  color: #10b981;
  font-weight: 600;
}

.summary-divider {
  height: 1px;
  background: var(--border);
  margin: 24px 0;
}

.vat-note {
  font-size: 13px;
  color: var(--text-muted);
  text-align: center;
  margin: 16px 0 24px;
}

.btn-checkout {
  height: 56px;
  font-weight: 700;
  letter-spacing: 0.5px;
}

.payment-methods {
  margin-top: 32px;
  text-align: center;
}

.payment-methods p {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 12px;
}

.payment-methods .icons {
  display: flex;
  justify-content: center;
  gap: 20px;
  font-size: 24px;
  color: #cbd5e1;
}

/* ── Empty State ── */
.empty-cart {
  padding: 100px 0;
  text-align: center;
}

.empty-content i {
  font-size: 100px;
  color: #cbd5e1;
  margin-bottom: 32px;
}

.empty-content h2 {
  font-size: 28px;
  font-weight: 800;
  margin-bottom: 16px;
}

.empty-content p {
  color: var(--text-muted);
  max-width: 500px;
  margin: 0 auto 40px;
  font-size: 16px;
  line-height: 1.6;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .cart-content {
    grid-template-columns: 1fr;
  }
  
  .cart-summary-section {
    order: -1;
  }
}

@media (max-width: 768px) {
  .items-header {
    display: none;
  }
  
  .cart-item {
    grid-template-columns: 1fr;
    gap: 20px;
    text-align: center;
  }
  
  .product-col {
    flex-direction: column;
    align-items: center;
  }
  
  .quantity-col, .subtotal-col, .action-col {
    display: flex;
    justify-content: center;
  }
  
  .page-title {
    font-size: 24px;
  }

  .combo-detail-head {
    align-items: flex-start;
    flex-direction: column;
  }

  .combo-benefit {
    text-align: left;
  }
}
</style>
