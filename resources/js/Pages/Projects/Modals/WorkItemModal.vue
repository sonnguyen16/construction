<template>
  <div
    class="modal fade"
    id="workItemModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="workItemModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="workItemModalLabel">
            {{ isEditing ? 'Sửa hạng mục' : 'Thêm hạng mục mới' }}
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="form-group d-flex gap-2">
              <label>Gói thầu:</label>
              <p>
                <strong>{{ bidPackage?.name }}</strong> ({{ bidPackage?.code }})
              </p>
            </div>

            <div class="form-group">
              <label for="name">Tên hạng mục <span class="text-danger">*</span></label>
              <input
                type="text"
                class="form-control"
                id="name"
                placeholder="Nhập tên hạng mục"
                v-model="form.name"
                :class="{ 'is-invalid': formErrors.name }"
              />
              <div class="invalid-feedback" v-if="formErrors.name">
                {{ formErrors.name }}
              </div>
            </div>

            <div class="form-group">
              <label for="code">Mã hạng mục</label>
              <input
                type="text"
                class="form-control"
                id="code"
                placeholder="Nhập mã hạng mục"
                v-model="form.code"
                :class="{ 'is-invalid': formErrors.code }"
              />
              <div class="invalid-feedback" v-if="formErrors.code">
                {{ formErrors.code }}
              </div>
            </div>

            <div class="form-group">
              <label for="price">Giá dự thầu (VNĐ)</label>
              <input
                type="text"
                class="form-control"
                id="price"
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

            <div class="form-group">
              <label for="description">Mô tả</label>
              <textarea
                class="form-control"
                id="description"
                rows="3"
                placeholder="Nhập mô tả hạng mục"
                v-model="form.description"
                :class="{ 'is-invalid': formErrors.description }"
              ></textarea>
              <div class="invalid-feedback" v-if="formErrors.description">
                {{ formErrors.description }}
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
          <button type="button" class="btn btn-primary" @click="submitForm" :disabled="isSubmitting">
            <i class="fas fa-save mr-1"></i> {{ isEditing ? 'Cập nhật' : 'Thêm mới' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { formatNumberInput, parseCurrency } from '@/utils'

const props = defineProps({
  bidPackage: Object,
  workItem: Object,
  isSubmitting: Boolean,
  isEditing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'update:contractor-id'])

// Local state
const form = ref({
  name: '',
  code: '',
  price: '',
  description: '',
  status: 'open',
  is_work_item: true,
  parent_id: props.bidPackage?.id || null
})

const formErrors = ref({})

// Reset form
const resetForm = () => {
  form.value = {
    name: '',
    code: '',
    price: '',
    description: '',
    status: 'open',
    is_work_item: true,
    parent_id: props.bidPackage?.id || null
  }
  formErrors.value = {}
}

// Reset form khi workItem thay đổi
watch(
  () => props.workItem,
  (newWorkItem) => {
    if (newWorkItem && props.isEditing) {
      form.value = {
        name: newWorkItem.name || '',
        code: newWorkItem.code || '',
        price: newWorkItem.price ? formatCurrency(newWorkItem.price) : '',
        description: newWorkItem.description || '',
        status: newWorkItem.status || 'open',
        is_work_item: true,
        parent_id: props.bidPackage?.id || null
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
    price: form.value.price ? parseCurrency(form.value.price) : null,
    estimated_price: form.value.price ? parseCurrency(form.value.price) : null,
    is_work_item: true,
    parent_id: props.bidPackage?.id || null
  }

  emit('submit', formData)
}

// Thiết lập sự kiện modal
const setupModalEvents = () => {
  // Xử lý sự kiện khi modal hiển thị
  window.$('#workItemModal').off('show.bs.modal').on('show.bs.modal', () => {
    // Đảm bảo form được reset trước khi hiển thị modal nếu không phải chế độ chỉnh sửa
    if (!props.isEditing) {
      resetForm()
    }
  })

  // Xử lý sự kiện khi modal đóng
  window.$('#workItemModal').off('hidden.bs.modal').on('hidden.bs.modal', () => {
    // Có thể thực hiện các hành động dọn dẹp khi modal đóng
    formErrors.value = {}
  })
}

// Lifecycle hooks
onMounted(() => {
  setupModalEvents()
})

onBeforeUnmount(() => {
  // Hủy tất cả sự kiện khi component bị hủy
  window.$('#workItemModal').off('hidden.bs.modal')
  window.$('#workItemModal').off('show.bs.modal')
})

// Expose methods to parent
defineExpose({
  resetForm,
  setErrors: (errors) => {
    formErrors.value = errors
  }
})
</script>
