<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useReviewStore } from '@/stores/review'
import { swalConfirmDelete } from '@/composables/useSwal'
import { useNotify } from '@/composables/useNotify'

import ReviewTable from './ReviewTable.vue'
import ReviewDetailModal from './ReviewDetailModal.vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
import BaseInputSelect from '@/components/base/BaseInputSelect.vue'

const props = defineProps({
    title: String,
    description: String
})

// ================= STORE =================
const store = useReviewStore()
const toast = useNotify()

// ================= COMPUTED =================
const reviews = computed(() => store.reviews)
const totalPages = computed(() => store.pagination.last_page)
const totalReviews = computed(() => store.pagination.total)

// ================= STATE =================
const loadingData = ref(false)
const isShowDetail = ref(false)
const selectedReview = ref(null)

const params = ref({
    search: '',
    rating: '',
    status: '',
    limit: 15,
    page: 1
})

// ================= MAPS =================
const filterMap = [
    { id: 'true', name: 'Đang hiển thị' },
    { id: 'false', name: 'Đang ẩn' },
]

const limitMap = [
    { id: 15, name: '15' },
    { id: 30, name: '30' },
    { id: 50, name: '50' },
]

const ratingFilterMap = [
    { id: '5', name: '5 sao' },
    { id: '4', name: '4 sao' },
    { id: '3', name: '3 sao' },
    { id: '2', name: '2 sao' },
    { id: '1', name: '1 sao' },
]

// ================= METHODS =================
const loadData = async () => {
    loadingData.value = true
    await store.adminIndex(params.value)
    loadingData.value = false
}

const showDetail = (item) => {
    selectedReview.value = { ...item }
    isShowDetail.value = true
}

const closeDetail = () => {
    isShowDetail.value = false
    selectedReview.value = null
}

const toggleStatus = async (item) => {
    loadingData.value = true
    try {
        const res = await store.toggleStatus(item.id)
        if (res?.success) {
            toast.success(res.message || "Cập nhật trạng thái thành công")
            if (selectedReview.value && selectedReview.value.id === item.id) {
                selectedReview.value.status = !selectedReview.value.status
            }
            await loadData()
        } else {
            toast.error(res?.message || "Không thể cập nhật trạng thái")
        }
    } catch (e) {
        toast.error("Lỗi khi kết nối hệ thống")
    } finally {
        loadingData.value = false
    }
}

const destroy = async (id) => {
    const result = await swalConfirmDelete('Xác nhận xóa', 'Bạn có chắc chắn muốn xóa đánh giá này không? Hành động này không thể hoàn tác.')
    if (!result.isConfirmed) return

    loadingData.value = true
    try {
        const res = await store.destroy(id)
        if (res?.success) {
            toast.success(res.message || "Xóa đánh giá thành công")
            if (isShowDetail.value && selectedReview.value?.id === id) {
                closeDetail()
            }
            await loadData()
        } else {
            toast.error(res?.message || "Không thể xóa đánh giá")
        }
    } catch (e) {
        toast.error("Lỗi khi kết nối hệ thống")
    } finally {
        loadingData.value = false
    }
}

const search = () => {
    params.value.page = 1
    loadData()
}

const changePage = (page) => {
    params.value.page = page
}

// ================= WATCH =================
watch(
    () => ({ ...params.value }),
    (newVal, oldVal) => {
        // If filters changed, reset to page 1
        if (newVal.search !== oldVal.search || newVal.status !== oldVal.status || newVal.rating !== oldVal.rating || newVal.limit !== oldVal.limit) {
            params.value.page = 1
        }
        loadData()
    },
    { deep: true }
)

// ================= INIT =================
onMounted(loadData)
</script>

<template>
    <BaseAdmin 
        :title="props.title" 
        :description="props.description" 
        :total="totalReviews" 
        :totalPages="totalPages"
        :currentPage="params.page" 
        v-model:params="params" 
        :filterMap="filterMap" 
        :limitMap="limitMap"
        :hideOpenBtn="true"
        @search="search" 
        @changePage="changePage"
    >
        <!-- Custom rating filter in filters slot -->
        <template #filters>
            <div class="rating-filter-wrapper">
                <BaseInputSelect 
                    v-model="params.rating" 
                    customId="filter-rating" 
                    :values="ratingFilterMap" 
                    placeholder="Tất cả đánh giá" 
                />
            </div>
        </template>

        <template #table>
            <ReviewTable 
                :params="params" 
                :loadingData="loadingData" 
                :data="reviews" 
                @showDetail="showDetail"
                @toggleStatus="toggleStatus" 
                @destroy="destroy" 
            />
        </template>
    </BaseAdmin>

    <!-- Detail Modal -->
    <ReviewDetailModal 
        v-model:isShow="isShowDetail" 
        :review="selectedReview" 
        @close="closeDetail"
        @toggleStatus="toggleStatus"
    />
</template>

<style scoped>
.rating-filter-wrapper {
    min-width: 160px;
}

:deep(.auth-form-group) {
    margin-bottom: 0;
}

:deep(.auth-form-group label) {
    display: none;
}

:deep(.rating-filter-wrapper .auth-select .auth-input) {
    padding: 10px 36px 10px 16px;
    min-width: 160px;
}
</style>
