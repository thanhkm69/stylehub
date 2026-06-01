import { defineStore } from 'pinia'
import { ref } from 'vue'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const usePostCommentStore = defineStore('postComment', () => {
  const tokenStore = useTokenStore()
  const comments = ref([])
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
  })
  const moderationSettings = ref({
    enabled: false,
    configured: false,
    model: 'openai/gpt-oss-safeguard-20b',
    provider: 'Groq',
  })
  const loading = ref(false)

  const index = async (slug, page = 1) => {
    loading.value = true

    try {
      const response = await axios.get(`${API_URL}/blogs/${slug}/comments`, {
        params: { page },
      })

      comments.value = response.data.data ?? []
      pagination.value = response.data.pagination ?? pagination.value
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    } finally {
      loading.value = false
    }
  }

  const create = async (slug, content, parentId = null) => {
    try {
      const response = await axios.post(
        `${API_URL}/blogs/${slug}/comments`,
        { content, parent_id: parentId },
        { headers: { Authorization: `Bearer ${tokenStore.token}` } },
      )
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    }
  }

  const update = async (id, content) => {
    try {
      const response = await axios.put(
        `${API_URL}/post-comments/${id}`,
        { content },
        { headers: { Authorization: `Bearer ${tokenStore.token}` } },
      )
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    }
  }

  const destroy = async (id) => {
    try {
      const response = await axios.delete(`${API_URL}/post-comments/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    }
  }

  const adminIndex = async (params) => {
    loading.value = true

    try {
      const response = await axios.get(`${API_URL}/admin/post-comments`, {
        params,
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })

      comments.value = response.data.data ?? []
      pagination.value = response.data.pagination ?? pagination.value
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    } finally {
      loading.value = false
    }
  }

  const toggleStatus = async (id) => {
    try {
      const response = await axios.patch(
        `${API_URL}/admin/post-comments/${id}/toggle-status`,
        {},
        { headers: { Authorization: `Bearer ${tokenStore.token}` } },
      )
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    }
  }

  const adminDestroy = async (id) => {
    try {
      const response = await axios.delete(`${API_URL}/admin/post-comments/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    }
  }

  const getModerationSettings = async () => {
    try {
      const response = await axios.get(`${API_URL}/admin/post-comments/moderation/settings`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      moderationSettings.value = response.data.data
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    }
  }

  const toggleModeration = async () => {
    try {
      const response = await axios.patch(
        `${API_URL}/admin/post-comments/moderation/toggle`,
        {},
        { headers: { Authorization: `Bearer ${tokenStore.token}` } },
      )
      moderationSettings.value = response.data.data
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    }
  }

  const moderate = async (id) => {
    try {
      const response = await axios.patch(
        `${API_URL}/admin/post-comments/${id}/moderate`,
        {},
        { headers: { Authorization: `Bearer ${tokenStore.token}` } },
      )
      return response.data
    } catch (error) {
      return error.response?.data || { success: false }
    }
  }

  return {
    comments,
    pagination,
    moderationSettings,
    loading,
    index,
    create,
    update,
    destroy,
    adminIndex,
    toggleStatus,
    adminDestroy,
    getModerationSettings,
    toggleModeration,
    moderate,
  }
})
