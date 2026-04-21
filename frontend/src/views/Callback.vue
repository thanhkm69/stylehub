<script setup>
import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { swalCallback } from '@/composables/useSwal';
import { useTokenStore } from '@/stores/token';

const route = useRoute()
const router = useRouter()
const tokenStore = useTokenStore()
const token = route.query.token

onMounted(async () => {
    await swalCallback()
    tokenStore.token = token
    router.push({ name: 'Home' })
})
</script>

<template>

</template>


<style scoped></style>