<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import BaseAdmin from '@/components/base/BaseAdmin.vue';
import BaseModal from '@/components/base/BaseModal.vue';
import BaseForm from '@/components/base/BaseForm.vue';
import BaseInputText from '@/components/base/BaseInputText.vue';
import BaseInputSelect from '@/components/base/BaseInputSelect.vue';
import BaseButton from '@/components/base/BaseButton.vue';
import BaseSpinner from '@/components/base/BaseSpinner.vue';
import BaseInputFile from '@/components/base/BaseInputFile.vue';
import BaseInputNumber from '@/components/base/BaseInputNumber.vue';
import BaseLoading from '@/components/base/BaseLoading.vue';
import ProductVariantValueModal from '@/components/features/products/ProductVariantValueModal.vue';
import { useProductVariantStore } from '@/stores/productVariant';
import { useProductStore } from '@/stores/product';
import { swalConfirmDelete } from '@/composables/useSwal';
import { useNotify } from '@/composables/useNotify';
import { API_URL_IMAGE } from '@/config/env';

const route = useRoute();
const router = useRouter();
const store = useProductVariantStore();
const productStore = useProductStore();
const toast = useNotify();

const productId = route.params.id;
const product = ref(null);

const loadingData = ref(false);
const loadingSubmit = ref(false);
const isEdit = ref(false);
const isShowValuesModal = ref(false);
const isShowFormModal = ref(false);
const selectedVariant = ref(null);
const errors = ref({});

const params = ref({
    search: '',
    sort: 'created_at_desc',
    status: '',
    limit: 15,
    page: 1
});

const dataForm = ref({
    id: null,
    product_id: productId,
    sku: '',
    price: 0,
    stock: 0,
    image: null,
    preview: '',
    status: 1
});

const sortMap = [
    { id: 'price_asc', name: "Giá tăng dần" },
    { id: 'price_desc', name: "Giá giảm dần" },
    { id: 'created_at_asc', name: "Cũ nhất" },
    { id: 'created_at_desc', name: "Mới nhất" },
];

const filterMap = [
    { id: 1, name: 'Hiển thị' },
    { id: 0, name: 'Đã ẩn' },
];

const limitMap = [
    { id: 15, name: '15' },
    { id: 30, name: '30' },
    { id: 50, name: '50' },
    { id: 100, name: '100' },
];

const statusMap = [
    { id: 1, name: 'Hiện' },
    { id: 0, name: 'Ẩn' }
];

const values = computed(() => store.productVariants);
const totalPages = computed(() => store.pagination.last_page);
const totalVariants = computed(() => store.pagination.total);

const loadData = async () => {
    loadingData.value = true;
    await store.index({ ...params.value, product_id: productId });
    loadingData.value = false;
};

const loadProduct = async () => {
    const res = await productStore.show(productId);
    if (res && res.success) {
        product.value = res.data;
    }
};

const resetForm = () => {
    dataForm.value = {
        id: null,
        product_id: productId,
        sku: '',
        price: 0,
        stock: 0,
        image: null,
        preview: '',
        status: 1
    };
    isEdit.value = false;
    errors.value = {};
};

const validate = () => {
    errors.value = {};

    if (!isEdit.value && !dataForm.value.image) {
        errors.value.image = 'Hình ảnh là bắt buộc';
    }

    if (!dataForm.value.sku?.trim()) {
        errors.value.sku = 'Mã SKU không được để trống';
    }

    if (dataForm.value.price === null || dataForm.value.price < 0) {
        errors.value.price = 'Giá không hợp lệ';
    }

    if (dataForm.value.image instanceof File) {
        const file = dataForm.value.image;
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];

        if (!validTypes.includes(file.type)) {
            errors.value.image = 'Chỉ chấp nhận png, jpg, jpeg, webp';
        }

        if (file.size > 2 * 1024 * 1024) {
            errors.value.image = 'Ảnh tối đa 2MB';
        }
    }

    return Object.keys(errors.value).length === 0;
};

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    dataForm.value.image = file;
    dataForm.value.preview = URL.createObjectURL(file);
};

const submit = async () => {
    if (!validate()) return;

    loadingSubmit.value = true;
    dataForm.value.product_id = productId;

    const formData = new FormData();
    formData.append('product_id', dataForm.value.product_id);
    formData.append('sku', dataForm.value.sku);
    formData.append('price', dataForm.value.price);
    formData.append('stock', dataForm.value.stock);
    formData.append('status', dataForm.value.status);

    if (dataForm.value.image instanceof File) {
        formData.append('image', dataForm.value.image);
    }

    let result;
    if (isEdit.value) {
        result = await store.update(dataForm.value.id, formData);
    } else {
        result = await store.store(formData);
    }

    if (!result?.success) {
        toast.error(result?.message || "Lỗi khi lưu dữ liệu");
        if (result?.errors) {
            errors.value = {
                image: result.errors.image?.[0] ?? "",
                sku: result.errors.sku?.[0] ?? "",
                price: result.errors.price?.[0] ?? "",
                stock: result.errors.stock?.[0] ?? "",
                status: result.errors.status?.[0] ?? "",
            };
        }
    } else {
        toast.success(result?.message || "Thành công");
        isShowFormModal.value = false;
        resetForm();
        await loadData();
    }
    loadingSubmit.value = false;
};

const editValue = (item) => {
    dataForm.value = { ...item, image: null, preview: item.image ? `${API_URL_IMAGE}/${item.image}` : '' };
    isEdit.value = true;
    isShowFormModal.value = true;
};

const destroyValue = async (id) => {
    const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa biến thể này không ?');
    if (!result.isConfirmed) return;
    const res = await store.destroy(id);
    if (!res?.success) {
        toast.error(res?.message || "Lỗi khi xóa dữ liệu");
    } else {
        toast.success(res?.message || "Thành công");
        await loadData();
    }
};

const openValues = (item) => {
    selectedVariant.value = item;
    isShowValuesModal.value = true;
};

const handleVariantValuesChanged = (type) => {
    if (selectedVariant.value) {
        if (type === 'add') {
            selectedVariant.value.product_variant_values_count = (selectedVariant.value.product_variant_values_count || 0) + 1;
        } else if (type === 'delete') {
            selectedVariant.value.product_variant_values_count = Math.max(0, (selectedVariant.value.product_variant_values_count || 0) - 1);
        }
    }
};

const openCreateForm = () => {
    resetForm();
    isShowFormModal.value = true;
};

const closeForm = () => {
    isShowFormModal.value = false;
    resetForm();
};

const search = () => {
    params.value.page = 1;
    loadData();
};

const changePage = (page) => {
    params.value.page = page;
};

const goBack = () => {
    router.push({ name: 'Products' });
};

watch(
    () => ({ ...params.value }),
    loadData,
    { deep: true }
);

onMounted(() => {
    loadProduct();
    loadData();
});
</script>

<template>
    <div>

        <!-- BaseAdmin Wrap -->
        <BaseAdmin title="Quản lý Biến thể" description="Quản lý các biến thể của sản phẩm." :total="totalVariants"
            :totalPages="totalPages" :currentPage="params.page" v-model:params="params" :sortMap="sortMap"
            :filterMap="filterMap" :limitMap="limitMap" @search="search" @open="openCreateForm"
            @changePage="changePage">
            <template #filters>
                <BaseButton @click="goBack" customClass="btn btn-outline" customText="Quay lại"
                    style="padding: 8px 16px; margin-right: 8px;" />
            </template>
            <template #table>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>SKU</th>
                            <th>Giá</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                            <th>Giá trị</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loadingData">
                            <td colspan="6" class="text-center" style="padding: 24px;">
                                <BaseLoading />
                            </td>
                        </tr>
                        <tr v-else-if="values.length === 0">
                            <td colspan="6" class="text-center" style="padding: 24px; color: var(--text-muted);">
                                Chưa có biến thể nào
                            </td>
                        </tr>
                        <tr v-else v-for="item in values" :key="item.id">
                            <td>
                                <img v-if="item.image" :src="`${API_URL_IMAGE}/${item.image}`" alt="preview"
                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />
                            </td>
                            <td><strong style="color: var(--text-main);">{{ item.sku }}</strong></td>
                            <td>{{ new Intl.NumberFormat('vi-VN', {
                                style: 'currency', currency: 'VND'
                            }).format(item.price)
                            }}</td>
                            <td>
                                <span :style="{ color: item.stock > 0 ? 'var(--text-main)' : 'var(--danger)' }">
                                    {{ item.stock > 0 ? item.stock : 'Hết hàng' }}
                                </span>
                            </td>
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
                            <td>
                                <div class="action-group">
                                    <BaseButton @click="openValues(item)" customText="Giá trị"
                                        customClass="btn-action btn-attribute" />
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </BaseAdmin>

        <!-- Modal Form Thêm/Sửa -->
        <BaseModal @close="closeForm" :isShow="isShowFormModal" :title="isEdit ? 'Sửa biến thể' : 'Thêm biến thể mới'"
            customWidth="600px">
            <BaseForm @handleSubmit="submit">
                <template #input>
                    <div class="image-upload-wrapper" style="margin-bottom: 16px;">
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
    <div class="admin-page-header">
        <div style="display: flex; align-items: center; gap: 16px;">
            <button @click="goBack" class="btn-back" title="Quay lại">
                <i class="ph ph-arrow-left"></i>
            </button>
            <div>
                <h2 class="admin-page-title">Biến thể: <span style="color: var(--primary);">{{ product?.name }}</span>
                </h2>
                <p class="admin-page-desc">Quản lý các biến thể của sản phẩm.</p>
            </div>
        </div>
    </div>

    <!-- BaseAdmin Wrap -->
    <BaseAdmin :total="totalVariants" :totalPages="totalPages" :currentPage="params.page" v-model:params="params"
        :sortMap="sortMap" :filterMap="filterMap" :limitMap="limitMap" @search="search" @open="openCreateForm"
        @changePage="changePage">
        <template #table>
            <table class="table">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>SKU</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                        <th>Giá trị</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loadingData">
                        <td colspan="6" class="text-center" style="padding: 24px;">
                            <BaseLoading />
                        </td>
                    </tr>
                    <tr v-else-if="values.length === 0">
                        <td colspan="6" class="text-center" style="padding: 24px; color: var(--text-muted);">
                            Chưa có biến thể nào
                        </td>
                    </tr>
                    <tr v-else v-for="item in values" :key="item.id">
                        <td>
                            <img v-if="item.image" :src="`${API_URL_IMAGE}/${item.image}`" alt="preview"
                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" />
                        </td>
                        <td><strong style="color: var(--text-main);">{{ item.sku }}</strong></td>
                        <td>{{ new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price)
                        }}</td>
                        <td>
                            <span :style="{ color: item.stock > 0 ? 'var(--text-main)' : 'var(--danger)' }">
                                {{ item.stock > 0 ? item.stock : 'Hết hàng' }}
                            </span>
                        </td>
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
                        <td>
                            <div class="action-group">
                                <BaseButton @click="openValues(item)" customText="Giá trị"
                                    customClass="btn-action btn-attribute" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </template>
    </BaseAdmin>

    <!-- Modal Form Thêm/Sửa -->
    <BaseModal @close="closeForm" :isShow="isShowFormModal" :title="isEdit ? 'Sửa biến thể' : 'Thêm biến thể mới'"
        customWidth="600px">
        <BaseForm @handleSubmit="submit">
            <template #input>
                <div class="image-upload-wrapper" style="margin-bottom: 16px;">
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

                    <BaseInputText labelContent="Mã SKU" customId="val_sku" v-model="dataForm.sku"
                        customPlaceholderInput="Ví dụ: AO-DO-M" :error="errors.sku" />
                    <BaseInputNumber labelContent="Giá sản phẩm" v-model="dataForm.price" :error="errors.price" />
                    <BaseInputNumber labelContent="Số lượng tồn kho" v-model="dataForm.stock" :error="errors.stock" />
                    <BaseInputSelect labelContent="Trạng thái" v-model="dataForm.status" :values="statusMap" />
                </template>
                <template #button>
                    <div class="form-actions">
                        <BaseButton @click="closeForm" customType="button" customText="Hủy bỏ"
                            customClass="btn-cancel" />
                        <BaseButton v-if="!loadingSubmit" customType="submit"
                            :customText="isEdit ? 'Lưu thay đổi' : 'Thêm'" :disabled="loadingSubmit"
                            customClass="btn btn-primary" />
                        <div v-else class="loading-submit">
                            <BaseSpinner />
                        </div>
                    </div>
                </template>
            </BaseForm>
        </BaseModal>

        <!-- Product Variant Values Modal -->
        <ProductVariantValueModal v-model:isShow="isShowValuesModal" :variant="selectedVariant"
            @variantValuesChanged="handleVariantValuesChanged" @close="isShowValuesModal = false" />
    </div>
</template>

<style scoped>
.admin-page-header {
    margin-bottom: 24px;
}

.btn-back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid var(--border-color, #e2e8f0);
    background: white;
    color: var(--text-main);
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.btn-back:hover {
    background: var(--primary, #3b82f6);
    color: white;
    border-color: var(--primary, #3b82f6);
    transform: translateX(-4px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.btn-back i {
    font-size: 20px;
}

.admin-page-title {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.admin-page-desc {
    color: var(--text-muted);
    font-size: 15px;
}

.image-upload-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.image-preview-large {
    width: 100%;
    height: 160px;
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
    height: 160px;
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
    justify-content: flex-end;
    gap: 16px;
    padding-top: 16px;
    margin-top: 8px;
    border-top: 1px solid #f1f5f9;
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
    font-weight: 600;
    padding: 10px 24px;
    border-radius: 12px;
}

.btn-cancel:hover {
    background: #e2e8f0;
}

.loading-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 40px;
}

.btn-attribute {
    background-color: #10b981 !important;
    color: white !important;
    border: none !important;
    border-radius: 999px !important;
    padding: 4px 16px !important;
    font-size: 13px !important;
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
</style>
