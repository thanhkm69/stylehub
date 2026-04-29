<script setup>
import { ref } from 'vue';
import BaseInputLabel from './BaseInputLabel.vue';
import BaseInputError from './BaseInputError.vue';

const props = defineProps({
    customId: {
        default: "",
        type: [String, Number],
    },
    labelContent: {
        default: "",
        type: String,
    },
    customAccept: {
        type: String,
        default: ""
    },
    error: {
        type: String,
        default: ""
    }
})

const emit = defineEmits(["change"])
const fileName = ref('');

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        fileName.value = file.name;
    } else {
        fileName.value = '';
    }
    emit('change', event);
};
</script>

<template>
    <div class="file-input-wrapper">
        <BaseInputLabel :customId="customId" :labelContent="labelContent" />
        
        <label :for="customId" class="custom-file-upload" :class="{ 'has-error': error }">
            <div class="upload-content">
                <div class="icon-box">
                    <i class="ph-bold ph-cloud-arrow-up"></i>
                </div>
                <div class="text-box">
                    <span v-if="!fileName" class="placeholder">Nhấn để chọn ảnh hoặc kéo thả vào đây</span>
                    <span v-else class="filename">{{ fileName }}</span>
                </div>
                <button type="button" class="browse-btn">Chọn tệp</button>
            </div>
            <input 
                type="file" 
                :id="customId" 
                :accept="customAccept" 
                @change="handleFileChange"
                class="hidden-input"
            >
        </label>

        <BaseInputError :error="error" />
    </div>
</template>

<style scoped>
.file-input-wrapper {
    margin-bottom: 20px;
}

.custom-file-upload {
    display: block;
    width: 100%;
    cursor: pointer;
    background: #f8fafc;
    border: 2px dashed #e2e8f0;
    border-radius: var(--radius-lg);
    padding: 12px;
    transition: all 0.2s ease;
    position: relative;
}

.custom-file-upload:hover {
    border-color: var(--primary);
    background: #f1f5f9;
}

.custom-file-upload.has-error {
    border-color: #ef4444;
    background: #fef2f2;
}

.upload-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.icon-box {
    width: 44px;
    height: 44px;
    background: #ffffff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 20px;
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
}

.text-box {
    flex: 1;
    min-width: 0;
}

.placeholder {
    font-size: 14px;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

.filename {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-main);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

.browse-btn {
    padding: 8px 16px;
    background: #ffffff;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-main);
    pointer-events: none; /* Let the label handle click */
    white-space: nowrap;
}

.hidden-input {
    display: none;
}

/* Focused state using label focus-within */
.custom-file-upload:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}
</style>