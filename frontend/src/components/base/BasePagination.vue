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
    <div class="pagination-wrapper">
        <BaseButton :disabled="currentPage === 1" @click="changePage(currentPage - 1)" customText="Trước" customClass="page-btn nav-btn" />

        <div class="page-numbers">
            <BaseButton v-for="page in totalPages" :key="page" :disabled="page === currentPage" @click="changePage(page)"
                :customClass="['page-btn', page === currentPage ? 'active' : '']" :customText="String(page)" />
        </div>

        <BaseButton :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)" customText="Sau" customClass="page-btn nav-btn" />
    </div>
</template>

<style scoped>
.pagination-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-numbers {
    display: flex;
    align-items: center;
    gap: 4px;
}

:deep(.page-btn) {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 12px;
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 500;
    background: var(--surface);
    color: var(--text-main);
    border: 1px solid var(--border);
    cursor: pointer;
    transition: var(--transition);
}

:deep(.page-btn:hover:not(:disabled)) {
    background: var(--background);
    border-color: var(--text-muted);
}

:deep(.page-btn:disabled) {
    opacity: 0.5;
    cursor: not-allowed;
}

:deep(.page-btn.active) {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    opacity: 1;
    cursor: default;
}

:deep(.nav-btn) {
    padding: 0 16px;
}
</style>