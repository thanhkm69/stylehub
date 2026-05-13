<script setup>
import { reactive, ref } from 'vue'
import { useContactStore } from '@/stores/contact'
import { useNotify } from '@/composables/useNotify'
import BaseButton from '@/components/base/BaseButton.vue'
import BaseInputText from '@/components/base/BaseInputText.vue'

const contactStore = useContactStore()
const toast = useNotify()

const form = reactive({
  full_name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})

const errors = ref({})
const loading = ref(false)
const successMessage = ref('')

const submit = async () => {
  loading.value = true
  errors.value = {}
  successMessage.value = ''

  const res = await contactStore.store(form)
  loading.value = false

  if (res.success) {
    successMessage.value = res.message || 'Gửi liên hệ thành công.'
    form.full_name = ''
    form.email = ''
    form.phone = ''
    form.subject = ''
    form.message = ''
    toast.success(successMessage.value)
    return
  }

  if (res.errors) {
    errors.value = res.errors
  } else {
    toast.error(res.message || 'Gửi liên hệ thất bại, vui lòng thử lại sau.')
  }
}
</script>

<template>
  <section class="contact-page container">
    <div class="contact-header">
      <div>
        <h1>Liên hệ StyleHub</h1>
        <p>Gửi cho chúng tôi thông tin liên hệ, đội ngũ sẽ phản hồi bạn trong vòng 24-48 giờ.</p>
      </div>
    </div>

    <div class="contact-grid">
      <div class="contact-card">
        <form @submit.prevent="submit">
          <div class="field-group">
            <BaseInputText v-model="form.full_name" customPlaceholderInput="Họ và tên" :error="errors.full_name" />
            <BaseInputText v-model="form.email" customPlaceholderInput="Email" :error="errors.email" />
          </div>

          <div class="field-group">
            <BaseInputText v-model="form.phone" customPlaceholderInput="Số điện thoại" :error="errors.phone" />
            <BaseInputText v-model="form.subject" customPlaceholderInput="Chủ đề" :error="errors.subject" />
          </div>

          <div class="auth-form-group">
            <label for="message">Nội dung</label>
            <textarea id="message" v-model="form.message" class="auth-input" rows="6" placeholder="Nội dung liên hệ"></textarea>
            <div v-if="errors.message" class="auth-error-text">{{ errors.message[0] || errors.message }}</div>
          </div>

          <div class="action-row">
            <BaseButton type="submit" :disabled="loading" customText="Gửi liên hệ" customClass="btn btn-primary" />
          </div>
        </form>
      </div>

      <div class="contact-info-card">
        <h3>Thông tin hỗ trợ</h3>
        <p>Nếu bạn cần hỗ trợ nhanh, vui lòng liên hệ với chúng tôi qua:</p>
        <ul>
          <li>Email: hotro@stylehub.com</li>
          <li>Điện thoại: 0123 456 789</li>
          <li>Địa chỉ: Buôn Ma Thuột, Đắk Lắk</li>
        </ul>
      </div>
    </div>

    <!-- Map Section -->
    <div class="map-section">
      <h2>Tìm chúng tôi</h2>
      <p class="map-note">Địa chỉ: Buôn Ma Thuột, Đắk Lắk</p>
      <div class="map-actions">
        <a href="https://maps.google.com/maps?q=Buon%20Ma%20Thuot%2C%20Dak%20Lak&z=13&output=embed" target="_blank" rel="noreferrer" class="map-button">Mở trong Maps</a>
      </div>
      <div class="map-container">
        <iframe
          src="https://maps.google.com/maps?q=Buon%20Ma%20Thuot%2C%20Dak%20Lak&z=13&output=embed"
          width="100%"
          height="400"
          style="border:0;"
          allowfullscreen
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      </div>
    </div>
  </section>
</template>

<style scoped>
.contact-page {
  padding: 40px 0;
}

.contact-header h1 {
  font-size: 36px;
  margin-bottom: 16px;
  color: var(--text-main);
}

.contact-header p {
  color: var(--text-muted);
  max-width: 640px;
  line-height: 1.8;
}

.contact-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: 1.5fr 1fr;
  margin-top: 32px;
}

.contact-card,
.contact-info-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 28px;
  box-shadow: var(--shadow-sm);
}

.field-group {
  display: grid;
  gap: 20px;
  grid-template-columns: 1fr 1fr;
}

.map-section {
  margin-top: 40px;
  text-align: center;
}

.map-section h2 {
  font-size: 28px;
  margin-bottom: 20px;
  color: var(--text-main);
}

.map-container {
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border);
}

.map-container iframe {
  width: 100%;
  height: 400px;
  border: 0;
}

.map-note {
  margin-bottom: 10px;
  color: var(--text-muted);
}

.map-actions {
  margin-bottom: 16px;
  display: flex;
  justify-content: center;
}

.map-button {
  display: inline-block;
  padding: 10px 18px;
  border-radius: 999px;
  background: var(--primary);
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  border: 1px solid transparent;
  transition: background 0.2s ease, transform 0.2s ease;
}

.map-button:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
}

.auth-form-group label {
  display: block;
  margin-bottom: 8px;
  color: var(--text-muted);
}

.auth-input {
  min-height: 48px;
}

.auth-error-text {
  margin-top: 8px;
}

.action-row {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.contact-info-card h3 {
  margin-bottom: 16px;
}

.contact-info-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 10px;
}

.contact-info-card li {
  color: var(--text-muted);
}

@media (max-width: 992px) {
  .contact-grid {
    grid-template-columns: 1fr;
  }
}
</style>