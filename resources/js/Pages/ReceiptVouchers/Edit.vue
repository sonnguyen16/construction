<template>
  <AdminLayout>
    <template #header>Chỉnh sửa phiếu thu</template>
    <template #breadcrumb>Chỉnh sửa phiếu thu</template>

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
                    <label>Mã phiếu thu:</label>
                    <input type="text" class="form-control" :value="receiptVoucher.code" disabled />
                  </div>

                  <!-- Select cho khách hàng -->
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

                  <!-- Select cho dự án -->
                  <div class="form-group">
                    <label for="project_id">Dự án <small class="text-muted">(không bắt buộc)</small></label>
                    <input
                      type="text"
                      class="form-control"
                      id="project_id"
                      placeholder="Chọn dự án hoặc để trống nếu là thu ngoài dự án"
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
                      @input="formatNumberInput($event)"
                    />
                    <div class="invalid-feedback" v-if="form.errors.amount">{{ form.errors.amount }}</div>
                  </div>

                  <!-- Select cho trạng thái -->
                  <div class="form-group">
                    <label for="status">Trạng thái thanh toán</label>
                    <select
                      class="form-control"
                      id="status"
                      v-model="form.status"
                      @change="onStatusChange"
                      :class="{ 'is-invalid': form.errors.status }"
                    >
                      <option value="unpaid">Dự thu</option>
                      <option value="paid">Đã thu</option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                  </div>

                  <div class="form-group">
                    <label for="receipt_category_id">Loại thu</label>
                    <select
                      class="form-control"
                      id="receipt_category_id"
                      v-model="form.receipt_category_id"
                      :class="{ 'is-invalid': form.errors.receipt_category_id }"
                    >
                      <option v-for="category in receiptCategories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.receipt_category_id">
                      {{ form.errors.receipt_category_id }}
                    </div>
                  </div>

                  <div class="form-group" v-if="form.status === 'paid'">
                    <label for="payment_date">Ngày thu</label>
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
                      placeholder="Nhập mô tả"
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
import { parseCurrency, showSuccess, formatCurrency, formatNumberInput } from '@/utils'
import { computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'

const props = defineProps({
  receiptVoucher: Object,
  customers: Array,
  projects: Array,
  bidPackages: Array,
  receiptCategories: Array,
  statuses: Object
})

const form = useForm({
  customer_id: props.receiptVoucher.customer_id || '',
  project_id: props.receiptVoucher.project_id || '',
  receipt_category_id: props.receiptVoucher.receipt_category_id || 1,
  amount: formatCurrency(props.receiptVoucher.amount || 0),
  status: props.receiptVoucher.status || 'unpaid',
  payment_date: props.receiptVoucher.payment_date
    ? new Date(props.receiptVoucher.payment_date).toISOString().split('T')[0]
    : '',
  description: props.receiptVoucher.description || ''
})

// Khai báo các biến InputPicker
let customerPicker = null
let projectPicker = null
let receiptCategoryPicker = null
let statusPicker = null

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

  // Đặt giá trị ban đầu
  if (form.customer_id) {
    const selectedCustomer = props.customers.find((c) => c.id == form.customer_id)
    if (selectedCustomer) {
      window.$('#customer_id').inputpicker('val', selectedCustomer.id)
    }
  }

  // Sự kiện thay đổi khách hàng
  window.$('#customer_id').on('change', function () {
    form.customer_id = window.$(this).val()
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
    filterOpen: false,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Đặt giá trị ban đầu
  if (form.project_id) {
    const selectedProject = props.projects.find((p) => p.id == form.project_id)
    if (selectedProject) {
      window.$('#project_id').inputpicker('val', selectedProject.id)
    }
  }

  window.$('#project_id').inputpicker('val', form.project_id)

  // Sự kiện thay đổi dự án
  window.$('#project_id').on('change', async function () {
    const newProjectId = window.$(this).val()
    form.project_id = newProjectId

    // Đợi Vue cập nhật DOM
    await nextTick()

    // Cập nhật mô tả nếu cần
    if (newProjectId) {
      const selectedProject = props.projects.find((p) => p.id == newProjectId)
      if (selectedProject) {
        // Có thể cập nhật mô tả dựa trên dự án đã chọn
        form.description = `Thu tiền từ dự án ${selectedProject.name}`
      }
    }
  })

  // Khởi tạo InputPicker cho loại thu
  receiptCategoryPicker = window.$('#receipt_category_id').inputpicker({
    data: props.receiptCategories.map((category) => ({
      value: category.id,
      text: category.name
    })),
    fields: [{ name: 'text', text: 'Tên loại thu' }],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: false,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Đặt giá trị ban đầu cho loại thu
  if (form.receipt_category_id) {
    window.$('#receipt_category_id').inputpicker('val', form.receipt_category_id)
  }

  // Sự kiện thay đổi loại thu
  window.$('#receipt_category_id').on('change', function () {
    form.receipt_category_id = window.$(this).val()
  })
})

const safeDestroyInputPicker = (selector) => {
  try {
    const $el = window.$(selector)

    // Kiểm tra xem phần tử có tồn tại không
    if ($el.length === 0) return

    // Hủy sự kiện trước
    $el.off('change')

    // Kiểm tra xem inputpicker đã được khởi tạo chưa
    const instance = $el.data('inputpicker')
    if (instance) {
      // Hủy các dropdown mở
      window.$('.inputpicker-div').remove()

      // Xóa data
      $el.removeData('inputpicker')

      // Xóa các thuộc tính liên quan đến inputpicker
      $el.removeAttr('data-inputpicker-uuid')
      $el.removeAttr('data-value')
    }
  } catch (e) {
    console.error(`Lỗi khi hủy InputPicker ${selector}:`, e)
  }
}

// Hủy InputPicker khi component unmount
onBeforeUnmount(() => {
  try {
    if (customerPicker) {
      window.$('#customer_id').off('change')
      window.$('#customer_id').inputpicker('destroy')
    }

    if (projectPicker) {
      window.$('#project_id').off('change')
      window.$('#project_id').inputpicker('destroy')
    }

    if (receiptCategoryPicker) {
      window.$('#receipt_category_id').off('change')
      window.$('#receipt_category_id').inputpicker('destroy')
    }

    if (statusPicker) {
      window.$('#status').off('change')
      window.$('#status').inputpicker('destroy')
    }
  } catch (e) {
    console.error('Lỗi khi hủy InputPicker:', e)
  }
})

// Xử lý khi thay đổi trạng thái
const onStatusChange = () => {
  // Tự động đặt ngày thanh toán
  if (form.status === 'paid' && !form.payment_date) {
    form.payment_date = new Date().toISOString().substring(0, 10)
  }
}

const submit = () => {
  // Chuyển đổi giá trị từ định dạng tiền tệ sang số
  form.amount = parseCurrency(form.amount)

  form.put(route('receipt-vouchers.update', props.receiptVoucher.id), {
    onSuccess: () => {
      showSuccess('Phiếu thu đã được cập nhật thành công.')
    }
  })
}
</script>

<style>
/* Đảm bảo menu hiển thị trên các thành phần khác */
.inputpicker-div {
  z-index: 10000 !important;
  overflow: auto !important;
}

/* Tránh xử lý sự kiện nhiều lần khi click */
.inputpicker-div * {
  pointer-events: auto;
}
</style>
