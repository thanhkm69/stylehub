<script setup>
import { computed } from 'vue'
import { useWishlistStore } from '@/stores/wishlist'
import { useTokenStore } from '@/stores/token'
import { useNotify } from '@/composables/useNotify'
import { useRouter } from 'vue-router'
import BaseSpinner from '@/components/base/BaseSpinner.vue'

const props = defineProps({
  productId: {
    type: Number,
    required: true
  },
  size: {
    type: String,
    default: 'md' // md, lg
  }
})

const emit = defineEmits(['toggled'])

const wishlistStore = useWishlistStore()
const tokenStore = useTokenStore()
const toast = useNotify()
const router = useRouter()

const isWishlisted = computed(() => wishlistStore.isWishlisted(props.productId))
const isLoading = computed(() => wishlistStore.isLoading(props.productId))

const handleToggle = async () => {
  if (!tokenStore.token) {
    toast.warning('Vui lòng đăng nhập để lưu sản phẩm yêu thích')
    // Delay a bit for toast to show
    setTimeout(() => {
      router.push('/login')
    }, 1500)
    return
  }

  const res = await wishlistStore.toggle(props.productId)

  if (res.success) {
    if (res.status === 'added') {
      toast.success('Đã thêm vào yêu thích ❤️')
    } else {
      toast.info('Đã xóa khỏi yêu thích')
    }
    emit('toggled', res.status)
  } else {
    toast.error(res.message || 'Có lỗi xảy ra')
  }
}
</script>

<template>
  <button 
    class="wishlist-btn" 
    :class="[
      `size-${size}`,
      { 'is-active': isWishlisted, 'is-loading': isLoading }
    ]"
    @click.prevent="handleToggle"
    :disabled="isLoading"
    type="button"
  >
    <BaseSpinner v-if="isLoading" size="sm" color="currentColor" label="" />
    <i v-else :class="isWishlisted ? 'ph-fill ph-heart' : 'ph ph-heart'"></i>
  </button>
</template>

<style scoped>
.wishlist-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border: 1px solid var(--border);
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  color: #94a3b8; /* Inactive color */
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
  padding: 0;
}

.wishlist-btn:hover:not(:disabled) {
  transform: scale(1.1);
  border-color: #ef4444;
  color: #ef4444;
}

.wishlist-btn.is-active {
  color: #ef4444; /* Active color */
  border-color: #fca5a5;
  background: #fff1f2;
  animation: pulse 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.wishlist-btn.size-md {
  width: 36px;
  height: 36px;
  font-size: 18px;
}

.wishlist-btn.size-lg {
  width: 48px;
  height: 48px;
  font-size: 24px;
}

.wishlist-btn:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

/* BaseSpinner integration */
:deep(.spinner-container) {
  margin: 0;
}
</style>
