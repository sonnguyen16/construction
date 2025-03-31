<template>
  <AdminLayout>
    <template #header>Chi tiết phiếu chi</template>
    <template #breadcrumb>Chi tiết phiếu chi</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu chi: {{ paymentVoucher.code }}</h3>
            <div class="card-tools">
              <Link :href="route('payment-vouchers.edit', paymentVoucher.id)" class="btn btn-sm btn-primary mr-1">
                <i class="fas fa-edit"></i> Sửa
              </Link>
              <button
                v-if="paymentVoucher.status === 'proposed'"
                @click="updateStatus('approved')"
                class="btn btn-sm btn-warning mr-1"
              >
                <i class="fas fa-check"></i> Duyệt chi
              </button>
              <button
                v-if="paymentVoucher.status === 'approved'"
                @click="updateStatus('paid')"
                class="btn btn-sm btn-success mr-1"
              >
                <i class="fas fa-money-bill"></i> Đánh dấu đã chi
              </button>
              <button
                v-if="paymentVoucher.status === 'paid'"
                @click="updateStatus('approved')"
                class="btn btn-sm btn-warning mr-1"
              >
                <i class="fas fa-undo"></i> Đánh dấu chưa chi
              </button>
              <button @click="confirmDelete" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Xóa</button>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Mã phiếu chi:</label>
                  <p>
                    <strong>{{ paymentVoucher.code }}</strong>
                  </p>
                </div>
                <div class="form-group">
                  <label>Nhà thầu:</label>
                  <p>
                    <strong>{{ paymentVoucher.contractor.name }}</strong>
                    <span v-if="paymentVoucher.contractor.phone" class="ml-2">
                      (SĐT: {{ paymentVoucher.contractor.phone }})
                    </span>
                  </p>
                </div>
                <div class="form-group" v-if="paymentVoucher.project">
                  <label>Dự án:</label>
                  <p>
                    <Link :href="route('projects.show', paymentVoucher.project.id)" class="text-primary">
                      <strong>{{ paymentVoucher.project.name }}</strong> ({{ paymentVoucher.project.code }})
                    </Link>
                  </p>
                </div>
                <div class="form-group" v-if="paymentVoucher.bid_package">
                  <label>Gói thầu:</label>
                  <p>
                    <Link :href="`/bid-packages/${paymentVoucher.bid_package.id}/edit`" class="text-primary">
                      <strong>{{ paymentVoucher.bid_package.name }}</strong> ({{ paymentVoucher.bid_package.code }})
                    </Link>
                  </p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Số tiền:</label>
                  <p class="text-danger font-weight-bold">{{ formatCurrency(paymentVoucher.amount) }}</p>
                </div>
                <div class="form-group">
                  <label>Trạng thái thanh toán:</label>
                  <p>
                    <span
                      :class="{
                        'badge badge-secondary': paymentVoucher.status === 'proposed',
                        'badge badge-warning': paymentVoucher.status === 'approved',
                        'badge badge-success': paymentVoucher.status === 'paid'
                      }"
                    >
                      {{
                        paymentVoucher.status === 'proposed'
                          ? 'Đề xuất chi'
                          : paymentVoucher.status === 'approved'
                          ? 'Đã duyệt'
                          : 'Đã chi'
                      }}
                    </span>
                  </p>
                </div>
                <div class="form-group" v-if="paymentVoucher.payment_date">
                  <label>Ngày thanh toán:</label>
                  <p>{{ formatDate(paymentVoucher.payment_date) }}</p>
                </div>
                <div class="form-group">
                  <label>Ngày tạo:</label>
                  <p>{{ formatDate(paymentVoucher.created_at) }}</p>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label>Mô tả:</label>
                  <p>{{ paymentVoucher.description || 'Không có mô tả' }}</p>
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
                          <p>{{ paymentVoucher.creator ? paymentVoucher.creator.name : '-' }}</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="form-group">
                          <label>Người cập nhật cuối:</label>
                          <p>{{ paymentVoucher.updater ? paymentVoucher.updater.name : '-' }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Lịch sử thanh toán -->
            <div class="row mt-4" v-if="paymentVoucher.payment_history && paymentVoucher.payment_history.length > 0">
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
                        <tr v-for="(history, index) in paymentVoucher.payment_history" :key="index">
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
            <Link :href="route('payment-vouchers.index')" class="btn btn-secondary">
              <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </Link>
            <Link
              v-if="paymentVoucher.contractor_id"
              :href="route('payment-vouchers.create')"
              :data="{ contractor_id: paymentVoucher.contractor_id }"
              class="btn btn-primary ml-2"
            >
              <i class="fas fa-plus"></i> Tạo phiếu chi mới cho nhà thầu này
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
  paymentVoucher: Object,
  statuses: Object
})

// Cập nhật trạng thái phiếu chi
const updateStatus = (status) => {
  let title, message

  if (status === 'approved') {
    title = props.paymentVoucher.status === 'paid' ? 'Chuyển về trạng thái chờ chi' : 'Duyệt phiếu chi'
    message =
      props.paymentVoucher.status === 'paid'
        ? 'Bạn có chắc chắn muốn chuyển phiếu chi này về trạng thái đã duyệt không?'
        : 'Bạn có chắc chắn muốn duyệt phiếu chi này không?'
  } else if (status === 'paid') {
    title = 'Đánh dấu đã chi'
    message = 'Bạn có chắc chắn muốn đánh dấu phiếu chi này là đã chi không?'
  }

  const paymentDate = status === 'paid' ? new Date().toISOString().substr(0, 10) : null

  showConfirm(title, message, 'Xác nhận', 'Hủy').then((result) => {
    if (result.isConfirmed) {
      router.patch(
        route('payment-vouchers.update-status', props.paymentVoucher.id),
        {
          status: status,
          payment_date: paymentDate
        },
        {
          onSuccess: () => {
            showSuccess(`Phiếu chi đã được cập nhật trạng thái thành công.`)
          }
        }
      )
    }
  })
}

// Xác nhận xóa phiếu chi
const confirmDelete = () => {
  showConfirm(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa phiếu chi "${props.paymentVoucher.code}" không?`,
    'Xóa',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('payment-vouchers.destroy', props.paymentVoucher.id), {
        onSuccess: () => {
          showSuccess('Phiếu chi đã được xóa thành công.')
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
