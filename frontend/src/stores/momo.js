import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useMoMoStore = defineStore('momo', () => {
  const tokenStore = useTokenStore()
  
  const transaction = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // ================= CREATE PAYMENT =================
  const createPayment = async (orderId) => {
    loading.value = true
    error.value = null
    try {
      const res = await axios.post(`${API_URL}/momo/create-payment`, { order_id: orderId }, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Lỗi khi tạo liên kết thanh toán MoMo'
      return err.response?.data || { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  // ================= CHECK STATUS =================
  const checkStatus = async (orderId) => {
    loading.value = true
    error.value = null
    try {
      const res = await axios.get(`${API_URL}/momo/status/${orderId}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      if (res.data.success) {
        transaction.value = res.data.data
      }
      return res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Lỗi khi kiểm tra trạng thái thanh toán'
      return err.response?.data || { success: false, message: error.value }
    } finally {
      loading.value = false
    }
  }

  return {
    transaction,
    loading,
    error,
    createPayment,
    checkStatus,
  }
})
