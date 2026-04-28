import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useNotify } from '@/composables/useNotify'
import { useTokenStore } from '@/stores/token'

export const useAttributeValueStore = defineStore('attributeValue', () => {
  const tokenStore = useTokenStore()
  const attributeValues = ref([])

  const toast = useNotify()

  // ================= GET LIST =================
  const index = async (attribute_id) => {
    try {
      const res = await axios.get(`${API_URL}/attribute-values`, {
        params: { attribute_id },
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      if (res.status === 200 && res.data.status) {
        attributeValues.value = res.data.data
      } else {
        toast.error('Lỗi khi tải giá trị thuộc tính')
      }
    } catch (error) {
      toast.error('Lỗi khi tải giá trị thuộc tính')
      console.error(error)
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/attribute-values`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response.data
    }
  }

  // ================= UPDATE =================
  const update = async (id, data) => {
    try {
      const res = await axios.post(`${API_URL}/attribute-values/${id}`, data, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response.data
    }
  }

  // ================= DELETE =================
  const destroy = async (id) => {
    try {
      const res = await axios.delete(`${API_URL}/attribute-values/${id}`, {
        headers: { Authorization: `Bearer ${tokenStore.token}` },
      })
      return res.data
    } catch (error) {
      return error.response.data
    }
  }

  return {
    attributeValues,
    index,
    store,
    update,
    destroy,
  }
})
