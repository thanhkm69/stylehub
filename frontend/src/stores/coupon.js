import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useCouponStore = defineStore('coupon', () => {
  const tokenStore = useTokenStore()
  const coupons = ref([])
  const pagination = ref({
    total: 0,
    last_page: 1,
  })

  // ================= GET LIST =================
  const index = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/coupons`, {
        params,
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })

      const data = res.data.data
      const meta = res.data.meta

      coupons.value = data.data ?? data
      pagination.value.total = meta?.total ?? data.total ?? coupons.value.length
      pagination.value.last_page = meta?.last_page ?? data.last_page ?? 1
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/coupons`, data, {
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
      const res = await axios.get(`${API_URL}/coupons/${id}`, {
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
      const res = await axios.put(`${API_URL}/coupons/${id}`, data, {
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
      const res = await axios.delete(`${API_URL}/coupons/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    coupons,
    pagination,
    index,
    store,
    show,
    update,
    destroy,
  }
})
