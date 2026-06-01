<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

defineProps({
  data: {
    type: Array,
    default: () => [],
  },
  loadingData: Boolean,
})

const emit = defineEmits(['moderate', 'toggleStatus', 'destroy'])

const moderationLabels = {
  approved: 'Đã duyệt',
  flagged: 'AI gắn cờ',
  pending: 'Chờ duyệt',
  hidden: 'Ẩn thủ công',
  not_checked: 'Chưa kiểm tra',
}

const formatDate = (value) => new Date(value).toLocaleDateString('vi-VN')
</script>

<template>
  <div class="admin-table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Người bình luận</th>
          <th>Bài viết</th>
          <th>Nội dung</th>
          <th>Kiểm duyệt</th>
          <th>Ngày đăng</th>
          <th>Hiển thị</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loadingData">
          <td colspan="7" class="loading-cell">
            <BaseLoading text="Đang tải bình luận..." />
          </td>
        </tr>
        <tr v-else-if="data.length === 0">
          <td colspan="7" class="empty-cell">Không có bình luận nào phù hợp.</td>
        </tr>
        <tr v-for="item in data" v-else :key="item.id">
          <td>
            <strong class="user-name">{{ item.user?.name || 'Người dùng' }}</strong>
            <span class="subtext">{{ item.parent_id ? 'Phản hồi' : 'Bình luận gốc' }} #{{ item.id }}</span>
          </td>
          <td>
            <router-link class="post-link" :to="`/blog/${item.post?.slug}`" target="_blank">
              {{ item.post?.title || 'Bài viết đã xóa' }}
            </router-link>
          </td>
          <td>
            <p v-if="item.parent" class="reply-to">Trả lời {{ item.parent.user?.name || 'người dùng' }}</p>
            <p class="content-cell">{{ item.content }}</p>
          </td>
          <td>
            <span :class="['badge-moderation', `moderation-${item.moderation_status || 'not_checked'}`]">
              {{ moderationLabels[item.moderation_status] || 'Chưa kiểm tra' }}
            </span>
            <p v-if="item.moderation_reason" class="moderation-reason">{{ item.moderation_reason }}</p>
          </td>
          <td class="text-muted">{{ formatDate(item.created_at) }}</td>
          <td>
            <span :class="['badge-status', item.status ? 'badge-active' : 'badge-inactive']">
              {{ item.status ? 'Hiển thị' : 'Đang ẩn' }}
            </span>
          </td>
          <td>
            <div class="action-group">
              <BaseButton @click="emit('moderate', item)" customText="AI kiểm tra"
                customClass="btn-action btn-ai" />
              <BaseButton @click="emit('toggleStatus', item)" :customText="item.status ? 'Ẩn' : 'Duyệt'"
                :customClass="['btn-action', item.status ? 'btn-deactivate' : 'btn-activate']" />
              <BaseButton @click="emit('destroy', item.id)" customText="Xóa"
                customClass="btn-action btn-delete" />
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.admin-table-wrapper {
  width: 100%;
}

.loading-cell {
  height: 320px;
  padding: 0 !important;
}

.empty-cell {
  color: var(--text-muted);
  padding: 48px 0 !important;
  text-align: center;
}

.user-name,
.subtext {
  display: block;
}

.subtext,
.reply-to {
  color: var(--text-muted);
  font-size: 12px;
  margin: 4px 0 0;
}

.post-link {
  color: var(--text-main);
  display: -webkit-box;
  font-weight: 600;
  max-width: 210px;
  overflow: hidden;
  text-decoration: none;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.content-cell {
  color: var(--text-main);
  display: -webkit-box;
  line-height: 1.5;
  margin: 5px 0 0;
  max-width: 260px;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
}

.badge-status,
.badge-moderation {
  border-radius: 999px;
  display: inline-flex;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 12px;
  white-space: nowrap;
}

.badge-active,
.moderation-approved {
  background: #dcfce7;
  color: #166534;
}

.badge-inactive,
.moderation-not_checked,
.moderation-hidden {
  background: #f1f5f9;
  color: #475569;
}

.moderation-flagged {
  background: #fee2e2;
  color: #b91c1c;
}

.moderation-pending {
  background: #fef3c7;
  color: #b45309;
}

.moderation-reason {
  color: #64748b;
  font-size: 12px;
  line-height: 1.4;
  margin: 7px 0 0;
  max-width: 205px;
}

.action-group {
  display: flex;
  gap: 8px;
}

:deep(.btn-ai) {
  background: #eff6ff;
  color: #2563eb;
}
</style>
