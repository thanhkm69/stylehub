import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useComboStore = defineStore('combo', () => {
  const tokenStore = useTokenStore()
  const combos = ref([])
  const pagination = ref({ total: 0, last_page: 1 })

  const index = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/combos`, {
        params,
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      const data = res.data.data
      const meta = res.data.meta
      combos.value = data.data ?? data
      pagination.value.total     = meta?.total     ?? data.total     ?? combos.value.length
      pagination.value.last_page = meta?.last_page ?? data.last_page ?? 1
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/combos`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const show = async (id) => {
    try {
      const res = await axios.get(`${API_URL}/combos/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const update = async (id, data) => {
    try {
      const res = await axios.post(`${API_URL}/combos/${id}`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const destroy = async (id) => {
    try {
      const res = await axios.delete(`${API_URL}/combos/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return { combos, pagination, index, store, show, update, destroy }
})
