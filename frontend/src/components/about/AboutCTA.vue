<template>
  <section class="about-cta py-6 text-white" data-aos="fade-up">
    <div class="container">
      <div class="cta-card rounded-5 p-5 p-lg-6 d-flex flex-column flex-lg-row align-items-center justify-content-between gap-4">
        <div>
          <span class="eyebrow text-uppercase opacity-75">Đồng hành cùng chúng tôi</span>
          <h2 class="mt-3">Lấy cảm hứng từ phong cách hiện đại, cùng tạo nên trải nghiệm khác biệt.</h2>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-3">
          <router-link to="/contact" class="btn btn-light btn-lg text-uppercase">Liên hệ ngay</router-link>
          <router-link to="/shop" class="btn btn-outline-light btn-lg text-uppercase">Khám phá bộ sưu tập</router-link>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.about-cta {
  background: linear-gradient(135deg, #0b1220 0%, #151d2c 45%, #0f1524 100%);
}

.cta-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(18px);
}

.cta-card h2 {
  font-size: clamp(2rem, 2.3vw, 3rem);
  max-width: 780px;
}
</style>
