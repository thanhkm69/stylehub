<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useContactStore } from '@/stores/contact'
import { useNotify } from '@/composables/useNotify'
import { swalConfirmDelete } from '@/composables/useSwal'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseInputText from '@/components/base/BaseInputText.vue'
import BaseInputSelect from '@/components/base/BaseInputSelect.vue'
import BasePagination from '@/components/base/BasePagination.vue'
import ContactTable from '@/components/features/contacts/ContactTable.vue'

const contactStore = useContactStore()
const toast = useNotify()

const loadingData = ref(false)
const loadingSubmit = ref(false)
const isEdit = ref(false)
const selectedContact = ref(null)
const errors = ref({})

const params = reactive({
  search: '',
  status: null,
  created_at_from: '',
  created_at_to: '',
  limit: 15,
  page: 1,
})

const statusOptions = [
  { id: 'pending', name: 'Chờ xử lý' },
  { id: 'replied', name: 'Đã phản hồi' },
  { id: 'closed', name: 'Đã đóng' },
]

const loadData = async () => {
  loadingData.value = true
  const res = await contactStore.index(params)
  loadingData.value = false

  if (!res.success) {
    toast.error(res.message || 'Không tải được danh sách liên hệ')
  }
}

const changePage = async (page) => {
  params.page = page
  await loadData()
}

const search = async () => {
  params.page = 1
  await loadData()
}

const show = (item) => {
  selectedContact.value = item
  isEdit.value = false
}

const edit = (item) => {
  selectedContact.value = { ...item }
  isEdit.value = true
  errors.value = {}
}

const updateContact = async () => {
  if (!selectedContact.value) return

  loadingSubmit.value = true
  const payload = {
    status: selectedContact.value.status,
    admin_note: selectedContact.value.admin_note,
  }

  const res = await contactStore.update(selectedContact.value.id, payload)
  loadingSubmit.value = false

  if (res.success) {
    toast.success(res.message || 'Cập nhật liên hệ thành công')
    await loadData()
    selectedContact.value = res.data
    isEdit.value = false
    return
  }

  if (res.errors) {
    errors.value = res.errors
  } else {
    toast.error(res.message || 'Cập nhật liên hệ thất bại')
  }
}

const destroy = async (id) => {
  const confirmed = await swalConfirmDelete('Xác nhận', 'Bạn có chắc muốn xóa liên hệ này không?')
  if (!confirmed) return

  const res = await contactStore.destroy(id)
  if (res.success) {
    toast.success(res.message || 'Xóa liên hệ thành công')
    await loadData()
    return
  }

  toast.error(res.message || 'Xóa liên hệ thất bại')
}

const selectedStatusName = computed(() => {
  if (!selectedContact.value) return ''
  return statusOptions.find((item) => item.id === selectedContact.value.status)?.name || selectedContact.value.status
})

onMounted(loadData)
</script>

<template>
  <div class="admin-page-header">
    <h2 class="admin-page-title">Quản lý Liên hệ</h2>
    <p class="admin-page-desc">Danh sách liên hệ của khách hàng và cập nhật trạng thái phản hồi.</p>
  </div>

  <div class="admin-card admin-card--contact">
    <div class="admin-toolbar">
      <div class="toolbar-left">
        <BaseInputText v-model="params.search" customPlaceholderInput="Tìm theo họ tên, email, chủ đề" />
        <BaseInputSelect v-model="params.status" :values="statusOptions" placeholder="Tất cả trạng thái" />
      </div>
      <div class="toolbar-right">
        <BaseInputText v-model="params.created_at_from" customPlaceholderInput="Từ ngày (YYYY-MM-DD)" />
        <BaseInputText v-model="params.created_at_to" customPlaceholderInput="Đến ngày (YYYY-MM-DD)" />
        <BaseButton @click="search" customText="Tìm" customClass="btn btn-primary admin-btn" />
      </div>
    </div>

    <ContactTable :params="params" :data="contactStore.contacts" :loadingData="loadingData" @show="show" @update="edit" @destroy="destroy" />

    <div class="admin-pagination">
      <BasePagination :total="contactStore.pagination.total" :currentPage="params.page" :totalPages="contactStore.pagination.last_page" @changePage="changePage" />
    </div>
  </div>

  <div v-if="selectedContact" class="contact-detail-card">
    <div class="detail-header">
      <div>
        <h3>Chi tiết liên hệ</h3>
        <p>Họ tên: {{ selectedContact.full_name }}</p>
        <p>Email: {{ selectedContact.email }}</p>
        <p>Số điện thoại: {{ selectedContact.phone || 'Không cung cấp' }}</p>
      </div>
      <div>
        <span class="detail-status">{{ selectedStatusName }}</span>
      </div>
    </div>

    <div class="detail-body">
      <div class="detail-row">
        <strong>Chủ đề</strong>
        <span>{{ selectedContact.subject }}</span>
      </div>
      <div class="detail-row">
        <strong>Nội dung</strong>
        <span>{{ selectedContact.message }}</span>
      </div>
      <div class="detail-row">
        <strong>Ghi chú Admin</strong>
        <span>{{ selectedContact.admin_note || 'Chưa có ghi chú' }}</span>
      </div>
      <div class="detail-row">
        <strong>Trả lời lúc</strong>
        <span>{{ selectedContact.replied_at || 'Chưa phản hồi' }}</span>
      </div>
    </div>

    <div class="detail-actions">
      <div class="edit-panel">
        <BaseInputSelect v-model="selectedContact.status" :values="statusOptions" placeholder="Chọn trạng thái" />
        <div class="auth-form-group">
          <label for="admin_note">Ghi chú Admin</label>
          <textarea id="admin_note" v-model="selectedContact.admin_note" class="auth-input" rows="4" placeholder="Ghi chú thêm cho liên hệ"></textarea>
          <div v-if="errors.admin_note" class="auth-error-text">{{ errors.admin_note[0] || errors.admin_note }}</div>
        </div>
      </div>
      <div class="button-group">
        <BaseButton @click="updateContact" :disabled="loadingSubmit" customText="Lưu thay đổi" customClass="btn btn-primary" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-page-header {
  margin-bottom: 24px;
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

.admin-card--contact {
  background: var(--surface);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  padding: 24px;
  margin-bottom: 24px;
}

.admin-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.toolbar-left,
.toolbar-right {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
}

.auth-input {
  min-height: 46px;
}

.admin-pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.contact-detail-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 24px;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
  border-bottom: 1px solid var(--border);
  padding-bottom: 18px;
  margin-bottom: 18px;
}

.detail-header h3 {
  margin: 0;
}

.detail-status {
  display: inline-flex;
  padding: 10px 16px;
  border-radius: 999px;
  background: #e2e8f0;
  color: #1f2937;
  font-weight: 700;
}

.detail-body {
  display: grid;
  gap: 14px;
}

.detail-row {
  display: grid;
  grid-template-columns: 150px 1fr;
  gap: 16px;
  align-items: start;
}

.detail-row strong {
  color: var(--text-muted);
}

.detail-actions {
  margin-top: 24px;
  display: grid;
  gap: 18px;
}

.edit-panel {
  display: grid;
  gap: 16px;
}

.button-group {
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 992px) {
  .admin-toolbar,
  .detail-row {
    flex-direction: column;
  }
}
</style>