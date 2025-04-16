<template>
  <AdminLayout>
    <template #header>Chi tiết phiếu nhập kho</template>
    <template #breadcrumb>Chi tiết phiếu nhập kho</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu nhập kho #{{ importVoucher.code }}</h3>
            <div class="card-tools">
              <Link :href="route('import-vouchers.edit', importVoucher.id)" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa
              </Link>
              <Link :href="route('import-vouchers.index')" class="btn btn-sm btn-default ml-2">
                <i class="fas fa-list"></i> Danh sách
              </Link>
            </div>
          </div>

          <div class="card-body">
            <div class="row">
              <!-- Thông tin cơ bản của phiếu -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title">Thông tin chung</h5>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-bordered">
                      <tr>
                        <th style="width: 35%">Mã phiếu nhập</th>
                        <td>{{ importVoucher.code }}</td>
                      </tr>
                      <tr>
                        <th>Ngày nhập kho</th>
                        <td>{{ formatDate(importVoucher.import_date) }}</td>
                      </tr>
                      <tr>
                        <th>Dự án</th>
                        <td>{{ importVoucher.project ? importVoucher.project.name : 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Nhà thầu</th>
                        <td>{{ importVoucher.contractor ? importVoucher.contractor.name : 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Tổng giá trị</th>
                        <td class="font-weight-bold text-primary">
                          {{ formatCurrency(importVoucher.total_amount) }} VNĐ
                        </td>
                      </tr>
                      <tr>
                        <th>Ghi chú</th>
                        <td>{{ importVoucher.notes || 'Không có ghi chú' }}</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Thông tin người tạo, sửa -->
              <div class="col-md-6">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title">Thông tin hệ thống</h5>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-bordered">
                      <tr>
                        <th style="width: 35%">Người tạo</th>
                        <td>{{ importVoucher.creator ? importVoucher.creator.name : 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Ngày tạo</th>
                        <td>{{ formatDateTime(importVoucher.created_at) }}</td>
                      </tr>
                      <tr>
                        <th>Người cập nhật</th>
                        <td>{{ importVoucher.updater ? importVoucher.updater.name : 'N/A' }}</td>
                      </tr>
                      <tr>
                        <th>Ngày cập nhật</th>
                        <td>{{ formatDateTime(importVoucher.updated_at) }}</td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Chi tiết phiếu nhập kho -->
            <div class="row mt-3">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title">Chi tiết phiếu nhập kho</h5>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped">
                        <thead>
                          <tr class="bg-light">
                            <th style="width: 40px">STT</th>
                            <th>Sản phẩm</th>
                            <th>Mã sản phẩm</th>
                            <th>Đơn vị</th>
                            <th>Số lượng</th>
                            <th>Giá nhập</th>
                            <th>Thành tiền</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="(item, index) in importVoucher.items" :key="item.id">
                            <td class="text-center">{{ index + 1 }}</td>
                            <td>{{ item.product ? item.product.name : 'N/A' }}</td>
                            <td>{{ item.product ? item.product.code : 'N/A' }}</td>
                            <td>{{ item.product && item.product.unit ? item.product.unit.name : 'N/A' }}</td>
                            <td class="text-center">{{ item.quantity }}</td>
                            <td class="text-end">{{ formatCurrency(item.import_price) }}</td>
                            <td class="text-end">{{ formatCurrency(item.quantity * item.import_price) }}</td>
                          </tr>
                          <tr v-if="importVoucher.items.length === 0">
                            <td colspan="7" class="text-center">Không có chi tiết nào</td>
                          </tr>
                          <tr v-if="importVoucher.items.length > 0" class="font-weight-bold">
                            <td colspan="6" class="text-end">Tổng cộng:</td>
                            <td class="text-end">{{ formatCurrency(importVoucher.total_amount) }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <button @click="confirmDelete(importVoucher)" class="btn btn-danger">
              <i class="fas fa-trash mr-1"></i> Xóa phiếu nhập kho
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
  importVoucher: Object
})

// Format date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(date)
}

// Format datetime
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

// Confirm delete
const confirmDelete = (voucher) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa phiếu nhập kho "${voucher.code}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('import-vouchers.destroy', voucher.id), {
          onSuccess: () => {
            showSuccess('Phiếu nhập kho đã được xóa thành công.')
            router.visit(route('import-vouchers.index'))
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa phiếu nhập kho này.')
          }
        })
      }
    }
  )
}
</script>

<style scoped>
.text-end {
  text-align: right !important;
}
</style>
