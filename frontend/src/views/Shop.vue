<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductPublicStore } from '@/stores/productPublic'
import ProductCard from '@/components/features/products/ProductCard.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const store = useProductPublicStore()
const route = useRoute()
const router = useRouter()

// ── Filter State ──
const search = ref('')
const categoryId = ref('')
const minPrice = ref('')
const maxPrice = ref('')
const sort = ref('created_at_desc')
const currentPage = ref(1)

// ── Init from URL query ──
const syncFromQuery = () => {
  const q = route.query
  search.value = q.search || ''
  categoryId.value = q.category_id || ''
  minPrice.value = q.min_price || ''
  maxPrice.value = q.max_price || ''
  sort.value = q.sort || 'created_at_desc'
  currentPage.value = parseInt(q.page) || 1
}

// ── Build params & fetch ──
const buildParams = () => {
  const params = { page: currentPage.value }
  if (search.value) params.search = search.value
  if (categoryId.value) params.category_id = categoryId.value
  if (minPrice.value) params.min_price = minPrice.value
  if (maxPrice.value) params.max_price = maxPrice.value
  if (sort.value !== 'created_at_desc') params.sort = sort.value
  return params
}

const loadProducts = async () => {
  const params = buildParams()
  router.replace({ query: params })
  await store.index(params)
}

// ── Event Handlers ──
const handleSearch = () => {
  currentPage.value = 1
  loadProducts()
}

const handleFilter = () => {
  currentPage.value = 1
  loadProducts()
}

const handleSort = () => {
  currentPage.value = 1
  loadProducts()
}

const handlePageChange = (page) => {
  currentPage.value = page
  loadProducts()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const clearFilters = () => {
  search.value = ''
  categoryId.value = ''
  minPrice.value = ''
  maxPrice.value = ''
  sort.value = 'created_at_desc'
  currentPage.value = 1
  loadProducts()
}

// Fetch categories for filter sidebar
const categories = ref([])
const fetchCategories = async () => {
  try {
    const res = await store.home()
    if (res?.data?.categories) {
      categories.value = res.data.categories
    }
  } catch {
    // silent — categories filter is optional
  }
}

// ── Lifecycle ──
onMounted(() => {
  syncFromQuery()
  loadProducts()
  if (!store.homeData.categories?.length) {
    fetchCategories()
  } else {
    categories.value = store.homeData.categories
  }
})

// Watch for category_id changes via router (e.g. from Home page category click)
watch(() => route.query.category_id, (newVal) => {
  if (newVal && newVal !== categoryId.value) {
    categoryId.value = newVal
    currentPage.value = 1
    loadProducts()
  }
})

const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price) + '₫'

// ── Computed pagination ──
const visiblePages = () => {
  const total = store.pagination.last_page
  const current = store.pagination.current_page
  const pages = []
  const delta = 2
  const left = Math.max(2, current - delta)
  const right = Math.min(total - 1, current + delta)

  pages.push(1)
  if (left > 2) pages.push('...')
  for (let i = left; i <= right; i++) pages.push(i)
  if (right < total - 1) pages.push('...')
  if (total > 1) pages.push(total)
  return pages
}
</script>

<template>
  <main class="shop-page">
    <!-- Page Header -->
    <section class="shop-header container">
      <div class="shop-header-inner">
        <h1>Cửa hàng</h1>
        <p>Khám phá bộ sưu tập đa dạng của chúng tôi</p>
      </div>
    </section>

    <section class="shop-body container">
      <!-- Sidebar Filters -->
      <aside class="shop-sidebar">
        <!-- Search -->
        <div class="filter-group">
          <label class="filter-label">Tìm kiếm</label>
          <div class="search-input-wrapper">
            <i class="ph ph-magnifying-glass"></i>
            <input
              v-model="search"
              type="text"
              placeholder="Tên sản phẩm..."
              class="filter-input"
              @keyup.enter="handleSearch"
            />
          </div>
        </div>

        <!-- Category Filter -->
        <div v-if="categories.length" class="filter-group">
          <label class="filter-label">Danh mục</label>
          <div class="category-filter-list">
            <button
              :class="['category-filter-item', { active: !categoryId }]"
              @click="categoryId = ''; handleFilter()"
            >
              Tất cả
            </button>
            <button
              v-for="cat in categories"
              :key="cat.id"
              :class="['category-filter-item', { active: categoryId == cat.id }]"
              @click="categoryId = cat.id; handleFilter()"
            >
              {{ cat.name }}
            </button>
          </div>
        </div>

        <!-- Price Range -->
        <div class="filter-group">
          <label class="filter-label">Khoảng giá</label>
          <div class="price-range">
            <input v-model="minPrice" type="number" placeholder="Từ" class="filter-input price-input" min="0" />
            <span class="price-separator">—</span>
            <input v-model="maxPrice" type="number" placeholder="Đến" class="filter-input price-input" min="0" />
          </div>
          <button class="btn-apply-price" @click="handleFilter">Áp dụng</button>
        </div>

        <!-- Clear All -->
        <button class="btn-clear-filters" @click="clearFilters">
          <i class="ph ph-arrow-counter-clockwise"></i> Xóa bộ lọc
        </button>
      </aside>

      <!-- Main Content -->
      <div class="shop-content">
        <!-- Toolbar -->
        <div class="shop-toolbar">
          <p class="result-count">
            <span v-if="store.pagination.total">{{ store.pagination.total }} sản phẩm</span>
            <span v-else>Không tìm thấy sản phẩm</span>
          </p>
          <div class="sort-wrapper">
            <label>Sắp xếp:</label>
            <select v-model="sort" class="sort-select" @change="handleSort">
              <option value="created_at_desc">Mới nhất</option>
              <option value="price_asc">Giá tăng dần</option>
              <option value="price_desc">Giá giảm dần</option>
            </select>
          </div>
        </div>

        <!-- Products Grid -->
        <BaseLoading v-if="store.loading" text="Đang tải sản phẩm..." />

        <div v-else-if="store.products?.length" class="product-grid">
          <ProductCard v-for="product in store.products" :key="product.id" :product="product" />
        </div>

        <div v-else class="empty-state">
          <i class="ph ph-magnifying-glass"></i>
          <h3>Không tìm thấy sản phẩm</h3>
          <p>Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm.</p>
          <button class="btn btn-primary" @click="clearFilters" style="margin-top: 16px;">Xóa bộ lọc</button>
        </div>

        <!-- Pagination -->
        <div v-if="store.pagination.last_page > 1 && !store.loading" class="pagination">
          <button
            class="page-btn nav-btn"
            :disabled="store.pagination.current_page === 1"
            @click="handlePageChange(store.pagination.current_page - 1)"
          >
            <i class="ph ph-caret-left"></i> Trước
          </button>

          <div class="page-numbers">
            <template v-for="page in visiblePages()" :key="page">
              <span v-if="page === '...'" class="page-dots">...</span>
              <button
                v-else
                :class="['page-btn', { active: page === store.pagination.current_page }]"
                @click="handlePageChange(page)"
              >
                {{ page }}
              </button>
            </template>
          </div>

          <button
            class="page-btn nav-btn"
            :disabled="store.pagination.current_page === store.pagination.last_page"
            @click="handlePageChange(store.pagination.current_page + 1)"
          >
            Sau <i class="ph ph-caret-right"></i>
          </button>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ── Page Header ── */
.shop-header {
  padding: 48px 0 0;
  animation: slideUp 0.6s ease-out forwards;
}

.shop-header-inner {
  background: var(--accent);
  border-radius: var(--radius-lg);
  padding: 48px 40px;
  text-align: center;
}

.shop-header-inner h1 {
  font-size: 40px;
  font-weight: 700;
  letter-spacing: -1px;
  margin-bottom: 8px;
}

.shop-header-inner p {
  color: var(--text-muted);
  font-size: 16px;
}

/* ── Layout ── */
.shop-body {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 40px;
  padding-top: 40px;
  padding-bottom: 80px;
}

/* ── Sidebar ── */
.shop-sidebar {
  position: sticky;
  top: 100px;
  align-self: start;
  display: flex;
  flex-direction: column;
  gap: 28px;
  animation: slideUp 0.6s ease-out 0.2s both;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.filter-label {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-muted);
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input-wrapper i {
  position: absolute;
  left: 12px;
  color: var(--text-muted);
  font-size: 16px;
}

.search-input-wrapper .filter-input {
  padding-left: 36px;
}

.filter-input {
  width: 100%;
  padding: 10px 14px;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  font-size: 14px;
  background: var(--surface);
  transition: var(--transition);
  color: var(--text-main);
  outline: none;
}

.filter-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
}

/* Category filter */
.category-filter-list {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.category-filter-item {
  text-align: left;
  padding: 8px 14px;
  border-radius: var(--radius-md);
  font-size: 14px;
  font-weight: 500;
  background: transparent;
  border: none;
  color: var(--text-main);
  cursor: pointer;
  transition: var(--transition);
}

.category-filter-item:hover {
  background: var(--accent);
}

.category-filter-item.active {
  background: var(--primary);
  color: #fff;
  font-weight: 600;
}

/* Price range */
.price-range {
  display: flex;
  align-items: center;
  gap: 8px;
}

.price-input {
  flex: 1;
  min-width: 0;
}

.price-separator {
  color: var(--text-muted);
  font-weight: 500;
}

.btn-apply-price {
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition);
}

.btn-apply-price:hover {
  background: var(--primary-hover);
}

.btn-clear-filters {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 10px 14px;
  font-size: 14px;
  font-weight: 500;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  color: var(--text-muted);
  cursor: pointer;
  transition: var(--transition);
}

.btn-clear-filters:hover {
  border-color: var(--text-muted);
  color: var(--text-main);
}

/* ── Content Area ── */
.shop-content {
  animation: slideUp 0.6s ease-out 0.4s both;
}

.shop-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.result-count {
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 500;
}

.sort-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: var(--text-muted);
}

.sort-select {
  padding: 8px 14px;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  font-size: 14px;
  background: var(--surface);
  color: var(--text-main);
  outline: none;
  cursor: pointer;
  transition: var(--transition);
}

.sort-select:focus {
  border-color: var(--primary);
}

/* ── Product Grid ── */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}

/* ── Empty State ── */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 56px;
  margin-bottom: 16px;
  display: block;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 20px;
  font-weight: 600;
  color: var(--text-main);
  margin-bottom: 8px;
}

/* ── Pagination ── */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 48px;
}

.page-numbers {
  display: flex;
  align-items: center;
  gap: 4px;
}

.page-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 40px;
  padding: 0 14px;
  border-radius: var(--radius-md);
  font-size: 14px;
  font-weight: 500;
  background: var(--surface);
  color: var(--text-main);
  border: 1px solid var(--border);
  cursor: pointer;
  transition: var(--transition);
  gap: 6px;
}

.page-btn:hover:not(:disabled):not(.active) {
  background: var(--accent);
  border-color: var(--text-muted);
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-btn.active {
  background: var(--primary);
  color: #fff;
  border-color: var(--primary);
}

.page-dots {
  padding: 0 4px;
  color: var(--text-muted);
}

/* ── Responsive ── */
@media (max-width: 992px) {
  .shop-body {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  .shop-sidebar {
    position: static;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 16px;
  }
  .filter-group {
    min-width: 200px;
    flex: 1;
  }
  .category-filter-list {
    flex-direction: row;
    flex-wrap: wrap;
  }
}

@media (max-width: 768px) {
  .shop-header-inner h1 {
    font-size: 28px;
  }
  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 16px;
  }
  .shop-toolbar {
    flex-direction: column;
    gap: 12px;
    align-items: flex-start;
  }
}
</style>