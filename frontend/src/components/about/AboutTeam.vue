<template>
  <section class="about-section bg-white py-6" data-aos="fade-up">
    <div class="container">
      <div class="text-center mb-5">
        <span class="eyebrow text-uppercase text-muted">Đội ngũ</span>
        <h2 class="section-title mt-3">Những con người đam mê tạo nên khác biệt.</h2>
      </div>

      <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <div class="col" v-for="member in team" :key="member.name">
          <div class="team-card p-4 rounded-4 shadow-sm border border-light h-100 d-flex flex-column">
            <div class="profile-preview mb-3" :style="{ backgroundImage: `url(${member.image})` }"></div>
            <h4 class="h5 mb-1">{{ member.name }}</h4>
            <p class="text-primary fw-semibold mb-3">{{ member.role }}</p>
            <p class="text-muted mb-0">{{ member.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const team = [
  {
    name: 'Lê Thị Hằng',
    role: 'Director of Design',
    description: 'Chịu trách nhiệm cho cảm hứng và chuẩn mực thẩm mỹ của từng bộ sưu tập.',
    image: 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?q=80&w=800&auto=format&fit=crop',
  },
  {
    name: 'Trần Văn Bình',
    role: 'Head of Product',
    description: 'Quản lý quá trình thiết kế, sản xuất và đảm bảo chất lượng quốc tế.',
    image: 'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?q=80&w=800&auto=format&fit=crop',
  },
  {
    name: 'Phạm Thùy Dung',
    role: 'Creative Director',
    description: 'Định hướng hình ảnh thương hiệu và chiến lược truyền thông đẳng cấp.',
    image: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?q=80&w=800&auto=format&fit=crop',
  },
]
</script>

<style scoped>
.team-leader-card {
  background: linear-gradient(180deg, rgba(10, 25, 45, 0.95), rgba(15, 23, 42, 0.95));
  color: #f8fafc;
}

.leader-avatar {
  width: 100%;
  height: 320px;
  border-radius: 32px;
  background-image: url('https://images.unsplash.com/photo-1517841905240-472988babdf9?q=80&w=800&auto=format&fit=crop');
  background-size: cover;
  background-position: center;
}

.team-card {
  transition: transform 0.35s ease, box-shadow 0.35s ease;
}

.team-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 28px 60px rgba(15, 23, 42, 0.12);
}

.profile-preview {
  width: 100%;
  height: 220px;
  border-radius: 28px;
  background-size: cover;
  background-position: center;
}
</style>
