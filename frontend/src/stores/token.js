import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useLocalStorage } from '@vueuse/core'
import { watch, ref } from 'vue'

export const useTokenStore = defineStore('token', () => {
  const token = useLocalStorage('token', null)
  const user = ref(null)

  const logout = async () => {
    if (!token.value) {
      token.value = null
      user.value = null
      return { success: false, message: 'Không có token để đăng xuất' }
    }

    try {
      const res = await axios.post(
        `${API_URL}/logout`,
        {},
        {
          headers: {
            Authorization: `Bearer ${token.value}`,
          },
        },
      )
      token.value = null
      user.value = null
      return res.data
    } catch (error) {
      token.value = null
      user.value = null
      return error.response?.data || { success: false, message: 'Lỗi khi đăng xuất' }
    }
  }

  const getUser = async () => {
    if (!token.value) {
      user.value = null
      return
    }

    try {
      const res = await axios.get(`${API_URL}/get-user`, {
        headers: {
          Authorization: `Bearer ${token.value}`,
        },
      })
      user.value = res.data
    } catch (error) {
      logout()
    }
  }

  watch(token, async () => await getUser())

  return {
    token,
    user,
    logout,
    getUser,
  }
})
