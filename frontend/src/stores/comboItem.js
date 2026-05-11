import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from '@/stores/token'

export const useComboItemStore = defineStore('comboItem', () => {
    const tokenStore = useTokenStore()
    const comboItems = ref([])

    const index = async (combo_id) => {
        try {
            const res = await axios.get(`${API_URL}/combo-items`, {
                params: { combo_id },
                headers: { Authorization: `Bearer ${tokenStore.token}` }
            })
            comboItems.value = res.data.data
            return res.data
        } catch (error) {
            return error.response?.data || {}
        }
    }

    const store = async (data) => {
        try {
            const res = await axios.post(`${API_URL}/combo-items`, data, {
                headers: { Authorization: `Bearer ${tokenStore.token}` }
            })
            return res.data
        } catch (error) {
            return error.response?.data || {}
        }
    }

    const show = async (id) => {
        try {
            const res = await axios.get(`${API_URL}/combo-items/${id}`, {
                headers: { Authorization: `Bearer ${tokenStore.token}` }
            })
            return res.data
        } catch (error) {
            return error.response?.data || {}
        }
    }

    const update = async (id, data) => {
        try {
            const res = await axios.put(`${API_URL}/combo-items/${id}`, data, {
                headers: { Authorization: `Bearer ${tokenStore.token}` }
            })
            return res.data
        } catch (error) {
            return error.response?.data || {}
        }
    }

    const destroy = async (id) => {
        try {
            const res = await axios.delete(`${API_URL}/combo-items/${id}`, {
                headers: { Authorization: `Bearer ${tokenStore.token}` }
            })
            return res.data
        } catch (error) {
            return error.response?.data || {}
        }
    }

    return { comboItems, index, store, show, update, destroy }
})
