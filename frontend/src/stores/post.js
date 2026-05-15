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
        loading: false,
        error: null,
    }),
    actions: {
        async fetchPosts() {
            this.loading = true;
            try {
                const tokenStore = useTokenStore();
                const response = await axios.get(`${API_URL}/posts`, {
                    headers: {
                        Authorization: `Bearer ${tokenStore.token}`
                    }
                });
                this.posts = response.data.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Error fetching posts';
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
                this.error = err.response?.data?.message || 'Error fetching post';
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
                toast.success('Post created successfully');
                return true;
            } catch (err) {
                this.error = err.response?.data?.message || 'Error creating post';
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
                toast.success('Post updated successfully');
                return true;
            } catch (err) {
                this.error = err.response?.data?.message || 'Error updating post';
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
                toast.success('Post deleted successfully');
                return true;
            } catch (err) {
                this.error = err.response?.data?.message || 'Error deleting post';
                toast.error(this.error);
                return false;
            }
        }
    }
});
