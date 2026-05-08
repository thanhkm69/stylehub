import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useNotify } from '@/composables/useNotify'
import { useTokenStore } from '@/stores/token'

export const useProductImageStore = defineStore('productImage', () => {
  const tokenStore = useTokenStore()
  const productImages = ref([])

  const toast = useNotify()

  // ================= GET LIST =================
  const index = async (product_id) => {
    try {
      const res = await axios.get(`${API_URL}/product-images`, {
        params: { product_id },
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      if (res.status === 200 && res.data.success) {
        productImages.value = res.data.data
      } else {
        toast.error('Lỗi khi tải danh sách hình ảnh')
      }
    } catch (error) {
      toast.error('Lỗi khi tải danh sách hình ảnh')
      console.error(error)
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/product-images`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= UPDATE =================
  const update = async (id, data) => {
    try {
      const res = await axios.post(`${API_URL}/product-images/${id}`, data, {
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
      const res = await axios.delete(`${API_URL}/product-images/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    productImages,
    index,
    store,
    update,
    destroy,
  }
})
