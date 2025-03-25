<template>
  <AdminLayout>
    <template #header>Chỉnh sửa phiếu chi</template>
    <template #breadcrumb>Chỉnh sửa phiếu chi</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu chi</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mã phiếu chi:</label>
                    <input type="text" class="form-control" :value="paymentVoucher.code" disabled />
                  </div>

                  <!-- Select cho nhà thầu -->
                  <div class="form-group">
                    <label for="contractor_id">Nhà thầu <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="contractor_id"
                      placeholder="Chọn nhà thầu"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.contractor_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.contractor_id">{{ form.errors.contractor_id }}</div>
                  </div>

                  <!-- Select cho dự án -->
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

                  <!-- Select cho gói thầu -->
                  <div class="form-group">
                    <label for="bid_package_id">Gói thầu</label>
                    <input
                      type="text"
                      class="form-control"
                      id="bid_package_id"
                      placeholder="Chọn gói thầu"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.bid_package_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.bid_package_id">
                      {{ form.errors.bid_package_id }}
                    </div>
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

                  <!-- Select cho trạng thái -->
                  <div class="form-group">
                    <label for="status">Trạng thái thanh toán</label>
                    <input
                      type="text"
                      class="form-control"
                      id="status"
                      placeholder="Chọn trạng thái"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.status }"
                    />
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
              <Link :href="route('payment-vouchers.index')" class="btn btn-secondary ml-2">
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
import { parseCurrency, showSuccess, formatCurrency, formatDate } from '@/utils'
import { computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  paymentVoucher: Object,
  contractors: Array,
  projects: Array,
  bidPackages: Array,
  statuses: Object
})

const form = useForm({
  contractor_id: props.paymentVoucher.contractor_id || '',
  project_id: props.paymentVoucher.project_id || '',
  bid_package_id: props.paymentVoucher.bid_package_id || '',
  amount: formatCurrency(props.paymentVoucher.amount) || 0,
  status: props.paymentVoucher.status || 'unpaid',
  payment_date: props.paymentVoucher.payment_date || '',
  description: props.paymentVoucher.description || ''
})

// Lọc gói thầu theo dự án đã chọn
const filteredBidPackages = computed(() => {
  if (!form.project_id) return props.bidPackages
  return props.bidPackages.filter((bp) => bp.project_id == form.project_id)
})

// Xử lý khi thay đổi dự án
const onProjectChange = () => {
  // Reset bid_package_id khi thay đổi dự án
  form.bid_package_id = ''
  window.$('#bid_package_id').inputpicker('val', '')
}

// Xử lý khi thay đổi gói thầu
const onBidPackageChange = () => {
  // Tự động điền mô tả nếu thay đổi gói thầu
  if (form.bid_package_id && form.bid_package_id !== props.paymentVoucher.bid_package_id) {
    const bidPackage = filteredBidPackages.value.find((bp) => bp.id == form.bid_package_id)
    if (bidPackage) {
      form.description = `Thanh toán cho gói thầu ${bidPackage.code || ''} - ${bidPackage.name}`
    }
  }
}

// Xử lý khi thay đổi trạng thái
const onStatusChange = () => {
  // Tự động đặt ngày thanh toán
  if (form.status === 'paid' && !form.payment_date) {
    form.payment_date = new Date().toISOString().substr(0, 10)
  }
}

const submit = () => {
  // Chuyển đổi số tiền từ định dạng tiền tệ sang số
  form.amount = parseCurrency(form.amount)

  form.put(route('payment-vouchers.update', props.paymentVoucher.id), {
    onSuccess: () => {
      showSuccess('Phiếu chi đã được cập nhật thành công.')
    }
  })
}

let contractorPicker, projectPicker, bidPackagePicker, statusPicker

onMounted(() => {
  // Khởi tạo InputPicker cho nhà thầu
  contractorPicker = window.$('#contractor_id').inputpicker({
    data: props.contractors.map((contractor) => ({
      value: contractor.id,
      text: contractor.name,
      phone: contractor.phone || '',
      email: contractor.email || '',
      address: contractor.address || ''
    })),
    fields: [
      { name: 'text', text: 'Tên nhà thầu' },
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

  window.$('#contractor_id').inputpicker('val', form.contractor_id)

  // Sự kiện thay đổi nhà thầu
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
    filterOpen: false,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  window.$('#project_id').inputpicker('val', form.project_id)

  // Sự kiện thay đổi dự án
  window.$('#project_id').on('change', async function () {
    const newProjectId = window.$(this).val()
    form.project_id = newProjectId
    form.bid_package_id = ''

    onProjectChange()

    await nextTick()

    if (newProjectId) {
      updateBidPackagePicker()
    }
  })

  bidPackagePicker = window.$('#bid_package_id').inputpicker({
    data: filteredBidPackages.value.map((bidPackage) => ({
      value: bidPackage.id,
      text: bidPackage.name,
      code: bidPackage.code || ''
    })),
    fields: [
      { name: 'text', text: 'Tên gói thầu' },
      { name: 'code', text: 'Mã gói thầu' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: false,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi gói thầu
  window.$('#bid_package_id').on('change', function () {
    form.bid_package_id = window.$(this).val()
    onBidPackageChange()
  })

  window.$('#bid_package_id').inputpicker('val', form.bid_package_id)
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
// Hàm cập nhật InputPicker cho gói thầu
const updateBidPackagePicker = () => {
  safeDestroyInputPicker('#bid_package_id')

  // Khởi tạo InputPicker mới cho gói thầu
  bidPackagePicker = window.$('#bid_package_id').inputpicker({
    data: filteredBidPackages.value.map((bidPackage) => ({
      value: bidPackage.id,
      text: bidPackage.name,
      code: bidPackage.code || ''
    })),
    fields: [
      { name: 'text', text: 'Tên gói thầu' },
      { name: 'code', text: 'Mã gói thầu' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: false,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi gói thầu
  window.$('#bid_package_id').on('change', function () {
    form.bid_package_id = window.$(this).val()
    onBidPackageChange()
  })
}

// Hủy InputPicker khi component unmount
onBeforeUnmount(() => {
  try {
    // Hủy các sự kiện trước khi destroy InputPicker
    if (contractorPicker) {
      window.$('#contractor_id').off('change')
      window.$('#contractor_id').inputpicker('destroy')
    }

    if (projectPicker) {
      window.$('#project_id').off('change')
      window.$('#project_id').inputpicker('destroy')
    }

    if (bidPackagePicker) {
      window.$('#bid_package_id').off('change')
      window.$('#bid_package_id').inputpicker('destroy')
    }
  } catch (e) {
    console.error('Lỗi khi hủy InputPicker:', e)
  }
})
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
