<template>
  <section class="about-section" data-aos="fade-up">
    <div class="container py-6">
      <div class="section-header mb-5">
        <div>
          <span class="eyebrow text-uppercase text-muted">Câu chuyện thương hiệu</span>
          <h2 class="section-title mt-3">Hành trình biến đam mê thành sản phẩm biểu tượng.</h2>
        </div>
      </div>

      <div class="row gx-5 gy-4 align-items-center">
        <div class="col-lg-5">
          <p class="text-muted fs-5">StyleHub khởi đầu từ niềm tin rằng thời trang cao cấp không chỉ là trang phục, mà còn là trải nghiệm phong cách sống. Chúng tôi xây dựng thương hiệu với tôn chỉ thẩm mỹ tối giản, chất lượng vượt trội và tinh thần đổi mới.</p>
          <p class="text-muted fs-5">Từ bộ sưu tập đầu tiên đến ngày nay, mỗi bước phát triển đều đi kèm với lời cam kết về giá trị, sự bền bỉ và kết nối cảm xúc với khách hàng.</p>
        </div>

        <div class="col-lg-7">
          <div class="timeline-card p-4 rounded-4 border border-2 shadow-sm">
            <div class="timeline-item d-flex align-items-start gap-4 mb-4">
              <div class="marker"></div>
              <div>
                <span class="text-uppercase text-primary fw-semibold">2014</span>
                <h3 class="h5 mt-2">Khởi nguồn</h3>
                <p class="mb-0 text-muted">StyleHub ra đời từ studio nhỏ với mục tiêu phục vụ khách hàng tìm kiếm phong cách tối giản nhưng sang trọng.</p>
              </div>
            </div>

            <div class="timeline-item d-flex align-items-start gap-4 mb-4">
              <div class="marker"></div>
              <div>
                <span class="text-uppercase text-primary fw-semibold">2018</span>
                <h3 class="h5 mt-2">Mở rộng thị trường</h3>
                <p class="mb-0 text-muted">Thương hiệu mở rộng sang các thị trường quốc tế và thiết lập hệ sinh thái dịch vụ mua sắm trực tuyến cao cấp.</p>
              </div>
            </div>

            <div class="timeline-item d-flex align-items-start gap-4">
              <div class="marker"></div>
              <div>
                <span class="text-uppercase text-primary fw-semibold">2024</span>
                <h3 class="h5 mt-2">Định hình tương lai</h3>
                <p class="mb-0 text-muted">StyleHub trở thành biểu tượng phong cách hiện đại với các thiết kế độc quyền, giàu tính ứng dụng và trải nghiệm khách hàng vượt trội.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.about-section {
  position: relative;
  background: var(--background);
}

.eyebrow {
  font-size: 0.85rem;
  letter-spacing: 0.2em;
}

.timeline-card {
  background: var(--surface);
  border-color: var(--border) !important;
}

.marker {
  min-width: 18px;
  min-height: 18px;
  border-radius: 50%;
  background: #0d6efd;
  margin-top: 6px;
  box-shadow: 0 0 0 6px rgba(13, 110, 253, 0.08);
}

.section-title {
  font-size: clamp(2rem, 2.5vw, 3rem);
}
</style>
