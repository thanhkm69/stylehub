<template>
  <div class="blog-detail-wrapper bg-light py-5 min-vh-100">
    <!-- Loading State -->
    <div v-if="blogStore.loading" class="container max-w-800px">
      <div class="placeholder-glow mb-4">
        <span class="placeholder col-8 placeholder-lg rounded"></span>
      </div>
      <div class="placeholder-glow mb-5">
        <span class="placeholder col-3 rounded"></span>
      </div>
      <div class="placeholder-glow mb-5">
        <div class="placeholder w-100 rounded-4" style="height: 400px;"></div>
      </div>
      <div class="placeholder-glow">
        <span class="placeholder col-12 mb-2"></span>
        <span class="placeholder col-12 mb-2"></span>
        <span class="placeholder col-10 mb-2"></span>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="blogStore.error || !post" class="container max-w-800px text-center py-5">
      <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
      <h1 class="h2 fw-bold text-dark mt-4 mb-3">Không tìm thấy bài viết</h1>
      <p class="text-muted mb-5">{{ blogStore.error || 'Bài viết bạn tìm kiếm không tồn tại hoặc đã bị xóa.' }}</p>
      <router-link to="/blog" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-arrow-left me-2"></i> Quay lại trang Tin tức
      </router-link>
    </div>

    <!-- Post Detail -->
    <article v-else class="container max-w-800px">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-5">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted breadcrumb-link">Trang chủ</router-link></li>
          <li class="breadcrumb-item"><router-link to="/blog" class="text-decoration-none text-muted breadcrumb-link">Tin tức</router-link></li>
          <li class="breadcrumb-item active" aria-current="page">
            <router-link :to="`/blog?category=${post.category?.slug}`" class="text-decoration-none text-muted breadcrumb-link">{{ post.category?.name }}</router-link>
          </li>
        </ol>
      </nav>

      <header class="mb-5 text-center">
        <router-link :to="`/blog?category=${post.category?.slug}`" class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-2 text-decoration-none mb-4 hover-badge transition-all">
          {{ post.category?.name }}
        </router-link>
        <h1 class="display-4 fw-bold text-dark mb-4 article-title">{{ post.title }}</h1>
        <div class="d-flex align-items-center justify-content-center text-muted small gap-4">
          <span><i class="bi bi-calendar3 me-2"></i> {{ formatDate(post.published_at || post.created_at) }}</span>
          <span><i class="bi bi-person me-2"></i> Quản trị viên</span>
        </div>
      </header>

      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div v-if="post.image" class="article-hero-img-wrapper">
          <img :src="post.image" :alt="post.title" class="w-100 article-hero-img">
        </div>
        
        <div class="card-body p-4 p-md-5">
          <div class="article-content text-dark" v-html="post.content"></div>
        </div>
      </div>

      <!-- Share -->
      <div class="d-flex align-items-center justify-content-between py-4 border-top border-bottom mb-5">
        <span class="fw-semibold text-dark">Chia sẻ bài viết:</span>
        <div class="d-flex gap-2">
          <button @click="shareOn('facebook')" class="btn btn-primary rounded-circle share-btn d-flex align-items-center justify-content-center" style="background-color: #1877f2; border-color: #1877f2;">
            <i class="bi bi-facebook"></i>
          </button>
          <button @click="shareOn('twitter')" class="btn btn-info text-white rounded-circle share-btn d-flex align-items-center justify-content-center" style="background-color: #1da1f2; border-color: #1da1f2;">
            <i class="bi bi-twitter-x"></i>
          </button>
          <button @click="copyLink" class="btn btn-light rounded-circle share-btn shadow-sm d-flex align-items-center justify-content-center" title="Sao chép liên kết">
            <i class="bi bi-link-45deg text-dark" style="font-size: 1.2rem;"></i>
          </button>
        </div>
      </div>

      <!-- Related Posts -->
      <div class="mt-5 pt-4">
        <h3 class="h3 fw-bold text-dark mb-4 d-flex align-items-center">
          <i class="bi bi-journals me-3 text-primary"></i> Bài viết liên quan
        </h3>
        <div v-if="relatedPosts.length > 0" class="row g-4">
          <div v-for="related in relatedPosts" :key="related.id" class="col-12 col-md-4">
            <router-link :to="`/blog/${related.slug}`" class="card h-100 border-0 shadow-sm rounded-4 text-decoration-none related-card transition-all">
              <div class="position-relative overflow-hidden" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem; aspect-ratio: 16/10;">
                <img v-if="related.image" :src="related.image" :alt="related.title" class="w-100 h-100 object-fit-cover related-img transition-all">
                <div v-else class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                  <i class="bi bi-image text-muted opacity-50" style="font-size: 2rem;"></i>
                </div>
              </div>
              <div class="card-body d-flex flex-column p-3">
                <h4 class="h6 fw-bold text-dark mb-2 text-truncate-2 related-title transition-all">{{ related.title }}</h4>
                <div class="text-muted small mt-auto d-flex align-items-center">
                  <i class="bi bi-calendar3 me-1"></i>
                  {{ formatDate(related.published_at || related.created_at) }}
                </div>
              </div>
            </router-link>
          </div>
        </div>
        <div v-else class="text-center py-4 bg-white rounded-4 border border-light shadow-sm">
          <i class="bi bi-journal-x text-muted opacity-50 mb-2 d-block" style="font-size: 2rem;"></i>
          <p class="text-muted mb-0">Chưa có bài viết liên quan nào.</p>
        </div>
      </div>
    </article>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useBlogPublicStore } from '@/stores/blogPublic';
import { useHead } from '@unhead/vue';
import { useToast } from 'vue-toastification';

const route = useRoute();
const router = useRouter();
const blogStore = useBlogPublicStore();
const toast = useToast();

const post = computed(() => blogStore.post);
const relatedPosts = computed(() => blogStore.relatedPosts);

const loadPost = async () => {
  const slug = route.params.slug;
  if (!slug) {
    router.push('/blog');
    return;
  }
  
  const fetchedPost = await blogStore.fetchPost(slug);
  
  // DYNAMIC SEO IMPLEMENTATION
  if (fetchedPost) {
    useHead({
      title: fetchedPost.meta_title || `${fetchedPost.title} - StyleHub`,
      meta: [
        { name: 'description', content: fetchedPost.meta_description || fetchedPost.summary || 'Đọc bài viết trên StyleHub.' },
        { name: 'keywords', content: fetchedPost.meta_keywords || '' },
        { property: 'og:title', content: fetchedPost.meta_title || fetchedPost.title },
        { property: 'og:description', content: fetchedPost.meta_description || fetchedPost.summary },
        { property: 'og:image', content: fetchedPost.image || 'https://stylehub.com/default-share-image.jpg' },
        { property: 'og:type', content: 'article' },
        { property: 'article:published_time', content: fetchedPost.published_at || fetchedPost.created_at }
      ]
    });
  }
};

onMounted(() => {
  loadPost();
  window.scrollTo(0, 0);
});

// Watch for route changes to reload data (e.g. related posts clicked)
watch(() => route.params.slug, (newSlug) => {
  if (newSlug) {
    loadPost();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', { year: 'numeric', month: 'long', day: 'numeric' });
};

const shareOn = (platform) => {
  const url = window.location.href;
  const text = post.value?.title;
  let shareUrl = '';

  if (platform === 'facebook') {
    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
  } else if (platform === 'twitter') {
    shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
  }

  if (shareUrl) {
    window.open(shareUrl, '_blank', 'width=600,height=400');
  }
};

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href);
    toast.success('Đã sao chép liên kết bài viết!');
  } catch (err) {
    toast.error('Không thể sao chép liên kết');
  }
};
</script>

<style scoped>
.transition-all {
  transition: all 0.3s ease;
}

.breadcrumb-link:hover {
  color: var(--bs-primary) !important;
}

.hover-badge:hover {
  background-color: rgba(var(--bs-primary-rgb), 0.15) !important;
}

.article-title {
  letter-spacing: -1px;
}

.article-hero-img-wrapper {
  max-height: 500px;
  overflow: hidden;
}

.article-hero-img {
  object-fit: cover;
  width: 100%;
  height: 100%;
}

.article-content :deep(h2), .article-content :deep(h3) {
  margin-top: 2rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.article-content :deep(p) {
  line-height: 1.8;
  margin-bottom: 1.5rem;
}

.article-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 1rem;
  margin: 2rem 0;
  box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
}

.share-btn {
  width: 40px;
  height: 40px;
  transition: transform 0.2s;
}

.share-btn:hover {
  transform: translateY(-2px);
}

.related-card {
  border: 1px solid transparent !important;
}

.related-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important;
  border-color: rgba(var(--bs-primary-rgb), 0.2) !important;
}

.related-img {
  transition: transform 0.5s ease;
}

.related-card:hover .related-img {
  transform: scale(1.05);
}

.related-title {
  transition: color 0.2s;
}

.related-card:hover .related-title {
  color: var(--bs-primary) !important;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (max-width: 768px) {
  .article-title {
    font-size: 2.5rem;
  }
}
</style>
