<script setup>
import { useAddressStore } from '@/stores/address'
import { useNotify } from '@/composables/useNotify'
import { swalConfirmDelete } from '@/composables/useSwal'

const props = defineProps({
    address: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['edit'])

const addressStore = useAddressStore()
const toast = useNotify()

const handleSetDefault = async () => {
    const result = await addressStore.setDefault(props.address.id)
    if (result.success) {
        toast.success(result.message)
    } else {
        toast.error(result.message)
    }
}

const handleDelete = async () => {
    const confirm = await swalConfirmDelete('Xác nhận', 'Bạn có chắc chắn muốn xóa địa chỉ này?')
    if (!confirm.isConfirmed) return

    const result = await addressStore.destroy(props.address.id)
    if (result.success) {
        toast.success(result.message)
    } else {
        toast.error(result.message)
    }
}
</script>

<template>
    <div class="address-item p-3 border rounded-3 mb-3" :class="{ 'border-primary shadow-sm': address.is_default }">
        <div class="d-flex justify-content-between align-items-start">
            <div class="address-info">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h6 class="mb-0 fw-bold">{{ address.name }}</h6>
                    <span class="text-muted">|</span>
                    <span class="text-muted">{{ address.phone }}</span>
                    <span v-if="address.is_default" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Mặc định</span>
                </div>
                <p class="address-text mb-1 text-secondary">
                    {{ address.address }}
                </p>
                <p class="address-location mb-0 text-secondary">
                    {{ address.ward_name }}, {{ address.district_name }}, {{ address.province_name }}
                </p>
            </div>
            
            <div class="address-actions d-flex flex-column gap-2">
                <button class="btn btn-sm btn-outline-primary" @click="emit('edit', address)">
                    <i class="ph ph-note-pencil"></i> Sửa
                </button>
                <button class="btn btn-sm btn-outline-danger" @click="handleDelete" :disabled="!!address.is_default && addressStore.addresses.length > 1">
                    <i class="ph ph-trash"></i> Xóa
                </button>
            </div>
        </div>
        
        <div class="mt-3 pt-3 border-top d-flex justify-content-end" v-if="!address.is_default">
            <button class="btn btn-sm btn-link text-decoration-none p-0" @click="handleSetDefault">
                Thiết lập mặc định
            </button>
        </div>
    </div>
</template>

<style scoped>
.address-item {
    background: var(--surface);
    color: var(--text-main);
    transition: all 0.2s ease;
}
.address-item:hover {
    border-color: var(--primary);
}
.address-info h6 {
    font-size: 16px;
}
.address-text, .address-location {
    font-size: 14px;
}
.badge {
    font-size: 10px;
    font-weight: 600;
    padding: 4px 8px;
}
.btn-sm {
    font-size: 12px;
}
</style>
