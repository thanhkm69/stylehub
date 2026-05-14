import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useUserStore = defineStore('user', () => {
  const tokenStore = useTokenStore()
  const users = ref([])
  const pagination = ref({
    total: 0,
    last_page: 1,
  })

  const getHeaders = () => ({
    Authorization: `Bearer ${tokenStore.token}`,
  })

  const index = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/users`, {
        params,
        headers: getHeaders(),
      })

      const meta = res.data.meta
      users.value = res.data.data
      pagination.value.total = meta?.total ?? users.value.length
      pagination.value.last_page = meta?.last_page ?? 1

      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const store = async (formData) => {
    try {
      const payload = {
        name:   formData.name,
        email:  formData.email,
        role:   formData.role,
        status: formData.status,
      }

      const res = await axios.post(`${API_URL}/users`, payload, {
        headers: getHeaders(),
      })

      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const show = async (id) => {
    try {
      const res = await axios.get(`${API_URL}/users/${id}`, {
        headers: getHeaders(),
      })

      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const update = async (id, formData) => {
    try {
      const payload = {
        name:   formData.name,
        email:  formData.email,
        role:   formData.role,
        status: formData.status,
      }

      if (formData.password) {
        payload.password = formData.password
      }

      const res = await axios.post(`${API_URL}/users/${id}`, payload, {
        headers: getHeaders(),
      })

      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const destroy = async (id) => {
    try {
      const res = await axios.delete(`${API_URL}/users/${id}`, {
        headers: getHeaders(),
      })

      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const changePassword = async (formData) => {
    try {
      const payload = {
        currentPassword: formData.currentPassword,
        newPassword: formData.newPassword,
        newPassword_confirmation: formData.newPassword_confirmation,
      }

      const res = await axios.post(`${API_URL}/change-password`, payload, {
        headers: getHeaders(),
      })

      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    users,
    pagination,
    index,
    store,
    show,
    update,
    destroy,
    changePassword,
  }
})