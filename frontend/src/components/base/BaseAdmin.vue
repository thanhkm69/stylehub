<script setup>
import BaseButton from './BaseButton.vue';
import BaseInputText from './BaseInputText.vue';
import BaseInputSelect from './BaseInputSelect.vue';
import BasePagination from './BasePagination.vue';

const props = defineProps({
    loadingData: Boolean,
    sortMap: Array,
    filterMap: Array,
    limitMap: Array,
    data: Array,
    total: Number,
    totalPages: Number,
    currentPage: Number,
    hideOpenBtn: {
        type: Boolean,
        default: false
    },
    title: String,
    description: String
})

const emit = defineEmits(["open", "update", "destroy", "show", "search", "changePage"])

const params = defineModel('params')

const update = (item) => {
    emit('update', item)
}
const destroy = (id) => {
    emit('destroy', id)
}
const show = (item) => {
    emit('show', item)
}
const changePage = (page) => {
    emit('changePage', page)
}

</script>

<template>
    <div class="admin-page">
        <!-- Header -->
        <div class="admin-header" v-if="title || description">
            <h1 class="admin-title" v-if="title">{{ title }}</h1>
            <p class="admin-desc" v-if="description">{{ description }}</p>
        </div>

        <div class="admin-card">
            <!-- Toolbar -->
            <div class="admin-toolbar">
                <!-- Trái: search + tìm -->
                <BaseInputText v-model="params.search" customPlaceholderInput="Tìm kiếm..." />
                <BaseButton @click="emit('search')" customText="Tìm" customClass="btn btn-primary admin-btn" />

                <div class="toolbar-spacer"></div>

                <!-- Phải: filters slot + limit + sort + thêm mới -->
                <slot name="filters"></slot>

                <BaseInputSelect v-if="limitMap" v-model="params.limit" customId="limit" :values="limitMap"
                    placeholder="Số lượng" />
                <BaseInputSelect v-if="filterMap" v-model="params.status" customId="status" :values="filterMap"
                    placeholder="Tất cả trạng thái" />
                <BaseInputSelect v-if="sortMap" v-model="params.sort" customId="sort" :values="sortMap"
                    placeholder="Sắp xếp" />
                <BaseButton v-if="!hideOpenBtn" @click="emit('open')" customClass="btn btn-primary admin-btn"
                    customText="+ Thêm mới" />
            </div>

            <!-- Table Container -->
            <div class="table-responsive">
                <slot name="table"></slot>
            </div>

            <!-- Pagination -->
            <div class="admin-pagination">
                <BasePagination :total="total" :currentPage="currentPage" :totalPages="totalPages"
                    @changePage="changePage" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.admin-page {
    padding: 24px 0;
}

.admin-header {
    margin-bottom: 24px;
}

.admin-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.admin-desc {
    color: var(--text-muted);
    font-size: 15px;
}

.admin-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    padding: 24px;
}

.admin-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 12px;
    margin-bottom: 24px;
}

.toolbar-spacer {
    flex: 1;
}

:deep(.auth-form-group) {
    margin-bottom: 0;
}

:deep(.auth-form-group label) {
    display: none;
}

:deep(.admin-toolbar .auth-input) {
    padding: 10px 16px;
}

:deep(.toolbar-search .auth-input) {
    width: 220px;
}

:deep(.admin-toolbar .auth-select .auth-input) {
    padding: 10px 36px 10px 16px;
    min-width: 150px;
}

:deep(.admin-btn) {
    padding: 10px 20px;
    font-size: 14px;
}

.table-responsive {
    overflow-x: auto;
    margin-bottom: 24px;
}

.admin-pagination {
    display: flex;
    justify-content: flex-end;
}

@media (max-width: 992px) {
    .admin-toolbar {
        flex-wrap: wrap;
    }

    .toolbar-spacer {
        display: none;
    }

    :deep(.admin-toolbar .auth-input) {
        flex: 1;
        width: 100%;
    }
}
</style>