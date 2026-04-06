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
      ],
    },

    {
      path: '/',
      component: DefaultLayout,
      children: [{ path: '', name: 'Home', component: () => import('@/views/Home.vue') }],
    },
  ],
})

export default router
