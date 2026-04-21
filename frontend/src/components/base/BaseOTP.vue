<script setup>
import { ref, onMounted, onUnmounted, watch, useTemplateRef } from "vue";
import BaseInputLabel from "./BaseInputLabel.vue";
import BaseInputError from "./BaseInputError.vue";
import BaseButton from "@/components/base/BaseButton.vue";

const modelValue = defineModel();

const props = defineProps({
  time: {
    type: Number,
    default: 0,
  },
  error: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["resend"]);

const otp = ref(["", "", "", "", "", ""]);
const inputs = useTemplateRef("inputs");

const count = ref(props.time);
let interval = null;

const startCountdown = () => {
  stopCountdown();
  count.value = props.time;
  interval = setInterval(() => {
    if (count.value <= 0) {
      stopCountdown();
      return;
    }
    count.value--;
  }, 1000);
};

const stopCountdown = () => {
  if (interval) {
    clearInterval(interval);
    interval = null;
  }
};

watch(
  () => props.time,
  (newVal) => {
    if (newVal > 0) {
      startCountdown();
    }
  }
);

onMounted(() => {
  focusInput(0);
  if (props.time > 0) {
    startCountdown();
  }
});

onUnmounted(() => {
  stopCountdown();
});

function focusInput(index) {
  if (index < 0 || index > otp.value.length) return;
  inputs.value[index]?.focus();
}

function handleInput(index) {
  if (!otp.value[index]) return;
  focusInput(index + 1);
}

function handleDown(e, index) {
  if (e.ctrlKey || e.metaKey) return;

  const allowKeys = ["Backspace", "Delete", "ArrowLeft", "ArrowRight", "Tab"];

  if (allowKeys.includes(e.key)) {
    if (e.key === "Backspace" || e.key === "Delete") {
      e.preventDefault();

      if (otp.value[index]) {
        otp.value[index] = "";
      } else {
        focusInput(index - 1);
      }
    }

    if (e.key === "ArrowLeft") {
      e.preventDefault();
      focusInput(index === 0 ? otp.value.length - 1 : index - 1);
    }

    if (e.key === "ArrowRight") {
      e.preventDefault();
      focusInput(index === otp.value.length - 1 ? 0 : index + 1);
    }

    return;
  }

  if (!/^[0-9]$/.test(e.key)) {
    e.preventDefault();
  }
}

function handlePaste(e) {
  e.preventDefault();
  const data = e.clipboardData
    .getData("text")
    .replace(/\D/g, "")
    .slice(0, otp.value.length);
  if (data.length !== otp.value.length) {
    alert("OTP không hợp lệ");
    return;
  }
  data.split("").forEach((char, index) => {
    otp.value[index] = char;
  });

  focusInput(otp.value.length - 1);
}

const resetOtp = () => {
  otp.value = ["", "", "", "", "", ""];
  focusInput(0);
};

const handleResend = () => {
  if (count.value <= 0) {
    emit("resend");
  }
};

const formatTime = (s) => {
  const min = Math.floor(s / 60);
  const sec = s % 60;
  return min > 0 ? `${min}:${sec.toString().padStart(2, '0')}` : `${sec}s`;
};

watch(
  otp,
  () => {
    modelValue.value = otp.value.join("");
  },
  { deep: true }
);

defineExpose({
  startCountdown,
  stopCountdown,
});
</script>

<template>
  <div class="d-flex flex-column gap-2">
    <!-- Label -->
    <div class="d-flex justify-content-between align-items-center">
      <BaseInputLabel labelContent="Mã OTP" />
      <div v-if="time > 0" class="small">
        <span v-if="count > 0" class="text-muted">
          Hết hạn sau <strong>{{ count }}s</strong>
        </span>
        <span v-else class="text-danger"> Mã OTP đã hết hạn </span>
      </div>
    </div>

    <!-- OTP inputs -->
    <div class="d-flex gap-2 justify-content-center">
      <input v-for="(item, index) in otp" :key="index" ref="inputs" type="text" pattern="[0-9]+" inputmode="numeric"
        maxlength="1" class="form-control text-center otp-input" v-model="otp[index]" @input="handleInput(index)"
        @keydown="handleDown($event, index)" @paste="handlePaste($event)" />
      <BaseButton customText="Làm mới" @click="resetOtp" customType="button" />
    </div>

    <!-- Actions -->
    <div v-if="time > 0" class="d-flex justify-content-center mt-2">
      <BaseButton :customText="count > 0 ? `Gửi lại sau ${count}s` : 'Gửi lại OTP'" customClass="btn btn-secondary"
        customType="button" @click="handleResend" :disabled="count > 0" />
    </div>

    <!-- Error -->
    <BaseInputError :error="error" style="text-align: center; margin-top: 0.75rem" />
  </div>
</template>

<style scoped>
.otp-input {
  width: 50px;
  height: 55px;
  font-size: 20px;
  border-radius: 10px;
}

.otp-input:focus {
  border-color: var(--auth-accent, #d4a574);
  background: var(--auth-card-bg, #ffffff);
  box-shadow: 0 0 0 4px rgba(212, 165, 116, 0.15);
  transform: translateY(-2px);
}
</style>