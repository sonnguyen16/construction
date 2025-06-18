<template>
  <div class="task-report-widget">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Công việc được giao</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-3">
          <div class="spinner-border" role="status"></div>
        </div>
        <div v-else-if="assignedTasks.length === 0" class="text-center py-3">
          <p class="text-muted mb-0">Bạn không có công việc nào được giao</p>
        </div>
        <div v-else>
          <ul class="list-group list-group-flush">
            <li v-for="task in assignedTasks" :key="task.id" class="list-group-item">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                  <Link :href="route('tasks.show', task.id)" class="task-name fw-bold">
                    {{ task.name }}
                  </Link>
                  <span class="badge bg-secondary ms-2">{{ task.project.name }}</span>
                </div>
                <div>
                  <button
                    v-if="!reportingTaskId || reportingTaskId !== task.id"
                    class="btn btn-sm btn-primary"
                    @click="startReporting(task)"
                  >
                    <i class="fas fa-chart-line me-1"></i> Báo cáo
                  </button>
                  <Link 
                    :href="`/task-reports/reviewed?task_id=${task.id}`" 
                    class="btn btn-sm btn-info ms-1"
                  >
                    <i class="fas fa-history me-1"></i> Lịch sử
                  </Link>
                </div>
              </div>

              <div class="task-info small text-muted mb-2">
                <div>
                  <i class="far fa-calendar-alt me-1"></i> {{ formatDate(task.start_date) }} -
                  {{ formatDate(task.end_date) }}
                </div>
                <div><i class="fas fa-tasks me-1"></i> Tiến độ: {{ task.progress * 100 || 0 }}%</div>
              </div>

              <div class="progress" style="height: 5px">
                <div
                  class="progress-bar"
                  role="progressbar"
                  :style="{ width: (task.progress * 100 || 0) + '%' }"
                  :class="getProgressBarClass(task)"
                  :aria-valuenow="task.progress * 100 || 0"
                  aria-valuemin="0"
                  aria-valuemax="100"
                ></div>
              </div>

              <!-- Form báo cáo tiến độ -->
              <div v-if="reportingTaskId === task.id" class="report-form mt-3 pt-2 border-top">
                <form @submit.prevent="submitReport">
                  <div class="mb-3">
                    <label for="message" class="form-label">Nội dung báo cáo <span class="text-danger">*</span></label>
                    <textarea
                      id="message"
                      v-model="newReport.message"
                      class="form-control"
                      rows="3"
                      required
                    ></textarea>
                  </div>
                  <div class="mb-3">
                    <label for="progress" class="form-label"
                      >Tiến độ hoàn thành (%) <span class="text-danger">*</span></label
                    >
                    <div class="d-flex align-items-center">
                      <input
                        type="range"
                        id="progress"
                        v-model.number="newReport.progress"
                        class="form-range flex-grow-1"
                        min="0"
                        max="100"
                        step="1"
                      />
                      <span class="ms-2 fw-bold">{{ newReport.progress }}%</span>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label for="files" class="form-label">Tệp đính kèm</label>
                    <input
                      type="file"
                      id="files"
                      ref="fileInput"
                      class="form-control"
                      multiple
                      @change="handleFileChange"
                    />
                    <small class="text-muted">Có thể chọn nhiều file, tối đa 10MB mỗi file</small>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" @click="cancelReport">Hủy</button>
                    <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                      <span
                        v-if="isSubmitting"
                        class="spinner-border spinner-border-sm me-1"
                        role="status"
                        aria-hidden="true"
                      ></span>
                      Gửi báo cáo
                    </button>
                  </div>
                </form>
              </div>

              <!-- Báo cáo mới nhất -->
              <div v-if="task.latest_report" class="latest-report mt-2 pt-2 border-top">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="d-flex align-items-center">
                    <div class="avatar me-2">
                      <img
                        :src="task.latest_report.user.avatar || '/images/default-avatar.png'"
                        alt="Avatar"
                        class="rounded-circle"
                        width="24"
                        height="24"
                      />
                    </div>
                    <div>
                      <small class="text-muted"
                        >Báo cáo gần nhất: {{ formatDate(task.latest_report.created_at) }}</small
                      >
                    </div>
                  </div>
                  <span :class="getStatusBadgeClass(task.latest_report.status)">{{
                    getStatusText(task.latest_report.status)
                  }}</span>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { formatDate, showSuccess, showError } from '@/utils'

// Trạng thái
const assignedTasks = ref([])
const loading = ref(true)
const reportingTaskId = ref(null)
const isSubmitting = ref(false)
const fileInput = ref(null)

// Form dữ liệu
const newReport = ref({
  message: '',
  progress: 0,
  files: []
})

// Methods
const loadAssignedTasks = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/tasks/assigned')
    assignedTasks.value = response.data
  } catch (error) {
    console.error('Lỗi khi tải danh sách công việc:', error)
  } finally {
    loading.value = false
  }
}

// Sử dụng hàm formatDate từ utils.js

const getProgressBarClass = (task) => {
  const progress = task.progress || 0
  if (progress >= 100) return 'bg-success'
  if (progress >= 70) return 'bg-info'
  if (progress >= 40) return 'bg-primary'
  if (progress >= 20) return 'bg-warning'
  return 'bg-danger'
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'approved':
      return 'badge bg-success'
    case 'rejected':
      return 'badge bg-danger'
    default:
      return 'badge bg-warning'
  }
}

const getStatusText = (status) => {
  switch (status) {
    case 'approved':
      return 'Đã duyệt'
    case 'rejected':
      return 'Đã từ chối'
    default:
      return 'Chờ duyệt'
  }
}

const startReporting = (task) => {
  reportingTaskId.value = task.id
  newReport.value.progress = task.progress || 0
}

const cancelReport = () => {
  reportingTaskId.value = null
  newReport.value = {
    message: '',
    progress: 0,
    files: []
  }
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleFileChange = (event) => {
  newReport.value.files = event.target.files
}

const submitReport = async () => {
  if (!reportingTaskId.value) return

  isSubmitting.value = true

  const task = assignedTasks.value.find((t) => t.id === reportingTaskId.value)
  if (!task) return

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
      route('task-reports.store', {
        projectId: task.project_id,
        taskId: task.id
      }),
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    // Hiển thị thông báo thành công
    showSuccess(response.data.message)

    // Cập nhật task trong danh sách
    const index = assignedTasks.value.findIndex((t) => t.id === task.id)
    if (index !== -1) {
      assignedTasks.value[index] = {
        ...assignedTasks.value[index],
        progress: newReport.value.progress,
        latest_report: response.data.report
      }
    }

    // Reset form
    cancelReport()
  } catch (error) {
    console.error('Lỗi khi gửi báo cáo:', error)
    const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi gửi báo cáo. Vui lòng thử lại sau.'
    showError(errorMessage)
  } finally {
    isSubmitting.value = false
  }
}

// Lifecycle hooks
onMounted(() => {
  loadAssignedTasks()
})
</script>

<style scoped>
.task-name {
  color: #333;
  text-decoration: none;
}

.task-name:hover {
  color: #007bff;
  text-decoration: underline;
}

.latest-report {
  font-size: 0.85rem;
}
</style>
