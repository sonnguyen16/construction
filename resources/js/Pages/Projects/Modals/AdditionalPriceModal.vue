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
          <form @submit.prevent="submitForm">
            <div class="form-group">
              <label for="additional_price">Giá phát sinh (VNĐ) <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="additional_price"
                placeholder="Nhập giá phát sinh"
                v-model="form.additional_price"
                :class="{ 'is-invalid': formErrors.additional_price }"
                @input="formatNumberInput($event)"
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
  bid: Object,
  isSubmitting: Boolean
})

const emit = defineEmits(['submit'])

// Local state
const form = ref({
  additional_price: '',
  notes: ''
})

const formErrors = ref({})

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
  }
})
</script>
