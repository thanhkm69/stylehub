import { defineStore } from 'pinia'
import axios from 'axios'
import { API_URL } from '@/config/env'
import { useTokenStore } from './token'

export const useCheckoutStore = defineStore('checkout', {
  state: () => ({
    cartItems: [],
    addresses: [],
    customerName: '',
    customerPhone: '',
    customerEmail: '',
    cartSummary: {
      total_items: 0,
      total_price: 0,
      original_total_price: 0,
      flash_sale_savings: 0,
      subtotal_before_combo: 0,
      combo_discount: 0,
      applied_combos: [],
      applied_combo: null,
      coupon_discount: 0,
      coupon: null
    },
    selectedAddressId: null,
    shippingFee: 0,
    paymentMethod: 'cod',
    note: '',
    loading: false,
    processing: false,
    couponProcessing: false
  }),

  getters: {
    totalAmount: (state) => Math.max(state.cartSummary.total_price - (state.cartSummary.coupon_discount || 0), 0) + state.shippingFee,
    selectedAddress: (state) => state.addresses.find(addr => addr.id === state.selectedAddressId)
  },

  actions: {
    async fetchCheckoutData() {
      const tokenStore = useTokenStore()
      
      // Early pre-fill from token store (logged in user)
      if (tokenStore.user) {
        // user.data because the response from /get-user is {success: true, message: ..., data: user}
        // Let's check tokenStore again... wait, tokenStore.user is set to res.data.
        // Let's re-verify tokenStore.js line 39: user.value = res.data
        const userData = tokenStore.user.data || tokenStore.user
        this.customerName = userData.name || ''
        this.customerEmail = userData.email || ''
      }

      this.loading = true
      try {
        const res = await axios.get(`${API_URL}/checkout`, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        
        if (res.data.success) {
          const data = res.data.data
          this.cartItems = data.cart_items
          this.addresses = data.addresses
          this.cartSummary = data.cart_summary
          
          if (data.default_address_id) {
            this.selectedAddressId = data.default_address_id
          } else if (data.addresses.length > 0) {
            this.selectedAddressId = data.addresses[0].id
          }

          this.shippingFee = data.initial_shipping_fee
          
          // If an address is selected, update contact info from that address
          const selected = data.addresses.find(a => a.id === this.selectedAddressId)
          if (selected) {
            this.customerPhone = selected.phone
            this.customerName = selected.name
          }
        }
      } catch (error) {
        console.error('Fetch checkout data error', error)
      } finally {
        this.loading = false
      }
    },

    async updateShippingFee(addressId) {
      const tokenStore = useTokenStore()
      this.selectedAddressId = addressId
      
      const addr = this.addresses.find(a => a.id === addressId)
      if (addr) {
        this.customerName = addr.name
        this.customerPhone = addr.phone
      }

      try {
        const res = await axios.post(`${API_URL}/checkout/preview`, {
          address_id: addressId,
          coupon_code: this.cartSummary.coupon?.code || null
        }, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        if (res.data.success) {
          this.shippingFee = res.data.data.shipping_fee
          this.cartSummary.coupon = res.data.data.coupon
          this.cartSummary.coupon_discount = res.data.data.coupon_discount
        }
      } catch (error) {
        if (this.cartSummary.coupon) {
          this.removeCoupon()
        }
        console.error('Update shipping fee error', error)
      }
    },

    async applyCoupon(couponCode) {
      const tokenStore = useTokenStore()
      this.couponProcessing = true
      try {
        const res = await axios.post(`${API_URL}/checkout/preview`, {
          address_id: this.selectedAddressId,
          coupon_code: couponCode.trim()
        }, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })

        this.shippingFee = res.data.data.shipping_fee
        this.cartSummary.coupon = res.data.data.coupon
        this.cartSummary.coupon_discount = res.data.data.coupon_discount

        return { success: true, message: 'Áp dụng mã giảm giá thành công.' }
      } catch (error) {
        const data = error.response?.data
        return {
          success: false,
          message: data?.errors?.coupon_code?.[0] || data?.message || 'Mã giảm giá không hợp lệ.'
        }
      } finally {
        this.couponProcessing = false
      }
    },

    removeCoupon() {
      this.cartSummary.coupon = null
      this.cartSummary.coupon_discount = 0
    },

    async placeOrder() {
      const tokenStore = useTokenStore()
      this.processing = true
      try {
        const res = await axios.post(`${API_URL}/checkout/process`, {
          address_id: this.selectedAddressId,
          payment_method: this.paymentMethod,
          note: this.note,
          customer_name: this.customerName,
          customer_phone: this.customerPhone,
          customer_email: this.customerEmail,
          coupon_code: this.cartSummary.coupon?.code || null
        }, {
          headers: { Authorization: `Bearer ${tokenStore.token}` }
        })
        return res.data
      } catch (error) {
        return error.response?.data || { success: false, message: 'Lỗi khi đặt hàng' }
      } finally {
        this.processing = false
      }
    }
  }
})
