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
            {{ isEditing ? 'Chỉnh sửa gói thầu' : 'Thêm gói thầu mới' }}
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="form-group d-flex gap-2">
              <label>Dự án:</label>
              <p>
                <strong>{{ project.name }}</strong> ({{ project.code }})
              </p>
            </div>
            <div class="form-group">
              <label for="code">Mã gói thầu <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="code"
                placeholder="Nhập mã gói thầu"
                v-model="form.code"
                :class="{ 'is-invalid': formErrors.code }"
              />
              <div class="invalid-feedback" v-if="formErrors.code">
                {{ formErrors.code }}
              </div>
            </div>
            <div class="form-group">
              <label for="name">Tên gói thầu <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="name"
                placeholder="Nhập tên gói thầu"
                v-model="form.name"
                :class="{ 'is-invalid': formErrors.name }"
              />
              <div class="invalid-feedback" v-if="formErrors.name">
                {{ formErrors.name }}
              </div>
            </div>
            <div class="form-group">
              <label for="estimated_price">Giá dự thầu (VNĐ)</label>
              <input
                type="text"
                class="form-control"
                id="estimated_price"
                placeholder="Nhập giá dự thầu"
                v-model="form.estimated_price"
                :class="{ 'is-invalid': formErrors.estimated_price }"
                @input="formatNumberInput($event)"
              />
              <div class="invalid-feedback" v-if="formErrors.estimated_price">
                {{ formErrors.estimated_price }}
              </div>
            </div>
            <div class="form-group">
              <label for="description">Ghi chú</label>
              <textarea
                class="form-control"
                id="description"
                rows="3"
                placeholder="Nhập ghi chú"
                v-model="form.description"
                :class="{ 'is-invalid': formErrors.description }"
              ></textarea>
              <div class="invalid-feedback" v-if="formErrors.description">
                {{ formErrors.description }}
              </div>
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
            <i class="fas fa-save mr-1"></i> Lưu
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue'
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
  client_price: '',
  status: 'open',
  is_work_item: false
})

const formErrors = ref({})

// Reset form
const resetForm = () => {
  form.value = {
    code: '',
    name: '',
    description: '',
    estimated_price: '',
    client_price: '',
    status: 'open'
  }
  formErrors.value = {}
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
        client_price: formatCurrency(newBidPackage.client_price || 0),
        status: newBidPackage.status || 'open'
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
    client_price: parseCurrency(form.value.client_price),
    is_work_item: props.isWorkItem
  }

  emit('submit', formData)
}

onBeforeUnmount(() => {
  // Hủy sự kiện khi modal đóng
  window.$(`#${props.modalId}`).off('hidden.bs.modal')
  window.$(`#${props.modalId}`).off('show.bs.modal')
})

// Expose methods to parent
defineExpose({
  resetForm,
  setErrors: (errors) => {
    formErrors.value = errors
  }
})
</script>
