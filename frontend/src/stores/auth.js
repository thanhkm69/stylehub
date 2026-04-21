import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from './token'

export const useAuthStore = defineStore('auth', () => {
  const tokenStore = useTokenStore()
  const verify = async (email) => {
    try {
      const res = await axios.post(`${API_URL}/verify`, email)
      return res.data
    } catch (error) {
      return error.response.data
    }
  }
  const register = async (user) => {
    try {
      const res = await axios.post(`${API_URL}/register`, user)
      tokenStore.token = res.data.token
      return res.data
    } catch (error) {
      return error.response.data
    }
  }
  const login = async (user) => {
    try {
      const res = await axios.post(`${API_URL}/login`, user)
      tokenStore.token = res.data.token
      return res.data
    } catch (error) {
      return error.response.data
    }
  }

  return {
    verify,
    register,
    login,
  }
})
