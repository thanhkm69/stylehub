<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const token = route.query.token;

</script>
<template>
   <h1>Đây là trang chủ</h1>
   <a href="http://localhost:8000/api/github">Github</a>
   <br>
   <a href="http://localhost:8000/api/google">Google</a>
   <br>
   <a href="http://localhost:8000/api/facebook">Facebook</a>

   <p>{{ token }}</p>
</template>
<style scoped></style>