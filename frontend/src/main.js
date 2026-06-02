import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createHead } from '@unhead/vue/client'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import '@phosphor-icons/web/regular'
import '@phosphor-icons/web/thin'
import '@phosphor-icons/web/light'
import '@phosphor-icons/web/bold'
import '@phosphor-icons/web/fill'

import './assets/main.css'

import App from './App.vue'
import router from './router'

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import { useTheme } from '@/composables/useTheme'

const app = createApp(App)
const head = createHead()
useTheme().initTheme()
app.use(Toast, {
  timeout: 3000,
  position: 'top-right',
  closeOnClick: true,
  pauseOnHover: true,
})

app.use(createPinia())
app.use(router)
app.use(head)

app.mount('#app')

if (typeof window !== 'undefined' && window.AOS) {
  window.AOS.init({
    once: true,
    duration: 900,
    offset: 90,
    easing: 'ease-out-cubic',
  })
}
