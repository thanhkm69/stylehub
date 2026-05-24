<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { useProductPublicStore } from '@/stores/productPublic'
import { API_URL_IMAGE } from '@/config/env'
import ProductCard from '@/components/features/products/ProductCard.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import HomeCoupons from '@/components/home/HomeCoupons.vue'

const store = useProductPublicStore()
const carouselRef = ref(null)
let slideInterval = null

const scrollCarousel = (direction) => {
  if (carouselRef.value) {
    const scrollAmount = carouselRef.value.clientWidth + 20 // 20px is the gap
    const currentScroll = carouselRef.value.scrollLeft
    const maxScroll = carouselRef.value.scrollWidth - carouselRef.value.clientWidth

    let targetScroll = 0
    if (direction === 'next') {
      if (currentScroll >= maxScroll - 10) {
        targetScroll = 0
      } else {
        targetScroll = currentScroll + scrollAmount
      }
    } else {
      if (currentScroll <= 10) {
        targetScroll = maxScroll
      } else {
        targetScroll = currentScroll - scrollAmount
      }
    }

    carouselRef.value.scrollTo({
      left: targetScroll,
      behavior: 'smooth'
    })
  }
}

const startAutoSlide = () => {
  slideInterval = setInterval(() => {
    scrollCarousel('next')
  }, 4000)
}

const stopAutoSlide = () => {
  if (slideInterval) clearInterval(slideInterval)
}

onMounted(async () => {
  await store.home()
  if (store.homeData.banners?.length > 1) {
    startAutoSlide()
  }
})

onUnmounted(() => {
  stopAutoSlide()
})
</script>

<template>
  <main>
    <!-- Hero Section -->
    <section class="hero container relative group" @mouseenter="stopAutoSlide" @mouseleave="startAutoSlide">
      <div v-if="store.homeData.banners?.length" class="banner-carousel" ref="carouselRef">
        <a :href="banner.link || '#'" :target="banner.link?.startsWith('http') ? '_blank' : '_self'" class="hero-inner" v-for="banner in store.homeData.banners" :key="banner.id">
          <img :src="`${API_URL_IMAGE}/${banner.image}`" :alt="banner.title || 'Banner'" class="hero-image-full">
        </a>
      </div>
      
      <!-- Next/Prev Buttons -->
      <button v-if="store.homeData.banners?.length > 1" @click="scrollCarousel('prev')" class="carousel-btn prev-btn">
        <i class="ph ph-caret-left"></i>
      </button>
      <button v-if="store.homeData.banners?.length > 1" @click="scrollCarousel('next')" class="carousel-btn next-btn">
        <i class="ph ph-caret-right"></i>
      </button>

      <!-- Fallback -->
      <div v-if="!store.homeData.banners?.length" class="hero-inner">
        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=2070&auto=format&fit=crop" alt="Thời trang StyleHub" class="hero-image-full">
      </div>
    </section>

    <!-- Featured Coupons -->
    <HomeCoupons />

    <!-- Categories Section -->
    <section v-if="store.homeData.categories?.length" class="categories-section container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Danh mục nổi bật</h2>
          <p style="color: var(--text-muted); margin-top: 8px;">Khám phá theo phong cách yêu thích của bạn.</p>
        </div>
      </div>
      <div class="category-grid">
        <router-link
          v-for="cat in store.homeData.categories"
          :key="cat.id"
          :to="{ name: 'Shop', query: { category_id: cat.id } }"
          class="category-card"
        >
          <div class="category-image-wrapper">
            <img
              :src="cat.image ? `${API_URL_IMAGE}/${cat.image}` : 'https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=500&auto=format&fit=crop'"
              :alt="cat.name"
              class="category-image"
            />
            <div class="category-overlay"></div>
          </div>
          <div class="category-info">
            <h3>{{ cat.name }}</h3>
            <span v-if="cat.children?.length" class="category-count">{{ cat.children.length }} danh mục con</span>
          </div>
        </router-link>
      </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="products-section container">
      <div class="section-header">
        <div>
          <h2 class="section-title">Hàng mới về</h2>
          <p style="color: var(--text-muted); margin-top: 8px;">Những mẫu thiết kế vừa cập bến StyleHub.</p>
        </div>
        <router-link to="/shop" class="view-all">Xem tất cả <i class="ph ph-caret-right"></i></router-link>
      </div>

      <BaseLoading v-if="store.loading" text="Đang tải sản phẩm..." />

      <div v-else-if="store.homeData.new_arrivals?.length" class="product-grid">
        <ProductCard
          v-for="product in store.homeData.new_arrivals"
          :key="product.id"
          :product="product"
          :showBadge="true"
        />
      </div>

      <div v-else-if="!store.loading" class="empty-state">
        <i class="ph ph-package"></i>
        <p>Chưa có sản phẩm nào.</p>
      </div>
    </section>
  </main>
</template>

<style scoped>
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(40px); }
  to { opacity: 1; transform: translateY(0); }
}

.hero {
  padding: 60px 0;
  animation: fadeInUp 0.8s ease-out forwards;
}

.banner-carousel {
  display: flex;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  gap: 20px;
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}
.banner-carousel::-webkit-scrollbar {
  display: none;
}

.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 48px;
  height: 48px;
  background-color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-main);
  font-size: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  border: none;
  z-index: 10;
  opacity: 0.8;
  transition: all 0.3s ease;
}

.carousel-btn:hover {
  background-color: var(--primary);
  color: white;
  opacity: 1;
}

.hero.group:hover .carousel-btn {
  opacity: 1;
}

.prev-btn {
  left: 20px;
}

.next-btn {
  right: 20px;
}

.hero-inner {
  scroll-snap-align: center;
  flex: 0 0 100%;
  border-radius: var(--radius-lg);
  display: block;
  overflow: hidden;
  height: 500px;
  position: relative;
  text-decoration: none;
}

.hero-image-full {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: var(--radius-lg);
  display: block;
}

/* ── Categories ── */
.categories-section {
  padding: 0 0 60px;
  animation: fadeInUp 0.8s ease-out 0.2s both;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 16px;
}

.category-card {
  position: relative;
  border-radius: var(--radius-md);
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  transition: var(--transition);
}

.category-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-hover);
}

.category-image-wrapper {
  position: relative;
  aspect-ratio: 1/1;
  overflow: hidden;
}

.category-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.category-card:hover .category-image {
  transform: scale(1.08);
}

.category-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 60%);
}

.category-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 20px;
  color: #fff;
}

.category-info h3 {
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 2px;
}

.category-count {
  font-size: 13px;
  opacity: 0.85;
}

/* ── Products ── */
.products-section {
  padding: 0 0 80px;
  animation: fadeInUp 0.8s ease-out 0.4s both;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 24px;
}
.product-name {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
    display: -webkit-box;
    /* -webkit-line-clamp: 1; */
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 12px;
  display: block;
}

/* ── Responsive ── */
@media (max-width: 992px) {
  .hero-inner {
    height: 400px;
  }
  .hero-image-full {
    border-radius: var(--radius-md);
  }
  .category-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .hero-inner {
    height: 300px;
  }
  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
  }
}

@media (max-width: 576px) {
  .category-grid {
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
}
</style>
