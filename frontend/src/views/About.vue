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
</style>
