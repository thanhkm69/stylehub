import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useAdminOrderStore = defineStore('adminOrder', () => {
  const tokenStore = useTokenStore()
  const orders = ref([])
  const pagination = ref({
    total: 0,
    last_page: 1,
    current_page: 1
  })

  // ================= GET LIST =================
  const index = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/admin/orders`, {
        params,
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })

      const { data, meta } = res.data
      orders.value = data
      pagination.value.total = meta?.total ?? data.length
      pagination.value.last_page = meta?.last_page ?? 1
      pagination.value.current_page = meta?.current_page ?? 1
      
      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Lỗi khi tải danh sách đơn hàng' }
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/admin/orders`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Lỗi khi tạo đơn hàng' }
    }
  }

  // ================= SHOW =================
  const show = async (id) => {
    try {
      const res = await axios.get(`${API_URL}/admin/orders/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Lỗi khi tải chi tiết đơn hàng' }
    }
  }

  // ================= UPDATE =================
  const update = async (id, data) => {
    try {
      const res = await axios.post(`${API_URL}/admin/orders/${id}`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Lỗi khi cập nhật đơn hàng' }
    }
  }

  // ================= DELETE =================
  const destroy = async (id) => {
    try {
      const res = await axios.delete(`${API_URL}/admin/orders/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || { success: false, message: 'Lỗi khi xóa đơn hàng' }
    }
  }

  return {
    orders,
    pagination,
    index,
    store,
    show,
    update,
    destroy,
  }
})
