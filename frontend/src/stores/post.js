import { defineStore } from 'pinia';
import axios from 'axios';
import { useTokenStore } from './token';
import { useToast } from 'vue-toastification';

const API_URL = import.meta.env.VITE_API_URL;
const toast = useToast();

export const usePostStore = defineStore('post', {
    state: () => ({
        posts: [],
        post: null,
        pagination: {
            total: 0,
            last_page: 1,
        },
        loading: false,
        error: null,
    }),
    actions: {
        async fetchPosts(params = {}) {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.get(`${API_URL}/posts`, {
                    params,
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                this.posts = response.data.data;
                this.pagination.total = response.data.meta?.total ?? this.posts.length;
                this.pagination.last_page = response.data.meta?.last_page ?? 1;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể tải danh sách bài viết';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        },
        async fetchPost(id) {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.get(`${API_URL}/posts/${id}`, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                this.post = response.data.data;
                return response.data.data;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể tải bài viết';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        },
        async createPost(formData) {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.post(`${API_URL}/posts`, formData, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`,
                        'Content-Type': 'multipart/form-data',
                    }
                });
                this.posts.unshift(response.data.data);
        toast.success('Thêm bài viết thành công');
                return true;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể thêm bài viết';
                toast.error(this.error);
                return false;
            } finally {
                this.loading = false;
            }
        },
        async updatePost(id, formData) {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.post(`${API_URL}/posts/${id}`, formData, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`,
                        'Content-Type': 'multipart/form-data',
                    }
                });
                const index = this.posts.findIndex(p => p.id === id);
                if (index !== -1) {
                    this.posts[index] = response.data.data;
                }
        toast.success('Cập nhật bài viết thành công');
                return true;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể cập nhật bài viết';
                toast.error(this.error);
                return false;
            } finally {
                this.loading = false;
            }
        },
        async deletePost(id) {
            try {
                const tokenStore = useTokenStore();
                await axios.delete(`${API_URL}/posts/${id}`, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                this.posts = this.posts.filter(p => p.id !== id);
        toast.success('Xóa bài viết thành công');
                return true;
            } catch (err) {
        this.error = err.response?.data?.message || 'Không thể xóa bài viết';
                toast.error(this.error);
                return false;
            }
        }
    }
});
