<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
  params: Object,
  data: Array,
  loadingData: Boolean,
})

const emit = defineEmits(['show', 'update', 'destroy'])

const formatDate = (value) => {
  if (!value) return ''

  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value))
}
</script>

<template>
  <div class="admin-table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>STT</th>
          <th>Họ tên</th>
          <th>Email</th>
          <th>Chủ đề</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
          <th>Hành động</th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="loadingData">
          <td colspan="7" class="text-center" style="padding: 32px 0;">
            <BaseLoading />
          </td>
        </tr>

        <tr v-else-if="!data || data.length === 0">
          <td colspan="7" class="text-center" style="padding: 32px 0; color: var(--text-muted);">
            Không có dữ liệu
          </td>
        </tr>

        <tr v-else v-for="(item, index) in data" :key="item.id">
          <td>{{ (params.page - 1) * params.limit + index + 1 }}</td>
          <td>{{ item.full_name }}</td>
          <td>{{ item.email }}</td>
          <td>{{ item.subject }}</td>
          <td>
            <span :class="['badge-status', item.status === 'pending' ? 'badge-pending' : item.status === 'replied' ? 'badge-replied' : 'badge-closed']">
              {{ item.status === 'pending' ? 'Chờ xử lý' : item.status === 'replied' ? 'Đã phản hồi' : 'Đã đóng' }}
            </span>
          </td>
          <td class="created-at">{{ formatDate(item.created_at) }}</td>
          <td>
            <div class="action-group">
              <BaseButton @click="emit('show', item)" customText="Xem" customClass="btn-action btn-view" />
              <BaseButton @click="emit('update', item)" customText="Sửa" customClass="btn-action btn-edit" />
              <BaseButton @click="emit('destroy', item.id)" customText="Xóa" customClass="btn-action btn-delete" />
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table {
  table-layout: fixed;
  min-width: 1180px;
}

.table th:nth-child(1) {
  width: 5%;
}

.table th:nth-child(2) {
  width: 12%;
}

.table th:nth-child(3) {
  width: 24%;
}

.table th:nth-child(4) {
  width: 9%;
}

.table th:nth-child(5) {
  width: 13%;
}

.table th:nth-child(6) {
  width: 15%;
}

.table th:nth-child(7) {
  width: 22%;
}

.admin-table-wrapper {
  overflow-x: auto;
}

.table td {
  overflow-wrap: anywhere;
}

.created-at,
.badge-status {
  white-space: nowrap;
}

.action-group {
  flex-wrap: nowrap;
}

:deep(.btn-action) {
  min-width: 52px;
  padding: 10px 14px;
  white-space: nowrap;
}

.btn-action {
  transition: all 0.2s ease;
  cursor: pointer;
  font-weight: 600;
}

.btn-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  filter: brightness(1.05);
}

.badge-status {
  display: inline-flex;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.badge-pending {
  background: #fef3c7;
  color: #92400e;
}

.badge-replied {
  background: #d1fae5;
  color: #047857;
}

.badge-closed {
  background: #e2e8f0;
  color: #334155;
}
</style>
