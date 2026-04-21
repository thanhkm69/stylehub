import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'

export const useForgotStore = defineStore('forgot', () => {
  const forgot = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/forgot`, data)
      return res.data
    } catch (error) {
      return error.response.data
    }
  }

  const verifyOtp = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/verify-otp`, data)
      return res.data
    } catch (error) {
      return error.response.data
    }
  }

  const resetPassword = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/reset`, data)
      return res.data
    } catch (error) {
      return error.response.data
    }
  }

  return {
    forgot,
    verifyOtp,
    resetPassword,
  }
})
