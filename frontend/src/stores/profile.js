import { ref } from 'vue'
import { defineStore } from 'pinia'
import { profileService } from '@/services/profile'

export const useProfileStore = defineStore('profile', () => {
  const profile = ref(null)
  const profiles = ref([])
  const pagination = ref({ total: 0, last_page: 1 })

  const me = async () => {
    try {
      const res = await profileService.me()
      profile.value = res.data.data
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const updateCurrent = async (data) => {
    try {
      const res = await profileService.updateCurrent(data)
      profile.value = res.data.data
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const index = async (params) => {
    try {
      const res = await profileService.index(params)
      const data = res.data.data
      const meta = res.data.meta
      profiles.value = data.data ?? data
      pagination.value.total = meta?.total ?? data.total ?? profiles.value.length
      pagination.value.last_page = meta?.last_page ?? data.last_page ?? 1
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const store = async (data) => {
    try {
      const res = await profileService.store(data)
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const show = async (id) => {
    try {
      const res = await profileService.show(id)
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const update = async (id, data) => {
    try {
      const res = await profileService.update(id, data)
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  const destroy = async (id) => {
    try {
      const res = await profileService.destroy(id)
      return res.data
    } catch (error) {
      return error.response?.data || {}
    }
  }

  return {
    profile,
    profiles,
    pagination,
    me,
    updateCurrent,
    index,
    store,
    show,
    update,
    destroy,
  }
})
