<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProductPublicStore } from '@/stores/productPublic'
import { useCartStore } from '@/stores/cart'
import { useNotify } from '@/composables/useNotify'
import { API_URL_IMAGE } from '@/config/env'
import ProductCard from '@/components/features/products/ProductCard.vue'
import WishlistButton from '@/components/features/products/WishlistButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import BaseButton from '@/components/base/BaseButton.vue'

const route = useRoute()
const store = useProductPublicStore()
const cartStore = useCartStore()
const toast = useNotify()

const route = useRoute()
const store = useProductPublicStore()

// ── Image Gallery ──
const selectedImageIndex = ref(0)

const galleryImages = computed(() => {
  if (!store.product) return []
  const images = []
  if (store.product.thumbnail) {
    images.push({ id: 'thumb', image: store.product.thumbnail, alt: store.product.name })
  }
  if (store.product.images?.length) {
    store.product.images.forEach((img) => {
      if (img.image !== store.product.thumbnail) {
        images.push(img)
      }
    })
  }
  return images.length ? images : [{ id: 'placeholder', image: null, alt: 'No image' }]
})

const currentImage = computed(() => {
  const img = galleryImages.value[selectedImageIndex.value]
  if (!img || !img.image) {
    return 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=1000&auto=format&fit=crop'
  }
  return `${API_URL_IMAGE}/${img.image}`
})

// ── Variant Selection ──
const selectedVariant = ref(null)
const quantity = ref(1)

const groupedAttributes = computed(() => {
  if (!store.product?.variants?.length) return []
  const attrMap = new Map()

  store.product.variants.forEach((variant) => {
    variant.attribute_values?.forEach((av) => {
      const attrKey = av.attribute?.id
      if (!attrKey) return
      if (!attrMap.has(attrKey)) {
        attrMap.set(attrKey, {
          attribute: av.attribute,
          values: new Map(),
        })
      }
      const group = attrMap.get(attrKey)
      if (!group.values.has(av.slug)) {
        group.values.set(av.slug, { value: av.value, slug: av.slug })
      }
    })
  })

  return Array.from(attrMap.values()).map((g) => ({
    ...g.attribute,
    values: Array.from(g.values.values()),
  }))
})

const selectedAttributes = ref({})

const matchedVariant = computed(() => {
  if (!store.product?.variants?.length) return null
  const selectedKeys = Object.keys(selectedAttributes.value)
  if (selectedKeys.length === 0) return null

  return store.product.variants.find((variant) => {
    if (!variant.attribute_values) return false
    return selectedKeys.every((attrId) => {
      return variant.attribute_values.some(
        (av) => String(av.attribute?.id) === attrId && av.slug === selectedAttributes.value[attrId],
      )
    })
  }) || null
})

const displayPrice = computed(() => {
  if (matchedVariant.value) return matchedVariant.value.price
  return store.product?.price || 0
})

const displayStock = computed(() => {
  if (matchedVariant.value) return matchedVariant.value.stock
  return null
})

const selectAttribute = (attrId, valueSlug) => {
  selectedAttributes.value[attrId] = valueSlug
}

const increaseQty = () => { 
  if (displayStock.value !== null && quantity.value >= displayStock.value) {
    toast.warning(`Chỉ còn ${displayStock.value} sản phẩm trong kho`)
    return
  }
  quantity.value++ 
}
const increaseQty = () => { quantity.value++ }
const decreaseQty = () => { if (quantity.value > 1) quantity.value-- }
const handleQtyBlur = () => {
  if (typeof quantity.value !== 'number' || isNaN(quantity.value) || quantity.value < 1) {
    quantity.value = 1
  }
  if (displayStock.value !== null && quantity.value > displayStock.value) {
    toast.warning(`Chỉ còn ${displayStock.value} sản phẩm trong kho`)
    quantity.value = displayStock.value
  }
}

// ── Format ──
const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price) + '₫'

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

// ── Cart ──
const handleAddToCart = async () => {
  // 1. If has variants, must select all attributes
  if (groupedAttributes.value.length > 0 && !matchedVariant.value) {
    toast.warning('Vui lòng chọn đầy đủ các tùy chọn sản phẩm')
    return
  }

  const payload = {
    product_id: store.product.id,
    product_variant_id: matchedVariant.value ? matchedVariant.value.id : null,
    quantity: quantity.value
  }

  const res = await cartStore.store(payload)
  if (res.success) {
    toast.success('Đã thêm vào giỏ hàng thành công')
  } else {
    toast.error(res.message)
  }
}

// ── Fetch ──
const loadProduct = async (slug) => {
  selectedImageIndex.value = 0
  selectedVariant.value = null
  selectedAttributes.value = {}
  quantity.value = 1
  await store.show(slug)
}

onMounted(() => {
  loadProduct(route.params.slug)
})

const mockReviews = [
  { id: 1, author: 'Nguyễn Văn A', rating: 5, date: '2026-05-10', comment: 'Sản phẩm tuyệt vời! Chất vải rất mềm và mát, form dáng chuẩn như hình. Giao hàng cực nhanh.' },
  { id: 2, author: 'Trần Thị B', rating: 4, date: '2026-05-08', comment: 'Đồ đẹp, đường may sắc sảo. Tuy nhiên màu sắc thực tế hơi đậm hơn trên ảnh một chút nhưng vẫn rất hài lòng.' },
  { id: 3, author: 'Lê Văn C', rating: 5, date: '2026-05-05', comment: 'Shop phục vụ rất nhiệt tình, tư vấn size chuẩn. Lần sau sẽ tiếp tục ủng hộ shop.' },
  { id: 4, author: 'Phạm Minh D', rating: 5, date: '2026-05-01', comment: 'Xứng đáng 5 sao cho chất lượng và dịch vụ. Phối đồ rất dễ và trông rất sang.' }
];

watch(() => route.params.slug, (newSlug) => {
  if (newSlug) {
    loadProduct(newSlug)
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
})
</script>

<template>
  <main class="detail-page">
    <template v-if="store.product">
      <!-- Breadcrumb -->
      <nav class="breadcrumb container">
        <router-link to="/">Trang chủ</router-link>
        <i class="ph ph-caret-right"></i>
        <router-link to="/shop">Cửa hàng</router-link>
        <i class="ph ph-caret-right"></i>
        <span v-if="store.product.category">
          <router-link :to="{ name: 'Shop', query: { category_id: store.product.category.id } }">
            {{ store.product.category.name }}
          </router-link>
          <i class="ph ph-caret-right"></i>
        </span>
        <span class="current">{{ store.product.name }}</span>
      </nav>

      <!-- Product Main -->
      <section class="product-detail container">
        <!-- Gallery -->
        <div class="gallery">
          <div class="gallery-main">
            <img :src="currentImage" :alt="store.product.name" class="main-image" />
          </div>
          <div v-if="galleryImages.length > 1" class="gallery-thumbs">
            <button
              v-for="(img, idx) in galleryImages"
              :key="img.id"
              :class="['thumb', { active: idx === selectedImageIndex }]"
              @click="selectedImageIndex = idx"
            >
              <img
                :src="img.image ? `${API_URL_IMAGE}/${img.image}` : currentImage"
                :alt="img.alt || store.product.name"
              />
            </button>
          </div>
        </div>

        <!-- Product Info -->
        <div class="product-info">
          <div class="info-header">
            <router-link
              v-if="store.product.category"
              :to="{ name: 'Shop', query: { category_id: store.product.category.id } }"
              class="product-category-link"
            >
              {{ store.product.category.name }}
            </router-link>
            <div class="product-title-row">
              <h1 class="product-title">{{ store.product.name }}</h1>
              <WishlistButton :product-id="store.product.id" size="lg" />
            </div>
            <div class="product-meta">
              <span class="meta-item star-rating">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#f59e0b" viewBox="0 0 256 256"><path d="M234.29,114.85l-45,38.83L203,211.75a16.4,16.4,0,0,1-24.5,17.82L128,198.49,77.47,229.57A16.4,16.4,0,0,1,53,211.75l13.76-58.07-45-38.83A16.46,16.46,0,0,1,31.08,86l59-4.76,22.76-55.08a16.36,16.36,0,0,1,30.27,0l22.75,55.08,59,4.76a16.46,16.46,0,0,1,9.37,28.86Z"></path></svg>
                4.8 <span class="review-count">(124 đánh giá)</span>
              </span>
              <span class="meta-separator">•</span>
              <span class="meta-item"><i class="ph ph-eye"></i> {{ store.product.views }} lượt xem</span>
            </div>
          </div>

          <div class="price-block">
            <span class="current-price">{{ formatPrice(displayPrice) }}</span>
            <span v-if="displayStock !== null" :class="['stock-badge', displayStock > 0 ? 'in-stock' : 'out-of-stock']">
              {{ displayStock > 0 ? `Còn ${displayStock} sản phẩm` : 'Hết hàng' }}
            </span>
          </div>

          <!-- Variant Attributes -->
          <div v-if="groupedAttributes.length" class="variants-section">
            <div v-for="attr in groupedAttributes" :key="attr.id" class="variant-group">
              <label class="variant-label">{{ attr.name }}</label>
              <div class="variant-options">
                <button
                  v-for="val in attr.values"
                  :key="val.slug"
                  :class="['variant-btn', { active: selectedAttributes[attr.id] === val.slug }]"
                  @click="selectAttribute(String(attr.id), val.slug)"
                >
                  {{ val.value }}
                </button>
              </div>
            </div>
          </div>

          <!-- Variant SKU info -->
          <div v-if="matchedVariant" class="matched-variant-info">
            <span class="sku-label">SKU: <strong>{{ matchedVariant.sku }}</strong></span>
          </div>

          <!-- Quantity & Actions -->
          <div class="actions-section">
            <div class="quantity-selector">
              <button class="qty-btn" @click="decreaseQty" :disabled="quantity <= 1">
                <i class="ph ph-minus"></i>
              </button>
              <input
                v-model.number="quantity"
                type="number"
                class="qty-input"
                min="1"
                @blur="handleQtyBlur"
              />
              <button class="qty-btn" @click="increaseQty">
                <i class="ph ph-plus"></i>
              </button>
            </div>
            <BaseButton 
              variant="primary"
              class="btn-add-cart" 
              @click="handleAddToCart"
              :loading="cartStore.loading"
              :disabled="displayStock !== null && displayStock <= 0"
            >
              <i class="ph ph-shopping-cart-simple"></i> {{ displayStock !== null && displayStock <= 0 ? 'Hết hàng' : 'Thêm vào giỏ hàng' }}
            </BaseButton>
            <button class="btn btn-primary btn-add-cart">
              <i class="ph ph-shopping-cart-simple"></i> Thêm vào giỏ hàng
            </button>
          </div>

          <!-- Description -->
          <div v-if="store.product.description" class="description-section">
            <h3>Mô tả sản phẩm</h3>
            <div class="description-content" v-html="store.product.description"></div>
          </div>
        </div>
      </section>

      <!-- Outfit Products (AI) -->
      <section v-if="store.product.outfit_products?.length" class="related-section container">
        <div class="section-header">
          <div>
            <h2 class="section-title">Gợi ý phối đồ</h2>
            <p style="color: var(--text-muted); margin-top: 8px;">Được đề xuất bởi Stylist AI</p>
          </div>
        </div>
        <div class="product-grid">
          <ProductCard
            v-for="rp in store.product.outfit_products"
            :key="rp.id"
            :product="rp"
          />
        </div>
      </section>

      <!-- Similar Products -->
      <section v-if="store.product.similar_products?.length" class="related-section container">
        <div class="section-header">
          <div>
            <h2 class="section-title">Sản phẩm tương tự</h2>
            <p style="color: var(--text-muted); margin-top: 8px;">Có thể bạn cũng thích</p>
          </div>
        </div>
        <div class="product-grid">
          <ProductCard
            v-for="rp in store.product.similar_products"
            :key="rp.id"
            :product="rp"
          />
        </div>
      </section>

      <!-- Reviews Section -->
      <section class="reviews-section container">
        <div class="section-header">
          <h2 class="section-title">Đánh giá từ khách hàng</h2>
          <div class="rating-summary">
            <div class="avg-rating">4.8</div>
            <div class="rating-stars">
              <i v-for="i in 5" :key="i" class="ph-fill ph-star" :style="{ color: i <= 4 ? '#f59e0b' : '#e2e8f0' }"></i>
            </div>
            <div class="total-reviews">({{ mockReviews.length }} đánh giá)</div>
          </div>
        </div>

        <div class="reviews-list">
          <div v-for="review in mockReviews" :key="review.id" class="review-item">
            <div class="review-user">
              <div class="user-avatar">{{ review.author.charAt(0) }}</div>
              <div class="user-info">
                <h4 class="user-name">{{ review.author }}</h4>
                <div class="review-meta">
                  <div class="item-stars">
                    <i v-for="i in 5" :key="i" class="ph-fill ph-star" :style="{ color: i <= review.rating ? '#f59e0b' : '#e2e8f0' }"></i>
                  </div>
                  <span class="review-date">{{ formatDate(review.date) }}</span>
                </div>
              </div>
            </div>
            <p class="review-comment">{{ review.comment }}</p>
          </div>
        </div>
      </section>
    </template>

    <!-- Error State -->
    <div v-else-if="store.error" class="error-state container">
      <i class="ph ph-warning-circle"></i>
      <h2>Không tìm thấy sản phẩm</h2>
      <p>{{ store.error }}</p>
      <router-link to="/shop" class="btn btn-primary" style="margin-top: 20px;">Quay lại cửa hàng</router-link>
    </div>
  </main>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.detail-page {
  min-height: 60vh;
}

/* ── Breadcrumb ── */
.breadcrumb {
  padding: 24px 0 0;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: var(--text-muted);
  flex-wrap: wrap;
  animation: fadeIn 0.5s ease-out forwards;
}

.breadcrumb a {
  color: var(--text-muted);
  transition: var(--transition);
}

.breadcrumb a:hover {
  color: var(--primary);
}

.breadcrumb .current {
  color: var(--text-main);
  font-weight: 500;
}

.breadcrumb i {
  font-size: 10px;
  opacity: 0.5;
}

/* ── Product Detail Layout ── */
.product-detail {
  display: grid;
  grid-template-columns: 380px 1fr;
  gap: 32px;
  padding-top: 24px;
  padding-bottom: 60px;
  max-width: 900px;
  margin: 0 auto;
}

/* ── Gallery ── */
.gallery {
  position: sticky;
  top: 100px;
  align-self: start;
  animation: slideUp 0.6s ease-out 0.2s both;
}

.gallery-main {
  border-radius: var(--radius-md);
  overflow: hidden;
  aspect-ratio: 1/1;
  background: var(--accent);
  margin-bottom: 12px;
}

.main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.gallery-main:hover .main-image {
  transform: scale(1.03);
}

.gallery-thumbs {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.thumb {
  width: 72px;
  height: 72px;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 2px solid transparent;
  cursor: pointer;
  transition: var(--transition);
  flex-shrink: 0;
  padding: 0;
  background: var(--accent);
}

.thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumb:hover, .thumb.active {
  border-color: var(--primary);
}

/* ── Product Info ── */
.product-info {
  display: flex;
  flex-direction: column;
  gap: 20px;
  animation: slideUp 0.6s ease-out 0.3s both;
}

.product-category-link {
  display: inline-block;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: var(--text-muted);
  margin-bottom: 8px;
  transition: var(--transition);
}

.product-category-link:hover {
  color: var(--primary);
}

.product-title {
  font-size: 24px;
  font-weight: 800;
  color: var(--text-main);
  margin: 0;
}

.product-title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  margin-top: 10px;
}

.product-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
}

.meta-separator {
  color: var(--border);
  font-size: 14px;
}

.star-rating {
  color: var(--text-main);
  font-weight: 600;
}

.review-count {
  color: var(--text-muted);
  font-weight: 400;
  margin-left: 2px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: var(--text-muted);
}

.meta-item i {
  font-size: 16px;
}

/* Price */
.price-block {
  display: flex;
  align-items: center;
  gap: 16px;
}

.current-price {
  font-size: 22px;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.stock-badge {
  padding: 6px 14px;
  border-radius: var(--radius-full);
  font-size: 13px;
  font-weight: 600;
}

.in-stock {
  background: #dcfce7;
  color: #166534;
}

.out-of-stock {
  background: #fef2f2;
  color: #dc2626;
}

/* ── Variants ── */
.variants-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.variant-label {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
  display: block;
}

.variant-options {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.variant-btn {
  padding: 8px 16px;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  font-size: 13px;
  font-weight: 500;
  background: var(--surface);
  cursor: pointer;
  transition: var(--transition);
  color: var(--text-main);
}

.variant-btn:hover {
  border-color: var(--primary);
}

.variant-btn.active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}

.matched-variant-info {
  font-size: 13px;
  color: var(--text-muted);
}

.sku-label strong {
  color: var(--text-main);
}

/* ── Actions ── */
.actions-section {
  display: flex;
  align-items: center;
  gap: 16px;
}

.quantity-selector {
  display: flex;
  align-items: center;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.qty-btn {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--surface);
  border: none;
  cursor: pointer;
  transition: var(--transition);
  color: var(--text-main);
  font-size: 16px;
}

.qty-btn:hover:not(:disabled) {
  background: var(--accent);
}

.qty-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.qty-input {
  width: 50px;
  text-align: center;
  font-weight: 600;
  font-size: 16px;
  border: none;
  border-left: 1px solid var(--border);
  border-right: 1px solid var(--border);
  height: 44px;
  line-height: 44px;
  background: transparent;
  color: var(--text-main);
  outline: none;
  -moz-appearance: textfield;
}

.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.btn-add-cart {
  flex: 1;
  gap: 8px;
  font-size: 15px;
  height: 48px;
}

.btn-wishlist {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  border: 1px solid var(--border);
  background: var(--surface);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: var(--text-muted);
  cursor: pointer;
  transition: var(--transition);
  flex-shrink: 0;
}

.btn-wishlist:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: #fef2f2;
}

/* ── Description ── */
.description-section {
  padding-top: 8px;
  border-top: 1px solid var(--border);
}

.description-section h3 {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 16px;
}

.description-content {
  font-size: 15px;
  line-height: 1.8;
  color: var(--text-muted);
}

/* ── Related Products ── */
.related-section {
  padding-bottom: 80px;
  animation: slideUp 0.6s ease-out 0.5s both;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
}

/* ── Error State ── */
.error-state {
  text-align: center;
  padding: 120px 20px;
  color: var(--text-muted);
}

.error-state i {
  font-size: 64px;
  margin-bottom: 16px;
  display: block;
  color: #ef4444;
}

.error-state h2 {
  color: var(--text-main);
  margin-bottom: 8px;
}

/* ── Responsive ── */
@media (max-width: 992px) {
  .product-detail {
    grid-template-columns: 1fr;
    gap: 32px;
  }
  .gallery {
    position: static;
  }
}

@media (max-width: 768px) {
  .product-title {
    font-size: 24px;
  }
  .current-price {
    font-size: 24px;
  }
  .actions-section {
    flex-wrap: wrap;
  }
  .btn-add-cart {
    width: 100%;
    order: 3;
  }
  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
  }
}
/* Reviews Section */
.reviews-section {
  padding: 60px 0;
  border-top: 1px solid var(--border);
}

.rating-summary {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 12px;
}

.avg-rating {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-main);
}

.rating-stars {
  display: flex;
  gap: 3px;
  font-size: 18px;
}

.total-reviews {
  color: var(--text-muted);
  font-weight: 500;
}

.reviews-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
  margin-top: 32px;
}

.review-item {
  background: #f8fafc;
  padding: 16px 20px;
  border-radius: 12px;
  border: 1px solid transparent;
  transition: all 0.3s ease;
}

.review-item:hover {
  background: white;
  border-color: var(--border);
  box-shadow: var(--shadow-md);
  transform: translateY(-4px);
}

.review-user {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 16px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: var(--accent);
  color: #C8883A;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 16px;
}

.user-name {
  font-size: 15px;
  font-weight: 700;
  margin-bottom: 2px;
}

.review-meta {
  display: flex;
  align-items: center;
  gap: 12px;
}

.item-stars {
  display: flex;
  gap: 2px;
  font-size: 14px;
}

.review-date {
  font-size: 13px;
  color: var(--text-muted);
}

.review-comment {
  font-size: 14px;
  line-height: 1.6;
  color: var(--text-main);
  opacity: 0.9;
}

@media (max-width: 768px) {
  .reviews-list {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .avg-rating {
    font-size: 28px;
  }
}
</style>
