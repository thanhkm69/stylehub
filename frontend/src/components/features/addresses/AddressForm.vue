<script setup>
import { ref, onMounted, watch } from 'vue'
import { useGHNStore } from '@/stores/ghn'
import { useAddressStore } from '@/stores/address'
import { useNotify } from '@/composables/useNotify'

const props = defineProps({
    addressId: {
        type: [Number, null],
        default: null
    },
    initialData: {
        type: Object,
        default: () => ({})
    }
})

const emit = defineEmits(['success', 'cancel'])

const ghnStore = useGHNStore()
const addressStore = useAddressStore()
const toast = useNotify()

const loading = ref(false)
const form = ref({
    name: '',
    phone: '',
    province_id: '',
    province_name: '',
    district_id: '',
    district_name: '',
    ward_code: '',
    ward_name: '',
    address: '',
    is_default: false
})

onMounted(async () => {
    await ghnStore.loadProvinces()
    
    if (props.addressId && props.initialData) {
        form.value = { ...props.initialData }
        // Fetch districts and wards for existing address
        if (form.value.province_id) await ghnStore.loadDistricts(form.value.province_id)
        if (form.value.district_id) await ghnStore.loadWards(form.value.district_id)
    }
})

const handleProvinceChange = async () => {
    const province = ghnStore.provinces.find(p => p.ProvinceID === form.value.province_id)
    form.value.province_name = province ? province.ProvinceName : ''
    form.value.district_id = ''
    form.value.district_name = ''
    form.value.ward_code = ''
    form.value.ward_name = ''
    if (form.value.province_id) {
        await ghnStore.loadDistricts(form.value.province_id)
    }
}

const handleDistrictChange = async () => {
    const district = ghnStore.districts.find(d => d.DistrictID === form.value.district_id)
    form.value.district_name = district ? district.DistrictName : ''
    form.value.ward_code = ''
    form.value.ward_name = ''
    if (form.value.district_id) {
        await ghnStore.loadWards(form.value.district_id)
    }
}

const handleWardChange = () => {
    const ward = ghnStore.wards.find(w => w.WardCode === form.value.ward_code)
    form.value.ward_name = ward ? ward.WardName : ''
}

const submit = async () => {
    loading.value = true
    let result
    if (props.addressId) {
        result = await addressStore.update(props.addressId, form.value)
    } else {
        result = await addressStore.store(form.value)
    }

    if (result.success) {
        toast.success(result.message)
        emit('success')
    } else {
        toast.error(result.message || 'Có lỗi xảy ra')
    }
    loading.value = false
}
</script>

<template>
    <form @submit.prevent="submit" class="address-form row g-3">
        <div class="col-md-6">
            <label class="form-label fw-bold">Họ tên người nhận</label>
            <input v-model="form.name" type="text" class="form-control" placeholder="Nhập họ tên" :class="{'is-invalid': addressStore.errors.name}">
            <div class="invalid-feedback">{{ addressStore.errors.name?.[0] }}</div>
        </div>
        
        <div class="col-md-6">
            <label class="form-label fw-bold">Số điện thoại</label>
            <input v-model="form.phone" type="text" class="form-control" placeholder="Nhập số điện thoại" :class="{'is-invalid': addressStore.errors.phone}">
            <div class="invalid-feedback">{{ addressStore.errors.phone?.[0] }}</div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold d-flex align-items-center gap-2">
                Tỉnh/Thành phố
                <div v-if="ghnStore.loading && !ghnStore.provinces.length" class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </label>
            <select v-model="form.province_id" class="form-select" @change="handleProvinceChange" :class="{'is-invalid': addressStore.errors.province_id}">
                <option value="">Chọn Tỉnh/Thành</option>
                <option v-for="p in ghnStore.provinces" :key="p.ProvinceID" :value="p.ProvinceID">{{ p.ProvinceName }}</option>
            </select>
            <div class="invalid-feedback">{{ addressStore.errors.province_id?.[0] }}</div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold d-flex align-items-center gap-2">
                Quận/Huyện
                <div v-if="ghnStore.loading && form.province_id && !ghnStore.districts.length" class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </label>
            <select v-model="form.district_id" class="form-select" @change="handleDistrictChange" :disabled="!form.province_id" :class="{'is-invalid': addressStore.errors.district_id}">
                <option value="">Chọn Quận/Huyện</option>
                <option v-for="d in ghnStore.districts" :key="d.DistrictID" :value="d.DistrictID">{{ d.DistrictName }}</option>
            </select>
            <div class="invalid-feedback">{{ addressStore.errors.district_id?.[0] }}</div>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-bold d-flex align-items-center gap-2">
                Phường/Xã
                <div v-if="ghnStore.loading && form.district_id && !ghnStore.wards.length" class="spinner-border spinner-border-sm text-primary" role="status"></div>
            </label>
            <select v-model="form.ward_code" class="form-select" @change="handleWardChange" :disabled="!form.district_id" :class="{'is-invalid': addressStore.errors.ward_code}">
                <option value="">Chọn Phường/Xã</option>
                <option v-for="w in ghnStore.wards" :key="w.WardCode" :value="w.WardCode">{{ w.WardName }}</option>
            </select>
            <div class="invalid-feedback">{{ addressStore.errors.ward_code?.[0] }}</div>
        </div>

        <div class="col-12">
            <label class="form-label fw-bold">Địa chỉ chi tiết</label>
            <textarea v-model="form.address" class="form-control" rows="2" placeholder="Số nhà, tên đường..." :class="{'is-invalid': addressStore.errors.address}"></textarea>
            <div class="invalid-feedback">{{ addressStore.errors.address?.[0] }}</div>
        </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input v-model="form.is_default" class="form-check-input" type="checkbox" id="isDefault">
                <label class="form-check-label" for="isDefault">Đặt làm địa chỉ mặc định</label>
            </div>
        </div>

        <div class="col-12 d-flex gap-2 justify-content-end mt-4">
            <button type="button" class="btn btn-outline-secondary px-4" @click="emit('cancel')">Hủy</button>
            <button type="submit" class="btn btn-primary px-4" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                {{ addressId ? 'Cập nhật' : 'Thêm mới' }}
            </button>
        </div>
    </form>
</template>

<style scoped>
.address-form {
    padding: 10px;
}
.form-label {
    font-size: 14px;
    color: var(--text-main);
}
.form-control, .form-select {
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid var(--border);
}
.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.1);
}
</style>
