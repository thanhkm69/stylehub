<script setup>
import { ref, computed, watch } from 'vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import BaseInputFile from '@/components/base/BaseInputFile.vue';
import BaseInputNumber from '@/components/base/BaseInputNumber.vue';
import BaseLoading from '@/components/base/BaseLoading.vue';
import { useProductImageStore } from '@/stores/productImage';
import { swalConfirmDelete } from '@/composables/useSwal';
import { useNotify } from '@/composables/useNotify';
import { API_URL_IMAGE } from '@/config/env';

const props = defineProps({
    product: Object
})

const emit = defineEmits(["close", "imagesChanged"])
const isShow = defineModel("isShow")

const store = useProductImageStore()
const toast = useNotify()

const loadingData = ref(false)
const loadingSubmit = ref(false)
const isEdit = ref(false)
const errors = ref({})

const dataForm = ref({
    id: null,
    product_id: null,
    image: null,
    preview: '',
    alt: '',
    display: 0,
    status: 1
})

const statusMap = [
    { id: 1, name: 'Hiện' },
    { id: 0, name: 'Ẩn' }
]

const values = computed(() => store.productImages)

const loadData = async () => {
    loadingData.value = true
    await store.index(props.product.id)
    loadingData.value = false
}

const resetForm = () => {
    dataForm.value = {
        id: null,
        product_id: props.product?.id || null,
        image: null,
        preview: '',
        alt: '',
        display: 0,
        status: 1
    }
    isEdit.value = false
    errors.value = {}
}

const validate = () => {
    errors.value = {}

    if (!isEdit.value && !dataForm.value.image) {
        errors.value.image = 'Hình ảnh không được để trống'
    }

    if (!dataForm.value.alt?.trim()) {
        errors.value.alt = 'Thẻ alt không được để trống'
    }

    if (dataForm.value.image instanceof File) {
        const file = dataForm.value.image
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp']

        if (!validTypes.includes(file.type)) {
            errors.value.image = 'Chỉ chấp nhận png, jpg, jpeg, webp'
        }

        if (file.size > 2 * 1024 * 1024) {
            errors.value.image = 'Ảnh tối đa 2MB'
        }
    }

    return Object.keys(errors.value).length === 0
}

const handleImageChange = (event) => {
    const file = event.target.files[0]
    if (!file) return

    dataForm.value.image = file
    dataForm.value.preview = URL.createObjectURL(file)
}

const submit = async () => {
    if (!validate()) return

    loadingSubmit.value = true
    dataForm.value.product_id = props.product.id

    const formData = new FormData()
    formData.append('product_id', dataForm.value.product_id)
    formData.append('alt', dataForm.value.alt)
    formData.append('display', dataForm.value.display)
    formData.append('status', dataForm.value.status)

    if (dataForm.value.image instanceof File) {
        formData.append('image', dataForm.value.image)
    }

    let result
    if (isEdit.value) {
        result = await store.update(dataForm.value.id, formData)
    } else {
        result = await store.store(formData)
    }

    if (!result?.success) {
        toast.error(result?.message || "Lỗi khi lưu dữ liệu");
        if (result?.errors) {
            errors.value = {
                image: result.errors.image?.[0] ?? "",
                alt: result.errors.alt?.[0] ?? "",
                display: result.errors.display?.[0] ?? "",
                status: result.errors.status?.[0] ?? "",
            }
        }
    } else {
        toast.success(result?.message || "Thành công");
        resetForm()
        await loadData()
        emit('imagesChanged', 'add')
    }
    loadingSubmit.value = false
}

const editValue = (item) => {
    dataForm.value = { ...item, image: null, preview: item.image ? `${API_URL_IMAGE}/${item.image}` : '' }
    isEdit.value = true
}

const destroyValue = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa hình ảnh này không ?')
    if (!result.isConfirmed) return
    await store.destroy(id)
    await loadData()
    emit('imagesChanged', 'delete')
}

watch(() => isShow.value, (newVal) => {
    if (newVal) {
        resetForm()
        loadData()
    }
})

</script>

<template>
    <BaseModal @close="emit('close')" :isShow="isShow" customWidth="1100px">
        <div class="prod-img-container">
            <!-- Trái: Danh sách hình ảnh -->
            <div class="prod-img-list">
                <h3 class="modal-title">Sản phẩm: <span style="color: var(--primary);">{{ product?.name }}</span></h3>
                
                <div class="admin-table-wrapper" style="max-height: 600px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Hình ảnh</th>
                                <th>Thẻ Alt</th>
                                <th>Thứ tự</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="loadingData">
                                <td colspan="6" class="text-center" style="padding: 48px;">
                                    <BaseSpinner size="lg" label="Đang tải dữ liệu..." />
                                </td>
                            </tr>
                            <tr v-else-if="values.length === 0">
                                <td colspan="6" class="text-center" style="padding: 24px; color: var(--text-muted);">
                                    Chưa có hình ảnh nào
                                </td>
                            </tr>
                            <tr v-else v-for="(item, index) in values" :key="item.id">
                                <td>{{ index + 1 }}</td>
                                <td>
                                    <img v-if="item.image" :src="item.image" alt="preview" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />
                                </td>
                                <td><strong style="color: var(--text-main);">{{ item.alt }}</strong></td>
                                <td>{{ item.display }}</td>
                                <td>
                                    <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
                                        {{ item.status ? 'Hiện' : 'Ẩn' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <BaseButton @click="editValue(item)" customText="Sửa"
                                            customClass="btn-action btn-edit" />
                                        <BaseButton @click="destroyValue(item.id)" customText="Xóa"
                                            customClass="btn-action btn-delete" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Phải: Form Thêm/Sửa -->
            <div class="prod-img-form">
                <div class="form-card">
                    <h4 class="form-title">{{ isEdit ? 'Sửa hình ảnh' : 'Thêm hình ảnh mới' }}</h4>
                    <BaseForm @handleSubmit="submit">
                        <template #input>
                            <div class="image-upload-wrapper" style="margin-bottom: 20px;">
                                <div v-if="dataForm.preview" class="image-preview-large">
                                    <img :src="dataForm.preview" alt="Preview" />
                                    <div class="preview-overlay">Xem trước</div>
                                </div>
                                <div v-else class="image-placeholder">
                                    <i class="ph ph-image-square"></i>
                                    <p>Chưa có ảnh</p>
                                </div>
                                
                                <div class="upload-input-container">
                                    <BaseInputFile labelContent="" customId="image" :error="errors.image" customAccept="image/*"
                                        @change="handleImageChange" />
                                    <p class="upload-hint">Định dạng: JPG, PNG, WEBP. Tối đa 2MB.</p>
                                </div>
                            </div>

                            <BaseInputText labelContent="Thẻ Alt" customId="val_alt" v-model="dataForm.alt" customPlaceholderInput="Ví dụ: Hình mặt trước"
                                :error="errors.alt" />
                            <BaseInputNumber labelContent="Thứ tự hiển thị" v-model="dataForm.display" :error="errors.display" />
                            <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status"
                                :values="statusMap" />
                        </template>
                        <template #button>
                            <div class="form-actions">
                                <BaseButton v-if="!loadingSubmit" customType="submit"
                                    :customText="isEdit ? 'Cập nhật' : 'Thêm'" :disabled="loadingSubmit"
                                    customClass="btn btn-primary" style="flex: 1;" />
                                <div v-else style="flex: 1; display: flex; justify-content: center; padding: 8px 0;">
                                    <BaseSpinner />
                                </div>
                                <BaseButton v-if="isEdit" @click="resetForm" customType="button" customText="Hủy"
                                    customClass="btn" style="background: var(--background); color: var(--text-main); border: 1px solid var(--border);" />
                            </div>
                        </template>
                    </BaseForm>
                </div>
            </div>
        </div>
    </BaseModal>
</template>

<style scoped>
.prod-img-container {
    display: flex;
    gap: 32px;
}

.prod-img-list {
    flex: 6;
}

.prod-img-form {
    flex: 4;
}

.modal-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 24px;
    color: var(--text-main);
}

.form-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    padding: 24px;
}

.form-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
    color: var(--text-main);
}

.image-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.image-preview-large {
    width: 100%;
    height: 180px;
    border-radius: 12px;
    overflow: hidden;
    border: 2px dashed var(--border);
    position: relative;
    background: #f8fafc;
}

.image-preview-large img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.image-placeholder {
    width: 100%;
    height: 180px;
    border-radius: 12px;
    border: 2px dashed var(--border);
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    gap: 8px;
}

.image-placeholder i {
    font-size: 40px;
}

.preview-overlay {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.5);
    color: white;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 12px;
    backdrop-filter: blur(4px);
}

.upload-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 4px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

.btn-action {
    transition: all 0.2s ease;
    cursor: pointer;
    font-weight: 600;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    filter: brightness(1.1);
}

.btn-action:active {
    transform: translateY(0);
}

.table tbody tr {
    animation: fadeIn 0.4s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .prod-img-container {
        flex-direction: column;
    }
}
</style>
