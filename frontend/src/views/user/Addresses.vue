<script setup>
import AddressList from '@/components/features/addresses/AddressList.vue';
</script>

<template>
    <div class="user-addresses-page">
        <div class="profile-section-header mb-4">
            <h4 class="fw-bold mb-1">Địa chỉ của tôi</h4>
            <p class="text-muted small mb-0">Quản lý địa chỉ giao hàng để đặt hàng nhanh chóng hơn.</p>
        </div>
        
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <AddressList />
        </div>
    </div>
</template>

<style scoped>
.user-addresses-page {
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
