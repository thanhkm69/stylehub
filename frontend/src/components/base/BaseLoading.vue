<script setup>
// Bạn có thể thêm các logic quản lý trạng thái (ví dụ: const isLoading = ref(true)) ở đây nếu cần.
</script>

<template>
  <div class="fashion-loader-container">
    <div class="loader-content">
      <div class="monogram-logo">
        <span class="letter-n">N</span>
        <span class="letter-r">R</span>
      </div>
      
      <div class="brand-name">STYLEHUB</div>
      <div class="brand-tagline">FASHION BRAND</div>

      <div class="progress-bar-container">
        <div class="progress-bar"></div>
      </div>
      
      <div class="loading-text">Đang xử lý...</div>
    </div>
  </div>
</template>

<style scoped>
/* Bao phủ toàn màn hình */
.fashion-loader-container {
  background-color: #ffffff;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.loader-content {
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* Kiểu dáng cho cụm chữ NR */
.monogram-logo {
  font-family: 'Playfair Display', 'Times New Roman', Times, serif; /* Font có chân giống logo */
  font-size: 90px;
  font-weight: 700;
  color: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  /* Hiệu ứng nhịp đập nhẹ nhàng */
  animation: pulse 2s infinite ease-in-out;
}

/* Lồng ghép chữ N và R */
.letter-n {
  z-index: 2;
}

.letter-r {
  margin-left: -20px; /* Kéo chữ R lại gần chữ N để tạo độ lồng ghép */
  z-index: 1;
}

/* Tên thương hiệu */
.brand-name {
  font-family: 'Arial', sans-serif; /* Font không chân giống STYLEHUB */
  font-size: 24px;
  letter-spacing: 3px;
  margin-top: 10px;
  color: #000;
}

.brand-tagline {
  font-family: 'Arial', sans-serif;
  font-size: 11px;
  letter-spacing: 2px;
  color: #333;
  margin-bottom: 30px;
}

/* Thanh Loading (Progress bar) */
.progress-bar-container {
  width: 180px;
  height: 2px;
  background-color: #f0f0f0;
  overflow: hidden;
  position: relative;
  margin-bottom: 12px;
}

.progress-bar {
  position: absolute;
  top: 0;
  left: -50%;
  height: 100%;
  width: 50%;
  background-color: #000;
  /* Hiệu ứng trượt qua lại */
  animation: slide-loading 1.5s infinite ease-in-out;
}

/* Chữ "Đang xử lý..." */
.loading-text {
  font-family: 'Arial', sans-serif;
  font-size: 13px;
  color: #666;
  letter-spacing: 1px;
  animation: fade-text 1.5s infinite ease-in-out;
}

/* --- Các Animation --- */

@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.05); opacity: 0.8; }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes slide-loading {
  0% { left: -50%; width: 50%; }
  50% { width: 100%; }
  100% { left: 100%; width: 50%; }
}

@keyframes fade-text {
  0% { opacity: 0.4; }
  50% { opacity: 1; }
  100% { opacity: 0.4; }
}
</style>