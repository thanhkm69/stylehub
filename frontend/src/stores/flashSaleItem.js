import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useFlashSaleItemStore = defineStore('flashSaleItem', () => {
  const tokenStore = useTokenStore()
  const flashSaleItems = ref([])

  // ================= GET LIST =================
  const index = async (flash_sale_id) => {
    try {
      const res = await axios.get(`${API_URL}/flash-sale-items`, {
        params: { flash_sale_id },
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      if (res.status === 200 && res.data.success) {
        flashSaleItems.value = res.data.data
      }
    } catch (error) {
      console.error('FlashSaleItemStore index error:', error)
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/flash-sale-items`, data, {
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
      const res = await axios.put(`${API_URL}/flash-sale-items/${id}`, data, {
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
      const res = await axios.delete(`${API_URL}/flash-sale-items/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    flashSaleItems,
    index,
    store,
    update,
    destroy,
  }
})
