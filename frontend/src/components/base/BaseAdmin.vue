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
        <div class="admin-card">
            <!-- Toolbar -->
            <div class="admin-toolbar">
                <div class="toolbar-left">
                    <BaseInputText v-model="params.search" customPlaceholderInput="Tìm kiếm..." />
                    <BaseButton @click="emit('search')" customText="Tìm" customClass="btn btn-primary admin-btn" />
                </div>
                <div class="toolbar-right">
                    <BaseInputSelect v-model="params.limit" :isDisabled="true" customId="limit"
                        :values="limitMap" placeholder="Số lượng" />
                    <BaseInputSelect v-model="params.status" customId="status" :values="filterMap"
                        placeholder="Tất cả trạng thái" />
                    <BaseInputSelect v-model="params.sort" :isDisabled="true" customId="sort" :values="sortMap"
                        placeholder="Sắp xếp" />
                    <BaseButton @click="emit('open')" customClass="btn btn-primary admin-btn" customText="+ Thêm mới" />
                </div>
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

.admin-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    padding: 24px;
}

.admin-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}

.toolbar-left, .toolbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Override Base Inputs inside Toolbar to be compact */
:deep(.toolbar-left .auth-form-group),
:deep(.toolbar-right .auth-form-group) {
    margin-bottom: 0;
}

:deep(.toolbar-left label),
:deep(.toolbar-right label) {
    display: none;
}

:deep(.toolbar-left .auth-input) {
    width: 250px;
    padding: 10px 16px;
}

:deep(.toolbar-right .auth-input) {
    padding: 10px 36px 10px 16px;
    min-width: 160px;
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
        flex-direction: column;
        align-items: stretch;
    }
    .toolbar-left, .toolbar-right {
        flex-wrap: wrap;
    }
    :deep(.toolbar-left .auth-input) {
        flex: 1;
        width: 100%;
    }
}
</style>