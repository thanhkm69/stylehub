import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'

export const useProductPublicStore = defineStore('productPublic', () => {
  // ── State ──────────────────────────────────────────────
  const products = ref([])
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0,
  })
  const product = ref(null)
  const homeData = ref({
    new_arrivals: [],
    categories: [],
  })
  const loading = ref(false)
  const error = ref(null)

  // ── Actions ────────────────────────────────────────────

  /**
   * GET /api/home
   * Fetches homepage data: new_arrivals + categories
   */
  const home = async () => {
    loading.value = true
    error.value = null
    try {
      const res = await axios.get(`${API_URL}/home`)
      homeData.value = res.data.data
      return res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Không thể tải dữ liệu trang chủ'
      return err.response?.data || {}
    } finally {
      loading.value = false
    }
  }

  /**
   * GET /api/products
   * Fetches product list with search, filter, sort, pagination
   */
  const index = async (params = {}) => {
    loading.value = true
    error.value = null
    try {
      const res = await axios.get(`${API_URL}/shop`, { params })
      products.value = res.data.data
      pagination.value = {
        current_page: res.data.meta?.current_page ?? 1,
        last_page: res.data.meta?.last_page ?? 1,
        total: res.data.meta?.total ?? 0,
      }
      return res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Không thể tải danh sách sản phẩm'
      return err.response?.data || {}
    } finally {
      loading.value = false
    }
  }

  /**
   * GET /api/products/{slug}
   * Fetches single product detail + related_products
   */
  const show = async (slug) => {
    loading.value = true
    error.value = null
    try {
      const res = await axios.get(`${API_URL}/shop/${slug}`)
      product.value = res.data.data
      return res.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Không thể tải chi tiết sản phẩm'
      return err.response?.data || {}
    } finally {
      loading.value = false
    }
  }

  return {
    products,
    pagination,
    product,
    homeData,
    loading,
    error,
    home,
    index,
    show,
  }
})
