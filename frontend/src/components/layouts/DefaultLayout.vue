<script setup>
import Header from '@/components/partials/Header.vue';
import Footer from '@/components/partials/Footer.vue';
</script>
<template>
    <Header />
    <slot></slot>
    <RouterView />
    <Footer />
</template>
<style scoped>
</style>