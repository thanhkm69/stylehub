<script setup>
const props = defineProps({
    labelContent: {
        type: String,
        default: ""
    },
    customId: {
        type: [String, Number],
        default: ""
    }
})

</script>

<template>
    <label :for="customId">{{ labelContent }}</label>
</template>

<style scoped></style>