<template>
  <section class="about-section py-6" data-aos="fade-up">
    <div class="container">
      <div class="text-center mb-5">
        <span class="eyebrow text-uppercase text-muted">Cam kết</span>
        <h2 class="section-title mt-3">Cam kết StyleHub với khách hàng và đối tác.</h2>
      </div>

      <div class="row g-4">
        <div class="col-md-6 col-xl-3" v-for="item in promises" :key="item.title">
          <div class="promise-card p-4 rounded-4 h-100 shadow-sm border">
            <span class="promise-icon d-inline-flex align-items-center justify-content-center mb-3 rounded-circle text-white fs-4" :class="item.colorClass">
              <i :class="item.icon"></i>
            </span>
            <h3 class="h6 fw-semibold">{{ item.title }}</h3>
            <p class="text-muted mb-0">{{ item.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const promises = [
  {
    title: 'Chất lượng',
    description: 'Mỗi sản phẩm đều trải qua kiểm soát kỹ thuật tỷ mỉ trước khi đến tay khách hàng.',
    icon: 'ph-bold ph-check-circle',
    colorClass: 'bg-primary',
  },
  {
    title: 'Uy tín',
    description: 'Cam kết minh bạch, giao hàng đúng hạn và dịch vụ sau bán hàng tận tâm.',
    icon: 'ph-bold ph-shield-check',
    colorClass: 'bg-dark',
  },
  {
    title: 'Hỗ trợ',
    description: 'Đội ngũ chăm sóc khách hàng sẵn sàng hỗ trợ 24/7 với giải pháp nhanh chóng.',
    icon: 'ph-bold ph-headset',
    colorClass: 'bg-success',
  },
  {
    title: 'Đổi mới',
    description: 'Luôn tìm kiếm công nghệ mới và phương pháp sáng tạo để nâng tầm sản phẩm.',
    icon: 'ph-bold ph-lightbulb',
    colorClass: 'bg-secondary',
  },
]
</script>

<style scoped>
.about-section {
  background: var(--background);
}

.promise-card {
  background: var(--surface);
  border-color: var(--border) !important;
  min-height: 220px;
  transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.promise-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 24px 50px rgba(15, 23, 42, 0.14);
}

.promise-icon {
  width: 60px;
  height: 60px;
}
</style>
