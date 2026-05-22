import { createRouter, createWebHistory } from 'vue-router'
import UserLayout from '@/components/layouts/UserLayout.vue'
import AdminLayout from '@/components/layouts/AdminLayout.vue'
import DefaultLayout from '@/components/layouts/DefaultLayout.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
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
        {
          path: 'products',
          name: 'Products',
          component: () => import('@/views/admin/Products.vue'),
        },
        {
          path: 'products/:id/variants',
          name: 'ProductVariants',
          component: () => import('@/views/admin/ProductVariants.vue'),
        },
        {
          path: 'users',
          name: 'Users',
          component: () => import('@/views/admin/Users.vue'),
        },
        {
          path: 'coupons',
          name: 'Coupons',
          component: () => import('@/views/admin/Coupons.vue'),
        },
        {
          path: 'contacts',
          name: 'Contacts',
          component: () => import('@/views/admin/Contacts.vue'),
        },
        {
          path: 'flash-sales',
          name: 'FlashSales',
          component: () => import('@/views/admin/FlashSales.vue'),
        },
        {
          path: 'combos',
          name: 'Combos',
          component: () => import('@/views/admin/Combos.vue'),
        },
        {
          path: 'orders',
          name: 'Orders',
          component: () => import('@/views/admin/Orders.vue')
        },
        {
          path: 'blog-categories',
          name: 'BlogCategories',
          component: () => import('@/views/admin/BlogCategories.vue'),
        },
        {
          path: 'posts',
          name: 'Posts',
          component: () => import('@/views/admin/Posts.vue'),
        },
      ],
    },

    {
      path: '/',
      component: DefaultLayout,
      children: [
        {
          path: 'user',
          component: UserLayout,
          children: [
            { path: '', name: 'Profile', component: () => import('@/views/user/Profile.vue') },
            { path: 'password', name: 'ChangePassword', component: () => import('@/views/user/ChangePassword.vue') },
            { 
              path: 'wishlist', 
              name: 'Wishlist', 
              component: () => import('@/views/user/WishlistView.vue'),
              meta: { requiresAuth: true }
            },
            {
              path: 'addresses',
              name: 'UserAddresses',
              component: () => import('@/views/user/Addresses.vue'),
              meta: { requiresAuth: true }
            },
            {
              path: 'orders',
              name: 'UserOrders',
              component: () => import('@/views/user/Orders.vue'),
              meta: { requiresAuth: true }
            },
          ],
        },
        { path: 'register', name: 'Register', component: () => import('@/views/Register.vue') },
        { path: 'login', name: 'Login', component: () => import('@/views/Login.vue') },
        { path: 'forgot', name: 'Forgot', component: () => import('@/views/Forgot.vue') },
        { path: 'callback', name: 'Callback', component: () => import('@/views/Callback.vue') },
        { path: '', name: 'Home', component: () => import('@/views/Home.vue') },
        { path: 'shop', name: 'Shop', component: () => import('@/views/Shop.vue') },
        { path: 'products/:slug', name: 'ProductDetail', component: () => import('@/views/Detail.vue') },
        { path: 'cart', name: 'Cart', component: () => import('@/views/CartView.vue'), meta: { requiresAuth: true } },
        { path: 'checkout', name: 'Checkout', component: () => import('@/views/Checkout.vue'), meta: { requiresAuth: true } },
        { path: 'order-success/:code', name: 'OrderSuccess', component: () => import('@/views/OrderSuccess.vue'), meta: { requiresAuth: true } },
        {
          path: 'payment/momo/return',
          name: 'MoMoReturn',
          component: () => import('@/views/user/payment/PaymentReturn.vue')
        },
        {
          path: 'payment/status/:orderId',
          name: 'PaymentStatus',
          component: () => import('@/views/user/payment/PaymentStatus.vue'),
          meta: { requiresAuth: true }
        },
        { path: 'contact', name: 'Contact', component: () => import('@/views/Contact.vue') },
        { path: 'about', name: 'About', component: () => import('@/views/About.vue') },
        { path: 'blog', name: 'Blog', component: () => import('@/views/Blog.vue') },
        { path: 'blog/:slug', name: 'BlogDetail', component: () => import('@/views/BlogDetail.vue') },
      ],
    },
  ],
})

router.beforeEach(async (to, from) => {
  const isAuthenticated = localStorage.getItem('token') ?? null

  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'Login' }
  }

  if (isAuthenticated) {
    if (to.name === 'Login' || to.name === 'Register') {
      return { name: 'Home' }
    }
  }
})

export default router
