<script setup>
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

    <button :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
        Trước
    </button>

    <button v-for="page in totalPages" :disabled="page === currentPage" :key="page" @click="changePage(page)"
        :class="{ active: page === currentPage }">
        {{ page }}
    </button>

    <button :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
        Sau
    </button>

</template>

<style scoped></style>