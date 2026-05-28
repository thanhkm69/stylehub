<script setup>
import { ref, onMounted, watch, nextTick, computed } from 'vue'
import { useChatStore } from '@/stores/chat'
import { useTokenStore } from '@/stores/token'

const chatStore = useChatStore()
const tokenStore = useTokenStore()
const messageInput = ref('')
const messagesContainer = ref(null)

onMounted(async () => {
  await chatStore.fetchAdminConversations()
})

const user = computed(() => tokenStore.user?.data)

const activeConversation = computed(() => {
  return chatStore.adminConversations.find(c => c.id === chatStore.activeAdminConversationId)
})

const selectConversation = (id) => {
  chatStore.setActiveAdminConversation(id)
}

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

watch(() => chatStore.messages, () => {
  scrollToBottom()
}, { deep: true })

const sendMessage = async () => {
  if (!messageInput.value.trim() || !chatStore.activeAdminConversationId) return
  
  await chatStore.sendMessage(chatStore.activeAdminConversationId, messageInput.value)
  messageInput.value = ''
  
  // Update last message in the list
  const idx = chatStore.adminConversations.findIndex(c => c.id === chatStore.activeAdminConversationId)
  if (idx !== -1) {
    chatStore.adminConversations[idx].last_message_at = new Date().toISOString()
  }
}
</script>

<template>
  <div class="admin-page">
    <!-- Header -->
    <div class="admin-header">
      <h1 class="admin-title">Hỗ trợ Trực tuyến</h1>
      <p class="admin-desc">Giải đáp thắc mắc và hỗ trợ khách hàng trực tiếp theo thời gian thực.</p>
    </div>

    <!-- Chat Card Layout -->
    <div class="chat-layout">
      <!-- Sidebar -->
      <div class="chat-sidebar">
        <div class="sidebar-header">
          <h3>Hội thoại</h3>
        </div>
        <div class="conversation-list">
          <div 
            v-for="conv in chatStore.adminConversations" 
            :key="conv.id"
            :class="['conversation-item', { active: chatStore.activeAdminConversationId === conv.id }]"
            @click="selectConversation(conv.id)"
          >
            <div class="avatar">
              {{ conv.user?.name?.charAt(0).toUpperCase() || 'K' }}
            </div>
            <div class="conv-info">
              <h4>{{ conv.user?.name || 'Khách hàng' }}</h4>
              <p v-if="conv.messages && conv.messages.length > 0" class="last-message">
                {{ conv.messages[0].message }}
              </p>
              <p v-else class="last-message">Đã bắt đầu hội thoại</p>
            </div>
            <div class="time">
              {{ new Date(conv.last_message_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
            </div>
          </div>
          <div v-if="chatStore.adminConversations.length === 0" class="empty-list">
            Chưa có cuộc hội thoại nào
          </div>
        </div>
      </div>

      <!-- Main Chat Area -->
      <div class="chat-main">
        <template v-if="chatStore.activeAdminConversationId">
          <div class="chat-header-main">
            <div class="user-info">
              <div class="avatar">{{ activeConversation?.user?.name?.charAt(0).toUpperCase() || 'K' }}</div>
              <div>
                <h4>{{ activeConversation?.user?.name || 'Khách hàng' }}</h4>
                <p>{{ activeConversation?.user?.email }}</p>
              </div>
            </div>
          </div>
          
          <div class="chat-messages" ref="messagesContainer">
            <div 
              v-for="msg in chatStore.messages" 
              :key="msg.id"
              :class="['message', msg.sender_id === user?.id ? 'sent' : 'received']"
            >
              <div class="msg-content">{{ msg.message }}</div>
              <div class="msg-time">{{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</div>
            </div>
          </div>

          <div class="chat-input-area">
            <input 
              v-model="messageInput" 
              type="text" 
              placeholder="Nhập câu trả lời phản hồi..." 
              @keyup.enter="sendMessage"
            />
            <button class="btn btn-primary send-btn" @click="sendMessage" :disabled="!messageInput.trim()">
              Gửi
            </button>
          </div>
        </template>
        <div v-else class="empty-chat-main">
          <i class="ph ph-chat-circle-dots"></i>
          <p>Chọn một cuộc hội thoại từ danh sách để bắt đầu trò chuyện</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-page {
  padding: 24px 0;
  display: flex;
  flex-direction: column;
  height: calc(100vh - 100px);
}

.admin-header {
  margin-bottom: 24px;
  flex-shrink: 0;
}

.admin-title {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 8px;
  letter-spacing: -0.5px;
}

.admin-desc {
  color: var(--text-muted);
  font-size: 15px;
}

/* Chat Layout structure */
.chat-layout {
  display: flex;
  flex: 1;
  background: var(--surface);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
  overflow: hidden;
}

.chat-sidebar {
  width: 320px;
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  background: var(--surface);
  flex-shrink: 0;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid var(--border);
  background: var(--surface);
}

.sidebar-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
  color: var(--text-main);
}

.conversation-list {
  flex: 1;
  overflow-y: auto;
}

.conversation-item {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  gap: 12px;
  cursor: pointer;
  border-bottom: 1px solid var(--border);
  transition: var(--transition);
}

.conversation-item:hover {
  background: var(--background);
}

.conversation-item.active {
  background: var(--background);
  border-left: 4px solid var(--primary);
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--surface);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 600;
  flex-shrink: 0;
}

.conv-info {
  flex: 1;
  overflow: hidden;
}

.conv-info h4 {
  margin: 0 0 4px;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
}

.last-message {
  margin: 0;
  font-size: 13px;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.time {
  font-size: 12px;
  color: var(--text-muted);
}

.empty-list {
  padding: 32px;
  text-align: center;
  color: var(--text-muted);
  font-size: 14px;
}

/* Chat Main Area */
.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: var(--background);
}

.chat-header-main {
  padding: 18px 24px;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.user-info h4 {
  margin: 0 0 2px;
  font-size: 16px;
  font-weight: 600;
  color: var(--text-main);
}

.user-info p {
  margin: 0;
  font-size: 13px;
  color: var(--text-muted);
}

.chat-messages {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
  background: var(--background);
}

.message {
  max-width: 65%;
  display: flex;
  flex-direction: column;
}

.message.sent {
  align-self: flex-end;
  align-items: flex-end;
}

.message.received {
  align-self: flex-start;
  align-items: flex-start;
}

.msg-content {
  padding: 12px 18px;
  border-radius: 18px;
  font-size: 14px;
  line-height: 1.5;
  word-break: break-word;
}

.message.sent .msg-content {
  background: var(--primary);
  color: var(--surface);
  border-bottom-right-radius: 4px;
}

.message.received .msg-content {
  background: var(--surface);
  color: var(--text-main);
  border-bottom-left-radius: 4px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
}

.msg-time {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 4px;
}

/* Chat Input Area */
.chat-input-area {
  padding: 20px 24px;
  background: var(--surface);
  border-top: 1px solid var(--border);
  display: flex;
  gap: 16px;
  align-items: center;
}

.chat-input-area input {
  flex: 1;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 12px 20px;
  outline: none;
  font-size: 14px;
  background: var(--background);
  color: var(--text-main);
  transition: var(--transition);
}

.chat-input-area input:focus {
  border-color: var(--primary);
  background: var(--surface);
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.03);
}

.send-btn {
  padding: 12px 24px;
  border-radius: var(--radius-md);
  font-size: 14px;
  font-weight: 600;
  transition: var(--transition);
}

.send-btn:hover:not(:disabled) {
  transform: translateY(-1px);
}

.empty-chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  background: var(--background);
  padding: 32px;
}

.empty-chat-main i {
  font-size: 48px;
  margin-bottom: 16px;
  color: var(--text-muted);
  opacity: 0.7;
}

.empty-chat-main p {
  font-size: 15px;
}
</style>
