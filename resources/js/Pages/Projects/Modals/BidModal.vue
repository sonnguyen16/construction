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
            {{ isEditing ? 'Sửa giá dự thầu' : 'Thêm giá dự thầu' }}
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="form-group d-flex gap-2">
              <label>Dự án:</label>
              <p>{{ project.name }} ({{ project.code }})</p>
            </div>
            <div class="form-group d-flex gap-2">
              <label>Gói thầu:</label>
              <p>
                {{ bidPackage?.name }}
                ({{ bidPackage?.code }})
              </p>
            </div>

            <!-- Select cho nhà thầu -->
            <div class="form-group">
              <label for="contractor_id">Nhà thầu:</label>
              <input
                type="text"
                class="form-control"
                :id="'contractor_id_' + modalId"
                placeholder="Nhập tên nhà thầu"
                v-model="form.contractor_id"
                :class="{ 'is-invalid': formErrors.contractor_id }"
              />
              <div class="invalid-feedback" v-if="formErrors.contractor_id">
                {{ formErrors.contractor_id }}
              </div>
            </div>

            <!-- Hiển thị thông tin nhà thầu đã chọn -->
            <div class="form-group bg-light p-3 rounded" v-if="selectedContractor">
              <h6>Thông tin nhà thầu đã chọn</h6>
              <div><strong>Tên:</strong> {{ selectedContractor.name }}</div>
              <div v-if="selectedContractor.phone"><strong>SĐT:</strong> {{ selectedContractor.phone }}</div>
              <div v-if="selectedContractor.email"><strong>Email:</strong> {{ selectedContractor.email }}</div>
              <div v-if="selectedContractor.address"><strong>Địa chỉ:</strong> {{ selectedContractor.address }}</div>
              <div v-if="selectedContractor.notes"><strong>Ghi chú:</strong> {{ selectedContractor.notes }}</div>
            </div>

            <div class="form-group">
              <label for="price">Giá dự thầu (VNĐ) <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                :id="'price_' + modalId"
                placeholder="Nhập giá dự thầu"
                v-model="form.price"
                :class="{ 'is-invalid': formErrors.price }"
                @input="formatNumberInput($event)"
              />
              <div class="invalid-feedback" v-if="formErrors.price">
                {{ formErrors.price }}
              </div>
            </div>
            <div class="form-group">
              <label for="notes">Ghi chú</label>
              <textarea
                class="form-control"
                :id="'notes_' + modalId"
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
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { formatNumberInput, parseCurrency, formatCurrency } from '@/utils'

const props = defineProps({
  project: Object,
  bidPackage: Object,
  bid: Object,
  contractors: Array,
  isSubmitting: Boolean,
  isEditing: {
    type: Boolean,
    default: false
  },
  modalId: {
    type: String,
    default: 'bidModal'
  }
})

const emit = defineEmits(['submit'])

// Local state
const form = ref({
  contractor_id: '',
  price: '',
  notes: ''
})

const formErrors = ref({})
const selectedContractor = ref(null)
let inputpickerInstance = null
let isInputPickerInitialized = false

// Reset form
const resetForm = () => {
  form.value = {
    contractor_id: '',
    price: '',
    notes: ''
  }
  formErrors.value = {}
  selectedContractor.value = null
}

// Reset form khi bid thay đổi
watch(
  () => props.bid,
  (newBid) => {
    if (newBid && props.isEditing) {
      form.value = {
        contractor_id: newBid.contractor_id || '',
        price: formatCurrency(newBid.price || 0),
        notes: newBid.notes || ''
      }

      if (newBid.contractor) {
        selectedContractor.value = newBid.contractor
      }
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

// Khởi tạo InputPicker
const initInputPicker = async () => {
  // Nếu đã khởi tạo, không khởi tạo lại
  if (isInputPickerInitialized) {
    updateInputPickerValue()
    return
  }

  try {
    await nextTick()
    const contractorInputId = `contractor_id_${props.modalId}`

    // Đảm bảo element tồn tại
    const inputElement = document.getElementById(contractorInputId)
    if (!inputElement) {
      console.error(`Không tìm thấy element với id ${contractorInputId}`)
      return
    }

    // Đảm bảo jQuery đã được khởi tạo
    if (!window.$ || !window.$.fn || !window.$.fn.inputpicker) {
      console.error('jQuery hoặc plugin inputpicker chưa được khởi tạo')
      return
    }

    // Khởi tạo InputPicker
    const $input = window.$(`#${contractorInputId}`)

    // Đảm bảo element đã được jQuery chọn đúng
    if ($input.length === 0) {
      console.error(`jQuery không thể tìm thấy element với id ${contractorInputId}`)
      return
    }

    // Xóa tất cả sự kiện trước
    $input.off('change')

    // Khởi tạo InputPicker mới
    $input.inputpicker({
      data: props.contractors.map((contractor) => ({
        value: contractor.id,
        text: contractor.name,
        phone: contractor.phone || '',
        email: contractor.email || '',
        address: contractor.address || '',
        notes: contractor.notes || ''
      })),
      fields: [
        { name: 'text', text: 'Tên nhà thầu' },
        { name: 'notes', text: 'Ghi chú' }
      ],
      fieldText: 'text',
      fieldValue: 'value',
      filterOpen: false,
      autoOpen: true,
      headShow: true,
      width: '100%'
    })

    // Lưu instance để tham chiếu sau này
    inputpickerInstance = $input
    isInputPickerInitialized = true

    // Đặt giá trị ban đầu nếu có
    updateInputPickerValue()

    // Xử lý sự kiện change
    $input.on('change', function () {
      const contractorId = window.$(this).val()
      form.value.contractor_id = contractorId

      if (contractorId) {
        const contractor = props.contractors.find((c) => c.id == contractorId)
        if (contractor) {
          selectedContractor.value = contractor
        }
      } else {
        selectedContractor.value = null
      }
    })
  } catch (error) {
    console.error('Lỗi khi khởi tạo InputPicker:', error)
  }
}

// Cập nhật giá trị InputPicker
const updateInputPickerValue = () => {
  try {
    if (!isInputPickerInitialized || !inputpickerInstance) return

    // Đặt giá trị cho InputPicker nếu có
    if (form.value.contractor_id) {
      inputpickerInstance.inputpicker('val', form.value.contractor_id)
    } else {
      inputpickerInstance.inputpicker('val', '')
    }
  } catch (error) {
    console.error('Lỗi khi cập nhật giá trị InputPicker:', error)
  }
}

// Gửi form
const submitForm = () => {
  const formData = {
    ...form.value,
    price: parseCurrency(form.value.price)
  }

  emit('submit', formData)
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

  // Xử lý sự kiện khi modal đã hiển thị hoàn toàn
  $modal.on('shown.bs.modal', () => {
    // Khởi tạo hoặc cập nhật InputPicker sau khi modal đã hiển thị
    setTimeout(() => {
      initInputPicker()
    }, 100)
  })
}

// Lifecycle hooks
onMounted(() => {
  setupModalEvents()
})

onBeforeUnmount(() => {
  // Hủy tất cả sự kiện khi component bị hủy
  window.$(`#${props.modalId}`).off('shown.bs.modal')
  window.$(`#${props.modalId}`).off('show.bs.modal')

  // Không hủy InputPicker khi component bị hủy, chỉ xóa sự kiện
  if (inputpickerInstance) {
    inputpickerInstance.off('change')
  }
})

// Expose methods to parent
defineExpose({
  resetForm,
  setErrors: (errors) => {
    formErrors.value = errors
  }
})
</script>
