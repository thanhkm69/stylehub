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
    _currentListeningChannel: null,
    // Admin notifications
    adminUnreadCount: 0,
    adminNotifications: [],
    _adminNotifListening: false,
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
      const channelName = `chat.${conversationId}`

      // Don't re-subscribe if already listening to this channel
      if (this._currentListeningChannel === channelName) {
        return
      }

      // Leave old channel first
      if (this._currentListeningChannel) {
        echo.leave(this._currentListeningChannel)
      }

      this._currentListeningChannel = channelName

      echo.private(channelName)
        .listen('MessageSent', (e) => {
          const messageExists = this.messages.some(m => Number(m.id) === Number(e.id))
          if (!messageExists) {
            this.messages.push(e)

            // Update the conversation in sidebar (Messenger-style realtime preview)
            const conv = this.adminConversations.find(c => Number(c.id) === Number(conversationId))
            if (conv) {
              conv.last_message_at = e.created_at
              conv.messages = [{ message: e.message }]
            }
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
    
    async setActiveAdminConversation(conversationId) {
      this.activeAdminConversationId = conversationId
      await this.fetchMessages(conversationId)
      this.listenToConversation(conversationId)
      // Reset unread count locally for this conversation (API already marked as read)
      const conv = this.adminConversations.find(c => Number(c.id) === Number(conversationId))
      if (conv) {
        conv.unread_count = 0
      }
      // Remove notifications for this conversation from the bell dropdown
      this.adminNotifications = this.adminNotifications.filter(n => Number(n.conversation_id) !== Number(conversationId))
      // Refresh global unread count for the bell badge
      this.fetchAdminUnreadCount()
    },

    // Admin notification methods
    async fetchAdminUnreadCount() {
      const tokenStore = useTokenStore()
      try {
        const res = await axios.get(`${API_URL}/admin/chats/unread-count`, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          this.adminUnreadCount = res.data.data.count
        }
      } catch (error) {
        console.error('Failed to fetch unread count', error)
      }
    },

    listenForAdminNotifications() {
      if (this._adminNotifListening) return
      this._adminNotifListening = true

      echo.private('admin.notifications')
        .listen('NewSupportMessage', (e) => {
          // Update the conversation in sidebar (Messenger-style unread indicator)
          const conv = this.adminConversations.find(c => Number(c.id) === Number(e.conversation_id))
          if (conv) {
            conv.last_message_at = e.created_at
            conv.messages = [{ message: e.message }]

            // Only increment count and show bell notifications if we are NOT currently chatting with this user
            if (Number(this.activeAdminConversationId) !== Number(e.conversation_id)) {
              conv.unread_count = (conv.unread_count || 0) + 1
              this.adminUnreadCount++
              this.adminNotifications.unshift({
                id: e.id,
                conversation_id: e.conversation_id,
                sender_name: e.sender_name,
                message: e.message,
                created_at: e.created_at,
              })
              // Keep only last 20 notifications
              if (this.adminNotifications.length > 20) {
                this.adminNotifications = this.adminNotifications.slice(0, 20)
              }
            }
          } else {
            // New conversation created by a new customer
            this.fetchAdminConversations()

            this.adminUnreadCount++
            this.adminNotifications.unshift({
              id: e.id,
              conversation_id: e.conversation_id,
              sender_name: e.sender_name,
              message: e.message,
              created_at: e.created_at,
            })
          }
        })
    },
  }
})

