<template>
  <div class="blog-detail-wrapper min-vh-100">
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
    <article v-else class="article-shell h-entry">
      <a :href="canonicalUrl" class="u-url visually-hidden" tabindex="-1" aria-hidden="true">{{ post.title }}</a>
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="article-breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><router-link to="/" class="text-decoration-none text-muted breadcrumb-link">Trang chủ</router-link></li>
          <li class="breadcrumb-item"><router-link to="/blog" class="text-decoration-none text-muted breadcrumb-link">Tin tức</router-link></li>
          <li class="breadcrumb-item active" aria-current="page">
            <router-link :to="`/blog?category=${post.category?.slug}`" class="text-decoration-none text-muted breadcrumb-link">{{ post.category?.name }}</router-link>
          </li>
        </ol>
      </nav>

      <section class="article-hero">
        <header class="article-header">
          <router-link :to="`/blog?category=${post.category?.slug}`" class="category-chip text-decoration-none">
            {{ post.category?.name }}
          </router-link>
          <h1 class="article-title p-name">{{ post.title }}</h1>
          <p v-if="post.summary" class="article-summary">{{ post.summary }}</p>
          <div class="article-meta">
            <time class="dt-published" :datetime="post.published_at || post.created_at">
              <i class="bi bi-calendar3"></i> {{ formatDate(post.published_at || post.created_at) }}
            </time>
            <span class="meta-dot"></span>
            <span class="p-author h-card"><i class="bi bi-person"></i> Quản trị viên</span>
          </div>
        </header>

        <figure v-if="post.image" class="article-cover">
          <img :src="post.image" :alt="post.title" class="article-hero-img u-photo">
        </figure>
      </section>

      <div class="reading-layout">
        <aside class="reading-aside">
          <span class="reading-aside-label">StyleHub Journal</span>
          <p>Mẹo phối đồ thanh lịch và ứng dụng cho phong cách công sở hiện đại.</p>
        </aside>

        <div class="article-body">
          <div class="article-content text-dark e-content" v-html="articleContent"></div>
        </div>
      </div>

      <!-- Share -->
      <div class="article-share">
        <span class="share-label">Chia sẻ bài viết</span>
        <div class="share-actions">
          <button @click="shareOn('facebook')" class="share-btn share-facebook"
            title="Đăng nhập Facebook để chia sẻ" aria-label="Đăng nhập Facebook để chia sẻ">
            <svg class="share-icon" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M13.5 22v-8h2.75l.5-3h-3.25V9.1c0-.87.3-1.46 1.63-1.46h1.74V4.96c-.3-.04-1.33-.13-2.53-.13-2.5 0-4.21 1.52-4.21 4.32V11H8.3v3h2.83v8h2.37Z" />
            </svg>
          </button>
          <button @click="shareOn('twitter')" class="share-btn share-twitter" aria-label="Chia sẻ X">
            <svg class="share-icon share-x-icon" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M18.9 2h3.7l-8.1 9.25L24 22h-7.43l-5.82-7.52L4.17 22H.46l8.66-9.9L0 2h7.62l5.26 6.95L18.9 2Zm-1.3 18h2.05L6.5 3.9H4.3L17.6 20Z" />
            </svg>
          </button>
          <button @click="copyLink" class="share-btn share-copy" title="Sao chép liên kết" aria-label="Sao chép liên kết">
            <svg class="share-icon" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M10.59 13.41a1 1 0 0 1 0-1.41l2.83-2.83a3 3 0 1 1 4.24 4.24l-3.18 3.18a3 3 0 0 1-4.24 0 1 1 0 1 1 1.41-1.41 1 1 0 0 0 1.41 0L16.24 12a1 1 0 0 0-1.41-1.41L12 13.41a1 1 0 0 1-1.41 0Z" />
              <path d="M13.41 10.59a1 1 0 0 1 0 1.41l-2.83 2.83a3 3 0 1 1-4.24-4.24l3.18-3.18a3 3 0 0 1 4.24 0 1 1 0 1 1-1.41 1.41 1 1 0 0 0-1.41 0L7.76 12a1 1 0 0 0 1.41 1.41L12 10.59a1 1 0 0 1 1.41 0Z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Related Posts -->
      <section class="related-section">
        <h2 class="related-heading">
          Bài viết liên quan
        </h2>
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
      </section>

      <PostCommentSection :post-slug="post.slug" />
    </article>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useBlogPublicStore } from '@/stores/blogPublic';
import { useHead } from '@unhead/vue';
import { useToast } from 'vue-toastification';
import PostCommentSection from '@/components/features/postComments/PostCommentSection.vue';

const route = useRoute();
const router = useRouter();
const blogStore = useBlogPublicStore();
const toast = useToast();

const post = computed(() => blogStore.post);
const relatedPosts = computed(() => blogStore.relatedPosts);
const canonicalUrl = computed(() => {
  if (typeof window === 'undefined') {
    return route.path;
  }

  return `${window.location.origin}${route.path}`;
});
const seoTitle = computed(() => post.value?.meta_title || (post.value ? `${post.value.title} | StyleHub` : 'StyleHub'));
const seoDescription = computed(() => {
  return post.value?.meta_description || post.value?.summary || 'Đọc bài viết thời trang mới nhất trên StyleHub.';
});
const articleContent = computed(() => {
  if (!post.value?.content) {
    return '';
  }

  let content = post.value.content;

  if (typeof DOMParser === 'undefined') {
    return content.replace(/<h1\b[^>]*>[\s\S]*?<\/h1>/i, '');
  }

  if (content.includes('&lt;')) {
    const escapedContent = new DOMParser().parseFromString(content, 'text/html');
    content = escapedContent.body.textContent || content;
  }

  const parsedContent = new DOMParser().parseFromString(content, 'text/html');
  parsedContent.body.querySelectorAll('h1').forEach((heading) => heading.remove());
  parsedContent.body.querySelectorAll('p').forEach((paragraph) => {
    const text = paragraph.textContent.replace(/\u00a0/g, ' ').trim();

    if (!text && !paragraph.querySelector('img')) {
      paragraph.remove();
      return;
    }

    if (!text.startsWith('-')) {
      return;
    }

    let list = paragraph.previousElementSibling;
    if (!list || !list.classList.contains('generated-list')) {
      list = parsedContent.createElement('ul');
      list.classList.add('generated-list');
      paragraph.before(list);
    }

    const item = parsedContent.createElement('li');
    item.innerHTML = paragraph.innerHTML.replace(/^(?:&nbsp;|\s)*-(?:&nbsp;|\s)*/i, '');
    list.appendChild(item);
    paragraph.remove();
  });

  return parsedContent.body.innerHTML;
});
const structuredData = computed(() => ({
  '@context': 'https://schema.org',
  '@type': 'BlogPosting',
  headline: post.value?.title || '',
  description: seoDescription.value,
  image: post.value?.image ? [post.value.image] : undefined,
  datePublished: post.value?.published_at || post.value?.created_at || undefined,
  author: {
    '@type': 'Organization',
    name: 'StyleHub',
  },
  publisher: {
    '@type': 'Organization',
    name: 'StyleHub',
  },
  mainEntityOfPage: {
    '@type': 'WebPage',
    '@id': canonicalUrl.value,
  },
}));

useHead({
  title: () => seoTitle.value,
  meta: [
    { name: 'description', content: () => seoDescription.value },
    { name: 'keywords', content: () => post.value?.meta_keywords || '' },
    { property: 'og:title', content: () => seoTitle.value },
    { property: 'og:description', content: () => seoDescription.value },
    { property: 'og:image', content: () => post.value?.image || '' },
    { property: 'og:url', content: () => canonicalUrl.value },
    { property: 'og:type', content: 'article' },
    { property: 'article:published_time', content: () => post.value?.published_at || post.value?.created_at || '' },
    { name: 'twitter:card', content: 'summary_large_image' },
    { name: 'twitter:title', content: () => seoTitle.value },
    { name: 'twitter:description', content: () => seoDescription.value },
    { name: 'twitter:image', content: () => post.value?.image || '' },
  ],
  link: [
    { rel: 'canonical', href: () => canonicalUrl.value },
  ],
  script: [
    {
      type: 'application/ld+json',
      innerHTML: () => JSON.stringify(structuredData.value),
    },
  ],
});

const loadPost = async () => {
  const slug = route.params.slug;
  if (!slug) {
    router.push('/blog');
    return;
  }
  
  await blogStore.fetchPost(slug);
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
    const facebookShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
    shareUrl = `https://www.facebook.com/login.php?next=${encodeURIComponent(facebookShareUrl)}`;
  } else if (platform === 'twitter') {
    shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`;
  }

  if (shareUrl) {
    window.open(shareUrl, '_blank', 'width=680,height=680');
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
.blog-detail-wrapper {
  background:
    radial-gradient(circle at 88% 9%, rgba(210, 187, 160, 0.2), transparent 28%),
    #faf9f7;
  color: #161616;
  padding: clamp(1.5rem, 3vw, 2.5rem) 0 5rem;
}

.article-shell {
  max-width: 1320px;
  margin: 0 auto;
  padding: 0 clamp(1.1rem, 3.5vw, 3rem);
}

.transition-all {
  transition: all 0.3s ease;
}

.article-breadcrumb {
  margin: 0 0 clamp(1.5rem, 3vw, 2.4rem);
  font-size: 0.9rem;
}

.breadcrumb-link:hover {
  color: #111 !important;
}

.article-hero {
  align-items: stretch;
  display: grid;
  gap: clamp(2rem, 4vw, 4.5rem);
  grid-template-columns: minmax(360px, 0.94fr) minmax(400px, 0.86fr);
  margin-bottom: clamp(3.25rem, 7vw, 5.75rem);
}

.article-header {
  align-self: center;
  max-width: 620px;
  padding: clamp(1rem, 3vw, 2rem) 0;
  text-align: left;
}

.category-chip {
  display: inline-flex;
  align-items: center;
  background: #111;
  border-radius: 999px;
  color: #fff;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.13em;
  margin-bottom: clamp(1.4rem, 3vw, 2rem);
  padding: 0.65rem 1.15rem;
  text-transform: uppercase;
}

.category-chip:hover {
  background: #383838;
  color: #fff;
}

.article-title {
  color: #111;
  font-size: clamp(2.45rem, 4vw, 4rem);
  font-weight: 700;
  letter-spacing: -0.065em;
  line-height: 1.06;
  margin: 0 0 clamp(1.25rem, 2vw, 1.65rem);
  text-wrap: balance;
}

.article-summary {
  color: #59544d;
  font-size: clamp(1.04rem, 1.25vw, 1.16rem);
  line-height: 1.75;
  margin: 0 0 clamp(1.6rem, 3vw, 2.2rem);
  max-width: 560px;
}

.article-meta {
  align-items: center;
  color: #777;
  display: flex;
  font-size: 0.9rem;
  gap: 0.9rem;
  justify-content: flex-start;
}

.article-meta time,
.article-meta span:not(.meta-dot) {
  align-items: center;
  display: inline-flex;
  gap: 0.45rem;
}

.meta-dot {
  background: #c6bbae;
  border-radius: 50%;
  height: 4px;
  width: 4px;
}

.article-cover {
  background: #efeae4;
  border-radius: 26px;
  box-shadow: 0 22px 56px rgba(36, 28, 19, 0.12);
  height: min(72vh, 720px);
  margin: 0;
  overflow: hidden;
  position: relative;
}

.article-cover::after {
  border: 1px solid rgba(255, 255, 255, 0.38);
  border-radius: 19px;
  content: '';
  inset: 14px;
  pointer-events: none;
  position: absolute;
}

.article-hero-img {
  display: block;
  height: 100%;
  object-fit: cover;
  object-position: center center;
  width: 100%;
}

.reading-layout {
  display: grid;
  gap: 1.15rem;
  grid-template-columns: minmax(0, 1120px);
  justify-content: center;
}

.reading-aside {
  align-items: center;
  background: #f2ede6;
  border-radius: 18px;
  color: #686259;
  display: flex;
  font-size: 0.88rem;
  gap: clamp(1.25rem, 3vw, 2.25rem);
  line-height: 1.65;
  padding: 1.1rem clamp(1.25rem, 3vw, 1.8rem);
}

.reading-aside p {
  border-left: 1px solid #d9cec1;
  margin: 0;
  padding-left: clamp(1.25rem, 3vw, 2.25rem);
}

.reading-aside-label {
  color: #131313;
  display: block;
  flex: 0 0 auto;
  font-size: 0.73rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.article-body {
  background: #fff;
  border: 1px solid #eee8e1;
  border-radius: 24px;
  box-shadow: 0 12px 36px rgba(34, 27, 18, 0.045);
  padding: clamp(2rem, 4.5vw, 4rem) clamp(1.4rem, 4vw, 3.75rem);
}

.article-content {
  color: #282624;
  font-size: 1.04rem;
  line-height: 1.9;
}

.article-content :deep(h2) {
  color: #111;
  font-size: clamp(1.4rem, 2vw, 1.62rem);
  font-weight: 700;
  letter-spacing: -0.025em;
  line-height: 1.35;
  margin: 2.9rem 0 1rem;
}

.article-content :deep(h3) {
  color: #171717;
  font-size: 1.25rem;
  font-weight: 650;
  margin: 2.2rem 0 0.85rem;
}

.article-content :deep(p) {
  margin: 0 0 1.35rem;
}

.article-content :deep(strong) {
  color: #111;
  font-weight: 650;
}

.article-content :deep(ul),
.article-content :deep(ol) {
  background: #faf8f5;
  border-left: 3px solid #161616;
  border-radius: 0 14px 14px 0;
  margin: 1.35rem 0 2rem;
  padding: 1.15rem 1.35rem 1.15rem 2rem;
}

.article-content :deep(li) {
  margin: 0.45rem 0;
}

.article-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 18px;
  margin: 2.3rem 0;
}

.article-share {
  align-items: center;
  background: #fff;
  border: 1px solid #ede7df;
  border-radius: 18px;
  display: flex;
  justify-content: space-between;
  margin: 2rem auto 0;
  max-width: 1120px;
  padding: 0.85rem 1rem 0.85rem 1.4rem;
}

.share-label {
  color: #2b2825;
  font-size: 0.92rem;
  font-weight: 600;
}

.share-actions {
  display: flex;
  gap: 0.45rem;
}

.share-btn {
  align-items: center;
  border: 0;
  border-radius: 50%;
  color: #fff;
  display: inline-flex;
  justify-content: center;
  width: 40px;
  height: 40px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.share-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
}

.share-icon {
  fill: currentColor;
  height: 21px;
  width: 21px;
}

.share-x-icon {
  height: 18px;
  width: 18px;
}

.share-facebook {
  background: #1877f2;
}

.share-twitter {
  background: #111;
}

.share-copy {
  background: #f3efea;
  color: #222;
}

.related-section {
  margin-left: auto;
  margin-right: auto;
  max-width: 1070px;
  margin-top: clamp(3rem, 5vw, 4.5rem);
}

.related-heading {
  color: #121212;
  font-size: clamp(1.5rem, 2vw, 1.85rem);
  font-weight: 700;
  letter-spacing: -0.035em;
  margin: 0 0 1.7rem;
}

.related-card {
  border: 1px solid #ede7df !important;
  overflow: hidden;
}

.related-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important;
  border-color: #dfd4c8 !important;
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
  color: #73553c !important;
}

.text-truncate-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (max-width: 768px) {
  .blog-detail-wrapper {
    padding-top: 1.5rem;
  }

  .article-shell {
    padding: 0 16px;
  }

  .article-hero {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    margin-bottom: 2rem;
  }

  .article-header {
    order: 0;
    padding: 0;
  }

  .article-title {
    font-size: clamp(2rem, 10vw, 2.65rem);
    letter-spacing: -0.045em;
  }

  .article-meta {
    flex-wrap: wrap;
  }

  .article-cover {
    border-radius: 18px;
    height: min(120vw, 560px);
    order: 1;
  }

  .article-cover::after {
    border-radius: 12px;
    inset: 9px;
  }

  .reading-layout {
    display: block;
  }

  .reading-aside {
    align-items: flex-start;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    margin-bottom: 1rem;
  }

  .reading-aside p {
    border: 0;
    padding-left: 0;
  }

  .article-body {
    border-radius: 18px;
  }

  .article-share {
    margin-top: 1.25rem;
  }
}

@media (min-width: 769px) and (max-width: 1100px) {
  .article-hero {
    grid-template-columns: 1fr 0.84fr;
    gap: 2rem;
  }

  .article-cover {
    height: min(62vw, 650px);
  }

  .reading-layout {
    max-width: 1120px;
    margin: 0 auto;
  }
}
</style>
