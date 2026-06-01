<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import BaseLoading from '@/components/base/BaseLoading.vue'
import { useNotify } from '@/composables/useNotify'
import { swalConfirmDelete } from '@/composables/useSwal'
import { usePostCommentStore } from '@/stores/postComment'
import { useTokenStore } from '@/stores/token'

const props = defineProps({
  postSlug: {
    type: String,
    required: true,
  },
})

const store = usePostCommentStore()
const tokenStore = useTokenStore()
const toast = useNotify()
const content = ref('')
const submitting = ref(false)
const editId = ref(null)
const editContent = ref('')
const replyParentId = ref(null)
const replyContent = ref('')
const submittingReply = ref(false)

const isAuthenticated = computed(() => Boolean(tokenStore.token))
const currentUserId = computed(() => tokenStore.user?.data?.id ?? tokenStore.user?.id)
const comments = computed(() => store.comments)
const pagination = computed(() => store.pagination)

const loadComments = async (page = 1) => {
  await store.index(props.postSlug, page)
}

const formatDate = (value) => {
  return new Date(value).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

const submitComment = async () => {
  if (!content.value.trim()) {
    toast.error('Vui lòng nhập nội dung bình luận.')
    return
  }

  submitting.value = true
  const response = await store.create(props.postSlug, content.value.trim())
  submitting.value = false

  if (!response.success) {
    toast.error(response.message || 'Không thể đăng bình luận.')
    return
  }

  content.value = ''
  toast.success(response.message)
  await loadComments()
}

const startEdit = (comment) => {
  editId.value = comment.id
  editContent.value = comment.content
}

const cancelEdit = () => {
  editId.value = null
  editContent.value = ''
}

const saveEdit = async () => {
  if (!editContent.value.trim()) {
    toast.error('Nội dung bình luận không được để trống.')
    return
  }

  const response = await store.update(editId.value, editContent.value.trim())

  if (!response.success) {
    toast.error(response.message || 'Không thể sửa bình luận.')
    return
  }

  cancelEdit()
  toast.success(response.message)
  await loadComments(pagination.value.current_page)
}

const removeComment = async (comment) => {
  const result = await swalConfirmDelete('Xác nhận', 'Bạn có chắc xóa bình luận này không?')

  if (!result.isConfirmed) {
    return
  }

  const response = await store.destroy(comment.id)

  if (!response.success) {
    toast.error(response.message || 'Không thể xóa bình luận.')
    return
  }

  toast.success(response.message)
  await loadComments(pagination.value.current_page)
}

const startReply = (comment) => {
  replyParentId.value = comment.id
  replyContent.value = ''
}

const cancelReply = () => {
  replyParentId.value = null
  replyContent.value = ''
}

const submitReply = async () => {
  if (!replyContent.value.trim()) {
    toast.error('Vui lòng nhập nội dung trả lời.')
    return
  }

  submittingReply.value = true
  const response = await store.create(props.postSlug, replyContent.value.trim(), replyParentId.value)
  submittingReply.value = false

  if (!response.success) {
    toast.error(response.message || 'Không thể đăng trả lời.')
    return
  }

  cancelReply()
  toast.success(response.message)
  await loadComments(pagination.value.current_page)
}

onMounted(async () => {
  if (tokenStore.token && !tokenStore.user) {
    await tokenStore.getUser()
  }

  await loadComments()
})

watch(
  () => props.postSlug,
  () => loadComments(),
)
</script>

<template>
  <section class="comment-section" aria-label="Bình luận bài viết">
    <div class="comment-heading">
      <div>
        <span class="comment-kicker">Thảo luận</span>
        <h2>Bình luận <span>({{ pagination.total }})</span></h2>
      </div>
      <p>Chia sẻ cảm nhận và mẹo phối đồ của bạn cùng cộng đồng StyleHub.</p>
    </div>

    <form v-if="isAuthenticated" class="comment-form" @submit.prevent="submitComment">
      <textarea v-model="content" maxlength="1000" rows="4"
        placeholder="Viết bình luận của bạn..." aria-label="Nội dung bình luận"></textarea>
      <div class="comment-form-footer">
        <span>{{ content.length }}/1000</span>
        <button type="submit" :disabled="submitting">
          {{ submitting ? 'Đang đăng...' : 'Đăng bình luận' }}
        </button>
      </div>
    </form>

    <div v-else class="login-prompt">
      <p>Đăng nhập để tham gia bình luận bài viết này.</p>
      <RouterLink to="/login">Đăng nhập</RouterLink>
    </div>

    <BaseLoading v-if="store.loading" text="Đang tải bình luận..." />

    <div v-else-if="comments.length" class="comment-list">
      <article v-for="comment in comments" :key="comment.id" class="comment-item">
        <div class="comment-avatar">{{ comment.user?.name?.charAt(0)?.toUpperCase() || 'U' }}</div>
        <div class="comment-main">
          <header>
            <strong>{{ comment.user?.name || 'Người dùng' }}</strong>
            <time>{{ formatDate(comment.created_at) }}</time>
          </header>

          <template v-if="editId === comment.id">
            <textarea v-model="editContent" maxlength="1000" rows="3"></textarea>
            <div class="comment-edit-actions">
              <button class="save-btn" type="button" @click="saveEdit">Lưu</button>
              <button type="button" @click="cancelEdit">Hủy</button>
            </div>
          </template>
          <p v-else>{{ comment.content }}</p>

          <div v-if="editId !== comment.id" class="comment-actions">
            <button v-if="isAuthenticated" type="button" @click="startReply(comment)">Trả lời</button>
            <button v-if="currentUserId === comment.user?.id" type="button" @click="startEdit(comment)">Sửa</button>
            <button v-if="currentUserId === comment.user?.id" type="button" @click="removeComment(comment)">Xóa</button>
          </div>

          <form v-if="replyParentId === comment.id" class="reply-form" @submit.prevent="submitReply">
            <textarea v-model="replyContent" maxlength="1000" rows="3"
              placeholder="Viết câu trả lời của bạn..."></textarea>
            <div class="comment-edit-actions">
              <button class="save-btn" type="submit" :disabled="submittingReply">
                {{ submittingReply ? 'Đang gửi...' : 'Gửi trả lời' }}
              </button>
              <button type="button" @click="cancelReply">Hủy</button>
            </div>
          </form>

          <div v-if="comment.replies?.length" class="reply-list">
            <article v-for="reply in comment.replies" :key="reply.id" class="reply-item">
              <div class="comment-avatar reply-avatar">{{ reply.user?.name?.charAt(0)?.toUpperCase() || 'U' }}</div>
              <div class="comment-main">
                <header>
                  <strong>{{ reply.user?.name || 'Người dùng' }}</strong>
                  <time>{{ formatDate(reply.created_at) }}</time>
                </header>
                <template v-if="editId === reply.id">
                  <textarea v-model="editContent" maxlength="1000" rows="3"></textarea>
                  <div class="comment-edit-actions">
                    <button class="save-btn" type="button" @click="saveEdit">Lưu</button>
                    <button type="button" @click="cancelEdit">Hủy</button>
                  </div>
                </template>
                <p v-else>{{ reply.content }}</p>
                <div v-if="currentUserId === reply.user?.id && editId !== reply.id" class="comment-actions">
                  <button type="button" @click="startEdit(reply)">Sửa</button>
                  <button type="button" @click="removeComment(reply)">Xóa</button>
                </div>
              </div>
            </article>
          </div>
        </div>
      </article>
    </div>

    <div v-else class="comment-empty">Chưa có bình luận nào. Hãy là người đầu tiên chia sẻ cảm nhận.</div>

    <div v-if="pagination.last_page > 1" class="comment-pagination">
      <button type="button" :disabled="pagination.current_page === 1"
        @click="loadComments(pagination.current_page - 1)">Trước</button>
      <span>{{ pagination.current_page }} / {{ pagination.last_page }}</span>
      <button type="button" :disabled="pagination.current_page === pagination.last_page"
        @click="loadComments(pagination.current_page + 1)">Sau</button>
    </div>
  </section>
</template>

<style scoped>
.comment-section {
  margin: clamp(3rem, 6vw, 4.5rem) auto 0;
  max-width: 1120px;
}

.comment-heading {
  align-items: end;
  border-bottom: 1px solid #ece4db;
  display: flex;
  gap: 2rem;
  justify-content: space-between;
  margin-bottom: 1.7rem;
  padding-bottom: 1.35rem;
}

.comment-kicker {
  color: #826246;
  display: block;
  font-size: 0.73rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
}

.comment-heading h2 {
  color: #141312;
  font-size: clamp(1.55rem, 2.4vw, 2rem);
  font-weight: 700;
  letter-spacing: -0.045em;
  margin: 0;
}

.comment-heading h2 span {
  color: #80776d;
  font-weight: 500;
}

.comment-heading p {
  color: #6e675e;
  margin: 0;
  max-width: 420px;
  text-align: right;
}

.comment-form,
.login-prompt {
  background: #fff;
  border: 1px solid #ebe3d9;
  border-radius: 20px;
  margin-bottom: 1.6rem;
  padding: clamp(1.1rem, 3vw, 1.45rem);
}

.comment-form textarea,
.comment-main textarea {
  border: 1px solid #e4dbd1;
  border-radius: 13px;
  color: #282522;
  font: inherit;
  line-height: 1.65;
  outline: none;
  padding: 0.85rem 1rem;
  resize: vertical;
  transition: border-color 0.2s;
  width: 100%;
}

.comment-form textarea:focus,
.comment-main textarea:focus {
  border-color: #222;
}

.comment-form-footer {
  align-items: center;
  color: #91887f;
  display: flex;
  font-size: 0.86rem;
  justify-content: space-between;
  margin-top: 0.85rem;
}

.comment-form-footer button,
.login-prompt a {
  background: #111;
  border: 0;
  border-radius: 999px;
  color: #fff;
  font-weight: 600;
  padding: 0.7rem 1.3rem;
  text-decoration: none;
}

.comment-form-footer button:disabled {
  opacity: 0.6;
}

.login-prompt {
  align-items: center;
  display: flex;
  justify-content: space-between;
}

.login-prompt p {
  color: #605b55;
  margin: 0;
}

.comment-list {
  display: grid;
  gap: 0.9rem;
}

.comment-item {
  background: #fff;
  border: 1px solid #eee7df;
  border-radius: 18px;
  display: flex;
  gap: 1rem;
  padding: 1.15rem 1.2rem;
}

.comment-avatar {
  align-items: center;
  background: #f0e9e1;
  border-radius: 50%;
  color: #604c3b;
  display: inline-flex;
  flex: 0 0 44px;
  font-weight: 700;
  height: 44px;
  justify-content: center;
}

.comment-main {
  flex: 1;
  min-width: 0;
}

.comment-main header {
  align-items: center;
  display: flex;
  gap: 0.8rem;
  margin-bottom: 0.55rem;
}

.comment-main time {
  color: #91887e;
  font-size: 0.86rem;
}

.comment-main p {
  color: #443f39;
  line-height: 1.72;
  margin: 0;
  white-space: pre-wrap;
}

.comment-actions,
.comment-edit-actions {
  display: flex;
  gap: 0.8rem;
  margin-top: 0.7rem;
}

.comment-actions button,
.comment-edit-actions button {
  background: transparent;
  border: 0;
  color: #756859;
  font-size: 0.88rem;
  font-weight: 600;
  padding: 0;
}

.comment-edit-actions .save-btn {
  color: #111;
}

.reply-form {
  background: #faf7f3;
  border-radius: 14px;
  margin-top: 1rem;
  padding: 0.85rem;
}

.reply-list {
  border-left: 2px solid #eee5dc;
  display: grid;
  gap: 0.85rem;
  margin-top: 1rem;
  padding-left: 1rem;
}

.reply-item {
  display: flex;
  gap: 0.8rem;
}

.reply-avatar {
  flex-basis: 36px;
  height: 36px;
}

.comment-empty {
  background: #fff;
  border: 1px dashed #ddd1c5;
  border-radius: 18px;
  color: #756e66;
  padding: 2.2rem 1rem;
  text-align: center;
}

.comment-pagination {
  align-items: center;
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1.5rem;
}

.comment-pagination button {
  background: #fff;
  border: 1px solid #e7ded5;
  border-radius: 999px;
  padding: 0.55rem 1rem;
}

.comment-pagination button:disabled {
  opacity: 0.45;
}

@media (max-width: 768px) {
  .comment-heading,
  .login-prompt {
    align-items: flex-start;
    flex-direction: column;
  }

  .comment-heading p {
    text-align: left;
  }
}
</style>
