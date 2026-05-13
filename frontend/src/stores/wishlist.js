import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useWishlistStore = defineStore('wishlist', () => {
  const tokenStore = useTokenStore()
  const wishlists = ref([])
  const wishlistIds = ref(new Set())
  const loading = ref(false)
  const loadingIds = ref(new Set())
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
  })

  const isWishlisted = (productId) => wishlistIds.value.has(productId)
  const isLoading = (productId) => loadingIds.value.has(productId)

  // ================= GET LIST =================
  const index = async (page = 1) => {
    if (!tokenStore.token) return
    loading.value = true
    try {
      const res = await axios.get(`${API_URL}/wishlist?page=${page}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` }
      })

      const { data, meta } = res.data
      wishlists.value = data
      pagination.value = {
        current_page: meta?.current_page || 1,
        last_page: meta?.last_page || 1,
        total: meta?.total || 0,
      }

      await ids()
      return res.data
    } catch (error) {
      return error.response?.data || {}
    } finally {
      loading.value = false
    }
  }

  // ================= GET IDS =================
  const ids = async () => {
    if (!tokenStore.token) return
    try {
      const res = await axios.get(`${API_URL}/wishlist/ids`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` }
      })
      wishlistIds.value = new Set(res.data.data)
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= TOGGLE =================
  const toggle = async (productId) => {
    if (!tokenStore.token) {
      return { success: false, message: 'Vui lòng đăng nhập', requireLogin: true }
    }

    if (loadingIds.value.has(productId)) return
    loadingIds.value.add(productId)

    try {
      const res = await axios.post(`${API_URL}/wishlist/toggle`, { product_id: productId }, {
        headers: { Authorization: `Bearer ${tokenStore.token}` }
      })

      const { status } = res.data
      if (status === 'added') {
        wishlistIds.value.add(productId)
        pagination.value.total++
      } else {
        wishlistIds.value.delete(productId)
        wishlists.value = wishlists.value.filter(item => item.product.id !== productId)
        pagination.value.total = Math.max(0, pagination.value.total - 1)
      }

      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Có lỗi xảy ra' }
    } finally {
      loadingIds.value.delete(productId)
    }
  }

  // ================= DELETE =================
  const destroy = async (wishlistId, productId) => {
    if (!tokenStore.token) return
    loadingIds.value.add(productId)

    try {
      const res = await axios.delete(`${API_URL}/wishlist/${wishlistId}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` }
      })

      wishlists.value = wishlists.value.filter(item => item.id !== wishlistId)
      wishlistIds.value.delete(productId)
      pagination.value.total = Math.max(0, pagination.value.total - 1)

      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Có lỗi xảy ra' }
    } finally {
      loadingIds.value.delete(productId)
    }
  }

  return {
    wishlists,
    wishlistIds,
    loading,
    loadingIds,
    pagination,
    isWishlisted,
    isLoading,
    index,
    ids,
    toggle,
    destroy
  }
})
