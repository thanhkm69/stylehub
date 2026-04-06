<script setup>
const props = defineProps({
    preview: String
})
</script>
<template>
    <img :src="preview" class="image-preview" />
</template>

<style scoped>
.image-preview {
    width: 50px;
    height: 50px;
    object-fit: cover;
}
</style>