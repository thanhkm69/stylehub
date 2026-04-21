<script setup>
import BaseButton from './BaseButton.vue';
const props = defineProps({
    total: {
        type: Number,
        default: 0
    },
    currentPage: {
        type: Number,
        default: 1
    },
    totalPages: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(["changePage"])

const changePage = (page) => {
    if (page < 1 || page > props.totalPages) return
    emit("changePage", page)
}
</script>

<template>

    <BaseButton :disabled="currentPage === 1" @click="changePage(currentPage - 1)" customText="Trước" />

    <BaseButton v-for="page in totalPages" :disabled="page === currentPage" :key="page" @click="changePage(page)"
        :class="{ active: page === currentPage }" :customText="page" />

    <BaseButton :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)" customText="Sau" />

</template>

<style scoped>
.active {
    background-color: #000;
    color: #fff;
}
</style>