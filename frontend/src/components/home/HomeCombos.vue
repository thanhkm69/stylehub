<script setup>
import { API_URL_IMAGE } from '@/config/env'

defineProps({
  combos: {
    type: Array,
    required: true
  }
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0)
}

const discountLabel = (combo) => {
  if (combo.discount_type === 'percentage') {
    return `Giảm ${Number(combo.discount_value)}%`
  }

  return `Giảm ${formatCurrency(combo.discount_value)}`
}

const imageUrl = (path) => path ? `${API_URL_IMAGE}/${path}` : ''

const formatDeadline = (value) => {
  if (!value) return 'Không giới hạn thời gian'

  return `Kết thúc ${new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(new Date(value))}`
}
</script>

<template>
  <section class="combo-section container">
    <div class="section-heading">
      <div>
        <p class="eyebrow">Mua theo set, giá tốt hơn</p>
        <h2>Combo nổi bật</h2>
        <p class="subheading">Phối sẵn những lựa chọn phù hợp cho phong cách của bạn.</p>
      </div>
    </div>

    <div class="combo-grid">
      <article v-for="combo in combos" :key="combo.id" class="combo-card">
        <div class="combo-media">
          <img
            v-if="combo.thumbnail"
            :src="imageUrl(combo.thumbnail)"
            :alt="combo.name"
            class="combo-cover"
            loading="lazy"
          />
          <div v-else class="combo-placeholder">
            <i class="ph ph-package"></i>
          </div>
          <span class="discount-pill">{{ discountLabel(combo) }}</span>
        </div>

        <div class="combo-content">
          <div class="combo-meta">
            <span class="deadline">{{ formatDeadline(combo.ends_at) }}</span>
          </div>
          <h3>{{ combo.name }}</h3>
          <p v-if="combo.description" class="description">{{ combo.description }}</p>

          <div class="item-list">
            <router-link
              v-for="item in combo.items.slice(0, 4)"
              :key="item.id"
              :to="{ name: 'ProductDetail', params: { slug: item.product.slug } }"
              class="combo-item"
              :title="item.product.name"
            >
              <img
                v-if="item.product.thumbnail"
                :src="imageUrl(item.product.thumbnail)"
                :alt="item.product.name"
                loading="lazy"
              />
              <span v-else class="item-placeholder"><i class="ph ph-t-shirt"></i></span>
              <small v-if="item.quantity > 1">x{{ item.quantity }}</small>
            </router-link>
            <span v-if="combo.items.length > 4" class="extra-items">+{{ combo.items.length - 4 }}</span>
          </div>

          <router-link
            v-if="combo.items[0]?.product"
            :to="{ name: 'ProductDetail', params: { slug: combo.items[0].product.slug } }"
            class="combo-action"
          >
            Khám phá combo <i class="ph ph-arrow-right"></i>
          </router-link>
        </div>
      </article>
    </div>
  </section>
</template>

<style scoped>
.combo-section {
  padding: 0 0 60px;
  animation: fadeInUp 0.8s ease-out 0.25s both;
}

.section-heading {
  align-items: end;
  display: flex;
  justify-content: space-between;
  margin-bottom: 24px;
}

.eyebrow {
  color: #e95032;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1.4px;
  margin: 0 0 8px;
  text-transform: uppercase;
}

.section-heading h2 {
  color: var(--text-main);
  font-size: clamp(28px, 3vw, 34px);
  font-weight: 800;
  margin: 0 0 7px;
}

.subheading {
  color: var(--text-muted);
  margin: 0;
}

.combo-grid {
  display: grid;
  gap: 22px;
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.combo-card {
  background: #fff;
  border: 1px solid #f4ddd6;
  border-radius: 22px;
  box-shadow: 0 13px 35px rgba(108, 36, 18, 0.08);
  overflow: hidden;
  transition: transform 0.28s ease, box-shadow 0.28s ease;
}

.combo-card:hover {
  box-shadow: 0 22px 45px rgba(108, 36, 18, 0.15);
  transform: translateY(-5px);
}

.combo-media {
  background: #fff3ef;
  height: 210px;
  overflow: hidden;
  position: relative;
}

.combo-cover {
  display: block;
  height: 100%;
  object-fit: cover;
  transition: transform 0.45s ease;
  width: 100%;
}

.combo-card:hover .combo-cover {
  transform: scale(1.05);
}

.combo-placeholder {
  align-items: center;
  background: linear-gradient(135deg, #fff2ed, #ffe1d5);
  color: #e95032;
  display: flex;
  font-size: 54px;
  height: 100%;
  justify-content: center;
}

.discount-pill {
  background: linear-gradient(110deg, #e62d25, #ff6030);
  border-radius: 999px;
  bottom: 14px;
  box-shadow: 0 8px 17px rgba(230, 45, 37, 0.3);
  color: #fff;
  font-size: 13px;
  font-weight: 800;
  left: 14px;
  padding: 8px 14px;
  position: absolute;
}

.combo-content {
  padding: 18px;
}

.combo-meta {
  align-items: center;
  display: flex;
  gap: 10px;
  justify-content: space-between;
  margin-bottom: 9px;
}

.deadline {
  color: #8a837e;
  font-size: 11px;
}

.combo-content h3 {
  color: var(--text-main);
  font-size: 20px;
  font-weight: 750;
  line-height: 1.25;
  margin: 0 0 7px;
}

.description {
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  color: var(--text-muted);
  display: -webkit-box;
  font-size: 13px;
  line-height: 1.45;
  margin: 0 0 16px;
  min-height: 38px;
  overflow: hidden;
}

.item-list {
  align-items: center;
  display: flex;
  gap: 8px;
  margin: 13px 0 18px;
}

.combo-item,
.extra-items {
  align-items: center;
  border: 1px solid #f2ded7;
  border-radius: 12px;
  display: flex;
  height: 53px;
  justify-content: center;
  overflow: hidden;
  position: relative;
  width: 53px;
}

.combo-item img {
  height: 100%;
  object-fit: cover;
  width: 100%;
}

.item-placeholder {
  color: #d99a87;
  font-size: 22px;
}

.combo-item small {
  background: #e95032;
  border-radius: 8px 0 0;
  bottom: 0;
  color: #fff;
  font-size: 10px;
  padding: 2px 5px;
  position: absolute;
  right: 0;
}

.extra-items {
  background: #fff5f1;
  color: #e95032;
  font-size: 13px;
  font-weight: 750;
}

.combo-action {
  align-items: center;
  color: #e34829;
  display: inline-flex;
  font-size: 14px;
  font-weight: 750;
  gap: 6px;
  text-decoration: none;
}

.combo-action:hover {
  color: #c62c1d;
  gap: 10px;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(26px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 992px) {
  .combo-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .combo-grid {
    grid-template-columns: 1fr;
  }

  .combo-media {
    height: 198px;
  }
}
</style>
