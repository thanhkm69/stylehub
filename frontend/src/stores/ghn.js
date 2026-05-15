import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'

export const useGHNStore = defineStore('ghn', {
  state: () => ({
    provinces: [],
    districts: [],
    wards: [],
    loading: false
  }),

  actions: {
    async loadProvinces() {
      this.loading = true
      try {
        const res = await axios.get(`${API_URL}/ghn/provinces`)
        if (res.data.success) {
          this.provinces = res.data.data
        }
      } catch (error) {
        console.error('Fetch provinces error', error)
      } finally {
        this.loading = false
      }
    },

    async loadDistricts(provinceId) {
      this.loading = true
      try {
        const res = await axios.get(`${API_URL}/ghn/districts`, {
          params: { province_id: provinceId }
        })
        if (res.data.success) {
          this.districts = res.data.data
          this.wards = [] // Reset wards when district changes
        }
      } catch (error) {
        console.error('Fetch districts error', error)
      } finally {
        this.loading = false
      }
    },

    async loadWards(districtId) {
      this.loading = true
      try {
        const res = await axios.get(`${API_URL}/ghn/wards`, {
          params: { district_id: districtId }
        })
        if (res.data.success) {
          this.wards = res.data.data
        }
      } catch (error) {
        console.error('Fetch wards error', error)
      } finally {
        this.loading = false
      }
    },

    async calculateShippingFee(params) {
      try {
        const res = await axios.post(`${API_URL}/ghn/shipping-fee`, params)
        return res.data
      } catch (error) {
        console.error('Calculate shipping fee error', error)
        return error.response?.data || { success: false, message: 'Lỗi tính phí ship' }
      }
    }
  }
})
