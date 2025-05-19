<template>
  <div>
    <div class="toolbar flex align-items-center justify-between mb-3">
      <div class="flex gap-3">
        <label class="text-md font-normal">Dự án:</label>
        <select v-model="selectedProject" @change="loadDeletedTasks" class="mr-3 select">
          <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
        </select>
      </div>
      <Link :href="route('tasks.index')" class="btn btn-sm btn-info">
        <i class="fas fa-arrow-left mr-1"></i> Quay lại
      </Link>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Tên công việc
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Ngày bắt đầu
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Thời gian (ngày)
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Tiến độ
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Ngày xóa
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Thao tác
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="6" class="px-6 py-4 text-center">Đang tải dữ liệu...</td>
          </tr>
          <tr v-else-if="deletedTasks.length === 0">
            <td colspan="6" class="px-6 py-4 text-center">Không có công việc nào trong thùng rác</td>
          </tr>
          <tr v-for="task in deletedTasks" :key="task.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ task.text }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ task.start_date }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ task.duration }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: task.progress * 100 + '%' }"></div>
              </div>
              <div class="text-xs text-gray-500 mt-1">{{ Math.round(task.progress * 100) }}%</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ task.deleted_at }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="restoreTask(task.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                <i class="fas fa-trash-restore mr-1"></i> Khôi phục
              </button>
              <button @click="confirmDelete(task)" class="text-red-600 hover:text-red-900">
                <i class="fas fa-trash-alt mr-1"></i> Xóa vĩnh viễn
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Không cần modal tự tạo vì đã sử dụng SweetAlert -->
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'
const props = defineProps({
  projects: Array,
  defaultProject: Object
})

const selectedProject = ref(null)
const deletedTasks = ref([])
const loading = ref(false)

// Tải danh sách công việc đã xóa theo dự án
async function loadDeletedTasks() {
  if (!selectedProject.value) return

  loading.value = true
  try {
    const response = await axios.get(`/projects/${selectedProject.value}/deleted-tasks`)
    deletedTasks.value = response.data.data
  } catch (error) {
    console.error('Lỗi khi tải dữ liệu công việc đã xóa:', error)
    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: 'Không thể tải danh sách công việc đã xóa'
    })
  } finally {
    loading.value = false
  }
}

// Khôi phục công việc đã xóa
async function restoreTask(taskId) {
  try {
    await axios.post(`/tasks/${taskId}/restore`)
    Swal.fire({
      icon: 'success',
      title: 'Thành công',
      text: 'Khôi phục công việc thành công',
      timer: 1500,
      showConfirmButton: false
    })
    loadDeletedTasks() // Tải lại danh sách
  } catch (error) {
    console.error('Lỗi khi khôi phục công việc:', error)
    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: 'Không thể khôi phục công việc'
    })
  }
}

// Hiển thị modal xác nhận xóa vĩnh viễn
function confirmDelete(task) {
  Swal.fire({
    title: 'Xác nhận xóa vĩnh viễn',
    text: `Bạn có chắc chắn muốn xóa vĩnh viễn công việc "${task.text}"? Hành động này không thể hoàn tác.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Xóa vĩnh viễn',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      forceDeleteTask(task.id)
    }
  })
}

// Xóa vĩnh viễn công việc
async function forceDeleteTask(taskId) {
  if (!taskId) return

  try {
    await axios.delete(`/tasks/${taskId}/force-delete`)
    Swal.fire({
      icon: 'success',
      title: 'Thành công',
      text: 'Đã xóa vĩnh viễn công việc',
      timer: 1500,
      showConfirmButton: false
    })
    loadDeletedTasks() // Tải lại danh sách
  } catch (error) {
    console.error('Lỗi khi xóa vĩnh viễn công việc:', error)
    Swal.fire({
      icon: 'error',
      title: 'Lỗi',
      text: 'Không thể xóa vĩnh viễn công việc'
    })
  }
}

// Tải danh sách dự án khi component được tạo
onMounted(() => {
  // Chỉ sử dụng localStorage hoặc dự án mặc định
  const project_id_from_storage = localStorage.getItem('gantt_project_id')

  let selected_id = null

  if (project_id_from_storage) {
    // Kiểm tra xem dự án có tồn tại không
    const project_exists = props.projects.some((project) => project.id == project_id_from_storage)
    if (project_exists) {
      selected_id = project_id_from_storage
    }
  }

  // Nếu không có hoặc dự án không tồn tại, sử dụng dự án đầu tiên
  if (!selected_id && props.projects.length > 0) {
    selected_id = props.defaultProject?.id || props.projects[0].id
  }

  if (selected_id) {
    selectedProject.value = selected_id
    loadDeletedTasks()
  }
})
</script>

<style scoped>
.toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.select {
  padding: 4px 10px;
  width: 200px;
  border: 1px solid #ccc;
}
</style>
