<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import BaseAdmin from '@/components/base/BaseAdmin.vue'
import BaseInputSelect from '@/components/base/BaseInputSelect.vue'
import PostCommentTable from './PostCommentTable.vue'
import { swalConfirmDelete } from '@/composables/useSwal'
import { useNotify } from '@/composables/useNotify'
import { usePostCommentStore } from '@/stores/postComment'

const store = usePostCommentStore()
const toast = useNotify()
const loadingData = ref(false)
const togglingModeration = ref(false)

const params = ref({
  search: '',
  status: '',
  moderation_status: '',
  sort: 'created_at_desc',
  limit: 15,
  page: 1,
})

const filterMap = [
  { id: 'true', name: 'Đang hiển thị' },
  { id: 'false', name: 'Đang ẩn' },
]

const moderationMap = [
  { id: 'flagged', name: 'AI gắn cờ' },
  { id: 'pending', name: 'Chờ duyệt' },
  { id: 'approved', name: 'Đã duyệt' },
  { id: 'not_checked', name: 'Chưa kiểm tra' },
  { id: 'hidden', name: 'Ẩn thủ công' },
]

const sortMap = [
  { id: 'created_at_desc', name: 'Mới nhất' },
  { id: 'created_at_asc', name: 'Cũ nhất' },
]

const limitMap = [
  { id: 15, name: '15' },
  { id: 30, name: '30' },
  { id: 50, name: '50' },
]

const comments = computed(() => store.comments)
const moderationSettings = computed(() => store.moderationSettings)
const totalPages = computed(() => store.pagination.last_page)
const totalComments = computed(() => store.pagination.total)

const loadData = async () => {
  loadingData.value = true
  await store.adminIndex(params.value)
  loadingData.value = false
}

const loadModerationSettings = async () => {
  const response = await store.getModerationSettings()

  if (!response.success) {
    toast.error(response.message || 'Không thể tải cấu hình kiểm duyệt AI.')
  }
}

const toggleModeration = async () => {
  togglingModeration.value = true
  const response = await store.toggleModeration()
  togglingModeration.value = false

  if (!response.success) {
    toast.error(response.message || 'Không thể cập nhật chế độ kiểm duyệt AI.')
    return
  }

  toast.success(response.message)
}

const search = () => {
  if (params.value.page !== 1) {
    params.value.page = 1
    return
  }

  loadData()
}

const changePage = (page) => {
  params.value.page = page
}

const toggleStatus = async (comment) => {
  const response = await store.toggleStatus(comment.id)

  if (!response.success) {
    toast.error(response.message || 'Không thể cập nhật bình luận.')
    return
  }

  toast.success(response.message)
  await loadData()
}

const moderate = async (comment) => {
  const response = await store.moderate(comment.id)

  if (!response.success) {
    toast.error(response.message || 'Không thể kiểm tra bình luận bằng AI.')
    return
  }

  toast.success(response.message)
  await loadData()
}

const destroy = async (id) => {
  const result = await swalConfirmDelete('Xác nhận xóa', 'Bạn có chắc muốn xóa bình luận này không?')

  if (!result.isConfirmed) {
    return
  }

  const response = await store.adminDestroy(id)

  if (!response.success) {
    toast.error(response.message || 'Không thể xóa bình luận.')
    return
  }

  toast.success(response.message)
  await loadData()
}

watch(
  () => [params.value.status, params.value.moderation_status, params.value.sort, params.value.limit],
  () => {
    if (params.value.page !== 1) {
      params.value.page = 1
      return
    }

    loadData()
  },
)

watch(
  () => params.value.page,
  loadData,
)

onMounted(() => {
  loadData()
  loadModerationSettings()
})
</script>

<template>
  <section class="moderation-panel">
    <div>
      <span class="panel-kicker">Groq AI moderation</span>
      <h3>Kiểm duyệt bình luận tự động</h3>
      <p v-if="moderationSettings.configured">
        Dùng <strong>{{ moderationSettings.provider }}</strong> / <strong>{{ moderationSettings.model }}</strong> để ẩn bình luận độc hại hoặc spam trước khi hiển thị.
      </p>
      <p v-else class="config-warning">
        Chưa có <strong>GROQ_API_KEY</strong>. Khi bật, bình luận mới sẽ được giữ ở trạng thái chờ duyệt.
      </p>
    </div>
    <button class="toggle-button" :class="{ enabled: moderationSettings.enabled }" type="button"
      :disabled="togglingModeration" @click="toggleModeration">
      <span class="toggle-track"><span class="toggle-thumb"></span></span>
      {{ moderationSettings.enabled ? 'Đang bật' : 'Đang tắt' }}
    </button>
  </section>

  <BaseAdmin :total="totalComments" :totalPages="totalPages" :currentPage="params.page"
    v-model:params="params" :sortMap="sortMap" :filterMap="filterMap" :limitMap="limitMap"
    :hideOpenBtn="true" @search="search" @changePage="changePage">
    <template #filters>
      <BaseInputSelect v-model="params.moderation_status" customId="moderation-status" :values="moderationMap"
        placeholder="Tất cả kiểm duyệt" />
    </template>
    <template #table>
      <PostCommentTable :data="comments" :loadingData="loadingData" @moderate="moderate"
        @toggleStatus="toggleStatus" @destroy="destroy" />
    </template>
  </BaseAdmin>
</template>

<style scoped>
.moderation-panel {
  align-items: center;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  display: flex;
  gap: 30px;
  justify-content: space-between;
  margin: 4px 0 18px;
  padding: 20px 24px;
}

.panel-kicker {
  color: #2563eb;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.moderation-panel h3 {
  color: var(--text-main);
  font-size: 18px;
  margin: 5px 0 6px;
}

.moderation-panel p {
  color: var(--text-muted);
  margin: 0;
}

.moderation-panel .config-warning {
  color: #b45309;
}

.toggle-button {
  align-items: center;
  background: #f1f5f9;
  border: 1px solid #dbe2ea;
  border-radius: 999px;
  color: #475569;
  display: inline-flex;
  flex-shrink: 0;
  font-weight: 600;
  gap: 10px;
  padding: 8px 14px 8px 8px;
}

.toggle-track {
  background: #cbd5e1;
  border-radius: 999px;
  display: inline-flex;
  padding: 3px;
  transition: background 0.2s;
  width: 42px;
}

.toggle-thumb {
  background: #fff;
  border-radius: 50%;
  display: block;
  height: 18px;
  transition: transform 0.2s;
  width: 18px;
}

.toggle-button.enabled {
  background: #ecfdf5;
  border-color: #a7f3d0;
  color: #047857;
}

.toggle-button.enabled .toggle-track {
  background: #10b981;
}

.toggle-button.enabled .toggle-thumb {
  transform: translateX(18px);
}

@media (max-width: 720px) {
  .moderation-panel {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
