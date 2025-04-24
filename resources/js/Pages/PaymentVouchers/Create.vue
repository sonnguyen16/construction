<template>
  <AdminLayout>
    <template #header>Tạo phiếu chi mới</template>
    <template #breadcrumb>Tạo phiếu chi mới</template>

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
                    <label for="code">Mã phiếu chi</label>
                    <input
                      type="text"
                      class="form-control"
                      id="code"
                      v-model="form.code"
                      placeholder="Mã sẽ được tạo tự động"
                      disabled
                    />
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
                    <label for="project_id">Dự án <small class="text-muted">(không bắt buộc)</small></label>
                    <input
                      type="text"
                      class="form-control"
                      id="project_id"
                      placeholder="Chọn dự án hoặc để trống nếu là chi ngoài dự án"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.project_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.project_id">{{ form.errors.project_id }}</div>
                  </div>

                  <!-- Select cho gói thầu -->
                  <div class="form-group" v-if="form.project_id">
                    <label for="bid_package_id">Gói thầu <small class="text-muted">(không bắt buộc)</small></label>
                    <input
                      type="text"
                      class="form-control"
                      id="bid_package_id"
                      placeholder="Chọn gói thầu hoặc để trống"
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
                      <option value="proposed">Đề xuất chi</option>
                      <option value="approved">Đã duyệt</option>
                      <option value="paid">Đã chi</option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                  </div>

                  <div class="form-group">
                    <label for="payment_category_id">Loại chi</label>
                    <select
                      class="form-control"
                      id="payment_category_id"
                      v-model="form.payment_category_id"
                      :class="{ 'is-invalid': form.errors.payment_category_id }"
                    >
                      <option value="">Chọn loại chi</option>
                      <option v-for="category in paymentCategories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.payment_category_id">
                      {{ form.errors.payment_category_id }}
                    </div>
                  </div>

                  <div class="form-group" v-if="form.status === 'paid'">
                    <label for="payment_date">Ngày chi</label>
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
                      placeholder="Nhập mô tả phiếu chi"
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
import { parseCurrency, showSuccess, formatNumberInput, formatCurrency } from '@/utils'
import { computed, onMounted, onBeforeUnmount, nextTick } from 'vue'

const props = defineProps({
  contractors: Array,
  projects: Array,
  bidPackages: Array,
  paymentCategories: Array,
  statuses: Object,
  preselectedContractorId: [String, Number],
  preselectedProjectId: [String, Number],
  preselectedBidPackageId: [String, Number],
  redirectToExpenses: Boolean
})

const form = useForm({
  code: '',
  contractor_id: props.preselectedContractorId || '',
  project_id: props.preselectedProjectId || '',
  bid_package_id: props.preselectedBidPackageId || '',
  payment_category_id: '',
  amount: '',
  status: 'proposed',
  payment_date: null,
  description: '',
  redirect_to_expenses: props.redirectToExpenses ? true : false
})

// Lọc gói thầu theo dự án đã chọn
const filteredBidPackages = computed(() => {
  if (!form.project_id) return []
  return props.bidPackages.filter((bp) => bp.project_id == form.project_id)
})

// Xử lý khi thay đổi dự án
const onProjectChange = () => {
  // Reset bid_package_id khi thay đổi dự án
  form.bid_package_id = ''
}

// Xử lý khi thay đổi gói thầu
const onBidPackageChange = () => {
  // Tự động điền mô tả nếu thay đổi gói thầu
  if (form.bid_package_id) {
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
  // Kiểm tra nếu đã chọn project thì phải chọn bid_package
  if (form.project_id && !form.bid_package_id) {
    alert('Vui lòng chọn gói thầu cho dự án đã chọn')
    return
  }
  
  // Chuyển đổi số tiền từ định dạng tiền tệ sang số
  form.amount = parseCurrency(form.amount)

  form.post(route('payment-vouchers.store'), {
    onSuccess: () => {
      showSuccess('Phiếu chi đã được tạo thành công.')
    }
  })
}

// InputPicker instances để có thể hủy khi component unmount
let contractorPicker = null
let projectPicker = null
let bidPackagePicker = null

// Khởi tạo InputPicker sau khi component được mount
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

  // Nếu có preselected value
  if (props.preselectedContractorId) {
    const selectedContractor = props.contractors.find((c) => c.id == props.preselectedContractorId)
    if (selectedContractor) {
      window.$('#contractor_id').inputpicker('val', selectedContractor.id)
      form.contractor_id = props.preselectedContractorId
    }
  }

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

  // Nếu có preselected value
  if (props.preselectedProjectId) {
    const selectedProject = props.projects.find((p) => p.id == props.preselectedProjectId)
    if (selectedProject) {
      window.$('#project_id').inputpicker('val', selectedProject.id)
      form.project_id = props.preselectedProjectId
    }
  }

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

  // Nếu đã có dự án được chọn, khởi tạo InputPicker cho gói thầu
  if (form.project_id) {
    updateBidPackagePicker()
    // Nếu có preselected value cho gói thầu
    if (props.preselectedBidPackageId) {
      const selectedBidPackage = filteredBidPackages.value.find((bp) => bp.id == props.preselectedBidPackageId)
      if (selectedBidPackage) {
        window.$('#bid_package_id').inputpicker('val', selectedBidPackage.id)
        form.bid_package_id = props.preselectedBidPackageId
      }
    }
  }
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
