import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useNotify } from '@/composables/useNotify'
import { useTokenStore } from '@/stores/token'

export const useProductVariantStore = defineStore('productVariant', () => {
  const tokenStore = useTokenStore()
  const productVariants = ref([])
  const pagination = ref({
    total: 0,
    last_page: 1,
  })

  const toast = useNotify()

  // ================= GET LIST =================
  const index = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/product-variants`, {
        params,
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      if (res.status === 200 && res.data) {
        const data = res.data.data
        const meta = res.data.meta
        
        productVariants.value = data.data ?? data
        pagination.value.total = meta?.total ?? data.total ?? productVariants.value.length
        pagination.value.last_page = meta?.last_page ?? data.last_page ?? 1
      } else {
        toast.error('Lỗi khi tải danh sách biến thể')
      }
    } catch (error) {
      toast.error('Lỗi khi tải danh sách biến thể')
      console.error(error)
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/product-variants`, data, {
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
      const res = await axios.post(`${API_URL}/product-variants/${id}`, data, {
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
      const res = await axios.delete(`${API_URL}/product-variants/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    productVariants,
    pagination,
    index,
    store,
    update,
    destroy,
  }
})
