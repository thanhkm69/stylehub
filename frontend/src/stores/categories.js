import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useNotify } from '@/composables/useNotify'

export const useCategoriesStore = defineStore('categories', () => {
  const categories = ref([])
  const pagination = ref({
    total: 0,
    last_page: 1,
  })

  const toast = useNotify()

  //// ================= FLAT =================
  const flatCategories = (categories, level = 0) => {
    return categories.reduce((acc, category) => {
      const { childrens_recursive, ...rest } = category

      acc.push({ ...rest, level })

      if (childrens_recursive && childrens_recursive.length > 0) {
        acc.push(...flatCategories(childrens_recursive, level + 1))
      }

      return acc
    }, [])
  }

  // ================= GET LIST =================
  const index = async (params) => {
    try {
      const res = await axios.get(`${API_URL}/categories`, { params })

      const data = res.data.data

      categories.value = flatCategories(data.data ?? data)
      pagination.value.total = data.total ?? categories.value.length
      pagination.value.last_page = data.last_page ?? 1

      if (!res.status === 200 || !res.data.success) {
        toast.error('Lỗi khi tải danh mục')
      }
    } catch (error) {
      toast.error('Lỗi khi tải danh mục')
      console.error(error)
    }
  }

  // ================= CREATE =================
  const store = async (data) => {
    try {
      const res = await axios.post(`${API_URL}/categories`, data)

      if (res.status === 201 && res.data.success) {
        toast.success('Tạo danh mục thành công')
        return true
      } else {
        toast.error('Lỗi khi tạo danh mục')
        return false
      }
    } catch (error) {
      toast.error('Lỗi khi tạo danh mục')
      console.error(error)
      return false
    }
  }

  // ================= SHOW =================
  const show = async (id) => {
    try {
      const res = await axios.get(`${API_URL}/categories/${id}`)
      if (res.status === 200 && res.data.success) {
        return res.data
      } else {
        toast.error('Lỗi khi lấy chi tiết danh mục')
        return false
      }
    } catch (error) {
      toast.error('Lỗi khi lấy chi tiết danh mục')
      console.error(error)
      return false
    }
  }

  // ================= UPDATE =================
  const update = async (id, data) => {
    try {
      const res = await axios.post(`${API_URL}/categories/${id}`, data)

      if (res.status === 200 && res.data.success) {
        toast.success('Cập nhật danh mục thành công')
        return true
      } else {
        toast.error('Lỗi khi cập nhật danh mục')
        return false
      }
    } catch (error) {
      toast.error('Lỗi khi cập nhật danh mục')
      console.error(error)
      return false
    }
  }

  // ================= DELETE =================
  const destroy = async (id) => {
    try {
      const res = await axios.delete(`${API_URL}/categories/${id}`)

      if (res.status === 200 && res.data.success) {
        toast.success('Xóa danh mục thành công')
        return true
      } else {
        toast.error('Lỗi khi xóa danh mục')
        return false
      }
    } catch (error) {
      toast.error('Lỗi khi xóa danh mục')
      console.error(error)
      return false
    }
  }

  return {
    categories,
    pagination,
    index,
    store,
    show,
    update,
    destroy,
  }
})
