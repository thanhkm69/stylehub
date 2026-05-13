<script setup>
import { onMounted } from 'vue'
import { useWishlistStore } from '@/stores/wishlist'
import ProductCard from '@/components/features/products/ProductCard.vue'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import { useNotify } from '@/composables/useNotify'

const wishlistStore = useWishlistStore()
const toast = useNotify()

const loadData = (page = 1) => {
  wishlistStore.index(page)
}

const handleRemove = async (wishlistId, productId) => {
  const res = await wishlistStore.destroy(wishlistId, productId)
  if (res.success) {
    toast.success('Đã gỡ sản phẩm khỏi danh sách yêu thích')
  } else {
    toast.error(res.message)
  }
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="wishlist-page">
    <header class="wishlist-header">
      <h2 class="page-title">Sản phẩm yêu thích</h2>
      <span class="count-badge" v-if="wishlistStore.pagination.total > 0">
        {{ wishlistStore.pagination.total }} sản phẩm
      </span>
    </header>

    <!-- Loading State (Branded Loader like Home) -->
    <BaseLoading v-if="wishlistStore.loading && !wishlistStore.wishlists.length" text="Đang tải danh sách yêu thích..." />

    <!-- Empty State -->
    <div v-else-if="!wishlistStore.loading && wishlistStore.wishlists.length === 0" class="empty-wishlist">
      <div class="empty-content">
        <i class="ph ph-heart-break"></i>
        <h2>Bạn chưa có sản phẩm yêu thích</h2>
        <p>Hãy khám phá những bộ sưu tập mới nhất và lưu lại món đồ bạn ưng ý nhé!</p>
        <router-link to="/shop">
          <BaseButton variant="primary" size="lg">Khám phá ngay</BaseButton>
        </router-link>
      </div>
    </div>

    <!-- Data State -->
    <template v-else>
      <div class="wishlist-grid">
        <div v-for="item in wishlistStore.wishlists" :key="item.id" class="wishlist-item">
          <div class="item-wrapper">
            <button 
              class="remove-btn" 
              @click="handleRemove(item.id, item.product.id)"
              title="Gỡ khỏi yêu thích"
            >
              <i class="ph ph-x"></i>
            </button>
            <ProductCard :product="item.product" />
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="pagination-wrapper" v-if="wishlistStore.pagination.last_page > 1">
        <button 
          v-for="page in wishlistStore.pagination.last_page" 
          :key="page"
          class="page-btn"
          :class="{ active: wishlistStore.pagination.current_page === page }"
          @click="loadData(page)"
        >
          {{ page }}
        </button>
      </div>
    </template>
  </div>
</template>

<style scoped>
.wishlist-page {
  padding: 0;
  min-height: 400px;
}

.wishlist-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 16px;
}

.page-title {
  font-size: 20px;
  font-weight: 700;
  color: var(--text-main);
  margin: 0;
}

.count-badge {
  background: var(--accent);
  color: #C8883A;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.item-wrapper {
  position: relative;
}

.remove-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 10;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: white;
  border: 1px solid var(--border);
  color: var(--text-muted);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: var(--shadow-sm);
}

.remove-btn:hover {
  background: #fef2f2;
  color: #ef4444;
  border-color: #fca5a5;
  transform: scale(1.1);
}

.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.empty-wishlist {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 100px 0;
  text-align: center;
}

.empty-content i {
  font-size: 80px;
  color: #cbd5e1;
  margin-bottom: 24px;
}

.empty-content h2 {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 12px;
}

.empty-content p {
  color: var(--text-muted);
  margin-bottom: 32px;
  max-width: 400px;
}

/* Pagination */
.pagination-wrapper {
  margin-top: 60px;
  display: flex;
  justify-content: center;
  gap: 10px;
}

.page-btn {
  width: 40px;
  height: 40px;
  border: 1px solid var(--border);
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
}

.page-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.page-btn.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

@media (max-width: 1024px) {
  .wishlist-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .wishlist-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  
  .page-title {
    font-size: 24px;
  }
}
</style>
