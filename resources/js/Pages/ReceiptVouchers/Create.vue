<template>
  <AdminLayout>
    <template #header>Tạo phiếu thu mới</template>
    <template #breadcrumb>Tạo phiếu thu mới</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu thu</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="code">Mã phiếu thu</label>
                    <input
                      type="text"
                      class="form-control"
                      id="code"
                      v-model="form.code"
                      placeholder="Mã sẽ được tạo tự động"
                      disabled
                    />
                  </div>

                  <div class="form-group">
                    <label for="customer_id">Khách hàng <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="customer_id"
                      placeholder="Chọn khách hàng"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.customer_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.customer_id">{{ form.errors.customer_id }}</div>
                  </div>

                  <div class="form-group">
                    <label for="project_id">Dự án</label>
                    <input
                      type="text"
                      class="form-control"
                      id="project_id"
                      placeholder="Chọn dự án"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.project_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.project_id">{{ form.errors.project_id }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label for="amount">Số tiền <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="amount"
                      v-model="form.amount"
                      placeholder="Nhập số tiền"
                      :class="{ 'is-invalid': form.errors.amount }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.amount">{{ form.errors.amount }}</div>
                  </div>

                  <div class="form-group">
                    <label for="status">Trạng thái thanh toán</label>
                    <select
                      class="form-control"
                      id="status"
                      v-model="form.status"
                      @change="onStatusChange"
                      :class="{ 'is-invalid': form.errors.status }"
                    >
                      <option value="unpaid">Chưa thanh toán</option>
                      <option value="paid">Đã thanh toán</option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                  </div>

                  <div class="form-group" v-if="form.status === 'paid'">
                    <label for="payment_date">Ngày thanh toán</label>
                    <input
                      type="date"
                      class="form-control"
                      id="payment_date"
                      v-model="form.payment_date"
                      :class="{ 'is-invalid': form.errors.payment_date }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.payment_date">{{ form.errors.payment_date }}</div>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea
                      class="form-control"
                      id="description"
                      v-model="form.description"
                      rows="3"
                      placeholder="Nhập mô tả phiếu thu"
                      :class="{ 'is-invalid': form.errors.description }"
                    ></textarea>
                    <div class="invalid-feedback" v-if="form.errors.description">{{ form.errors.description }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> Lưu
              </button>
              <Link :href="route('receipt-vouchers.index')" class="btn btn-secondary ml-2">
                <i class="fas fa-times mr-1"></i> Hủy
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { parseCurrency, showSuccess } from '@/utils'
import { onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  customers: Array,
  projects: Array,
  bidPackages: Array,
  statuses: Object,
  preselectedCustomerId: [String, Number],
  preselectedProjectId: [String, Number]
})

const form = useForm({
  customer_id: props.preselectedCustomerId || '',
  project_id: props.preselectedProjectId || '',
  amount: '',
  status: 'unpaid',
  payment_date: '',
  description: ''
})

// InputPicker instances để có thể hủy khi component unmount
let customerPicker = null
let projectPicker = null

// Khởi tạo InputPicker sau khi component được mount
onMounted(() => {
  // Khởi tạo InputPicker cho khách hàng
  customerPicker = window.$('#customer_id').inputpicker({
    data: props.customers.map((customer) => ({
      value: customer.id,
      text: customer.name,
      phone: customer.phone || '',
      email: customer.email || '',
      address: customer.address || ''
    })),
    fields: [
      { name: 'text', text: 'Tên khách hàng' },
      { name: 'phone', text: 'Số điện thoại' },
      { name: 'email', text: 'Email' },
      { name: 'address', text: 'Địa chỉ' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: false,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  if (props.preselectedCustomerId) {
    const selectedCustomer = props.customers.find((c) => c.id == props.preselectedCustomerId)
    if (selectedCustomer) {
      window.$('#customer_id').inputpicker('val', selectedCustomer.name)
      form.customer_id = props.preselectedCustomerId
    }
  }

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
    filterOpen: false,
    headShow: true,
    autoOpen: true,
    width: '100%',
    onChange: (value) => {
      form.project_id = value
    }
  })

  // Nếu có preselected value
  if (props.preselectedProjectId) {
    const selectedProject = props.projects.find((p) => p.id == props.preselectedProjectId)
    if (selectedProject) {
      window.$('#project_id').inputpicker('val', selectedProject.id)
      form.project_id = selectedProject.id
    }
  }
})

// Hủy InputPicker khi component unmount
onBeforeUnmount(() => {
  try {
    if (customerPicker) window.$('#customer_id').inputpicker('destroy')
    if (projectPicker) window.$('#project_id').inputpicker('destroy')
  } catch (e) {
    console.error('Lỗi khi hủy InputPicker:', e)
  }
})

const onStatusChange = () => {
  if (form.status === 'paid' && !form.payment_date) {
    form.payment_date = new Date().toISOString().substr(0, 10)
  }
}

const submit = () => {
  form.amount = parseCurrency(form.amount)
  form.customer_id = window.$('#customer_id').val()
  form.project_id = window.$('#project_id').val()

  form.post(route('receipt-vouchers.store'), {
    onSuccess: () => {
      showSuccess('Phiếu thu đã được tạo thành công.')
    }
  })
}
</script>
