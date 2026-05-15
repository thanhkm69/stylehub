<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAddressStore } from '@/stores/address'
import AddressItem from './AddressItem.vue'
import AddressForm from './AddressForm.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import BasePagination from '@/components/base/BasePagination.vue'

const addressStore = useAddressStore()

const isShowForm = ref(false)
const selectedAddress = ref(null)

onMounted(() => {
    addressStore.index()
})

const openCreateForm = () => {
    selectedAddress.ref = null
    isShowForm.value = true
}

const openEditForm = (address) => {
    selectedAddress.value = { ...address }
    isShowForm.value = true
}

const handleSuccess = () => {
    isShowForm.value = false
    selectedAddress.value = null
}

const changePage = (page) => {
    addressStore.index(page)
}
</script>

<template>
    <div class="address-list-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-bold">Sổ địa chỉ</h5>
            <button class="btn btn-primary d-flex align-items-center gap-2" @click="openCreateForm">
                <i class="ph ph-plus-circle"></i> Thêm địa chỉ mới
            </button>
        </div>

        <div v-if="addressStore.loading && addressStore.addresses.length === 0" class="py-5">
            <BaseLoading text="Đang tải danh sách địa chỉ..." />
        </div>

        <div v-else-if="addressStore.addresses.length === 0" class="empty-address text-center py-5 bg-light rounded-4">
            <i class="ph ph-map-pin-line display-1 text-muted mb-3"></i>
            <p class="text-muted">Bạn chưa có địa chỉ nào trong sổ địa chỉ.</p>
            <button class="btn btn-outline-primary mt-2" @click="openCreateForm">Thêm địa chỉ ngay</button>
        </div>

        <div v-else class="address-list-wrapper">
            <div class="address-list mb-4">
                <AddressItem 
                    v-for="addr in addressStore.addresses" 
                    :key="addr.id" 
                    :address="addr"
                    @edit="openEditForm"
                />
            </div>

            <div class="d-flex justify-content-center" v-if="addressStore.pagination.last_page > 1">
                <BasePagination 
                    :currentPage="addressStore.pagination.current_page"
                    :totalPages="addressStore.pagination.last_page"
                    :total="addressStore.pagination.total"
                    @changePage="changePage"
                />
            </div>
        </div>

        <!-- Modal Form -->
        <div v-if="isShowForm" class="modal-overlay" @click.self="isShowForm = false">
            <div class="modal-card animate__animated animate__fadeInUp">
                <div class="modal-header d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">{{ selectedAddress ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ mới' }}</h5>
                    <button class="btn-close" @click="isShowForm = false"></button>
                </div>
                <div class="modal-body">
                    <AddressForm 
                        :addressId="selectedAddress?.id"
                        :initialData="selectedAddress"
                        @success="handleSuccess"
                        @cancel="isShowForm = false"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 20px;
}

.modal-card {
    background: white;
    width: 100%;
    max-width: 700px;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.empty-address {
    border: 2px dashed #dee2e6;
}
</style>
