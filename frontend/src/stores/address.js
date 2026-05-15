import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from './token'

export const useAddressStore = defineStore('address', {
  state: () => ({
    addresses: [],
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0
    },
    loading: false,
    errors: {}
  }),

  actions: {
    async index(page = 1) {
      const tokenStore = useTokenStore()
      this.loading = true
      try {
        const res = await axios.get(`${API_URL}/addresses`, {
          params: { page },
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          this.addresses = res.data.data
          this.pagination = res.data.pagination
        }
      } catch (error) {
        console.error('Fetch addresses error', error)
      } finally {
        this.loading = false
      }
    },

    async store(data) {
      const tokenStore = useTokenStore()
      this.loading = true
      this.errors = {}
      try {
        const res = await axios.post(`${API_URL}/addresses`, data, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          await this.index(1) // Refresh list to first page
        }
        return res.data
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        }
        return error.response?.data || { success: false, message: 'Lỗi khi lưu địa chỉ' }
      } finally {
        this.loading = false
      }
    },

    async update(id, data) {
      const tokenStore = useTokenStore()
      this.loading = true
      this.errors = {}
      try {
        const res = await axios.post(`${API_URL}/addresses/${id}`, data, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          await this.index(this.pagination.current_page) // Refresh current page
        }
        return res.data
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors
        }
        return error.response?.data || { success: false, message: 'Lỗi khi cập nhật địa chỉ' }
      } finally {
        this.loading = false
      }
    },

    async destroy(id) {
      const tokenStore = useTokenStore()
      try {
        const res = await axios.delete(`${API_URL}/addresses/${id}`, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          // If current page is now empty, go to previous page
          if (this.addresses.length === 1 && this.pagination.current_page > 1) {
            await this.index(this.pagination.current_page - 1)
          } else {
            await this.index(this.pagination.current_page)
          }
        }
        return res.data
      } catch (error) {
        return error.response?.data || { success: false, message: 'Lỗi khi xóa địa chỉ' }
      }
    },

    async setDefault(id) {
      const tokenStore = useTokenStore()
      try {
        const res = await axios.post(`${API_URL}/addresses/${id}/set-default`, {}, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          await this.index(this.pagination.current_page)
        }
        return res.data
      } catch (error) {
        return error.response?.data || { success: false, message: 'Lỗi khi đặt mặc định' }
      }
    }
  }
})
