<script setup>
import BaseRow from './BaseRow.vue'

const props = defineProps({
    columns: {
        type: Array,
        required: true
    },
    data: {
        type: Array,
        required: true
    }
})
</script>

<template>
    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th v-for="(column, index) in columns" :key="index">
                    {{ column.label }}
                </th>
            </tr>
        </thead>

        <tbody>
            <tr v-if="!data || data.length === 0">
                <td :colspan="columns.length" style="text-align: center;">
                    Không có dữ liệu
                </td>
            </tr>
            <BaseRow v-else :columns="columns" :data="data" />
        </tbody>
    </table>
</template>