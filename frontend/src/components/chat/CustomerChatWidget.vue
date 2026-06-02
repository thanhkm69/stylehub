<script setup>
import { ref, watch, nextTick, onMounted, computed } from 'vue'
import { useChatStore } from '@/stores/chat'
import { useTokenStore } from '@/stores/token'
import { useCartStore } from '@/stores/cart'
import { useWishlistStore } from '@/stores/wishlist'
import { useNotify } from '@/composables/useNotify'
import axios from 'axios'
import { API_URL, API_URL_IMAGE } from '@/config/env'

const chatStore = useChatStore()
const tokenStore = useTokenStore()
const cartStore = useCartStore()
const wishlistStore = useWishlistStore()
const toast = useNotify()

const user = computed(() => tokenStore.user?.data)

const chatMode = ref('ai') // 'ai' or 'support'
const messageInput = ref('')
const messagesContainer = ref(null)

const aiMessages = ref([
  {
    id: 'welcome',
    sender_id: 'ai',
    message: 'Xin chào! Tôi là Trợ lý ảo AI của StyleHub. Tôi có thể hỗ trợ tìm kiếm sản phẩm, tư vấn size hoặc giải đáp các thắc mắc mua sắm của bạn!',
    created_at: new Date().toISOString()
  }
])
const isAiTyping = ref(false)

// Quick Order States
const isQuickOrderOpen = ref(false)
const quickOrderLoading = ref(false)
const quickOrderProduct = ref(null)
const quickOrderVariants = ref([])
const quickOrderAddresses = ref([])
const selectedVariantId = ref(null)
const selectedAddressId = ref(null)
const quantity = ref(1)
const quickOrderNote = ref('')
const isSubmittingOrder = ref(false)
const quickOrderMode = ref('order')

const getProductImage = (thumbnail) => {
  if (!thumbnail) return '/logo.png'
  if (/^https?:\/\//i.test(thumbnail)) return thumbnail
  return `${API_URL_IMAGE}/${thumbnail.replace(/^\/+/, '')}`
}

const handleProductImageError = (event) => {
  event.target.onerror = null
  event.target.src = '/logo.png'
}

const formatPrice = (price) => new Intl.NumberFormat('vi-VN').format(price) + 'đ'

const hasProductCard = (message, productId) => {
  return message.products?.some(product => product.id === productId)
}

const selectedQuickOrderVariant = computed(() => {
  return quickOrderVariants.value.find(variant => variant.id === selectedVariantId.value)
})

const selectedQuickOrderPricing = computed(() => {
  return selectedQuickOrderVariant.value || quickOrderProduct.value
})

const isQuickOrderUnavailable = computed(() => {
  return quickOrderVariants.value.length > 0 && !selectedQuickOrderVariant.value
})

const scrollToBottom = async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

watch(() => chatStore.messages, () => {
  scrollToBottom()
}, { deep: true })

watch(chatMode, () => {
  scrollToBottom()
})

const parseMessage = (text) => {
  if (!text) return []
  const regex = /(\[ORDER:\d+\])/g
  const parts = text.split(regex)
  return parts.map(part => {
    const match = part.match(/\[ORDER:(\d+)\]/)
    if (match) {
      return { type: 'order', productId: parseInt(match[1]) }
    }
    return { type: 'text', content: part }
  }).filter(part => part.type === 'order' || part.content !== '')
}

const openQuickOrder = async (productId, mode = 'order') => {
  isQuickOrderOpen.value = true
  quickOrderLoading.value = true
  quickOrderMode.value = mode
  quickOrderProduct.value = null
  quickOrderVariants.value = []
  quickOrderAddresses.value = []
  selectedVariantId.value = null
  selectedAddressId.value = null
  quantity.value = 1
  quickOrderNote.value = ''
  
  try {
    const res = await axios.get(`${API_URL}/ai/product-details/${productId}`, {
      headers: { Authorization: `Bearer ${tokenStore.token}` }
    })
    if (res.data.success) {
      const data = res.data.data
      quickOrderProduct.value = data.product
      quickOrderVariants.value = data.variants
      quickOrderAddresses.value = data.addresses
      
      // Select first variant if exists
      if (data.variants && data.variants.length > 0) {
        selectedVariantId.value = data.variants.find(variant => variant.stock > 0)?.id ?? null
      }
      
      // Select default or first address if exists
      if (data.addresses && data.addresses.length > 0) {
        const defaultAddr = data.addresses.find(a => a.is_default)
        selectedAddressId.value = defaultAddr ? defaultAddr.id : data.addresses[0].id
      }
    }
  } catch (error) {
    console.error('Failed to load product details for quick order', error)
    alert('Không thể tải thông tin sản phẩm. Vui lòng thử lại.')
    isQuickOrderOpen.value = false
  } finally {
    quickOrderLoading.value = false
  }
}

const toggleWishlist = async (productId) => {
  const res = await wishlistStore.toggle(productId)

  if (!res) return
  if (res.success) {
    toast.success(res.status === 'added' ? 'Đã thêm vào yêu thích' : 'Đã xóa khỏi yêu thích')
  } else {
    toast.error(res.message || 'Không thể cập nhật danh sách yêu thích')
  }
}

const addQuickOrderToCart = async () => {
  isSubmittingOrder.value = true
  try {
    const res = await cartStore.store({
      product_id: quickOrderProduct.value.id,
      product_variant_id: selectedVariantId.value,
      quantity: quantity.value
    })

    if (res.success) {
      isQuickOrderOpen.value = false
      toast.success('Đã thêm vào giỏ hàng')
    } else {
      toast.error(res.message || 'Không thể thêm vào giỏ hàng')
    }
  } finally {
    isSubmittingOrder.value = false
  }
}

const submitQuickOrder = async () => {
  if (isQuickOrderUnavailable.value) {
    toast.error('Sản phẩm hiện đã hết hàng')
    return
  }

  if (quickOrderMode.value === 'cart') {
    await addQuickOrderToCart()
    return
  }

  if (!selectedAddressId.value) {
    alert('Vui lòng chọn hoặc thêm địa chỉ nhận hàng.')
    return
  }
  
  isSubmittingOrder.value = true
  try {
    const res = await axios.post(`${API_URL}/ai/quick-order`, {
      product_id: quickOrderProduct.value.id,
      variant_id: selectedVariantId.value,
      address_id: selectedAddressId.value,
      quantity: quantity.value,
      note: quickOrderNote.value
    }, {
      headers: { Authorization: `Bearer ${tokenStore.token}` }
    })
    
    if (res.data.success) {
      isQuickOrderOpen.value = false
      
      // Push confirmation message
      aiMessages.value.push({
        id: 'order-success-' + Date.now(),
        sender_id: 'ai',
        message: `🎉 Đặt hàng thành công! Đơn hàng của bạn đã được khởi tạo.\nMã đơn hàng: **${res.data.data.order_code}**.\nTổng cộng thanh toán (đã tính phí ship): **${new Intl.NumberFormat('vi-VN').format(res.data.data.total_amount)}đ**.\nPhương thức thanh toán: Thanh toán khi nhận hàng (COD).\nCảm ơn bạn đã lựa chọn mua sắm cùng StyleHub!`,
        created_at: new Date().toISOString()
      })
      
      scrollToBottom()
    } else {
      alert(res.data.message || 'Đặt hàng thất bại. Vui lòng thử lại.')
    }
  } catch (error) {
    console.error('Quick order submission failed', error)
    alert(error.response?.data?.message || 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại.')
  } finally {
    isSubmittingOrder.value = false
  }
}

const sendAiMessage = async () => {
  if (!messageInput.value.trim()) return
  
  const userMsgText = messageInput.value
  messageInput.value = ''
  
  // Push user message
  aiMessages.value.push({
    id: 'user-' + Date.now(),
    sender_id: user.value?.id,
    message: userMsgText,
    created_at: new Date().toISOString()
  })
  
  scrollToBottom()
  
  // Prepare history
  const history = aiMessages.value
    .filter(m => m.id !== 'welcome')
    .slice(0, -1)
    .map(m => ({
      role: m.sender_id === 'ai' ? 'assistant' : 'user',
      content: m.message
    }))
  
  isAiTyping.value = true
  
  try {
    const res = await axios.post(`${API_URL}/ai/chat`, {
      message: userMsgText,
      history: history
    }, {
      headers: { Authorization: `Bearer ${tokenStore.token}` }
    })
    
    if (res.data.success) {
      aiMessages.value.push({
        id: 'ai-' + Date.now(),
        sender_id: 'ai',
        message: res.data.reply,
        products: res.data.products || [],
        created_at: new Date().toISOString()
      })
    } else {
      throw new Error(res.data.message || 'API error')
    }
  } catch (error) {
    console.error('Failed to get AI reply', error)
    aiMessages.value.push({
      id: 'error-' + Date.now(),
      sender_id: 'ai',
      message: 'Xin lỗi, tôi gặp sự cố kết nối. Vui lòng thử lại sau ít phút.',
      created_at: new Date().toISOString()
    })
  } finally {
    isAiTyping.value = false
    scrollToBottom()
  }
}

const sendMessage = () => {
  if (!messageInput.value.trim() || !chatStore.conversation) return
  
  chatStore.sendMessage(chatStore.conversation.id, messageInput.value)
  messageInput.value = ''
}

const handleSend = () => {
  if (chatMode.value === 'ai') {
    sendAiMessage()
  } else {
    sendMessage()
  }
}

// Automatically scroll to bottom when opening chat
watch(() => chatStore.isOpen, (newVal) => {
  if (newVal) {
    scrollToBottom()
  }
})

onMounted(() => {
  wishlistStore.ids()
})
</script>

<template>
  <div v-if="user" class="chat-widget">
    <!-- Chat Button -->
    <button class="chat-toggle-btn" @click="chatStore.toggleChat">
      <i class="ph ph-chat-circle-dots"></i>
    </button>

    <!-- Chat Box -->
    <div v-if="chatStore.isOpen" class="chat-box">
      <div class="chat-header">
        <div>
          <h4>Hỗ trợ khách hàng</h4>
          <p class="status">Hỗ trợ 24/7</p>
        </div>
        <button class="close-btn" @click="chatStore.toggleChat">
          <i class="ph ph-x"></i>
        </button>
      </div>

      <!-- Chat Mode Tabs -->
      <div class="chat-tabs">
        <button 
          :class="['tab-btn', { active: chatMode === 'ai' }]" 
          @click="chatMode = 'ai'"
        >
          <i class="ph ph-robot"></i> Trợ lý AI
        </button>
        <button 
          :class="['tab-btn', { active: chatMode === 'support' }]" 
          @click="chatMode = 'support'"
        >
          <i class="ph ph-user"></i> Nhân viên
        </button>
      </div>

      <div class="chat-messages" ref="messagesContainer">
        <!-- AI Chat Mode -->
        <template v-if="chatMode === 'ai'">
          <div 
            v-for="msg in aiMessages" 
            :key="msg.id"
            :class="['message', msg.sender_id === 'ai' ? 'received' : 'sent']"
          >
            <div class="msg-content">
              <template v-for="(part, index) in parseMessage(msg.message)" :key="index">
                <span v-if="part.type === 'text'">{{ part.content }}</span>
                <button 
                  v-else-if="part.type === 'order' && !hasProductCard(msg, part.productId)" 
                  class="quick-order-btn"
                  @click="openQuickOrder(part.productId)"
                >
                  <i class="ph ph-lightning"></i> Mua nhanh sản phẩm này
                </button>
              </template>
            </div>
            <div v-if="msg.products?.length" class="chat-product-list">
              <article v-for="product in msg.products" :key="product.id" class="chat-product-card">
                <RouterLink
                  :to="{ name: 'ProductDetail', params: { slug: product.slug } }"
                  class="chat-product-link"
                  @click="chatStore.toggleChat"
                >
                  <img
                    :src="getProductImage(product.thumbnail)"
                    :alt="product.name"
                    class="chat-product-image"
                    @error="handleProductImageError"
                  />
                  <div class="chat-product-info">
                    <strong>{{ product.name }}</strong>
                    <span class="chat-product-price">{{ formatPrice(product.price) }}</span>
                    <span v-if="product.has_discount" class="chat-product-original-price">
                      {{ formatPrice(product.original_price) }}
                    </span>
                  </div>
                </RouterLink>
                <div class="chat-product-actions">
                  <button
                    class="chat-product-action-btn wishlist"
                    :class="{ active: wishlistStore.isWishlisted(product.id) }"
                    :disabled="wishlistStore.isLoading(product.id)"
                    @click="toggleWishlist(product.id)"
                  >
                    <i :class="wishlistStore.isWishlisted(product.id) ? 'ph-fill ph-heart' : 'ph ph-heart'"></i>
                    Yêu thích
                  </button>
                  <button class="chat-product-action-btn cart" @click="openQuickOrder(product.id, 'cart')">
                    <i class="ph ph-shopping-cart"></i> Giỏ hàng
                  </button>
                </div>
                <button class="chat-product-order-btn" @click="openQuickOrder(product.id, 'order')">
                  <i class="ph ph-lightning"></i> Mua nhanh
                </button>
              </article>
            </div>
            <div class="msg-time">{{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</div>
          </div>
          
          <!-- Typing Indicator -->
          <div v-if="isAiTyping" class="message received">
            <div class="msg-content typing-indicator">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </template>

        <!-- Support Human Chat Mode -->
        <template v-else>
          <template v-if="chatStore.messages.length > 0">
            <div 
              v-for="msg in chatStore.messages" 
              :key="msg.id"
              :class="['message', msg.sender_id === user?.id ? 'sent' : 'received']"
            >
              <div class="msg-content">{{ msg.message }}</div>
              <div class="msg-time">{{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</div>
            </div>
          </template>
          <div v-else class="empty-chat">
            <p>Xin chào! Nhắn tin ở đây để kết nối trực tiếp với nhân viên hỗ trợ của chúng tôi.</p>
          </div>
        </template>
      </div>

      <div class="chat-input-area">
        <input 
          v-model="messageInput" 
          type="text" 
          placeholder="Nhập tin nhắn..." 
          @keyup.enter="handleSend"
        />
        <button @click="handleSend" :disabled="!messageInput.trim()">
          <i class="ph ph-paper-plane-right"></i>
        </button>
      </div>

      <!-- Quick Order Overlay Form -->
      <div v-if="isQuickOrderOpen" class="quick-order-overlay">
        <div class="overlay-header">
          <h5>{{ quickOrderMode === 'cart' ? '🛒 Thêm vào giỏ hàng' : '⚡ Đặt hàng nhanh qua AI' }}</h5>
          <button @click="isQuickOrderOpen = false" class="close-overlay-btn">
            <i class="ph ph-x"></i>
          </button>
        </div>
        
        <div v-if="quickOrderLoading" class="overlay-loading">
          <div class="spinner"></div>
          <p>Đang tải thông tin...</p>
        </div>
        
        <div v-else class="overlay-content">
          <!-- Product Summary -->
          <div class="product-summary" v-if="quickOrderProduct">
            <img
              :src="getProductImage(quickOrderProduct.thumbnail)"
              :alt="quickOrderProduct.name"
              class="prod-thumb"
              @error="handleProductImageError"
            />
            <div class="prod-info">
              <h6>{{ quickOrderProduct.name }}</h6>
              <p class="prod-price">
                {{ formatPrice(selectedQuickOrderPricing.price) }}
                <span v-if="selectedQuickOrderPricing.has_discount" class="prod-original-price">
                  {{ formatPrice(selectedQuickOrderPricing.original_price) }}
                </span>
              </p>
            </div>
          </div>
          
          <!-- Selections -->
          <div class="form-group" v-if="quickOrderVariants.length > 0">
            <label>Phân loại (Kích thước / Màu sắc):</label>
            <select v-model="selectedVariantId" class="form-select">
              <option v-for="v in quickOrderVariants" :key="v.id" :value="v.id" :disabled="v.stock === 0">
                {{ v.name }} (Còn: {{ v.stock }}) - {{ formatPrice(v.price) }}{{ v.has_discount ? ` (Giá gốc: ${formatPrice(v.original_price)})` : '' }}
              </option>
            </select>
          </div>
          
          <div v-if="quickOrderMode === 'order'" class="form-group">
            <label>Địa chỉ giao hàng:</label>
            <select v-if="quickOrderAddresses.length > 0" v-model="selectedAddressId" class="form-select">
              <option v-for="addr in quickOrderAddresses" :key="addr.id" :value="addr.id">
                {{ addr.name }} ({{ addr.phone }}) - {{ addr.address }}, {{ addr.ward_name }}, {{ addr.district_name }}, {{ addr.province_name }}
              </option>
            </select>
            <div v-else class="no-address-warning">
              <p>Bạn chưa có địa chỉ nhận hàng nào. Vui lòng thêm địa chỉ nhận hàng trong trang cá nhân của bạn trước.</p>
            </div>
          </div>
          
          <div class="form-group">
            <label>Số lượng:</label>
            <input type="number" v-model.number="quantity" min="1" :max="selectedQuickOrderVariant?.stock || 99" class="form-input" />
          </div>

          <div v-if="quickOrderMode === 'order'" class="form-group">
            <label>Ghi chú:</label>
            <textarea v-model="quickOrderNote" placeholder="Ghi chú thêm cho đơn hàng..." class="form-textarea" rows="2"></textarea>
          </div>
          
          <button 
            class="submit-order-btn" 
            @click="submitQuickOrder"
            :disabled="isSubmittingOrder || isQuickOrderUnavailable || (quickOrderMode === 'order' && quickOrderAddresses.length === 0)"
          >
            <span v-if="isSubmittingOrder">Đang xử lý...</span>
            <span v-else>{{ quickOrderMode === 'cart' ? 'Thêm vào giỏ hàng' : 'Xác nhận đặt hàng (COD)' }}</span>
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.chat-widget {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1000;
}

.chat-toggle-btn {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #111827;
  color: white;
  border: none;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  transition: transform 0.2s;
}

.chat-toggle-btn:hover {
  transform: scale(1.05);
}

.chat-box {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 350px;
  height: 500px;
  background: var(--surface);
  color: var(--text-main);
  border-radius: var(--radius-lg);
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-header {
  padding: 16px;
  background: #111827;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chat-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.status {
  margin: 0;
  font-size: 12px;
  opacity: 0.8;
}

.close-btn {
  background: transparent;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
}

/* Chat Tabs */
.chat-tabs {
  display: flex;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
}

.tab-btn {
  flex: 1;
  padding: 10px;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s;
}

.tab-btn:hover {
  color: var(--text-main);
}

.tab-btn.active {
  color: var(--text-main);
  border-bottom-color: var(--text-main);
  font-weight: 600;
}

.chat-messages {
  flex: 1;
  padding: 16px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: var(--surface);
}

.message {
  max-width: 80%;
  display: flex;
  flex-direction: column;
}

.message.sent {
  align-self: flex-end;
  align-items: flex-end;
}

.message.received {
  align-self: flex-start;
  align-items: flex-start;
}

.msg-content {
  padding: 10px 14px;
  border-radius: 18px;
  font-size: 14px;
  white-space: pre-line;
}

.message.sent .msg-content {
  background: #111827;
  color: white;
  border-bottom-right-radius: 4px;
}

.message.received .msg-content {
  background: var(--background);
  color: var(--text-main);
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.chat-product-list {
  width: 260px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 8px;
}

.chat-product-card {
  padding: 8px;
  background: var(--background);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
}

.chat-product-link {
  display: flex;
  gap: 10px;
  color: inherit;
  text-decoration: none;
}

.chat-product-image {
  width: 58px;
  height: 58px;
  flex-shrink: 0;
  object-fit: cover;
  border-radius: var(--radius-sm);
}

.chat-product-info {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.chat-product-info strong {
  display: -webkit-box;
  overflow: hidden;
  color: var(--text-main);
  font-size: 13px;
  line-height: 1.35;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.chat-product-price {
  color: #ef4444;
  font-size: 13px;
  font-weight: 700;
}

.chat-product-original-price {
  color: var(--text-muted);
  font-size: 11px;
  text-decoration: line-through;
}

.chat-product-actions {
  display: flex;
  gap: 6px;
  margin-top: 8px;
}

.chat-product-action-btn {
  flex: 1;
  padding: 6px 4px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  color: var(--text-muted);
  cursor: pointer;
  font-size: 11px;
  font-weight: 600;
}

.chat-product-action-btn.wishlist:hover,
.chat-product-action-btn.wishlist.active {
  background: #fff1f2;
  border-color: #fca5a5;
  color: #ef4444;
}

.chat-product-action-btn.cart:hover {
  background: #eff6ff;
  border-color: #93c5fd;
  color: #2563eb;
}

.chat-product-action-btn:disabled {
  cursor: wait;
  opacity: 0.6;
}

.chat-product-order-btn {
  width: 100%;
  margin-top: 8px;
  padding: 6px 8px;
  background: #fff1f2;
  border: 1px solid #ffe4e6;
  border-radius: var(--radius-sm);
  color: #f43f5e;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
}

.chat-product-order-btn:hover {
  background: #ffe4e6;
}

.msg-time {
  font-size: 11px;
  color: var(--text-muted);
  margin-top: 4px;
}

.empty-chat {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: var(--text-muted);
  font-size: 14px;
}

/* Typing Indicator */
.typing-indicator {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 12px 16px !important;
}

.typing-indicator span {
  width: 6px;
  height: 6px;
  background: var(--text-muted);
  border-radius: 50%;
  animation: bounce 1.4s infinite ease-in-out both;
  opacity: 0.6;
}

.typing-indicator span:nth-child(1) {
  animation-delay: -0.32s;
}

.typing-indicator span:nth-child(2) {
  animation-delay: -0.16s;
}

@keyframes bounce {
  0%, 80%, 100% { 
    transform: scale(0);
  } 40% { 
    transform: scale(1.0);
  }
}

.chat-input-area {
  padding: 16px;
  background: var(--surface);
  border-top: 1px solid var(--border);
  display: flex;
  gap: 12px;
}

.chat-input-area input {
  flex: 1;
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 8px 16px;
  outline: none;
  font-size: 14px;
  background: var(--background);
  color: var(--text-main);
}

.chat-input-area button {
  background: #111827;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.chat-input-area button:disabled {
  background: var(--border);
  cursor: not-allowed;
}

/* Quick Order Overlay */
.quick-order-overlay {
  position: absolute;
  top: 50px;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--surface);
  color: var(--text-main);
  z-index: 10;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.25s ease-out;
}

@keyframes slideUp {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

.overlay-header {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--background);
}

.overlay-header h5 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-main);
}

.close-overlay-btn {
  background: transparent;
  border: none;
  color: var(--text-muted);
  font-size: 18px;
  cursor: pointer;
}

.overlay-loading {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  font-size: 13px;
  gap: 12px;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 2px solid var(--border);
  border-top-color: var(--text-main);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.overlay-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.product-summary {
  display: flex;
  gap: 12px;
  padding: 10px;
  background: var(--background);
  border-radius: var(--radius-md);
  align-items: center;
}

.prod-thumb {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: var(--radius-sm);
}

.prod-info h6 {
  margin: 0 0 4px 0;
  font-size: 13px;
  font-weight: 600;
  color: var(--text-main);
}

.prod-price {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: #ef4444;
}

.prod-original-price {
  margin-left: 6px;
  color: var(--text-muted);
  font-size: 11px;
  font-weight: 400;
  text-decoration: line-through;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 12px;
  font-weight: 500;
  color: var(--text-muted);
}

.form-select, .form-input, .form-textarea {
  width: 100%;
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  padding: 8px 12px;
  font-size: 13px;
  outline: none;
  background: var(--background);
  color: var(--text-main);
}

.form-select:focus, .form-input:focus, .form-textarea:focus {
  border-color: var(--text-main);
}

.no-address-warning {
  padding: 10px;
  background: #fffbeb;
  border: 1px solid #fef3c7;
  border-radius: var(--radius-md);
  color: #b45309;
  font-size: 12px;
  line-height: 1.5;
}

.submit-order-btn {
  margin-top: auto;
  width: 100%;
  background: #111827;
  color: white;
  border: none;
  padding: 12px;
  border-radius: var(--radius-md);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.submit-order-btn:hover {
  background: #334155;
}

.submit-order-btn:disabled {
  background: var(--border);
  cursor: not-allowed;
}

/* Quick Order Button inside Chat */
.quick-order-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fff1f2;
  color: #f43f5e;
  border: 1px solid #ffe4e6;
  padding: 6px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 8px;
  transition: all 0.2s;
}

.quick-order-btn:hover {
  background: #ffe4e6;
  transform: scale(1.02);
}

@media (max-width: 576px) {
  .chat-box {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    border-radius: 0;
  }
}
</style>
