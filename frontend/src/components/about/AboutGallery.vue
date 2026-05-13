<template>
  <section class="about-section py-6 bg-light" data-aos="fade-up">
    <div class="container">
      <div class="text-center mb-5">
        <span class="eyebrow text-uppercase text-muted">Hình ảnh hoạt động</span>
        <h2 class="section-title mt-3">Khám phá khoảnh khắc thương hiệu và trải nghiệm khách hàng.</h2>
      </div>

      <div class="gallery-grid row g-3">
        <div class="col-12 col-md-6 col-xl-4" v-for="image in gallery" :key="image.alt" data-aos="zoom-in">
          <div class="gallery-card rounded-4 overflow-hidden position-relative">
            <img :src="image.src" :alt="image.alt" class="img-fluid" />
            <div class="gallery-mask d-flex align-items-end p-4">
              <div>
                <p class="mb-1 text-white opacity-85">{{ image.label }}</p>
                <h3 class="h6 text-white mb-0">{{ image.caption }}</h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const gallery = [
  {
    src: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?q=80&w=900&auto=format&fit=crop',
    alt: 'Team meeting',
    label: 'Buổi gặp gỡ',
    caption: 'Bàn tròn sáng tạo cùng đội ngũ thiết kế',
  },
  {
    src: 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=900&auto=format&fit=crop',
    alt: 'Studio session',
    label: 'Studio',
    caption: 'Sản phẩm được tạo nên bởi kỹ thuật tỉ mỉ',
  },
  {
    src: 'https://images.unsplash.com/photo-1474631245212-32dc3c8310c6?q=80&w=900&auto=format&fit=crop',
    alt: 'Customer journey',
    label: 'Khách hàng',
    caption: 'Giao nhận dịch vụ cao cấp',
  },
  {
    src: 'https://images.unsplash.com/photo-1483058712412-4245e9b90334?q=80&w=900&auto=format&fit=crop',
    alt: 'Award display',
    label: 'Giải thưởng',
    caption: 'Tinh thần đổi mới sáng tạo được vinh danh',
  },
  {
    src: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=900&auto=format&fit=crop',
    alt: 'Retail environment',
    label: 'Cửa hàng',
    caption: 'Không gian mua sắm đẳng cấp và nhẹ nhàng',
  },
  {
    src: 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?q=80&w=900&auto=format&fit=crop',
    alt: 'Creative process',
    label: 'Quá trình sáng tạo',
    caption: 'Thử nghiệm màu sắc và chất liệu mới',
  },
]
</script>

<style scoped>
.gallery-card {
  min-height: 260px;
  transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.gallery-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.45s ease;
}

.gallery-card:hover img {
  transform: scale(1.03);
}

.gallery-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 24px 50px rgba(15, 23, 42, 0.16);
}

.gallery-mask {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 40%, rgba(11, 14, 23, 0.8));
}

.gallery-mask p,
.gallery-mask h3 {
  margin: 0;
}
</style>
