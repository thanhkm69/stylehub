import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useReviewStore = defineStore('review', () => {
  const tokenStore = useTokenStore()
  const reviews = ref([])
  const pagination = ref({
    total: 0,
    last_page: 1,
  })

  // ================= GET LIST PUBLIC =================
  const index = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/reviews`, {
        params,
      })

      const data = res.data.data
      const pagin = res.data.pagination

      reviews.value = data ?? []
      pagination.value.total = pagin?.total ?? reviews.value.length
      pagination.value.last_page = pagin?.last_page ?? 1
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= SHOW =================
  const show = async (id) => {
    try {
      const res = await axios.get(`${API_URL}/reviews/${id}`)
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= CREATE =================
  const store = async (formData) => {
    try {
      const res = await axios.post(`${API_URL}/reviews`, formData, {
        headers: {
          Authorization: `Bearer ${tokenStore.token}`,
          'Content-Type': 'multipart/form-data',
        },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= UPDATE =================
  const update = async (id, formData) => {
    try {
      const res = await axios.post(`${API_URL}/reviews/${id}`, formData, {
        headers: {
          Authorization: `Bearer ${tokenStore.token}`,
          'Content-Type': 'multipart/form-data',
        },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= DELETE =================
  const destroy = async (id) => {
    try {
      const res = await axios.delete(`${API_URL}/reviews/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= GET LIST ADMIN =================
  const adminIndex = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/admin/reviews`, {
        params,
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })

      const data = res.data.data
      const pagin = res.data.pagination

      reviews.value = data ?? []
      pagination.value.total = pagin?.total ?? reviews.value.length
      pagination.value.last_page = pagin?.last_page ?? 1
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= TOGGLE STATUS ADMIN =================
  const toggleStatus = async (id) => {
    try {
      const res = await axios.post(`${API_URL}/admin/reviews/${id}/toggle-status`, {}, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    reviews,
    pagination,
    index,
    show,
    store,
    update,
    destroy,
    adminIndex,
    toggleStatus,
  }
})
