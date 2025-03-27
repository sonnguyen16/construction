<template>
  <AdminLayout>
    <template #header>{{ project.name }}</template>
    <template #breadcrumb>Quản lý tài liệu dự án</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tài liệu dự án: {{ project.name }}</h3>
            <div class="card-tools">
              <Link :href="route('projects.show', project.id)" class="btn btn-sm btn-info">
                <i class="fas fa-arrow-left"></i> Quay lại
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div class="alert alert-info">
              <p>
                <strong>Chú ý:</strong> Hệ thống đã tự động tạo và mở thư mục dành riêng cho dự án này. Bạn có thể tạo
                các thư mục con bên trong để phân loại tài liệu theo mục đích.
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
  project: Object,
  fileManagerUrl: String,
  projectFolder: String
})

onMounted(() => {
  const iframe = document.getElementById('file-manager')
  iframe.onload = () => {
    iframe.contentWindow.goTo(`/1/${props.projectFolder}`)
  }
})
</script>

<style>
/* Thêm CSS để đảm bảo iframe hiển thị đúng */
iframe {
  min-height: 600px;
}
</style>
