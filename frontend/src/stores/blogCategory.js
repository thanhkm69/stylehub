import { defineStore } from 'pinia';
import axios from 'axios';
import { useTokenStore } from './token';
import { useToast } from 'vue-toastification';

const API_URL = import.meta.env.VITE_API_URL;
const toast = useToast();

export const useBlogCategoryStore = defineStore('blogCategory', {
    state: () => ({
        categories: [],
        category: null,
        loading: false,
        error: null,
    }),
    actions: {
        async fetchCategories() {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.get(`${API_URL}/blog-categories`, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                this.categories = response.data.data;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể tải danh sách danh mục bài viết';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        },
        async fetchCategory(id) {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.get(`${API_URL}/blog-categories/${id}`, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                this.category = response.data.data;
                return response.data.data;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể tải danh mục bài viết';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        },
        async createCategory(data) {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.post(`${API_URL}/blog-categories`, data, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                this.categories.unshift(response.data.data);
        toast.success('Thêm danh mục bài viết thành công');
                return true;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể thêm danh mục bài viết';
                toast.error(this.error);
                return false;
            } finally {
                this.loading = false;
            }
        },
        async updateCategory(id, data) {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.put(`${API_URL}/blog-categories/${id}`, data, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                const index = this.categories.findIndex(c => c.id === id);
                if (index !== -1) {
                    this.categories[index] = response.data.data;
                }
        toast.success('Cập nhật danh mục bài viết thành công');
                return true;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể cập nhật danh mục bài viết';
                toast.error(this.error);
                return false;
            } finally {
                this.loading = false;
            }
        },
        async deleteCategory(id) {
            try {
                const tokenStore = useTokenStore();
                await axios.delete(`${API_URL}/blog-categories/${id}`, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                this.categories = this.categories.filter(c => c.id !== id);
        toast.success('Xóa danh mục bài viết thành công');
                return true;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể xóa danh mục bài viết';
                toast.error(this.error);
                return false;
            }
        }
    }
});
