<template>
  <div class="blog-page-wrapper bg-light py-5 min-vh-100">
    <div class="container">
      <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-dark mb-3">Tin tức & Sự kiện</h1>
        <p class="lead text-muted mx-auto" style="max-width: 600px;">
          Cập nhật những xu hướng thời trang mới nhất, mẹo phối đồ và tin tức từ StyleHub.
        </p>
      </div>

      <!-- Categories Filter -->
      <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
        <button 
          @click="changeCategory(null)" 
          :class="['btn rounded-pill px-4 py-2 custom-filter-btn', !currentCategory ? 'btn-primary' : 'btn-outline-secondary bg-white']"
        >
          Tất cả
        </button>
        <button 
          v-for="category in blogStore.categories" 
          :key="category.id"
          @click="changeCategory(category.slug)"
          :class="['btn rounded-pill px-4 py-2 custom-filter-btn', currentCategory === category.slug ? 'btn-primary' : 'btn-outline-secondary bg-white']"
        >
          {{ category.name }}
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="blogStore.loading" class="row g-4">
        <div v-for="i in 6" :key="i" class="col-12 col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 placeholder-glow">
            <div class="placeholder bg-secondary" style="height: 200px; width: 100%; border-top-left-radius: 1rem; border-top-right-radius: 1rem;"></div>
            <div class="card-body p-4">
              <span class="placeholder col-4 mb-3"></span>
              <h5 class="card-title placeholder-glow"><span class="placeholder col-8"></span></h5>
              <p class="card-text placeholder-glow">
                <span class="placeholder col-12"></span>
                <span class="placeholder col-10"></span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="blogStore.error" class="text-center py-5">
        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
        <h3 class="h4 mt-3 text-dark">Đã xảy ra lỗi</h3>
        <p class="text-muted">{{ blogStore.error }}</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="blogStore.posts.length === 0" class="text-center py-5 bg-white rounded-4 shadow-sm border border-light">
        <i class="bi bi-journal-x text-muted opacity-50" style="font-size: 4rem;"></i>
        <h3 class="h4 mt-3 text-dark">Chưa có bài viết nào</h3>
        <p class="text-muted">Chúng tôi đang cập nhật thêm nội dung. Vui lòng quay lại sau.</p>
      </div>

      <!-- Posts Grid -->
      <div v-else class="row g-4">
        <div v-for="post in blogStore.posts" :key="post.id" class="col-12 col-md-6 col-lg-4">
          <router-link :to="`/blog/${post.slug}`" class="card h-100 border-0 shadow-sm rounded-4 text-decoration-none blog-card transition-all">
            <div class="position-relative overflow-hidden card-img-wrapper" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
              <img v-if="post.image" :src="post.image" :alt="post.title" class="card-img-top blog-img transition-all" style="height: 240px; object-fit: cover;">
              <div v-else class="bg-light d-flex align-items-center justify-content-center" style="height: 240px;">
                <i class="bi bi-image text-muted opacity-50" style="font-size: 3rem;"></i>
              </div>
              <span class="position-absolute top-0 start-0 m-3 badge bg-white text-primary rounded-pill shadow-sm px-3 py-2 fw-semibold">
                {{ post.category?.name }}
              </span>
            </div>
            <div class="card-body d-flex flex-column p-4">
              <div class="text-muted small mb-2 d-flex align-items-center">
                <i class="bi bi-calendar3 me-2"></i>
                {{ new Date(post.published_at || post.created_at).toLocaleDateString('vi-VN', { year: 'numeric', month: 'long', day: 'numeric' }) }}
              </div>
              <h3 class="h5 card-title fw-bold text-dark text-truncate-2 mb-3 blog-title transition-all">{{ post.title }}</h3>
              <p class="card-text text-muted text-truncate-3 small flex-grow-1">{{ post.summary }}</p>
              <div class="text-primary fw-semibold small mt-3 d-flex align-items-center read-more transition-all">
                Đọc tiếp <i class="bi bi-arrow-right ms-2"></i>
              </div>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="blogStore.pagination.lastPage > 1" class="mt-5 d-flex justify-content-center">
        <nav aria-label="Page navigation">
          <ul class="pagination gap-2">
            <li class="page-item" :class="{ disabled: blogStore.pagination.currentPage === 1 }">
              <button class="page-link rounded-circle border-0 text-dark pagination-btn shadow-sm" @click="changePage(blogStore.pagination.currentPage - 1)">
                <i class="bi bi-chevron-left"></i>
              </button>
            </li>
            
            <li class="page-item" v-for="page in blogStore.pagination.lastPage" :key="page">
              <button 
                class="page-link rounded-circle border-0 pagination-btn shadow-sm fw-medium" 
                :class="blogStore.pagination.currentPage === page ? 'bg-primary text-white' : 'text-dark bg-white'"
                @click="changePage(page)"
              >
                {{ page }}
              </button>
            </li>
            
            <li class="page-item" :class="{ disabled: blogStore.pagination.currentPage === blogStore.pagination.lastPage }">
              <button class="page-link rounded-circle border-0 text-dark pagination-btn shadow-sm" @click="changePage(blogStore.pagination.currentPage + 1)">
                <i class="bi bi-chevron-right"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useBlogPublicStore } from '@/stores/blogPublic';
import { useHead } from '@unhead/vue';
import { useRoute, useRouter } from 'vue-router';

const blogStore = useBlogPublicStore();
const route = useRoute();
const router = useRouter();

const currentCategory = ref(route.query.category || null);
const currentPage = ref(Number(route.query.page) || 1);

useHead({
  title: 'Tin tức thời trang - StyleHub',
  meta: [
    { name: 'description', content: 'Khám phá những xu hướng thời trang mới nhất, mẹo phối đồ và tin tức từ StyleHub.' },
    { property: 'og:title', content: 'Tin tức thời trang - StyleHub' },
    { property: 'og:description', content: 'Khám phá những xu hướng thời trang mới nhất, mẹo phối đồ và tin tức từ StyleHub.' },
    { property: 'og:type', content: 'website' }
  ]
});

const loadData = async () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
  await blogStore.fetchPosts(currentPage.value, currentCategory.value);
};

onMounted(async () => {
  await blogStore.fetchCategories();
  await loadData();
});

const changeCategory = (slug) => {
  currentCategory.value = slug;
  currentPage.value = 1;
  updateUrl();
  loadData();
};

const changePage = (page) => {
  if (page < 1 || page > blogStore.pagination.lastPage) return;
  currentPage.value = page;
  updateUrl();
  loadData();
};

const updateUrl = () => {
  const query = {};
  if (currentCategory.value) query.category = currentCategory.value;
  if (currentPage.value > 1) query.page = currentPage.value;
  
  router.push({ query });
};
</script>

<style scoped>
.transition-all {
  transition: all 0.3s ease;
}

.blog-card {
  border: 1px solid transparent !important;
}

.blog-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important;
  border-color: rgba(var(--bs-primary-rgb), 0.2) !important;
}

.card-img-wrapper {
  overflow: hidden;
}

.blog-img {
  transition: transform 0.5s ease;
}

.blog-card:hover .blog-img {
  transform: scale(1.05);
}

.blog-title {
  transition: color 0.2s;
}

.blog-card:hover .blog-title {
  color: var(--bs-primary) !important;
}

.read-more {
  transform: translateX(0);
}

.blog-card:hover .read-more {
  transform: translateX(4px);
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.text-truncate-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.custom-filter-btn {
  font-size: 0.875rem;
  font-weight: 500;
}

.custom-filter-btn.btn-outline-secondary {
  border-color: #dee2e6;
  color: #6c757d;
}

.custom-filter-btn.btn-outline-secondary:hover {
  background-color: #f8f9fa !important;
  color: #495057;
}

.pagination-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.page-item.disabled .pagination-btn {
  background-color: #f8f9fa !important;
  color: #adb5bd !important;
}
</style>
