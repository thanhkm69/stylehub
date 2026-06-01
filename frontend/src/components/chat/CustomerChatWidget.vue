<script setup>
import { ref, watch, nextTick, onMounted, computed } from 'vue'
import { useChatStore } from '@/stores/chat'
import { useTokenStore } from '@/stores/token'

const chatStore = useChatStore()
const tokenStore = useTokenStore()

const user = computed(() => tokenStore.user?.data)

const messageInput = ref('')
const messagesContainer = ref(null)

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

watch(() => chatStore.messages, () => {
  scrollToBottom()
}, { deep: true })

const sendMessage = () => {
  if (!messageInput.value.trim() || !chatStore.conversation) return
  
  chatStore.sendMessage(chatStore.conversation.id, messageInput.value)
  messageInput.value = ''
}

// Automatically scroll to bottom when opening chat
watch(() => chatStore.isOpen, (newVal) => {
  if (newVal) {
    scrollToBottom()
  }
})
</script>

<template>
  <div v-if="user" class="chat-widget">
    <!-- Chat Button -->
    <button class="chat-toggle-btn" @click="chatStore.toggleChat">
      <i class="ph ph-chat-circle-dots"></i>
    </button>

    <!-- Chat Box -->
    <div v-if="chatStore.isOpen" class="chat-box">
      <div class="chat-header">
        <div>
          <h4>Hỗ trợ khách hàng</h4>
          <p class="status">Đang trực tuyến</p>
        </div>
        <button class="close-btn" @click="chatStore.toggleChat">
          <i class="ph ph-x"></i>
        </button>
      </div>

      <div class="chat-messages" ref="messagesContainer">
        <template v-if="chatStore.messages.length > 0">
          <div 
            v-for="msg in chatStore.messages" 
            :key="msg.id"
            :class="['message', msg.sender_id === user?.id ? 'sent' : 'received']"
          >
            <div class="msg-content">{{ msg.message }}</div>
            <div class="msg-time">{{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</div>
          </div>
        </template>
        <div v-else class="empty-chat">
          <p>Xin chào! Chúng tôi có thể giúp gì cho bạn?</p>
        </div>
      </div>

      <div class="chat-input-area">
        <input 
          v-model="messageInput" 
          type="text" 
          placeholder="Nhập tin nhắn..." 
          @keyup.enter="sendMessage"
        />
        <button @click="sendMessage" :disabled="!messageInput.trim()">
          <i class="ph ph-paper-plane-right"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chat-widget {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1000;
}

.chat-toggle-btn {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: var(--text-main);
  color: white;
  border: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  transition: transform 0.2s;
}

.chat-toggle-btn:hover {
  transform: scale(1.05);
}

.chat-box {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 350px;
  height: 500px;
  background: white;
  border-radius: var(--radius-lg);
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-header {
  padding: 16px;
  background: var(--text-main);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chat-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.status {
  margin: 0;
  font-size: 12px;
  opacity: 0.8;
}

.close-btn {
  background: transparent;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
}

.chat-messages {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: var(--surface);
}

.message {
  max-width: 80%;
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
  padding: 10px 14px;
  border-radius: 18px;
  font-size: 14px;
}

.message.sent .msg-content {
  background: var(--text-main);
  color: white;
  border-bottom-right-radius: 4px;
}

.message.received .msg-content {
  background: white;
  color: var(--text-main);
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.msg-time {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 4px;
}

.empty-chat {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: var(--text-muted);
  font-size: 14px;
}

.chat-input-area {
  padding: 16px;
  background: white;
  border-top: 1px solid var(--border);
  display: flex;
  gap: 12px;
}

.chat-input-area input {
  flex: 1;
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 8px 16px;
  outline: none;
  font-size: 14px;
}

.chat-input-area button {
  background: var(--text-main);
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.chat-input-area button:disabled {
  background: var(--border);
  cursor: not-allowed;
}

@media (max-width: 576px) {
  .chat-box {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    border-radius: 0;
  }
}
</style>
