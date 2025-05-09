<template>
  <AdminLayout>
    <template #header>Chi tiết công việc</template>
    <template #breadcrumb>Chi tiết công việc</template>

    <div class="row">
      <!-- Tabs cho Nhân sự, Vật tư và Tài liệu -->
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h3 class="card-title mb-0">{{ task.name }}</h3>
              </div>
              <div class="card-tools">
                <Link :href="route('tasks.index')" class="btn btn-sm btn-info">
                  <i class="fas fa-arrow-left"></i> Quay lại
                </Link>
              </div>
            </div>
          </div>
          <div class="card-header p-2">
            <ul class="nav nav-pills">
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeTab === 'users' }"
                  href="#"
                  @click.prevent="changeTab('users')"
                >
                  <i class="fas fa-users mr-1"></i> Nhân sự
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeTab === 'products' }"
                  href="#"
                  @click.prevent="changeTab('products')"
                >
                  <i class="fas fa-tools mr-1"></i> Vật tư
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link"
                  :class="{ active: activeTab === 'files' }"
                  href="#"
                  @click.prevent="changeTab('files')"
                >
                  <i class="fas fa-file mr-1"></i> Tài liệu
                </a>
              </li>
            </ul>
          </div>
          <div class="card-body">
            <div class="tab-content">
              <!-- Tab Nhân sự -->
              <div class="tab-pane" :class="{ active: activeTab === 'users' }">
                <TaskUsers :task-id="task.id" />
              </div>
              <!-- Tab Vật tư -->
              <div class="tab-pane" :class="{ active: activeTab === 'products' }">
                <TaskProducts :task-id="task.id" />
              </div>
              <!-- Tab Tài liệu - Nhúng trực tiếp laravel-filemanager -->
              <div class="tab-pane" :class="{ active: activeTab === 'files' }">
                <div class="file-manager-container">
                  <iframe
                    id="file-manager"
                    :src="fileManagerUrl"
                    style="width: 100%; height: 600px; overflow: hidden; border: none"
                  ></iframe>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { formatDate } from '@/utils'
import TaskUsers from './Components/TaskUsers.vue'
import TaskProducts from './Components/TaskProducts.vue'

const props = defineProps({
  task: Object,
  project: Object
})

const activeTab = ref('users')

// Tạo đường dẫn thư mục của task
const taskFolder = computed(() => {
  return `tasks/${props.task.id}_${props.task.name.replace(/[\/\\?%*:|"<>]/g, '_')}`
})

// Tạo đường dẫn đến laravel-filemanager
const fileManagerUrl = computed(() => {
  // Trả về URL của laravel-filemanager với tham số type=files
  return `/laravel-filemanager?type=files`
})

// Xử lý iframe khi mount
onMounted(() => {
  // Kiểm tra nếu tab files được chọn mặc định
  if (activeTab.value === 'files') {
    initFileManager()
  }
})

// Hàm khởi tạo file manager
const initFileManager = () => {
  // Đợi iframe load xong
  setTimeout(() => {
    const iframe = document.getElementById('file-manager')
    if (iframe) {
      // Đảm bảo iframe đã load xong
      if (iframe.contentWindow && iframe.contentWindow.goTo) {
        // Mở thư mục của task
        iframe.contentWindow.goTo(`/1/${taskFolder.value}`)
      } else {
        // Nếu chưa load xong, đặt sự kiện onload
        iframe.onload = () => {
          if (iframe.contentWindow && iframe.contentWindow.goTo) {
            iframe.contentWindow.goTo(`/1/${taskFolder.value}`)
          }
        }
      }
    }
  }, 500) // Đợi 500ms để đảm bảo iframe đã được tạo trong DOM
}

// Khi chuyển tab, kiểm tra nếu là tab files thì khởi tạo file manager
const changeTab = (tab) => {
  activeTab.value = tab

  // Nếu chuyển sang tab files, khởi tạo file manager
  if (tab === 'files') {
    initFileManager()
  }
}
</script>
