import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

const headers = () => {
  const tokenStore = useTokenStore()

  return {
    headers: { Authorization: `Bearer ${tokenStore.token}` },
  }
}

export const profileService = {
  me: () => axios.get(`${API_URL}/profile`, headers()),
  updateCurrent: (data) => axios.put(`${API_URL}/profile`, data, headers()),
  index: (params) => axios.get(`${API_URL}/profiles`, { params, ...headers() }),
  show: (id) => axios.get(`${API_URL}/profiles/${id}`, headers()),
  store: (data) => axios.post(`${API_URL}/profiles`, data, headers()),
  update: (id, data) => axios.put(`${API_URL}/profiles/${id}`, data, headers()),
  destroy: (id) => axios.delete(`${API_URL}/profiles/${id}`, headers()),
}
