<template>
  <section class="about-hero position-relative overflow-hidden text-white" data-aos="fade-up">
    <div class="hero-media">
      <video autoplay muted loop playsinline class="hero-video">
        <source src="https://assets.mixkit.co/videos/preview/mixkit-fashion-woman-walking-on-pedestrian-41902-large.mp4" type="video/mp4" />
      </video>
      <div class="hero-overlay"></div>
      <div class="hero-accent"></div>
    </div>

    <div class="container position-relative py-6">
      <div class="row align-items-center gy-5">
        <div class="col-lg-6">
          <span class="eyebrow text-uppercase text-light opacity-75">Chúng tôi là StyleHub</span>
          <h1 class="display-5 fw-bold mt-3">Thiết kế phong cách đỉnh cao cho cuộc sống hiện đại.</h1>
          <p class="text-lead text-light opacity-80 mt-4">Kết hợp công nghệ, tinh thần sáng tạo và độ hoàn thiện cao cấp để tạo nên trải nghiệm thương hiệu sang trọng, tối giản và bền bỉ.</p>

          <div class="d-flex flex-column flex-sm-row gap-3 mt-5">
            <router-link to="/contact" class="btn btn-light btn-lg text-uppercase">Liên hệ</router-link>
            <router-link to="/shop" class="btn btn-outline-light btn-lg text-uppercase">Khám phá sản phẩm</router-link>
          </div>
        </div>

        <div class="col-lg-6 d-none d-lg-block">
          <div class="hero-card p-4 bg-white bg-opacity-10 border border-white border-opacity-15 rounded-4 shadow-xl">
            <p class="text-uppercase fs-7 letter-spacing-2 opacity-75 mb-3">Thương hiệu dẫn đầu</p>
            <h2 class="h3 fw-semibold">Tinh tế trong từng chi tiết, uy tín trong mỗi sản phẩm.</h2>
            <p class="mt-4 opacity-80">Mỗi bộ sưu tập đều được chắt lọc để phản ánh sự chuyên nghiệp và phong cách sống thời thượng của khách hàng toàn cầu.</p>
            <div class="d-flex align-items-center gap-3 mt-4">
              <div class="stat-text">
                <span class="h4 fw-bold mb-1 d-block">12+</span>
                <p class="mb-0 opacity-75">Năm xây dựng thương hiệu</p>
              </div>
              <div class="stat-text">
                <span class="h4 fw-bold mb-1 d-block">20+</span>
                <p class="mb-0 opacity-75">Quốc gia hiện diện</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.about-hero {
  min-height: 760px;
}

.hero-media {
  position: absolute;
  inset: 0;
  z-index: 0;
  overflow: hidden;
}

.hero-video,
.hero-media::after {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-video {
  filter: brightness(0.55) contrast(1.05);
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.14), transparent 30%),
    linear-gradient(180deg, rgba(9, 25, 42, 0.45), rgba(9, 25, 42, 0.85));
}

.hero-accent {
  position: absolute;
  bottom: -32px;
  right: -32px;
  width: 420px;
  height: 420px;
  background: rgba(255, 255, 255, 0.06);
  border-radius: 50%;
  filter: blur(32px);
}

.eyebrow {
  letter-spacing: 0.35em;
  font-size: 0.84rem;
}

.text-lead {
  font-size: 1.12rem;
  max-width: 560px;
}

.hero-card {
  backdrop-filter: blur(18px);
  border: 1px solid rgba(255, 255, 255, 0.13);
}

.shadow-xl {
  box-shadow: 0 32px 80px rgba(0, 0, 0, 0.18);
}

@media (max-width: 992px) {
  .about-hero {
    min-height: 680px;
  }
}

@media (max-width: 768px) {
  .about-hero {
    min-height: 620px;
    padding-top: 2.5rem;
    padding-bottom: 2.5rem;
  }
}
</style>
