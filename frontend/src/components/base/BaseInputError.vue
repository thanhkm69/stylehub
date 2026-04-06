<script setup>
const props = defineProps({
    error: {
        type: String,
        default: ""
    }
})

</script>

<template>
    <span v-if="error" class="text-danger">
        {{ error }}
    </span>
</template>

<style scoped>

</style>