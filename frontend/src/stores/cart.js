import { ref, watch } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'
import { useLocalStorage } from '@vueuse/core'

export const useCartStore = defineStore('cart', () => {
  const tokenStore = useTokenStore()
  const items = useLocalStorage('cart_items', [])
  const summary = useLocalStorage('cart_summary', {
    total_items: 0,
    total_price: 0
  })
  
  const loading = ref(false)

  // ================= GET LIST =================
  const index = async () => {
    if (!tokenStore.token) return
    loading.value = true
    try {
      const res = await axios.get(`${API_URL}/cart`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` }
      })
      items.value = res.data.data
      summary.value = res.data.cart_summary
      return res.data
    } catch (error) {
      return error.response?.data || {}
    } finally {
      loading.value = false
    }
  }

  // ================= ADD TO CART =================
  const store = async (payload) => {
    if (tokenStore.token) {
      try {
        const res = await axios.post(`${API_URL}/cart`, payload, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          await index()
        }
        return res.data
      } catch (error) {
        return error.response?.data || { success: false, message: 'Có lỗi xảy ra' }
      }
    } 
    
    return { 
      success: false, 
      message: 'Vui lòng đăng nhập để mua hàng', 
      requireLogin: true 
    }
  }

  // ================= UPDATE QUANTITY =================
  const update = async (cartId, quantity) => {
    if (!tokenStore.token) return { success: false }
    
    try {
      const res = await axios.post(`${API_URL}/cart/${cartId}`, { quantity }, {
        headers: { Authorization: `Bearer ${tokenStore.token}` }
      })
      if (res.data.success) {
        summary.value = res.data.cart_summary
        const itemIndex = items.value.findIndex(i => i.id === cartId)
        if (itemIndex !== -1) {
          items.value[itemIndex] = res.data.data
        }
      }
      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Có lỗi xảy ra' }
    }
  }

  // ================= REMOVE ITEM =================
  const destroy = async (cartId) => {
    if (!tokenStore.token) return { success: false }

    try {
      const res = await axios.delete(`${API_URL}/cart/${cartId}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` }
      })
      if (res.data.success) {
        summary.value = res.data.cart_summary
        items.value = items.value.filter(i => i.id !== cartId)
      }
      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Có lỗi xảy ra' }
    }
  }

  // ================= CLEAR CART =================
  const clear = async () => {
    if (!tokenStore.token) {
      items.value = []
      summary.value = { total_items: 0, total_price: 0 }
      return { success: true }
    }

    try {
      const res = await axios.delete(`${API_URL}/cart/clear`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` }
      })
      if (res.data.success) {
        items.value = []
        summary.value = { total_items: 0, total_price: 0 }
      }
      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Có lỗi xảy ra' }
    }
  }

  // ================= CLEAR LOCAL (Sau khi đặt hàng) =================
  const clearLocal = () => {
    items.value = []
    summary.value = { total_items: 0, total_price: 0 }
  }

  return {
    items,
    loading,
    summary,
    index,
    store,
    update,
    destroy,
    clear,
    clearLocal
  }
})
