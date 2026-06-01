import { defineStore } from 'pinia'
import axios from 'axios'
import echo from '@/plugins/echo'
import { API_URL } from '@/config/env'
import { useTokenStore } from './token'

export const useChatStore = defineStore('chat', {
  state: () => ({
    conversation: null,
    messages: [],
    isOpen: false,
    adminConversations: [],
    activeAdminConversationId: null,
  }),

  actions: {
    toggleChat() {
      this.isOpen = !this.isOpen
      if (this.isOpen && !this.conversation) {
        this.fetchMyConversation()
      }
    },

    async fetchMyConversation() {
      const tokenStore = useTokenStore()
      try {
        const res = await axios.get(`${API_URL}/chat/my-conversation`, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          this.conversation = res.data.data
          this.listenToConversation(this.conversation.id)
          await this.fetchMessages(this.conversation.id)
        }
      } catch (error) {
        console.error('Failed to fetch conversation', error)
      }
    },

    async fetchMessages(conversationId) {
      const tokenStore = useTokenStore()
      try {
        const res = await axios.get(`${API_URL}/chat/${conversationId}/messages`, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          this.messages = res.data.data
        }
      } catch (error) {
        console.error('Failed to fetch messages', error)
      }
    },

    async sendMessage(conversationId, message) {
      const tokenStore = useTokenStore()
      try {
        const res = await axios.post(`${API_URL}/chat/${conversationId}/messages`, { message }, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          this.messages.push(res.data.data)
        }
      } catch (error) {
        console.error('Failed to send message', error)
      }
    },

    listenToConversation(conversationId) {
      echo.private(`chat.${conversationId}`)
        .listen('MessageSent', (e) => {
          const messageExists = this.messages.some(m => m.id === e.id)
          if (!messageExists) {
            this.messages.push(e)
          }
        })
    },

    // Admin methods
    async fetchAdminConversations() {
      const tokenStore = useTokenStore()
      try {
        const res = await axios.get(`${API_URL}/admin/chats`, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          this.adminConversations = res.data.data
        }
      } catch (error) {
        console.error('Failed to fetch admin conversations', error)
      }
    },
    
    setActiveAdminConversation(conversationId) {
      this.activeAdminConversationId = conversationId
      this.fetchMessages(conversationId)
      this.listenToConversation(conversationId)
    }
  }
})
