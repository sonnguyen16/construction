<template>
  <AdminLayout>
    <template #header>Thêm phiếu xuất kho mới</template>
    <template #breadcrumb>Thêm phiếu xuất kho mới</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu xuất kho</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <!-- Mã phiếu xuất -->
                  <div class="form-group">
                    <label for="code">Mã phiếu xuất <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="code"
                      v-model="form.code"
                      placeholder="Mã phiếu xuất kho"
                      :class="{ 'is-invalid': form.errors.code }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.code">{{ form.errors.code }}</div>
                  </div>

                  <!-- Ngày xuất kho -->
                  <div class="form-group">
                    <label for="export_date">Ngày xuất kho <span class="text-danger">*</span></label>
                    <input
                      type="date"
                      class="form-control"
                      id="export_date"
                      v-model="form.export_date"
                      :class="{ 'is-invalid': form.errors.export_date }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.export_date">{{ form.errors.export_date }}</div>
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

                  <!-- Khách hàng -->
                  <div class="form-group">
                    <label for="customer_id">Khách hàng</label>
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

              <!-- Chi tiết phiếu xuất kho -->
              <div class="row mt-4">
                <div class="col-md-12">
                  <h4 class="mb-3">Chi tiết phiếu xuất kho</h4>

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
                        <label for="export_price">Giá xuất <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <input
                            type="text"
                            class="form-control"
                            id="export_price"
                            v-model="currentItem.export_price"
                            placeholder="Giá xuất"
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
                          <th>Giá xuất</th>
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
                          <td class="text-end">{{ formatCurrency(item.export_price) }}</td>
                          <td class="text-end">
                            {{ formatCurrency(item.quantity * parseCurrency(item.export_price)) }}
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
              <Link :href="route('export-vouchers.index')" class="btn btn-secondary ml-2">
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { formatNumberInput, parseCurrency, formatCurrency, showSuccess } from '@/utils'

const props = defineProps({
  projects: Array,
  customers: Array,
  products: Array,
  suggestedCode: String
})

const form = useForm({
  code: props.suggestedCode || '',
  project_id: '',
  export_date: new Date().toISOString().split('T')[0],
  customer_id: '',
  notes: '',
  items: []
})

const currentItem = ref({
  product_id: '',
  quantity: 1,
  export_price: '',
  product_name: '',
  product_unit: ''
})

// Tính tổng tiền của phiếu xuất kho
const totalAmount = computed(() => {
  return form.items.reduce((total, item) => {
    return total + item.quantity * parseCurrency(item.export_price)
  }, 0)
})

// Kiểm tra xem có thể thêm mặt hàng không
const canAddItem = computed(() => {
  return currentItem.value.product_id && currentItem.value.quantity > 0 && currentItem.value.export_price
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
      export_price: parseCurrency(currentItem.value.export_price)
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
    export_price: ''
  }

  // Reset InputPicker
  window.$('#product_id').inputpicker('val', '')
}

// Khởi tạo các InputPicker khi component được mount
let projectPicker, customerPicker, productPicker

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

  // Sự kiện thay đổi dự án
  window.$('#project_id').on('change', function () {
    form.project_id = window.$(this).val()
  })

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
    filterOpen: true,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi khách hàng
  window.$('#customer_id').on('change', function () {
    form.customer_id = window.$(this).val()
  })

  // Khởi tạo InputPicker cho sản phẩm
  productPicker = window.$('#product_id').inputpicker({
    data: props.products.map((product) => ({
      value: product.id,
      text: product.name,
      code: product.code || '',
      unit: product.unit ? product.unit.name : '',
      export_price: formatCurrency(product.price || 0)
    })),
    fields: [
      { name: 'text', text: 'Tên sản phẩm' },
      { name: 'code', text: 'Mã sản phẩm' },
      { name: 'unit', text: 'Đơn vị' },
      { name: 'export_price', text: 'Giá xuất' }
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
        // Tự động điền giá xuất
        currentItem.value.export_price = formatCurrency(product.price || 0)
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

    if (customerPicker) {
      window.$('#customer_id').off('change')
      window.$('#customer_id').inputpicker('destroy')
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
    alert('Vui lòng thêm ít nhất một sản phẩm vào phiếu xuất kho.')
    return
  }

  form.post(route('export-vouchers.store'), {
    onSuccess: () => {
      showSuccess('Phiếu xuất kho đã được tạo thành công.')
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
