<template>
  <AdminLayout>
    <template #header>Chi tiết khách hàng</template>
    <template #breadcrumb>Chi tiết khách hàng</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin khách hàng</h3>
            <div class="card-tools">
              <Link :href="route('customers.edit', customer.id)" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Sửa
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Tên khách hàng</span>
                    <span class="info-box-number">{{ customer.name }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Email</span>
                    <span class="info-box-number">{{ customer.email || 'Không có' }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Số điện thoại</span>
                    <span class="info-box-number">{{ customer.phone || 'Không có' }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Địa chỉ</span>
                    <span class="info-box-number">{{ customer.address || 'Không có' }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Mô tả</span>
                    <span class="info-box-number">{{ customer.description || 'Không có mô tả' }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Người tạo</span>
                    <span class="info-box-number">{{ customer.creator ? customer.creator.name : 'Không rõ' }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Ngày tạo</span>
                    <span class="info-box-number">{{ formatDate(customer.created_at) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Danh sách phiếu thu -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách phiếu thu</h3>
            <div class="card-tools">
              <Link
                :href="route('receipt-vouchers.create', { customer_id: customer.id })"
                class="btn btn-sm btn-primary"
              >
                <i class="fas fa-plus"></i> Tạo phiếu thu mới
              </Link>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Mã phiếu thu</th>
                    <th>Dự án</th>
                    <th>Gói thầu</th>
                    <th>Số tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="voucher in customer.receipt_vouchers" :key="voucher.id">
                    <td>{{ voucher.code }}</td>
                    <td>{{ voucher.project ? voucher.project.name : '-' }}</td>
                    <td>
                      {{ voucher.bid_package ? `${voucher.bid_package.code} - ${voucher.bid_package.name}` : '-' }}
                    </td>
                    <td>{{ formatCurrency(voucher.amount) }}</td>
                    <td>
                      <span
                        :class="{
                          'badge badge-warning': voucher.status === 'pending',
                          'badge badge-success': voucher.status === 'completed'
                        }"
                      >
                        {{ voucher.status === 'pending' ? 'Chưa thanh toán' : 'Đã thanh toán' }}
                      </span>
                    </td>
                    <td>{{ formatDate(voucher.created_at) }}</td>
                    <td>
                      <div class="btn-group">
                        <Link :href="route('receipt-vouchers.show', voucher.id)" class="btn btn-xs btn-info">
                          <i class="fas fa-eye"></i> Xem
                        </Link>
                        <Link :href="route('receipt-vouchers.edit', voucher.id)" class="btn btn-xs btn-primary">
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button @click="confirmDeleteVoucher(voucher)" class="btn btn-xs btn-danger">
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="customer.receipt_vouchers.length === 0">
                    <td colspan="7" class="text-center">Khách hàng này chưa có phiếu thu nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { formatCurrency, formatDate, showConfirm, showSuccess } from '@/utils'

const props = defineProps({
  customer: Object
})

// Hàm xác nhận xóa phiếu thu
const confirmDeleteVoucher = (voucher) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa phiếu thu "${voucher.code}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('receipt-vouchers.destroy', voucher.id), {
          onSuccess: () => {
            showSuccess('Phiếu thu đã được xóa thành công.')
          }
        })
      }
    }
  )
}
</script>
