import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useNotify } from '@/composables/useNotify'
import { useTokenStore } from '@/stores/token'

export const useBannerStore = defineStore('banner', () => {
  const tokenStore = useTokenStore()
  const banners = ref([])
  const pagination = ref({
    total: 0,
    last_page: 1,
  })

  const toast = useNotify()

  // ================= GET LIST =================
  const index = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/banners`, {
        params,
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })

      const data = res.data.data
      const meta = res.data.meta

      banners.value = data.data ?? data
      pagination.value.total = meta?.total ?? data.total ?? banners.value.length
      pagination.value.last_page = meta?.last_page ?? data.last_page ?? 1

      if (res.status !== 200 || !res.data.success) {
        toast.error('Lỗi khi tải danh sách banner')
      }
    } catch (error) {
      toast.error('Lỗi khi tải danh sách banner')
      console.error(error)
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/banners`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= SHOW =================
  const show = async (id) => {
    try {
      const res = await axios.get(`${API_URL}/banners/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      if (res.status === 200 && res.data.success) {
        return res.data
      } else {
        toast.error('Lỗi khi lấy chi tiết banner')
        return false
      }
    } catch (error) {
      toast.error('Lỗi khi lấy chi tiết banner')
      console.error(error)
      return false
    }
  }

  // ================= UPDATE =================
  const update = async (id, data) => {
    try {
      const res = await axios.post(`${API_URL}/banners/${id}`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= DELETE =================
  const destroy = async (id) => {
    try {
      const res = await axios.delete(`${API_URL}/banners/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    banners,
    pagination,
    index,
    store,
    show,
    update,
    destroy,
  }
})
