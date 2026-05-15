import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createHead } from '@unhead/vue/client'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

import './assets/main.css'

import App from './App.vue'
import router from './router'

import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const app = createApp(App)
const head = createHead()
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
