<template>
  <div
    class="modal fade"
    :id="modalId"
    tabindex="-1"
    role="dialog"
    :aria-labelledby="modalId + 'Label'"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" :id="modalId + 'Label'">
            <template v-if="isWorkItem">
              {{ isEditing ? 'Sửa hạng mục' : 'Thêm hạng mục mới' }}
            </template>
            <template v-else>
              {{ isEditing ? 'Chỉnh sửa gói thầu' : 'Thêm gói thầu mới' }}
            </template>
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="form-group d-flex gap-2">
              <template v-if="isWorkItem && parentBidPackage">
                <label>Gói thầu:</label>
                <p>
                  <strong>{{ parentBidPackage.name }}</strong> ({{ parentBidPackage.code }})
                </p>
              </template>
              <template v-else>
                <label>Dự án:</label>
                <p>
                  <strong>{{ project.name }}</strong> ({{ project.code }})
                </p>
              </template>
            </div>
            <div class="form-group">
              <label for="code">{{ isWorkItem ? 'Mã hạng mục' : 'Mã gói thầu' }} <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="code"
                :placeholder="isWorkItem ? 'Nhập mã hạng mục' : 'Nhập mã gói thầu'"
                v-model="form.code"
                :class="{ 'is-invalid': formErrors.code }"
              />
              <div class="invalid-feedback" v-if="formErrors.code">
                {{ formErrors.code }}
              </div>
            </div>
            <div class="form-group">
              <label for="name">{{ isWorkItem ? 'Tên hạng mục' : 'Tên gói thầu' }} <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="name"
                :placeholder="isWorkItem ? 'Nhập tên hạng mục' : 'Nhập tên gói thầu'"
                v-model="form.name"
                :class="{ 'is-invalid': formErrors.name }"
              />
              <div class="invalid-feedback" v-if="formErrors.name">
                {{ formErrors.name }}
              </div>
            </div>
            <div class="form-group" :class="{ 'opacity-50': !isWorkItem && form.auto_calculate }">
              <label for="estimated_price">Giá dự toán (VNĐ)</label>
              <input
                type="text"
                class="form-control"
                id="estimated_price"
                placeholder="Nhập giá dự toán"
                v-model="form.estimated_price"
                :class="{ 'is-invalid': formErrors.estimated_price }"
                @input="formatNumberInput($event)"
                :disabled="!isWorkItem && form.auto_calculate"
              />
              <div class="invalid-feedback" v-if="formErrors.estimated_price">
                {{ formErrors.estimated_price }}
              </div>
              <small class="form-text text-muted" v-if="!isWorkItem && form.auto_calculate">
                Giá dự thầu sẽ được tính tự động từ các gói thầu con.
              </small>
            </div>
            <div class="form-group">
              <label for="description">{{ isWorkItem ? 'Mô tả' : 'Ghi chú' }}</label>
              <textarea
                class="form-control"
                id="description"
                rows="3"
                :placeholder="isWorkItem ? 'Nhập mô tả hạng mục' : 'Nhập ghi chú'"
                v-model="form.description"
                :class="{ 'is-invalid': formErrors.description }"
              ></textarea>
              <div class="invalid-feedback" v-if="formErrors.description">
                {{ formErrors.description }}
              </div>
            </div>

            <!-- Nút switch tính toán tự động từ gói con -->
            <div class="form-group" v-if="!isWorkItem">
              <label>Tính toán tự động từ gói con</label>
              <div class="custom-control custom-switch">
                <input
                  type="checkbox"
                  class="custom-control-input"
                  id="autoCalculate"
                  v-model="form.auto_calculate"
                  :checked="form.auto_calculate"
                >
                <label class="custom-control-label" for="autoCalculate">
                  {{ form.auto_calculate ? 'Bật' : 'Tắt' }}
                </label>
              </div>
              <small class="form-text text-muted">
                Khi bật, giá trị sẽ được tính tự động từ các gói thầu con. Khi tắt, bạn có thể nhập giá trị thủ công.
              </small>
            </div>
            <div class="form-group">
              <label for="status">Trạng thái <span class="text-danger">*</span></label>
              <select
                class="form-control"
                id="status"
                v-model="form.status"
                :class="{ 'is-invalid': formErrors.status }"
              >
                <option value="open">Đang mở thầu</option>
                <option value="awarded">Đã chọn nhà thầu</option>
                <option value="completed">Hoàn thành</option>
                <option value="cancelled">Đã hủy</option>
              </select>
              <div class="invalid-feedback" v-if="formErrors.status">
                {{ formErrors.status }}
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
          <button type="button" class="btn btn-primary" @click="submitForm" :disabled="isSubmitting">
            <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Cập nhật' : 'Lưu' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { formatNumberInput, parseCurrency, formatCurrency } from '@/utils'

const props = defineProps({
  project: Object,
  bidPackage: Object,
  isSubmitting: Boolean,
  isEditing: {
    type: Boolean,
    default: false
  },
  modalId: {
    type: String,
    default: 'bidPackageModal'
  },
  isWorkItem: {
    type: Boolean,
    default: false
  },
  parentBidPackage: Object
})

const emit = defineEmits(['submit'])

// Local state
const form = ref({
  code: '',
  name: '',
  description: '',
  estimated_price: '',
  status: 'open',
  is_work_item: false,
  parent_id: null,
  auto_calculate: true
})

const formErrors = ref({})

// Reset form
const resetForm = () => {
  form.value = {
    code: '',
    name: '',
    description: '',
    estimated_price: '',
    status: 'open',
    is_work_item: props.isWorkItem,
    parent_id: props.isWorkItem ? props.parentBidPackage?.id : null,
    auto_calculate: true
  }
  formErrors.value = {}
}

// Thiết lập sự kiện modal
const setupModalEvents = () => {
  const $modal = window.$(`#${props.modalId}`)

  // Xóa tất cả sự kiện cũ
  $modal.off('show.bs.modal')
  $modal.off('shown.bs.modal')

  // Xử lý sự kiện khi modal hiển thị
  $modal.on('show.bs.modal', () => {
    // Đảm bảo form được reset trước khi hiển thị modal nếu không phải chế độ chỉnh sửa
    if (!props.isEditing) {
      resetForm()
    }
  })
}

// Reset form khi bidPackage thay đổi
watch(
  () => props.bidPackage,
  (newBidPackage) => {
    if (newBidPackage && props.isEditing) {
      form.value = {
        project_id: newBidPackage.project_id,
        code: newBidPackage.code || '',
        name: newBidPackage.name || '',
        description: newBidPackage.description || '',
        estimated_price: formatCurrency(newBidPackage.estimated_price || 0),
        status: newBidPackage.status || 'open',
        is_work_item: props.isWorkItem,
        parent_id: props.isWorkItem ? props.parentBidPackage?.id : null,
        auto_calculate: newBidPackage.auto_calculate !== undefined ? newBidPackage.auto_calculate : true
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

// Gửi form
const submitForm = () => {
  const formData = {
    ...form.value,
    estimated_price: parseCurrency(form.value.estimated_price),
    is_work_item: props.isWorkItem,
    parent_id: props.isWorkItem ? props.parentBidPackage?.id : null,
    auto_calculate: props.isWorkItem ? false : form.value.auto_calculate
  }

  emit('submit', formData)
}

onMounted(() => {
  setupModalEvents()
})

onBeforeUnmount(() => {
  // Hủy sự kiện khi modal đóng
  window.$(`#${props.modalId}`).off('hidden.bs.modal')
  window.$(`#${props.modalId}`).off('show.bs.modal')
})

// Export methods
defineExpose({
  resetForm,
  setErrors(errors) {
    formErrors.value = errors
  }
})
</script>
