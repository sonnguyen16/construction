<template>
  <AdminLayout>
    <template #header>Thêm phiếu nhập kho mới</template>
    <template #breadcrumb>Thêm phiếu nhập kho mới</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu nhập kho</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <!-- Mã phiếu nhập -->
                  <div class="form-group">
                    <label for="code">Mã phiếu nhập <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="code"
                      v-model="form.code"
                      placeholder="Mã phiếu nhập kho"
                      :class="{ 'is-invalid': form.errors.code }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.code">{{ form.errors.code }}</div>
                  </div>

                  <!-- Ngày nhập kho -->
                  <div class="form-group">
                    <label for="import_date">Ngày nhập kho <span class="text-danger">*</span></label>
                    <input
                      type="date"
                      class="form-control"
                      id="import_date"
                      v-model="form.import_date"
                      :class="{ 'is-invalid': form.errors.import_date }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.import_date">{{ form.errors.import_date }}</div>
                  </div>

                   <!-- Nhà thầu -->
                   <div class="form-group">
                    <label for="contractor_id">Nhà thầu / nhà cung cấp</label>
                    <input
                      type="text"
                      class="form-control"
                      id="contractor_id"
                      placeholder="Chọn nhà thầu / nhà cung cấp"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.contractor_id }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.contractor_id">{{ form.errors.contractor_id }}</div>
                  </div>
                </div>

                <div class="col-md-6">
                  <!-- Dự án -->
                  <div class="form-group">
                    <label for="project_id">Dự án <span class="text-danger">*</span></label>
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

                  <!-- Gói thầu -->
                  <div class="form-group">
                    <label for="bid_package_id">Gói thầu <small class="text-muted">(không bắt buộc)</small></label>
                    <input
                      type="text"
                      class="form-control"
                      id="bid_package_id"
                      placeholder="Chọn gói thầu"
                      data-role="inputpicker"
                      :class="{ 'is-invalid': form.errors.bid_package_id }"
                      :disabled="!form.project_id"
                    />
                    <div class="invalid-feedback" v-if="form.errors.bid_package_id">{{ form.errors.bid_package_id }}</div>
                  </div>


                </div>

                <!-- Ghi chú -->
                <div class="col-md-12">
                  <div class="form-group">
                    <label for="notes">Ghi chú</label>
                    <textarea
                      class="form-control"
                      id="notes"
                      v-model="form.notes"
                      rows="2"
                      placeholder="Nhập ghi chú"
                      :class="{ 'is-invalid': form.errors.notes }"
                    ></textarea>
                    <div class="invalid-feedback" v-if="form.errors.notes">{{ form.errors.notes }}</div>
                  </div>
                </div>
              </div>

              <!-- Chi tiết phiếu nhập kho -->
              <div class="row mt-4">
                <div class="col-md-12">
                  <h4 class="mb-3">Chi tiết phiếu nhập kho</h4>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="product_id">Sản phẩm <span class="text-danger">*</span></label>
                        <input
                          type="text"
                          class="form-control"
                          id="product_id"
                          placeholder="Chọn sản phẩm"
                          data-role="inputpicker"
                        />
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="quantity">Số lượng <span class="text-danger">*</span></label>
                        <input
                          type="number"
                          class="form-control"
                          id="quantity"
                          v-model="currentItem.quantity"
                          placeholder="Số lượng"
                          min="1"
                        />
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="import_price">Giá nhập <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <input
                            type="text"
                            class="form-control"
                            id="import_price"
                            v-model="currentItem.import_price"
                            placeholder="Giá nhập"
                            @input="formatNumberInput($event)"
                          />
                          <div class="input-group-append">
                            <span class="input-group-text">VNĐ</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label>&nbsp;</label>
                        <button
                          type="button"
                          class="btn btn-primary btn-block"
                          @click="addItem"
                          :disabled="!canAddItem"
                        >
                          <i class="fas fa-plus"></i> Thêm vào phiếu
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Bảng chi tiết -->
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                      <thead>
                        <tr class="bg-light">
                          <th style="width: 40px">STT</th>
                          <th>Sản phẩm</th>
                          <th>Đơn vị</th>
                          <th>Số lượng</th>
                          <th>Giá nhập</th>
                          <th>Thành tiền</th>
                          <th style="width: 100px">Thao tác</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(item, index) in form.items" :key="index">
                          <td class="text-center">{{ index + 1 }}</td>
                          <td>{{ getProductName(item.product_id) }}</td>
                          <td>{{ getProductUnit(item.product_id) }}</td>
                          <td class="text-center">{{ item.quantity }}</td>
                          <td class="text-end">{{ formatCurrency(item.import_price) }}</td>
                          <td class="text-end">
                            {{ formatCurrency(item.quantity * parseCurrency(item.import_price)) }}
                          </td>
                          <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm" @click="removeItem(index)">
                              <i class="fas fa-trash"></i>
                            </button>
                          </td>
                        </tr>
                        <tr v-if="form.items.length === 0">
                          <td colspan="7" class="text-center">Chưa có sản phẩm nào</td>
                        </tr>
                        <tr v-if="form.items.length > 0" class="font-weight-bold">
                          <td colspan="5" class="text-end">Tổng cộng:</td>
                          <td class="text-end">{{ formatCurrency(totalAmount) }}</td>
                          <td></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing || form.items.length === 0">
                <i class="fas fa-save mr-1"></i> Lưu
              </button>
              <Link :href="route('import-vouchers.index')" class="btn btn-secondary ml-2">
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
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import { formatNumberInput, parseCurrency, formatCurrency, showSuccess } from '@/utils'
import { useCurrentProject } from '@/Composables/useCurrentProject'

// Sử dụng composable dự án hiện tại
const { currentProject } = useCurrentProject()

const props = defineProps({
  projects: Array,
  contractors: Array,
  products: Array,
  suggestedCode: String
})

const form = useForm({
  code: props.suggestedCode || '',
  project_id: currentProject.value ? currentProject.value.id : '',
  bid_package_id: '',
  import_date: new Date().toISOString().substr(0, 10),
  contractor_id: '',
  notes: '',
  items: []
})

const currentItem = ref({
  product_id: '',
  quantity: 1,
  import_price: '',
  product_name: '',
  product_unit: ''
})

// Tính tổng tiền của phiếu nhập kho
const totalAmount = computed(() => {
  return form.items.reduce((total, item) => {
    return total + item.quantity * parseCurrency(item.import_price)
  }, 0)
})

// Kiểm tra xem có thể thêm mặt hàng không
const canAddItem = computed(() => {
  return currentItem.value.product_id && currentItem.value.quantity > 0 && currentItem.value.import_price
})

// Lấy tên sản phẩm từ id
const getProductName = (productId) => {
  const product = props.products.find((p) => p.id === parseInt(productId))
  return product ? product.name : ''
}

// Lấy đơn vị sản phẩm từ id
const getProductUnit = (productId) => {
  const product = props.products.find((p) => p.id === parseInt(productId))
  return product && product.unit ? product.unit.name : ''
}

// Thêm sản phẩm vào danh sách
const addItem = () => {
  if (!canAddItem.value) return

  const product = props.products.find((p) => p.id === parseInt(currentItem.value.product_id))
  if (!product) return

  // Kiểm tra xem sản phẩm đã tồn tại trong danh sách chưa
  const existingIndex = form.items.findIndex((item) => item.product_id === currentItem.value.product_id)

  if (existingIndex >= 0) {
    // Cập nhật số lượng nếu sản phẩm đã tồn tại
    form.items[existingIndex].quantity += parseInt(currentItem.value.quantity)
  } else {
    // Thêm sản phẩm mới
    form.items.push({
      product_id: currentItem.value.product_id,
      quantity: parseInt(currentItem.value.quantity),
      import_price: parseCurrency(currentItem.value.import_price)
    })
  }

  // Reset form nhập chi tiết
  resetItemForm()
}

// Xóa sản phẩm khỏi danh sách
const removeItem = (index) => {
  form.items.splice(index, 1)
}

// Reset form nhập chi tiết
const resetItemForm = () => {
  currentItem.value = {
    product_id: '',
    quantity: 1,
    import_price: ''
  }

  // Reset InputPicker
  window.$('#product_id').inputpicker('val', '')
}

// Khởi tạo các InputPicker khi component được mount
let projectPicker, bidPackagePicker, contractorPicker, productPicker

// Hàm an toàn để hủy InputPicker
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

// Lọc gói thầu theo dự án được chọn
const filteredBidPackages = computed(() => {
  if (!form.project_id) return []
  const project = props.projects.find(p => p.id === parseInt(form.project_id))
  return project && project.bid_packages ? project.bid_packages : []
})

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
    filterOpen: true,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi gói thầu
  window.$('#bid_package_id').on('change', function () {
    form.bid_package_id = window.$(this).val()

    // Nếu gói thầu có nhà thầu được chọn, tự động chọn nhà thầu đó
    if (form.bid_package_id) {
      const project = props.projects.find(p => p.id === parseInt(form.project_id))
      if (project) {
        const bidPackage = project.bid_packages.find(bp => bp.id === parseInt(form.bid_package_id))
        if (bidPackage && bidPackage.selected_contractor_id) {
          form.contractor_id = bidPackage.selected_contractor_id
          window.$('#contractor_id').inputpicker('val', bidPackage.selected_contractor_id)
        }
      }
    }
  })
}

// Hủy InputPicker khi component unmount
onBeforeUnmount(() => {
  try {
    safeDestroyInputPicker('#project_id')
    safeDestroyInputPicker('#bid_package_id')
    safeDestroyInputPicker('#contractor_id')
    safeDestroyInputPicker('#product_id')
  } catch (e) {
    console.error('Lỗi khi hủy InputPicker:', e)
  }
})

onMounted(() => {
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
    // Cập nhật giao diện InputPicker
    const selectedProject = props.projects.find((p) => p.id == currentProject.value.id)
    if (selectedProject) {
      window.$('#project_id').inputpicker('val', selectedProject.id)
      // Cập nhật InputPicker cho gói thầu
      updateBidPackagePicker()
    }
  }

  // Sự kiện thay đổi dự án
  window.$('#project_id').on('change', function () {
    form.project_id = window.$(this).val()

    // Cập nhật InputPicker cho gói thầu
    if (form.project_id) {
      updateBidPackagePicker()
    } else {
      // Xóa gói thầu nếu không có dự án
      form.bid_package_id = ''
      safeDestroyInputPicker('#bid_package_id')
    }
  })
  
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
          
          // Cập nhật InputPicker cho gói thầu
          nextTick(() => {
            updateBidPackagePicker()
          })
        }
      }
    },
    { immediate: true }
  )

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
    filterOpen: true,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi nhà thầu
  window.$('#contractor_id').on('change', function () {
    form.contractor_id = window.$(this).val()
  })

  // Khởi tạo InputPicker cho sản phẩm
  productPicker = window.$('#product_id').inputpicker({
    data: props.products.map((product) => ({
      value: product.id,
      text: product.name,
      code: product.code || '',
      unit: product.unit ? product.unit.name : '',
      import_price: formatCurrency(product.import_price || 0)
    })),
    fields: [
      { name: 'text', text: 'Tên sản phẩm' },
      { name: 'code', text: 'Mã sản phẩm' },
      { name: 'unit', text: 'Đơn vị' },
      { name: 'import_price', text: 'Giá nhập' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: true,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi sản phẩm
  window.$('#product_id').on('change', function () {
    const productId = window.$(this).val()
    currentItem.value.product_id = productId

    if (productId) {
      const product = props.products.find((p) => p.id === parseInt(productId))
      if (product) {
        // Tự động điền giá nhập
        currentItem.value.import_price = formatCurrency(product.import_price || 0)
      }
    }
  })
})

// Hủy InputPicker khi component unmount
onBeforeUnmount(() => {
  try {
    // Hủy các sự kiện trước khi destroy InputPicker
    if (projectPicker) {
      window.$('#project_id').off('change')
      window.$('#project_id').inputpicker('destroy')
    }

    if (contractorPicker) {
      window.$('#contractor_id').off('change')
      window.$('#contractor_id').inputpicker('destroy')
    }

    if (productPicker) {
      window.$('#product_id').off('change')
      window.$('#product_id').inputpicker('destroy')
    }
  } catch (e) {
    console.error('Lỗi khi hủy InputPicker:', e)
  }
})

// Gửi form
const submit = () => {
  if (form.items.length === 0) {
    alert('Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập kho.')
    return
  }

  form.post(route('import-vouchers.store'), {
    onSuccess: () => {
      showSuccess('Phiếu nhập kho đã được tạo thành công.')
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

.text-end {
  text-align: right !important;
}
</style>
