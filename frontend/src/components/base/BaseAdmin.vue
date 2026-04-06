<script setup>
import CategoriesTable from '../features/categories/CategoriesTable.vue';
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

    <BaseButton @click="emit('open')" customClass="btn-primary" customText="Thêm" />

    <BaseInputText v-model="params.search" customPlaceholderInput="Tìm kiếm" />
    <BaseButton @click="emit('search')" customText="Tìm kiếm" />

    <BaseInputSelect labelContent="Hiển thị" v-model="params.limit" :isDisabled="true" customId="limit"
        :values="limitMap" placeholder="Hiển thị" />

    <BaseInputSelect labelContent="Lọc trạng thái" v-model="params.status" customId="status" :values="filterMap"
        placeholder="Tất cả" />

    <BaseInputSelect labelContent="Sắp xếp" v-model="params.sort" :isDisabled="true" customId="sort" :values="sortMap"
        placeholder="Sắp xếp" />

    <CategoriesTable :loadingData="loadingData" :data="data" @update="update" @destroy="destroy" @show="show" />

    <BasePagination :total="total" :currentPage="currentPage" :totalPages="totalPages"
        @changePage="changePage" />
</template>