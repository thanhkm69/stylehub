<script setup>
import BaseButton from '@/components/base/BaseButton.vue'
import BaseLoading from '@/components/base/BaseLoading.vue'

const props = defineProps({
    params: Object,
    data: Array,
    loadingData: Boolean
})

const emit = defineEmits(["show", "update", "destroy", "openValues"])

</script>

<template>
    <table class="table">
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên thuộc tính</th>
                <th>Giá trị</th>
                <th>Slug</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>

        <tbody>
            <!-- Loading -->
            <tr v-if="loadingData">
                <td colspan="6" class="text-center">
                    <BaseLoading />
                </td>
            </tr>

            <!-- Không có dữ liệu -->
            <tr v-else-if="!data || data.length === 0">
                <td colspan="6" class="text-center">
                    Không có dữ liệu
                </td>
            </tr>

            <!-- Có dữ liệu -->
            <tr v-else v-for="(item, index) in data" :key="item.id">
                <td>
                    {{ (params.page - 1) * params.limit + index + 1 }}
                </td>

                <td>
                    <strong>{{ item.name }}</strong>
                </td>

                <td>
                    <BaseButton @click="emit('openValues', item)" :customText="String(item.values_count || 0)" customClass="btn-outline-primary btn-sm rounded-pill px-3" />
                </td>

                <td>
                    <small class="text-muted">{{ item.slug }}</small>
                </td>

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
