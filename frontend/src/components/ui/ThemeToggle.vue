<script setup>
import { useTheme } from '@/composables/useTheme'

const { isDark, toggleTheme } = useTheme()
</script>

<template>
  <button
    type="button"
    class="theme-toggle"
    :aria-label="isDark ? 'Chuyển sang giao diện sáng' : 'Chuyển sang giao diện tối'"
    :title="isDark ? 'Giao diện sáng' : 'Giao diện tối'"
    @click="toggleTheme"
  >
    <i class="ph-bold" :class="isDark ? 'ph-sun' : 'ph-moon'"></i>
  </button>
</template>

<style scoped>
.theme-toggle {
  display: inline-flex;
  width: 2rem;
  height: 2rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  background: var(--background);
  color: var(--foreground);
  font-size: 1rem;
  transition: var(--transition);
}

.theme-toggle:hover {
  background: var(--muted);
}

.theme-toggle:focus-visible {
  outline: 3px solid color-mix(in oklch, var(--ring) 50%, transparent);
  outline-offset: 2px;
}
</style>
