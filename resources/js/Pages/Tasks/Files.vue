<template>
  <AdminLayout>
    <template #header>{{ task.name }}</template>
    <template #breadcrumb>Quản lý tài liệu công việc</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tài liệu công việc: {{ task.name }}</h3>
            <div class="card-tools">
              <Link :href="route('tasks.index')" class="btn btn-sm btn-info">
                <i class="fas fa-arrow-left"></i> Quay lại
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              <p>
                <i class="fas fa-info-circle mr-1"></i> Đây là khu vực quản lý tài liệu cho công việc. Bạn có thể tải
                lên, xem và tải xuống các tài liệu.
              </p>
              <p><strong>Dự án:</strong> {{ project.name }} ({{ project.code }})</p>
              <p><strong>Công việc:</strong> {{ task.name }}</p>
              <p>
                <strong>Chú ý:</strong> Hệ thống đã tự động tạo và mở thư mục dành riêng cho công việc này. Bạn có thể
                tạo các thư mục con bên trong để phân loại tài liệu theo mục đích.
              </p>
            </div>

            <!-- Nhúng File Manager -->
            <iframe
              id="file-manager"
              :src="fileManagerUrl"
              style="width: 100%; height: 600px; overflow: hidden; border: none"
            ></iframe>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { onMounted } from 'vue'

const props = defineProps({
  task: Object,
  project: Object,
  fileManagerUrl: String,
  taskFolder: String
})

onMounted(() => {
  const iframe = document.getElementById('file-manager')
  iframe.onload = () => {
    iframe.contentWindow.goTo(`/1/${props.taskFolder}`)
  }
})
</script>

<style>
/* Thêm CSS để đảm bảo iframe hiển thị đúng */
iframe {
  min-height: 600px;
}
</style>
