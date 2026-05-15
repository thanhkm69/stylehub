import { defineStore } from 'pinia';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

export const useBlogPublicStore = defineStore('blogPublic', {
    state: () => ({
        posts: [],
        post: null,
        relatedPosts: [],
        categories: [],
        loading: false,
        error: null,
        pagination: {
            currentPage: 1,
            lastPage: 1,
        }
    }),
    actions: {
        async fetchCategories() {
            try {
                const response = await axios.get(`${API_URL}/blog-categories/active`);
                this.categories = response.data.data;
            } catch (err) {
                console.error('Error fetching categories:', err);
            }
        },
        async fetchPosts(page = 1, categorySlug = null) {
            this.loading = true;
            try {
                let url = `${API_URL}/blogs?page=${page}`;
                if (categorySlug) {
                    url += `&category=${categorySlug}`;
                }
                const response = await axios.get(url);
                this.posts = response.data.data;
                this.pagination.currentPage = response.data.meta.current_page;
                this.pagination.lastPage = response.data.meta.last_page;
            } catch (err) {
                this.error = err.response?.data?.message || 'Error fetching posts';
            } finally {
                this.loading = false;
            }
        },
        async fetchPost(slug) {
            this.loading = true;
            try {
                const response = await axios.get(`${API_URL}/blogs/${slug}`);
                this.post = response.data.data;
                this.relatedPosts = response.data.related_posts || [];
                return response.data.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Error fetching post';
                return null;
            } finally {
                this.loading = false;
            }
        }
    }
});
