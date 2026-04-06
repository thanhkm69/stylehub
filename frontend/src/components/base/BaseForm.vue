<script setup>
const emit = defineEmits(["handleSubmit"])
</script>
<template>
    <form enctype="multipart/form-data" @submit.prevent="emit('handleSubmit')">
        <slot name="input"></slot>
        <slot name="button"></slot>
    </form>
</template>
<style scoped></style>