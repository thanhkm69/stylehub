<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'
import { API_URL_IMAGE } from '@/config/env'
import { computed } from 'vue'

const props = defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean
})

const emit = defineEmits(["show", "update", "destroy"])

const categorizedData = computed(() => {
    let rootIndex = 0

    return (props.data || []).map(item => {
        if (!item.parent_id) {
            rootIndex++
            return {
                ...item,
                stt: (props.params.page - 1) * props.params.limit + rootIndex
            }
        }

        return {
            ...item,
            stt: '-'.repeat(item.level)
        }
    })
})

</script>

<template>
    <table class="table">
        <thead>
            <tr>
                <th>STT</th>
                <th>Hình ảnh</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>

        <tbody>
            <!-- Loading -->
            <tr v-if="loadingData">
                <td colspan="7" class="text-center">
                    <BaseLoading />
                </td>
            </tr>

            <!-- Không có dữ liệu -->
            <tr v-else-if="!categorizedData || categorizedData.length === 0">
                <td colspan="7" class="text-center">
                    Không có dữ liệu
                </td>
            </tr>

            <!-- Có dữ liệu -->
            <tr v-else v-for="(item, index) in categorizedData" :key="item.id">
                <td>
                    {{ item.stt }}
                </td>
                <td>
                    <img v-if="item.image" :src="`${API_URL_IMAGE}/${item.image}`" :alt="item.name" width="50"
                        height="50">
                </td>

                <td>
                    <strong>{{ item.name }}</strong><br>
                    <small class="text-muted">{{ item.slug }}</small>
                </td>

                <td>{{ item.description }}</td>
                <td>{{ item.display }}</td>
                <td>{{ item.status ? 'Hiển thị' : 'Ẩn' }}</td>

                <td>
                    <BaseButton @click="emit('show', item)" customText="Xem" customClass="btn-info" />
                    <BaseButton @click="emit('update', item)" customText="Sửa" customClass="btn-warning" />
                    <BaseButton @click="emit('destroy', item.id)" customText="Xóa" customClass="btn-danger" />
                </td>
            </tr>
        </tbody>
    </table>
</template>