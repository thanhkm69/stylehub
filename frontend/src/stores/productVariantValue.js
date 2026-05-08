import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useNotify } from '@/composables/useNotify'
import { useTokenStore } from '@/stores/token'

export const useProductVariantValueStore = defineStore('productVariantValue', () => {
  const tokenStore = useTokenStore()
  const productVariantValues = ref([])

  const toast = useNotify()

  // ================= GET LIST =================
  const index = async (product_variant_id) => {
    try {
      const res = await axios.get(`${API_URL}/product-variant-values`, {
        params: { product_variant_id },
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      if (res.status === 200 && res.data.success) {
        productVariantValues.value = res.data.data
      } else {
        toast.error('Lỗi khi tải danh sách giá trị thuộc tính')
      }
    } catch (error) {
      toast.error('Lỗi khi tải danh sách giá trị thuộc tính')
      console.error(error)
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/product-variant-values`, data, {
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
      const res = await axios.put(`${API_URL}/product-variant-values/${id}`, data, {
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
      const res = await axios.delete(`${API_URL}/product-variant-values/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    productVariantValues,
    index,
    store,
    update,
    destroy,
  }
})
