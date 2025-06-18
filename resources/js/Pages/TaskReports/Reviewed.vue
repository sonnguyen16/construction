<template>
  <AdminLayout :title="task ? `Lịch sử báo cáo: ${task.name}` : 'Báo Cáo Đã Duyệt'">
    <template #header>
      <div class="d-flex justify-content-between align-items-center">
        <h4 class="font-semibold text-gray-800 leading-tight">
          {{ task ? `Lịch sử báo cáo: ${task.name}` : 'Lịch sử báo cáo' }}
        </h4>
      </div>
    </template>
    <div v-if="task">
      <button class="btn btn-primary mb-3" @click="startReporting"><i class="fas fa-plus me-1"></i>Nộp Báo cáo</button>
    </div>
    <div class="">
      <!-- Danh sách báo cáo -->
      <div class="card">
        <div class="card-body">
          <div v-if="reports.data.length === 0" class="text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <p class="text-muted">Không có báo cáo nào được tìm thấy</p>
          </div>
          <div v-else>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Công việc</th>
                    <th>Người báo cáo</th>
                    <th>Tiến độ</th>
                    <th>Nội dung báo cáo</th>
                    <th>Trạng thái</th>
                    <th>Phản hồi của người duyệt</th>
                    <th>File đính kèm</th>
                    <th>Ngày duyệt</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="report in reports.data" :key="report.id">
                    <td>{{ report.task_name }}</td>
                    <td>{{ report.user_name }}</td>
                    <td>
                      <div class="progress" style="height: 10px">
                        <div
                          class="progress-bar"
                          role="progressbar"
                          :style="{ width: report.progress + '%' }"
                          :class="getProgressBarClass(report.progress)"
                        >
                          {{ report.progress }}%
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="text-wrap" style="max-width: 200px">
                        {{ report.message }}
                      </div>
                    </td>
                    <td>
                      <span :class="getStatusBadgeClass(report.status)">
                        {{ getStatusText(report.status) }}
                      </span>
                    </td>
                    <td>
                      <div class="text-wrap" style="max-width: 200px">
                        {{ report.review_message || '(Không có phản hồi)' }}
                      </div>
                    </td>
                    <td>
                      <div v-if="report.files && report.files.length > 0">
                        <div v-for="file in report.files" :key="file.id" class="mb-1">
                          <a :href="'/storage/' + file.file_path" target="_blank" class="text-decoration-none">
                            <i class="fas fa-file me-1"></i> {{ file.file_name }}
                          </a>
                        </div>
                      </div>
                      <span v-else class="text-muted">(Không có file)</span>
                    </td>
                    <td>{{ formatDate(report.reviewed_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Phân trang -->
            <div class="mt-4">
              <Pagination :links="reports.links" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal báo cáo -->
    <div id="reportModal" class="modal fade" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reportModalLabel">Báo cáo tiến độ công việc</h5>
            <button type="button" class="btn-close" @click="cancelReport"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label class="form-label">Nội dung báo cáo <span class="text-danger">*</span></label>
                <textarea
                  class="form-control"
                  v-model="newReport.message"
                  placeholder="Nhập nội dung báo cáo"
                  rows="3"
                  required
                ></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Tiến độ <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center">
                  <input
                    type="range"
                    class="form-range flex-grow-1"
                    v-model.number="newReport.progress"
                    min="0"
                    max="100"
                    step="1"
                  />
                  <span class="ms-2 fw-bold">{{ newReport.progress }}%</span>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Tệp đính kèm</label>
                <input type="file" class="form-control" ref="fileInput" @change="handleFileChange" multiple />
                <small class="text-muted">Có thể chọn nhiều file, tối đa 10MB mỗi file</small>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="cancelReport">Hủy</button>
            <button type="button" class="btn btn-primary" @click="submitReport" :disabled="isSubmitting">
              Gửi báo cáo
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { formatDate, showSuccess, showError } from '@/utils'
import axios from 'axios'

// Lấy props từ controller
const props = defineProps({
  reports: Object,
  task: Object
})

// Lấy thông tin từ URL
const page = usePage()
const taskId = computed(() => {
  return usePage().url.searchParams.get('task_id')
})

// Xử lý điều hướng về trang chủ
const goBack = () => {
  if (taskId.value) {
    return `/tasks/${taskId.value}`
  }
  return '/dashboard'
}

// Trạng thái báo cáo
const isSubmitting = ref(false)
const fileInput = ref(null)

// Form dữ liệu
const newReport = ref({
  message: '',
  progress: props.task ? props.task.progress * 100 || 0 : 0,
  files: []
})

// Bắt đầu báo cáo
const startReporting = () => {
  // Hiển thị modal báo cáo
  document.getElementById('reportModal').classList.add('show')
  document.getElementById('reportModal').style.display = 'block'
  document.body.classList.add('modal-open')
  document.body.style.overflow = 'hidden'
  document.body.style.paddingRight = '17px'
  document.body.appendChild(createBackdrop())
}

// Tạo backdrop cho modal
const createBackdrop = () => {
  const backdrop = document.createElement('div')
  backdrop.className = 'modal-backdrop fade show'
  return backdrop
}

// Hủy báo cáo
const cancelReport = () => {
  newReport.value = {
    message: '',
    progress: props.task ? props.task.progress * 100 || 0 : 0,
    files: []
  }
  if (fileInput.value) {
    fileInput.value.value = ''
  }

  // Ẩn modal
  closeModal()
}

// Đóng modal
const closeModal = () => {
  document.getElementById('reportModal').classList.remove('show')
  document.getElementById('reportModal').style.display = 'none'
  document.body.classList.remove('modal-open')
  document.body.style.overflow = ''
  document.body.style.paddingRight = ''
  const backdrop = document.querySelector('.modal-backdrop')
  if (backdrop) backdrop.remove()
}

// Xử lý thay đổi file
const handleFileChange = (event) => {
  newReport.value.files = event.target.files
}

// Gửi báo cáo
const submitReport = async () => {
  if (!props.task) return

  isSubmitting.value = true

  const formData = new FormData()
  formData.append('message', newReport.value.message)
  formData.append('progress', newReport.value.progress)

  if (newReport.value.files && newReport.value.files.length) {
    for (let i = 0; i < newReport.value.files.length; i++) {
      formData.append(`files[${i}]`, newReport.value.files[i])
    }
  }

  try {
    const response = await axios.post(
      `/api/projects/${props.task.project_id}/tasks/${props.task.id}/reports`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    // Hiển thị thông báo thành công
    showSuccess(response.data.message || 'Báo cáo đã được gửi thành công')

    // Reset form và đóng modal
    cancelReport()

    // Tải lại trang sau 1 giây
    setTimeout(() => {
      router.reload()
    }, 1000)
  } catch (error) {
    console.error('Lỗi khi gửi báo cáo:', error)
    const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi gửi báo cáo. Vui lòng thử lại sau.'
    showError(errorMessage)
  } finally {
    isSubmitting.value = false
  }
}

// Lấy class cho badge trạng thái
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'approved':
      return 'badge bg-success'
    case 'rejected':
      return 'badge bg-danger'
    default:
      return 'badge bg-secondary'
  }
}

// Lấy text cho trạng thái
const getStatusText = (status) => {
  switch (status) {
    case 'approved':
      return 'Đã duyệt'
    case 'rejected':
      return 'Từ chối'
    default:
      return 'Không xác định'
  }
}

// Lấy class cho thanh tiến độ
const getProgressBarClass = (progress) => {
  if (progress < 30) return 'bg-danger'
  if (progress < 70) return 'bg-warning'
  return 'bg-success'
}
</script>

<style scoped>
.progress {
  height: 15px;
  border-radius: 5px;
}
.progress-bar {
  font-size: 10px;
  line-height: 15px;
  font-weight: bold;
}
.text-wrap {
  white-space: normal;
  word-break: break-word;
}
</style>
