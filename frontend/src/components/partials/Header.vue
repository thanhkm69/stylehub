<script setup>
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { useTokenStore } from '@/stores/token';
import { useNotify } from '@/composables/useNotify';
const tokenStore = useTokenStore()
const toast = useNotify()

const isAuth = computed(() => tokenStore.token)
const user = computed(() => tokenStore.user)

const loading = ref(false)

const logout = async () => {
    loading.value = true
    const result = await tokenStore.logout()
    if (result.success === true) {
        toast.success(result.message)
        localStorage.removeItem('token')
    } else {
        toast.error(result?.message || "Lỗi khi đăng xuất")
    }
    loading.value = false
}

onMounted(async () => {
    await tokenStore.getUser()
})

</script>
<template>
    <header>
        Header
        <div v-if="!isAuth">
            <RouterLink to="/login">Đăng nhập</RouterLink>
            <RouterLink to="/register">Đăng ký</RouterLink>
        </div>
        <div v-else>
            <p>{{ user?.data?.name }}</p>
            <BaseButton v-if="!loading" @click="logout" customText="Đăng xuất" customClass="btn-primary" />
            <BaseSpinner v-else />
        </div>
    </header>
</template>
<style scoped></style>