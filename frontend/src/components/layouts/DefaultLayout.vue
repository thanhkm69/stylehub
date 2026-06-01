<script setup>
import Header from '@/components/partials/Header.vue';
import Footer from '@/components/partials/Footer.vue';
import CustomerChatWidget from '@/components/chat/CustomerChatWidget.vue';
</script>
<template>
    <Header />
    <slot></slot>
    <RouterView />
    <Footer />
    <CustomerChatWidget />
</template>
<style scoped>
</style>