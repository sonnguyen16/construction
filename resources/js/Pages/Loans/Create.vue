<template>
  <AdminLayout>
    <template #header>Tạo khoản vay mới</template>
    <template #breadcrumb>Quản lý khoản vay / Tạo khoản vay mới</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin khoản vay</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <!-- Hiển thị thông báo lỗi chung -->
              <div v-if="Object.keys(form.errors).length > 0" class="alert alert-danger">
                <p class="mb-0"><strong>Có lỗi xảy ra khi gửi form:</strong></p>
                <ul class="mb-0">
                  <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                </ul>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="name">Tên khoản vay <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="name"
                      v-model="form.name"
                      placeholder="Nhập tên khoản vay"
                      :class="{ 'is-invalid': form.errors.name }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.name">{{ form.errors.name }}</div>
                  </div>

                  <!-- Select cho nhà cung cấp -->
                  <div class="form-group">
                    <label for="contractor_id">Nhà cung cấp <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="contractor_id"
                      placeholder="Chọn nhà cung cấp"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.contractor_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.contractor_id">{{ form.errors.contractor_id }}</div>
                  </div>

                  <!-- Select cho dự án -->
                  <div class="form-group">
                    <label for="project_id">Dự án <small class="text-muted">(không bắt buộc)</small></label>
                    <input
                      type="text"
                      class="form-control"
                      id="project_id"
                      placeholder="Chọn dự án hoặc để trống"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.project_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.project_id">{{ form.errors.project_id }}</div>
                  </div>

                  <div class="form-group">
                    <label for="amount">Số tiền vay <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input
                        type="text"
                        class="form-control"
                        id="amount"
                        v-model="form.amount"
                        placeholder="Nhập số tiền vay"
                        :class="{ 'is-invalid': form.errors.amount }"
                        @input="formatNumberInput($event)"
                      />
                      <div class="input-group-append">
                        <span class="input-group-text">VNĐ</span>
                      </div>
                    </div>
                    <div class="invalid-feedback" v-if="form.errors.amount">{{ form.errors.amount }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label for="interest_rate">Lãi suất (% / năm) <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input
                        type="number"
                        class="form-control"
                        id="interest_rate"
                        v-model="form.interest_rate"
                        placeholder="Nhập lãi suất"
                        min="0"
                        max="100"
                        step="0.01"
                        :class="{ 'is-invalid': form.errors.interest_rate }"
                      />
                      <div class="input-group-append">
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="invalid-feedback" v-if="form.errors.interest_rate">{{ form.errors.interest_rate }}</div>
                  </div>

                  <div class="form-group">
                    <label for="start_date">Ngày bắt đầu <span class="text-danger">*</span></label>
                    <input
                      type="date"
                      class="form-control"
                      id="start_date"
                      v-model="form.start_date"
                      :class="{ 'is-invalid': form.errors.start_date }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.start_date">{{ form.errors.start_date }}</div>
                  </div>

                  <div class="form-group">
                    <label for="end_date">Ngày kết thúc <span class="text-danger">*</span></label>
                    <input
                      type="date"
                      class="form-control"
                      id="end_date"
                      v-model="form.end_date"
                      :class="{ 'is-invalid': form.errors.end_date }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.end_date">{{ form.errors.end_date }}</div>
                  </div>

                  <div class="form-group">
                    <label for="status">Trạng thái <span class="text-danger">*</span></label>
                    <select
                      class="form-control"
                      id="status"
                      v-model="form.status"
                      :class="{ 'is-invalid': form.errors.status }"
                    >
                      <option value="">-- Chọn trạng thái --</option>
                      <option v-for="status in statuses" :key="status.value" :value="status.value">
                        {{ status.label }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-group">
                    <label for="contract_file">File hợp đồng <small class="text-muted">(không bắt buộc)</small></label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input
                          type="file"
                          class="custom-file-input"
                          id="contract_file"
                          @input="form.contract_file = $event.target.files[0]"
                          :class="{ 'is-invalid': form.errors.contract_file }"
                        />
                        <label class="custom-file-label" for="contract_file">
                          {{ form.contract_file ? form.contract_file.name : 'Chọn file' }}
                        </label>
                      </div>
                    </div>
                    <div class="invalid-feedback" v-if="form.errors.contract_file">{{ form.errors.contract_file }}</div>
                    <small class="form-text text-muted">Định dạng hỗ trợ: PDF, DOC, DOCX. Tối đa 5MB</small>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-group">
                    <label for="notes">Ghi chú</label>
                    <textarea
                      class="form-control"
                      id="notes"
                      v-model="form.notes"
                      :class="{ 'is-invalid': form.errors.notes }"
                      rows="3"
                      placeholder="Nhập ghi chú"
                    ></textarea>
                    <div class="invalid-feedback" v-if="form.errors.notes">{{ form.errors.notes }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                  <i class="fas fa-save mr-1"></i> {{ form.processing ? 'Đang xử lý...' : 'Lưu' }}
                </button>
                <Link :href="route('loans.index')" class="btn btn-secondary">
                  <i class="fas fa-times mr-1"></i> Hủy
                </Link>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { parseCurrency, showSuccess, formatNumberInput, formatCurrency } from '@/utils'
import { useCurrentProject } from '@/Composables/useCurrentProject'

// Sử dụng composable dự án hiện tại
const { currentProject } = useCurrentProject()

const props = defineProps({
  contractors: Array,
  projects: Array,
  statuses: Array
})

const form = useForm({
  name: '',
  contractor_id: '',
  project_id: currentProject.value ? currentProject.value.id : '',
  amount: '',
  interest_rate: '',
  start_date: '',
  end_date: '',
  status: 'active',
  notes: '',
  contract_file: null
})

// InputPicker instances để có thể hủy khi component unmount
let contractorPicker = null
let projectPicker = null

// Khởi tạo InputPicker sau khi component được mount
onMounted(() => {
  // Khởi tạo InputPicker cho nhà cung cấp
  contractorPicker = window.$('#contractor_id').inputpicker({
    data: props.contractors.map((contractor) => ({
      value: contractor.id,
      text: contractor.name,
      phone: contractor.phone || '',
      email: contractor.email || '',
      address: contractor.address || ''
    })),
    fields: [
      { name: 'text', text: 'Tên nhà cung cấp' },
      { name: 'phone', text: 'Số điện thoại' },
      { name: 'email', text: 'Email' },
      { name: 'address', text: 'Địa chỉ' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: true,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi nhà cung cấp
  window.$('#contractor_id').on('change', function () {
    form.contractor_id = window.$(this).val()
  })

  // Khởi tạo InputPicker cho dự án
  projectPicker = window.$('#project_id').inputpicker({
    data: props.projects.map((project) => ({
      value: project.id,
      text: project.name,
      code: project.code || ''
    })),
    fields: [
      { name: 'text', text: 'Tên dự án' },
      { name: 'code', text: 'Mã dự án' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: true,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })
  
  // Vô hiệu hóa InputPicker dự án nếu dùng currentProject
  if (currentProject.value) {
    window.$('#project_id').prop('disabled', true)
  }
  
  // Theo dõi thay đổi của dự án hiện tại
  watch(
    () => currentProject.value,
    (newProject) => {
      if (newProject) {
        // Cập nhật giá trị trong form
        form.project_id = newProject.id
        
        // Vô hiệu hóa InputPicker dự án
        window.$('#project_id').prop('disabled', true)
        
        // Cập nhật giao diện InputPicker
        const selectedProject = props.projects.find((p) => p.id == newProject.id)
        if (selectedProject && window.$('#project_id').length) {
          window.$('#project_id').inputpicker('val', selectedProject.id)
        }
      }
    },
    { immediate: true }
  )

  // Sự kiện thay đổi dự án
  window.$('#project_id').on('change', function () {
    form.project_id = window.$(this).val()
  })
})

// Hủy InputPicker khi component unmount
onBeforeUnmount(() => {
  try {
    if (contractorPicker) {
      window.$('#contractor_id').inputpicker('destroy')
    }
    if (projectPicker) {
      window.$('#project_id').inputpicker('destroy')
    }
  } catch (error) {
    console.error('Error destroying inputpicker:', error)
  }
})

// Hàm an toàn để hủy InputPicker
const safeDestroyInputPicker = (selector) => {
  try {
    if (window.$(selector).length) {
      window.$(selector).inputpicker('destroy')
    }
  } catch (error) {
    console.error(`Error destroying inputpicker for ${selector}:`, error)
  }
}

const submit = () => {
  // Chuyển đổi số tiền từ định dạng tiền tệ sang số
  form.amount = parseCurrency(form.amount)

  form.post(route('loans.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showSuccess('Tạo khoản vay thành công!')
    }
  })
}
</script>
