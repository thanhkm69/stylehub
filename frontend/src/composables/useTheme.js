import { ref } from 'vue'

const STORAGE_KEY = 'stylehub-theme'
const isDark = ref(false)

const applyTheme = (dark) => {
  isDark.value = dark
  document.documentElement.classList.toggle('dark', dark)
  document.documentElement.style.colorScheme = dark ? 'dark' : 'light'
  localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light')
}

const initTheme = () => {
  const savedTheme = localStorage.getItem(STORAGE_KEY)
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  applyTheme(savedTheme ? savedTheme === 'dark' : prefersDark)
}

export const useTheme = () => ({
  isDark,
  initTheme,
  toggleTheme: () => applyTheme(!isDark.value),
})
