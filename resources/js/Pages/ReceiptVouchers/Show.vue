<template>
  <AdminLayout>
    <template #header>Chi tiết phiếu thu</template>
    <template #breadcrumb>Chi tiết phiếu thu</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu thu: {{ receiptVoucher.code }}</h3>
            <div class="card-tools">
              <Link :href="route('receipt-vouchers.edit', receiptVoucher.id)" class="btn btn-sm btn-primary mr-1">
                <i class="fas fa-edit"></i> Sửa
              </Link>
              <button
                v-if="receiptVoucher.status === 'unpaid'"
                @click="updateStatus('paid')"
                class="btn btn-sm btn-success mr-1"
              >
                <i class="fas fa-check"></i> Đánh dấu đã thu
              </button>
              <button
                v-if="receiptVoucher.status === 'paid'"
                @click="updateStatus('unpaid')"
                class="btn btn-sm btn-warning mr-1"
              >
                <i class="fas fa-undo"></i> Đánh dấu dự thu
              </button>
              <button @click="confirmDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Xóa</button>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Mã phiếu thu:</label>
                  <p>
                    <strong>{{ receiptVoucher.code }}</strong>
                  </p>
                </div>
                <div class="form-group">
                  <label>Khách hàng:</label>
                  <p>
                    <strong>{{ receiptVoucher.customer.name }}</strong>
                    <span v-if="receiptVoucher.customer.phone" class="ml-2">
                      (SĐT: {{ receiptVoucher.customer.phone }})
                    </span>
                  </p>
                </div>
                <div class="form-group">
                  <label>Dự án:</label>
                  <p v-if="receiptVoucher.project">
                    <Link :href="route('projects.show', receiptVoucher.project.id)" class="text-primary">
                      <strong>{{ receiptVoucher.project.name }}</strong> ({{ receiptVoucher.project.code }})
                    </Link>
                  </p>
                  <p v-else><em>Không liên kết với dự án nào</em></p>
                </div>
                <div class="form-group" v-if="receiptVoucher.bid_package">
                  <label>Gói thầu:</label>
                  <p>
                    <Link :href="`/bid-packages/${receiptVoucher.bid_package.id}/edit`" class="text-primary">
                      <strong>{{ receiptVoucher.bid_package.name }}</strong> ({{ receiptVoucher.bid_package.code }})
                    </Link>
                  </p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Số tiền:</label>
                  <p class="text-success font-weight-bold">{{ formatCurrency(receiptVoucher.amount) }}</p>
                </div>
                <div class="form-group">
                  <label>Trạng thái thanh toán:</label>
                  <p>
                    <span
                      :class="{
                        'badge badge-warning': receiptVoucher.status === 'unpaid',
                        'badge badge-success': receiptVoucher.status === 'paid'
                      }"
                    >
                      {{ receiptVoucher.status === 'unpaid' ? 'Dự thu' : 'Đã thu' }}
                    </span>
                  </p>
                </div>
                <div class="form-group" v-if="receiptVoucher.payment_date">
                  <label>Ngày thanh toán:</label>
                  <p>{{ formatDate(receiptVoucher.payment_date) }}</p>
                </div>
                <div class="form-group">
                  <label>Ngày tạo:</label>
                  <p>{{ formatDate(receiptVoucher.created_at) }}</p>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label>Mô tả:</label>
                  <p>{{ receiptVoucher.description || 'Không có mô tả' }}</p>
                </div>
              </div>
            </div>

            <!-- Thông tin thêm -->
            <div class="row mt-4">
              <div class="col-md-12">
                <div class="card bg-light">
                  <div class="card-header">
                    <h5 class="card-title">Thông tin bổ sung</h5>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Người tạo:</label>
                          <p>{{ receiptVoucher.creator ? receiptVoucher.creator.name : '-' }}</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Người cập nhật cuối:</label>
                          <p>{{ receiptVoucher.updater ? receiptVoucher.updater.name : '-' }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Lịch sử thanh toán -->
            <div class="row mt-4" v-if="receiptVoucher.payment_history && receiptVoucher.payment_history.length > 0">
              <div class="col-md-12">
                <div class="card">
                  <div class="card-header">
                    <h5 class="card-title">Lịch sử thanh toán</h5>
                  </div>
                  <div class="card-body p-0">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Ngày</th>
                          <th>Trạng thái</th>
                          <th>Người thực hiện</th>
                          <th>Ghi chú</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(history, index) in receiptVoucher.payment_history" :key="index">
                          <td>{{ formatDate(history.date) }}</td>
                          <td>
                            <span
                              :class="{
                                'badge badge-warning': history.status === 'unpaid',
                                'badge badge-success': history.status === 'paid'
                              }"
                            >
                              {{ history.status === 'unpaid' ? 'Chưa thanh toán' : 'Đã thanh toán' }}
                            </span>
                          </td>
                          <td>{{ history.user }}</td>
                          <td>{{ history.notes }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <Link :href="route('receipt-vouchers.index')" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </Link>
            <Link
              v-if="receiptVoucher.customer_id"
              :href="route('receipt-vouchers.create')"
              :data="{ customer_id: receiptVoucher.customer_id }"
              class="btn btn-primary ml-2"
            >
              <i class="fas fa-plus"></i> Tạo phiếu thu mới cho khách hàng này
            </Link>
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
  receiptVoucher: Object,
  statuses: Object
})

// Cập nhật trạng thái phiếu thu
const updateStatus = (status) => {
  const isPaid = status === 'paid'
  const title = isPaid ? 'Đánh dấu đã thu' : 'Đánh dấu dự thu'
  const message = isPaid
    ? 'Bạn có chắc chắn muốn đánh dấu phiếu thu này là đã thu không?'
    : 'Bạn có chắc chắn muốn đánh dấu phiếu thu này là dự thu không?'

  const paymentDate = isPaid ? new Date().toISOString().substr(0, 10) : null

  showConfirm(title, message, 'Xác nhận', 'Hủy').then((result) => {
    if (result.isConfirmed) {
      router.patch(
        route('receipt-vouchers.update-status', props.receiptVoucher.id),
        {
          status: status,
          payment_date: paymentDate
        },
        {
          onSuccess: () => {
            showSuccess(`Phiếu thu đã được cập nhật trạng thái thành công.`)
          }
        }
      )
    }
  })
}

// Xác nhận xóa phiếu thu
const confirmDelete = () => {
  showConfirm(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa phiếu thu "${props.receiptVoucher.code}" không?`,
    'Xóa',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('receipt-vouchers.destroy', props.receiptVoucher.id), {
        onSuccess: () => {
          showSuccess('Phiếu thu đã được xóa thành công.')
        }
      })
    }
  })
}
</script>

<style scoped>
.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  font-weight: bold;
  display: block;
  margin-bottom: 0.5rem;
}

.form-group p {
  margin-bottom: 0;
}

.badge {
  font-size: 0.9rem;
  padding: 0.5em 0.75em;
}
</style>
