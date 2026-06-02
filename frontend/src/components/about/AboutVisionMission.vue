<template>
  <section class="about-section py-6" data-aos="fade-up">
    <div class="container">
      <div class="text-center mb-5">
        <span class="eyebrow text-uppercase text-muted">Tầm nhìn & Sứ mệnh</span>
        <h2 class="section-title mt-3">Giá trị dẫn dắt mọi quyết định của chúng tôi.</h2>
      </div>

      <div class="row g-4">
        <div class="col-md-6 col-xl-4">
          <div class="info-card p-4 rounded-4 h-100 shadow-sm border">
            <span class="info-icon bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"><i class="ph-bold ph-eye"></i></span>
            <h3 class="h5">Tầm nhìn</h3>
            <p class="text-muted">Trở thành thương hiệu thời trang công nghệ được yêu thích bởi sự tinh tế, trải nghiệm khách hàng chu đáo và thiết kế bền vững.</p>
          </div>
        </div>
        <div class="col-md-6 col-xl-4">
          <div class="info-card p-4 rounded-4 h-100 shadow-sm border">
            <span class="info-icon bg-dark text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"><i class="ph-bold ph-rocket"></i></span>
            <h3 class="h5">Sứ mệnh</h3>
            <p class="text-muted">Mang đến những sản phẩm thời trang cao cấp, linh hoạt và dễ tiếp cận, đồng thời tăng cường niềm tin bằng chất lượng vượt trội.</p>
          </div>
        </div>
        <div class="col-xl-4">
          <div class="info-card p-4 rounded-4 h-100 shadow-sm border">
            <span class="info-icon bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"><i class="ph-bold ph-heart-straight"></i></span>
            <h3 class="h5">Giá trị cốt lõi</h3>
            <ul class="list-unstyled mt-3 mb-0 text-muted">
              <li class="mb-2">• Chất lượng hoàn hảo</li>
              <li class="mb-2">• Uy tín và minh bạch</li>
              <li class="mb-2">• Đổi mới không ngừng</li>
              <li>• Trải nghiệm khách hàng tinh tế</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.about-section {
  background: var(--surface);
}

.info-card {
  background: var(--background);
  border-color: var(--border) !important;
  transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.info-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 28px 60px rgba(15, 23, 42, 0.12);
}

.info-icon {
  width: 56px;
  height: 56px;
  font-size: 1.25rem;
}
</style>
