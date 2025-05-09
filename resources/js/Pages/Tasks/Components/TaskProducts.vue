<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Danh sách vật tư</h5>
      <button class="btn btn-sm btn-primary" @click="openAddProductModal">
        <i class="fas fa-plus"></i> Thêm vật tư
      </button>
    </div>

    <!-- Bảng danh sách vật tư -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th style="width: 50px">STT</th>
            <th>Mã</th>
            <th>Tên vật tư</th>
            <th>Danh mục</th>
            <th>Đơn vị</th>
            <th>Số lượng</th>
            <th>Thời gian (ngày)</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
            <th>Ghi chú</th>
            <th style="width: 100px">Thao tác</th>
          </tr>
        </thead>
        <tbody v-if="products.length > 0">
          <tr v-for="(product, index) in products" :key="product.id">
            <td class="text-center">{{ index + 1 }}</td>
            <td>{{ product.code }}</td>
            <td>{{ product.name }}</td>
            <td>{{ product.category }}</td>
            <td>{{ product.unit }}</td>
            <td class="text-right">{{ product.quantity }}</td>
            <td class="text-right">{{ product.duration || 'N/A' }}</td>
            <td class="text-right">{{ formatCurrency(product.export_price) }}</td>
            <td class="text-right">{{ formatCurrency(product.total_price) }}</td>
            <td>{{ product.notes }}</td>
            <td class="text-center">
              <button class="btn btn-sm btn-info mr-1" @click="openEditProductModal(product)">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger" @click="confirmRemoveProduct(product)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
          <tr>
            <td colspan="8" class="text-right font-weight-bold">Tổng cộng:</td>
            <td class="text-right font-weight-bold">{{ formatCurrency(totalPrice) }}</td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="11" class="text-center">Chưa có vật tư nào được thêm vào công việc này</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal thêm vật tư -->
    <div
      class="modal fade"
      id="productModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="productModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="productModalLabel">{{ isEditing ? 'Cập nhật vật tư' : 'Thêm vật tư' }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group" v-if="!isEditing">
              <label for="product_id">Chọn vật tư</label>
              <input type="text" class="form-control" id="product_id_picker" placeholder="Nhập tên vật tư" />
              <div v-if="errors.product_id" class="text-danger mt-1">{{ errors.product_id }}</div>
            </div>
            <div class="form-group">
              <label for="quantity">Số lượng</label>
              <input
                type="number"
                v-model="form.quantity"
                class="form-control"
                id="quantity"
                step="0.01"
                min="0.01"
                placeholder="Nhập số lượng"
              />
              <div v-if="errors.quantity" class="text-danger mt-1">{{ errors.quantity }}</div>
            </div>
            <div class="form-group">
              <label for="duration">Thời gian sử dụng (ngày)</label>
              <input
                type="number"
                v-model="form.duration"
                class="form-control"
                id="duration"
                min="1"
                placeholder="Nhập số ngày sử dụng"
              />
              <div v-if="errors.duration" class="text-danger mt-1">{{ errors.duration }}</div>
            </div>
            <div class="form-group">
              <label for="notes">Ghi chú</label>
              <textarea
                v-model="form.notes"
                class="form-control"
                id="notes"
                rows="3"
                placeholder="Nhập ghi chú"
              ></textarea>
              <div v-if="errors.notes" class="text-danger mt-1">{{ errors.notes }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-primary" @click="saveProduct" :disabled="loading">
              <i v-if="loading" class="fas fa-spinner fa-spin"></i> {{ isEditing ? 'Cập nhật' : 'Thêm' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import axios from 'axios'
import { showSuccess, showError, formatCurrency } from '@/utils'

const props = defineProps({
  taskId: {
    type: Number,
    required: true
  }
})

const products = ref([])
const allProducts = ref([])
const availableProducts = ref([])
const loading = ref(false)
const errors = ref({})
const isEditing = ref(false)
const editingProductId = ref(null)
const inputpickerInstance = ref(null)
const isInputPickerInitialized = ref(false)
const selectedProduct = ref(null)

const form = ref({
  product_id: '',
  quantity: '1',
  duration: '1',
  notes: ''
})

// Tính tổng tiền
const totalPrice = computed(() => {
  return products.value.reduce((total, product) => total + product.total_price, 0)
})

// Lấy danh sách vật tư của công việc
const loadProducts = async () => {
  try {
    const response = await axios.get(`/tasks/${props.taskId}/products`)
    products.value = response.data
  } catch (error) {
    console.error('Lỗi khi tải danh sách vật tư:', error)
    showError('Không thể tải danh sách vật tư')
  }
}

// Lấy danh sách tất cả vật tư
const loadAllProducts = async () => {
  try {
    const response = await axios.get('/api/products')
    allProducts.value = response.data
  } catch (error) {
    console.error('Lỗi khi tải danh sách vật tư:', error)
    showError('Không thể tải danh sách vật tư')
  }
}

// Mở modal thêm vật tư
const openAddProductModal = () => {
  // Reset form và errors
  form.value = {
    product_id: '',
    quantity: '1',
    duration: '1',
    notes: ''
  }
  errors.value = {}
  isEditing.value = false

  // Tính toán danh sách vật tư có thể thêm
  availableProducts.value = allProducts.value.filter((product) => !products.value.some((p) => p.id === product.id))

  // Mở modal
  window.$('#productModal').modal('show')

  // Khởi tạo InputPicker sau khi modal hiển thị
  nextTick(() => {
    initInputPicker()
  })
}

// Mở modal sửa vật tư
const openEditProductModal = (product) => {
  isEditing.value = true
  editingProductId.value = product.id
  form.value = {
    quantity: product.quantity,
    duration: product.duration,
    notes: product.notes
  }
  errors.value = {}
  $('#productModal').modal('show')
}

// Lưu vật tư (thêm mới hoặc cập nhật)
const saveProduct = async () => {
  if (!isEditing.value && !form.value.product_id) {
    errors.value = { product_id: 'Vui lòng chọn vật tư' }
    return
  }

  if (!form.value.quantity || form.value.quantity <= 0) {
    errors.value = { quantity: 'Số lượng phải lớn hơn 0' }
    return
  }

  loading.value = true
  errors.value = {}

  try {
    let response
    if (isEditing.value && editingProductId.value) {
      // Cập nhật vật tư
      response = await axios.put(`/tasks/${props.taskId}/products/${editingProductId.value}`, {
        quantity: form.value.quantity,
        duration: form.value.duration,
        notes: form.value.notes
      })

      // Cập nhật danh sách
      const index = products.value.findIndex((p) => p.id === editingProductId.value)
      if (index !== -1) {
        products.value[index] = response.data
      }

      showSuccess('Đã cập nhật vật tư')
    } else {
      // Thêm vật tư mới
      response = await axios.post(`/tasks/${props.taskId}/products`, {
        product_id: form.value.product_id,
        quantity: form.value.quantity,
        duration: form.value.duration,
        notes: form.value.notes
      })

      products.value.push(response.data)
      showSuccess('Đã thêm vật tư vào công việc')
    }

    $('#productModal').modal('hide')
  } catch (error) {
    console.error('Lỗi khi lưu vật tư:', error)
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors
    } else if (error.response && error.response.data && error.response.data.message) {
      showError(error.response.data.message)
    } else {
      showError('Không thể lưu vật tư')
    }
  } finally {
    loading.value = false
  }
}

// Xác nhận xóa vật tư
const confirmRemoveProduct = (product) => {
  Swal.fire({
    title: 'Xác nhận xóa',
    text: `Bạn có chắc chắn muốn xóa vật tư ${product.name} khỏi công việc này?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      removeProduct(product)
    }
  })
}

// Xóa vật tư khỏi công việc
const removeProduct = async (product) => {
  try {
    await axios.delete(`/tasks/${props.taskId}/products/${product.id}`)
    products.value = products.value.filter((p) => p.id !== product.id)
    showSuccess('Đã xóa vật tư khỏi công việc')
  } catch (error) {
    console.error('Lỗi khi xóa vật tư:', error)
    showError('Không thể xóa vật tư')
  }
}

// Khởi tạo InputPicker
const initInputPicker = async () => {
  // Nếu đang sửa hoặc đã khởi tạo, không khởi tạo lại
  if (isEditing.value || isInputPickerInitialized.value) {
    updateInputPickerValue()
    return
  }

  try {
    await nextTick()
    const productInputId = 'product_id_picker'

    // Đảm bảo element tồn tại
    const inputElement = document.getElementById(productInputId)
    if (!inputElement) {
      console.error(`Không tìm thấy element với id ${productInputId}`)
      return
    }

    // Đảm bảo jQuery đã được khởi tạo
    if (!window.$ || !window.$.fn || !window.$.fn.inputpicker) {
      console.error('jQuery hoặc plugin inputpicker chưa được khởi tạo')
      return
    }

    // Khởi tạo InputPicker
    const $input = window.$(`#${productInputId}`)

    // Đảm bảo element đã được jQuery chọn đúng
    if ($input.length === 0) {
      console.error(`jQuery không thể tìm thấy element với id ${productInputId}`)
      return
    }

    // Xóa tất cả sự kiện trước
    $input.off('change')

    // Khởi tạo InputPicker mới
    $input.inputpicker({
      data: availableProducts.value.map((product) => ({
        value: product.id,
        text: product.name,
        code: product.code || '',
        unit: product.unit || '',
        category: product.category.name || ''
      })),
      fields: [
        { name: 'text', text: 'Tên vật tư' },
        { name: 'code', text: 'Mã' },
        { name: 'category', text: 'Danh mục' }
      ],
      fieldText: 'text',
      fieldValue: 'value',
      filterOpen: true,
      autoOpen: true,
      headShow: true,
      width: '100%'
    })

    // Lưu instance để tham chiếu sau này
    inputpickerInstance.value = $input
    isInputPickerInitialized.value = true

    // Đặt giá trị ban đầu nếu có
    updateInputPickerValue()

    // Xử lý sự kiện change
    $input.on('change', function () {
      const productId = window.$(this).val()
      form.value.product_id = productId

      if (productId) {
        const product = availableProducts.value.find((p) => p.id == productId)
        if (product) {
          selectedProduct.value = product
        }
      } else {
        selectedProduct.value = null
      }
    })
  } catch (error) {
    console.error('Lỗi khi khởi tạo InputPicker:', error)
  }
}

// Cập nhật giá trị InputPicker
const updateInputPickerValue = () => {
  if (!isEditing.value && inputpickerInstance.value && form.value.product_id) {
    inputpickerInstance.value.val(form.value.product_id)
    const product = availableProducts.value.find((p) => p.id == form.value.product_id)
    if (product) {
      selectedProduct.value = product
    }
  }
}

onMounted(() => {
  loadProducts()
  loadAllProducts()
})
</script>
