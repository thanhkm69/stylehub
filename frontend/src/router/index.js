import { createRouter, createWebHistory } from 'vue-router'
import UserLayout from '@/components/layouts/UserLayout.vue'
import AdminLayout from '@/components/layouts/AdminLayout.vue'
import DefaultLayout from '@/components/layouts/DefaultLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/user',
      component: UserLayout,
      children: [
        { path: '', name: 'Profile', component: () => import('@/views/user/Profile.vue') },
      ],
    },

    {
      path: '/admin',
      component: AdminLayout,
      children: [
        { path: '', name: 'Dashboard', component: () => import('@/views/admin/Dashboard.vue') },
        {
          path: 'categories',
          name: 'Categories',
          component: () => import('@/views/admin/Categories.vue'),
        },
        {
          path: 'attributes',
          name: 'Attributes',
          component: () => import('@/views/admin/Attributes.vue'),
        },
      ],
    },

    {
      path: '/',
      component: DefaultLayout,
      children: [
        { path: 'register', name: 'Register', component: () => import('@/views/Register.vue') },
        { path: 'login', name: 'Login', component: () => import('@/views/Login.vue') },
        { path: 'forgot', name: 'Forgot', component: () => import('@/views/Forgot.vue') },
        { path: 'callback', name: 'Callback', component: () => import('@/views/Callback.vue') },
        { path: '', name: 'Home', component: () => import('@/views/Home.vue') },
        { path: 'shop', name: 'Shop', component: () => import('@/views/Shop.vue') },
      ],
    },
  ],
})

router.beforeEach(async (to, from) => {
  if (localStorage.getItem('token') ?? null) {
    if (to.name === 'Login' || to.name === 'Register') {
      return { name: 'Home' }
    }
  }
})

export default router
