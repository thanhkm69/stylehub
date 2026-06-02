<script setup>
import AboutHero from '@/components/about/AboutHero.vue'
import AboutStory from '@/components/about/AboutStory.vue'
import AboutVisionMission from '@/components/about/AboutVisionMission.vue'
import AboutGallery from '@/components/about/AboutGallery.vue'
import AboutPromise from '@/components/about/AboutPromise.vue'
import AboutCTA from '@/components/about/AboutCTA.vue'
</script>

<template>
  <main class="about-page">
    <AboutHero />
    <AboutStory />
    <AboutVisionMission />
    <AboutGallery />
    <AboutPromise />
    <AboutCTA />
  </main>
</template>

<style scoped>
.about-page {
  overflow: hidden;
}

.about-page :deep(.text-muted) {
  color: var(--text-muted) !important;
}

.about-page :deep(.section-title),
.about-page :deep(h1),
.about-page :deep(h2),
.about-page :deep(h3),
.about-page :deep(h4),
.about-page :deep(h5),
.about-page :deep(h6) {
  color: var(--text-main);
}
</style>
