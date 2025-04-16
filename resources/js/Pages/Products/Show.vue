<template>
  <AdminLayout>
    <template #header>Chi tiết sản phẩm</template>
    <template #breadcrumb>Chi tiết sản phẩm</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin sản phẩm #{{ product.code }}</h3>
            <div class="card-tools">
              <Link :href="route('products.edit', product.id)" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa
              </Link>
              <Link :href="route('products.index')" class="btn btn-sm btn-default ml-2">
                <i class="fas fa-list"></i> Danh sách
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <th style="width: 30%">Mã sản phẩm</th>
                      <td>{{ product.code }}</td>
                    </tr>
                    <tr>
                      <th>Tên sản phẩm</th>
                      <td>{{ product.name }}</td>
                    </tr>
                    <tr>
                      <th>Giá nhập</th>
                      <td>{{ formatCurrency(product.import_price) }} VNĐ</td>
                    </tr>
                    <tr>
                      <th>Giá xuất</th>
                      <td>{{ formatCurrency(product.export_price) }} VNĐ</td>
                    </tr>
                    <tr>
                      <th>Tồn đầu</th>
                      <td>{{ product.initial_stock }} {{ product.unit ? product.unit.name : '' }}</td>
                    </tr>
                    <tr>
                      <th>Ngưỡng cảnh báo</th>
                      <td>
                        <span
                          :class="{
                            'text-danger font-weight-bold': product.initial_stock <= product.warning_threshold
                          }"
                        >
                          {{ product.warning_threshold }} {{ product.unit ? product.unit.name : '' }}
                          <i
                            v-if="product.initial_stock <= product.warning_threshold"
                            class="fas fa-exclamation-triangle text-warning ml-2"
                          ></i>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <th>Danh mục</th>
                      <td>{{ product.category ? product.category.name : 'N/A' }}</td>
                    </tr>
                    <tr>
                      <th>Đơn vị</th>
                      <td>{{ product.unit ? product.unit.name : 'N/A' }}</td>
                    </tr>
                    <tr>
                      <th>Ghi chú</th>
                      <td style="white-space: pre-line">{{ product.notes || 'Không có ghi chú' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <th style="width: 30%">Người tạo</th>
                      <td>{{ product.creator ? product.creator.name : 'N/A' }}</td>
                    </tr>
                    <tr>
                      <th>Ngày tạo</th>
                      <td>{{ formatDateTime(product.created_at) }}</td>
                    </tr>
                    <tr>
                      <th>Người cập nhật</th>
                      <td>{{ product.updater ? product.updater.name : 'N/A' }}</td>
                    </tr>
                    <tr>
                      <th>Ngày cập nhật</th>
                      <td>{{ formatDateTime(product.updated_at) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button @click="confirmDelete(product)" class="btn btn-danger">
              <i class="fas fa-trash"></i> Xóa sản phẩm
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { showConfirm, showSuccess, showError, formatCurrency } from '@/utils'

const props = defineProps({
  product: Object
})

// Định dạng ngày tháng
const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

// Xác nhận xóa sản phẩm
const confirmDelete = (product) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa sản phẩm "${product.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('products.destroy', product.id), {
          onSuccess: () => {
            showSuccess('Sản phẩm đã được xóa thành công.')
            router.visit(route('products.index'))
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa sản phẩm này.')
          }
        })
      }
    }
  )
}
</script>
