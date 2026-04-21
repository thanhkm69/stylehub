<script setup>
import { API_URL } from '@/config/env'
</script>
<template>
   <a :href="`${API_URL}/github`">Github</a>
    <br>
    <a :href="`${API_URL}/google`">Google</a>
    <br>
    <a :href="`${API_URL}/facebook`">Facebook</a>
</template>
<style scoped></style>