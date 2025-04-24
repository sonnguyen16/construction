<template>
  <div
    class="modal fade"
    id="additionalPriceModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="additionalPriceModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="additionalPriceModalLabel">Nhập giá phát sinh</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div v-if="isAutoCalculate" class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i> Gói thầu này đang được cấu hình để tính toán tự động từ các gói thầu con. Bạn cần tắt tính năng này để có thể nhập giá phát sinh thủ công.
          </div>
          <form @submit.prevent="submitForm">
            <div class="form-group" :class="{ 'opacity-50': isAutoCalculate }">
              <label for="additional_price">Giá phát sinh (VNĐ) <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="additional_price"
                placeholder="Nhập giá phát sinh"
                v-model="form.additional_price"
                :class="{ 'is-invalid': formErrors.additional_price }"
                @input="formatNumberInput($event)"
                :disabled="isAutoCalculate"
              />
              <div class="invalid-feedback" v-if="formErrors.additional_price">
                {{ formErrors.additional_price }}
              </div>
            </div>
            <div class="form-group">
              <label for="notes">Ghi chú</label>
              <textarea
                class="form-control"
                id="notes"
                rows="3"
                placeholder="Nhập ghi chú"
                v-model="form.notes"
                :class="{ 'is-invalid': formErrors.notes }"
              ></textarea>
              <div class="invalid-feedback" v-if="formErrors.notes">
                {{ formErrors.notes }}
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
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { formatNumberInput, parseCurrency, formatCurrency } from '@/utils'

const props = defineProps({
  bidPackage: Object,
  isSubmitting: Boolean,
  isAutoCalculate: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit'])

// Local state
const form = ref({
  additional_price: '',
  notes: ''
})

const formErrors = ref({})

// Trạng thái tính toán tự động
const isAutoCalculate = ref(props.isAutoCalculate)

// Reset form
const resetForm = () => {
  form.value = {
    additional_price: '',
    notes: ''
  }
  formErrors.value = {}
}

// Gửi form
const submitForm = () => {
  const formData = {
    ...form.value,
    additional_price: parseCurrency(form.value.additional_price)
  }

  emit('submit', formData)
}

// Xử lý khi modal hiển thị
const setupModalEvents = () => {
  window.$('#additionalPriceModal').on('show.bs.modal', function () {
    resetForm()
  })
}

// Lifecycle hooks
onMounted(() => {
  setupModalEvents()
})

onBeforeUnmount(() => {
  // Hủy sự kiện khi modal đóng
  window.$('#additionalPriceModal').off('show.bs.modal')
})

// Expose methods to parent
defineExpose({
  resetForm,
  setErrors: (errors) => {
    formErrors.value = errors
  },
  set isAutoCalculate(value) {
    isAutoCalculate.value = value
  }
})
</script>
