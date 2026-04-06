<script setup>
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
    <tr v-for="(row, index) in data" :key="index">
        <td v-for="col in columns" :key="col.key">
            <span v-if="col.key === 'stt'">
                {{ index + 1 }}
            </span>

            <span v-else>
                {{ row[col.key] ?? '-' }}
            </span>
        </td>
    </tr>
</template>