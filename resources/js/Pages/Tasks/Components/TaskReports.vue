<template>
  <div class="task-reports">
    <div class="d-flex mb-3">
      <button v-if="isAssignee" class="btn btn-primary btn-sm" @click="showReportForm = true">
        <i class="fas fa-plus"></i> Gửi báo cáo mới
      </button>
    </div>
    <!-- Form gửi báo cáo mới -->
    <div v-if="showReportForm" class="mb-4 p-3 border rounded bg-light">
      <h6 class="mb-3">Gửi báo cáo tiến độ mới</h6>
      <form @submit.prevent="submitReport">
        <div class="mb-3">
          <label for="message" class="form-label">Nội dung báo cáo <span class="text-danger">*</span></label>
          <textarea id="message" v-model="newReport.message" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label for="progress" class="form-label">Tiến độ hoàn thành (%) <span class="text-danger">*</span></label>
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
          <input type="file" id="files" ref="fileInput" class="form-control" multiple @change="handleFileChange" />
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

    <!-- Danh sách báo cáo -->
    <div v-if="loading" class="text-center py-4">
      <div class="spinner-border" role="status"></div>
    </div>
    <div v-else-if="reports.length === 0" class="text-center py-4">
      <p class="text-muted">Chưa có báo cáo tiến độ nào</p>
    </div>
    <div v-else class="reports-list">
      <div v-for="report in reports" :key="report.id" class="report-item mb-4 p-3 border rounded">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="d-flex align-items-center">
            <div class="avatar me-2">
              <img
                :src="report.user.avatar || '/images/default-avatar.png'"
                alt="Avatar"
                class="rounded-circle"
                width="40"
                height="40"
              />
            </div>
            <div>
              <h6 class="mb-0">{{ report.user.name }}</h6>
              <small class="text-muted">{{ formatDate(report.created_at) }}</small>
            </div>
          </div>
          <div class="d-flex align-items-center">
            <span :class="getStatusBadgeClass(report.status)">{{ getStatusText(report.status) }}</span>
            <div class="dropdown ms-2">
              <button class="btn btn-sm btn-light" type="button" data-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
              </button>
              <ul class="dropdown-menu">
                <li v-if="report.status === 'submitted'">
                  <a class="dropdown-item" href="#" @click.prevent="approveReport(report)">
                    <i class="fas fa-check text-success me-2"></i> Duyệt báo cáo
                  </a>
                </li>
                <li v-if="report.status === 'submitted'">
                  <a class="dropdown-item" href="#" @click.prevent="rejectReport(report)">
                    <i class="fas fa-times text-danger me-2"></i> Từ chối báo cáo
                  </a>
                </li>
                <li v-if="report.status !== 'approved'">
                  <a class="dropdown-item" href="#" @click.prevent="deleteReport(report)">
                    <i class="fas fa-trash text-danger me-2"></i> Xóa báo cáo
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <div class="progress mb-3" style="height: 8px">
          <div
            class="progress-bar"
            role="progressbar"
            :style="{ width: report.progress + '%' }"
            :aria-valuenow="report.progress"
            aria-valuemin="0"
            aria-valuemax="100"
          ></div>
        </div>

        <div class="report-content mb-3">
          <p class="mb-1">{{ report.message }}</p>
          <div class="d-flex align-items-center">
            <span class="badge bg-info me-2">Tiến độ: {{ report.progress }}%</span>
          </div>
        </div>

        <!-- Files đính kèm -->
        <div v-if="report.files && report.files.length > 0" class="report-files mb-3">
          <h6 class="mb-2 small">Tệp đính kèm:</h6>
          <div class="d-flex flex-wrap">
            <a
              v-for="file in report.files"
              :key="file.id"
              :href="file.path"
              target="_blank"
              class="file-item me-2 mb-2 p-1 border rounded d-flex align-items-center"
            >
              <i class="fas fa-file me-1"></i>
              <span>{{ file.file_name }}</span>
            </a>
          </div>
        </div>

        <!-- Phần đánh giá báo cáo -->
        <div v-if="report.reviewer" class="review-section mt-3 pt-2 border-top">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-2">
              <img
                :src="report.reviewer.avatar || '/images/default-avatar.png'"
                alt="Reviewer"
                class="rounded-circle"
                width="30"
                height="30"
              />
            </div>
            <div>
              <h6 class="mb-0 small">
                {{ report.reviewer.name }}
                <span class="text-muted">đã {{ report.status === 'approved' ? 'duyệt' : 'từ chối' }}</span>
              </h6>
              <small class="text-muted">{{ formatDate(report.reviewed_at) }}</small>
            </div>
          </div>
          <p v-if="report.review_message" class="mb-0 ps-4 small">{{ report.review_message }}</p>
        </div>

        <!-- Form duyệt/từ chối báo cáo -->
        <div v-if="report.id === reviewingReportId" class="review-form mt-3 pt-3 border-top">
          <form @submit.prevent="submitReview(report)">
            <div class="mb-3">
              <label for="reviewMessage" class="form-label">Phản hồi</label>
              <textarea id="reviewMessage" v-model="reviewData.review_message" class="form-control" rows="2"></textarea>
            </div>
            <div class="d-flex justify-content-end">
              <button type="button" class="btn btn-sm btn-secondary me-2" @click="cancelReview">Hủy</button>
              <button
                type="submit"
                class="btn btn-sm"
                :class="reviewData.status === 'approved' ? 'btn-success' : 'btn-danger'"
                :disabled="isSubmitting"
              >
                <span
                  v-if="isSubmitting"
                  class="spinner-border spinner-border-sm me-1"
                  role="status"
                  aria-hidden="true"
                ></span>
                {{ reviewData.status === 'approved' ? 'Xác nhận duyệt' : 'Xác nhận từ chối' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal xác nhận xóa -->
  <div class="modal fade" id="deleteReportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Xác nhận xóa</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">Bạn có chắc chắn muốn xóa báo cáo này không?</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="button" class="btn btn-danger" @click="confirmDelete" :disabled="isSubmitting">
            <span
              v-if="isSubmitting"
              class="spinner-border spinner-border-sm me-1"
              role="status"
              aria-hidden="true"
            ></span>
            Xóa
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { formatDate, showSuccess, showError, showWarning, showConfirm } from '@/utils'
import { usePermission } from '@/Composables/usePermission'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  projectId: {
    type: Number,
    required: true
  }
})

const { can, canInProject } = usePermission()
const user = usePage().props.auth.user

// Trạng thái
const reports = ref([])
const loading = ref(true)
const showReportForm = ref(false)
const isSubmitting = ref(false)
const reviewingReportId = ref(null)
const deleteReportId = ref(null)
const fileInput = ref(null)

// Form dữ liệu
const newReport = ref({
  message: '',
  progress: 0,
  files: []
})

const reviewData = ref({
  status: 'approved',
  review_message: ''
})

// Computed properties
const isAssignee = computed(() => {
  return props.task.assignees && props.task.assignees.some((user) => user.id === user.id)
})

const canReviewReport = computed(() => {
  return (
    canInProject('task.resources', props.projectId) ||
    (props.task.managers && props.task.managers.some((user) => user.id === user.id)) ||
    (props.task.supervisors && props.task.supervisors.some((user) => user.id === user.id))
  )
})

const canManageReports = computed(() => {
  return canInProject('task.resources', props.projectId)
})

// Methods
const loadReports = async () => {
  loading.value = true
  try {
    const response = await axios.get(
      route('task-reports.index', {
        projectId: props.projectId,
        taskId: props.task.id
      })
    )
    reports.value = response.data
  } catch (error) {
    console.error('Lỗi khi tải báo cáo:', error)
  } finally {
    loading.value = false
  }
}

const handleFileChange = (event) => {
  newReport.value.files = event.target.files
}

const submitReport = async () => {
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
      route('task-reports.store', {
        projectId: props.projectId,
        taskId: props.task.id
      }),
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    // Thêm báo cáo mới vào đầu danh sách
    reports.value.unshift(response.data.report)

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

const cancelReport = () => {
  newReport.value = {
    message: '',
    progress: 0,
    files: []
  }
  showReportForm.value = false
  if (fileInput.value) {
    fileInput.value.value = ''
  }
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

const approveReport = (report) => {
  reviewingReportId.value = report.id
  reviewData.value = {
    status: 'approved',
    review_message: ''
  }
}

const rejectReport = (report) => {
  reviewingReportId.value = report.id
  reviewData.value = {
    status: 'rejected',
    review_message: ''
  }
}

const cancelReview = () => {
  reviewingReportId.value = null
  reviewData.value = {
    status: 'approved',
    review_message: ''
  }
}

const submitReview = async (report) => {
  isSubmitting.value = true

  try {
    const response = await axios.post(
      route('task-reports.review', {
        projectId: props.projectId,
        taskId: props.task.id,
        reportId: report.id
      }),
      reviewData.value
    )

    // Hiển thị thông báo thành công
    showSuccess(response.data.message)
    
    // Cập nhật báo cáo trong danh sách
    const index = reports.value.findIndex((r) => r.id === report.id)
    if (index !== -1) {
      reports.value[index] = response.data.report
    }

    // Reset form
    cancelReview()
  } catch (error) {
    console.error('Lỗi khi duyệt báo cáo:', error)
    const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi duyệt báo cáo. Vui lòng thử lại sau.'
    showError(errorMessage)
  } finally {
    isSubmitting.value = false
  }
}

const deleteReport = (report) => {
  showConfirm(
    'Xóa báo cáo',
    'Bạn có chắc chắn muốn xóa báo cáo này không?',
    'Xóa',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      deleteReportId.value = report.id
      confirmDelete()
    }
  })
}

const confirmDelete = async () => {
  if (!deleteReportId.value) return

  isSubmitting.value = true

  try {
    const response = await axios.delete(
      route('task-reports.destroy', {
        projectId: props.projectId,
        taskId: props.task.id,
        reportId: deleteReportId.value
      })
    )

    // Xóa báo cáo khỏi danh sách
    reports.value = reports.value.filter((r) => r.id !== deleteReportId.value)
    deleteReportId.value = null

    showSuccess('Báo cáo đã được xóa thành công')
  } catch (error) {
    console.error('Lỗi khi xóa báo cáo:', error)
    const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi xóa báo cáo. Vui lòng thử lại sau.'
    showError(errorMessage)
  } finally {
    isSubmitting.value = false
  }
}

// Lifecycle hooks
onMounted(() => {
  loadReports()
})
</script>

<style scoped>
.file-item {
  font-size: 0.85rem;
  background-color: #f8f9fa;
  text-decoration: none;
  color: #212529;
}

.file-item:hover {
  background-color: #e9ecef;
}

.report-item {
  background-color: #fff;
  transition: all 0.2s;
}

.report-item:hover {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>
