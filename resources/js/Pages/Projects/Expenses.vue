<template>
  <AdminLayout>
    <template #header>{{ project.name }}</template>
    <template #breadcrumb>Chi phí dự án</template>

    <!-- Thông tin dự án -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Chi phí dự án</h3>
            <div class="card-tools">
              <Link :href="route('projects.show', project.id)" class="btn btn-sm btn-info">
                <i class="fas fa-arrow-left"></i> Quay lại
              </Link>
            </div>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover">
              <thead>
                <tr style="white-space: nowrap">
                  <th>STT</th>
                  <th>Mã</th>
                  <th>Tên gói thầu</th>
                  <th>Nhà thầu</th>
                  <th>Giá giao thầu</th>
                  <th>Chi lần 1</th>
                  <th>Chi lần 2</th>
                  <th>Chi lần 3</th>
                  <th>Chi lần 4</th>
                  <th>Chi lần 5</th>
                  <th>Còn lại</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(bidPackage, index) in project.bid_packages" :key="bidPackage.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ bidPackage.code }}</td>
                  <td>{{ bidPackage.name }}</td>
                  <td>{{ bidPackage.selected_contractor ? bidPackage.selected_contractor.name : '-' }}</td>
                  <td class="text-right font-bold">{{ formatCurrency(bidPackage.client_price || 0) }}</td>

                  <!-- Chi lần 1 -->
                  <td class="">
                    <div class="text-right" v-if="getPaymentVoucherAtIndex(bidPackage, 0)">
                      <button
                        @click="viewPaymentVoucher(getPaymentVoucherAtIndex(bidPackage, 0))"
                        class="btn font-bold pt-0 pe-0"
                      >
                        {{ formatCurrency(getPaymentVoucherAtIndex(bidPackage, 0).amount) }}
                      </button>
                    </div>
                    <button v-else @click="goToCreatePaymentVoucher(bidPackage)" class="btn btn-sm btn-success">
                      <i class="fas fa-plus me-1 mb-1"></i> Tạo
                    </button>
                  </td>

                  <!-- Chi lần 2 -->
                  <td class="">
                    <div class="text-right" v-if="getPaymentVoucherAtIndex(bidPackage, 1)">
                      <button
                        @click="viewPaymentVoucher(getPaymentVoucherAtIndex(bidPackage, 1))"
                        class="btn font-bold pt-0 pe-0"
                      >
                        {{ formatCurrency(getPaymentVoucherAtIndex(bidPackage, 1).amount) }}
                      </button>
                    </div>
                    <button
                      v-else-if="getPaymentVoucherAtIndex(bidPackage, 0)"
                      @click="goToCreatePaymentVoucher(bidPackage)"
                      class="btn btn-sm btn-success"
                    >
                      <i class="fas fa-plus me-1 mb-1"></i> Tạo
                    </button>
                    <span v-else>-</span>
                  </td>

                  <!-- Chi lần 3 -->
                  <td class="">
                    <div class="text-right" v-if="getPaymentVoucherAtIndex(bidPackage, 2)">
                      <button
                        @click="viewPaymentVoucher(getPaymentVoucherAtIndex(bidPackage, 2))"
                        class="btn font-bold pt-0 pe-0"
                      >
                        {{ formatCurrency(getPaymentVoucherAtIndex(bidPackage, 2).amount) }}
                      </button>
                    </div>
                    <button
                      v-else-if="getPaymentVoucherAtIndex(bidPackage, 1)"
                      @click="goToCreatePaymentVoucher(bidPackage)"
                      class="btn btn-sm btn-success"
                    >
                      <i class="fas fa-plus me-1 mb-1"></i> Tạo
                    </button>
                    <span v-else>-</span>
                  </td>

                  <!-- Chi lần 4 -->
                  <td class="">
                    <div class="text-right" v-if="getPaymentVoucherAtIndex(bidPackage, 3)">
                      <button
                        @click="viewPaymentVoucher(getPaymentVoucherAtIndex(bidPackage, 3))"
                        class="btn font-bold pt-0 pe-0"
                      >
                        {{ formatCurrency(getPaymentVoucherAtIndex(bidPackage, 3).amount) }}
                      </button>
                    </div>
                    <button
                      v-else-if="getPaymentVoucherAtIndex(bidPackage, 2)"
                      @click="goToCreatePaymentVoucher(bidPackage)"
                      class="btn btn-sm btn-success"
                    >
                      <i class="fas fa-plus me-1 mb-1"></i> Tạo
                    </button>
                    <span v-else>-</span>
                  </td>

                  <!-- Chi lần 5 -->
                  <td class="">
                    <div class="text-right" v-if="getPaymentVoucherAtIndex(bidPackage, 4)">
                      <button
                        @click="viewPaymentVoucher(getPaymentVoucherAtIndex(bidPackage, 4))"
                        class="btn font-bold pt-0 pe-0"
                      >
                        {{ formatCurrency(getPaymentVoucherAtIndex(bidPackage, 4).amount) }}
                      </button>
                    </div>
                    <button
                      v-else-if="getPaymentVoucherAtIndex(bidPackage, 3)"
                      @click="goToCreatePaymentVoucher(bidPackage)"
                      class="btn btn-sm btn-success"
                    >
                      <i class="fas fa-plus me-1 mb-1"></i> Tạo
                    </button>
                    <span v-else>-</span>
                  </td>

                  <!-- Còn lại -->
                  <td class="text-right font-bold">
                    {{ formatCurrency(calculateRemainingAmount(bidPackage)) }}
                  </td>
                </tr>
                <tr v-if="project.bid_packages.length === 0">
                  <td colspan="11" class="text-center">Chưa có gói thầu nào</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="bg-light font-weight-bold">
                  <td colspan="4" class="text-right">Tổng cộng:</td>
                  <td class="text-right">{{ formatCurrency(totalContractAmount) }}</td>
                  <td class="text-right">{{ formatCurrency(totalPaymentAmount(0)) }}</td>
                  <td class="text-right">{{ formatCurrency(totalPaymentAmount(1)) }}</td>
                  <td class="text-right">{{ formatCurrency(totalPaymentAmount(2)) }}</td>
                  <td class="text-right">{{ formatCurrency(totalPaymentAmount(3)) }}</td>
                  <td class="text-right">{{ formatCurrency(totalPaymentAmount(4)) }}</td>
                  <td class="text-right">{{ formatCurrency(totalRemainingAmount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal xem chi tiết phiếu chi -->
    <div
      class="modal fade"
      id="viewPaymentVoucherModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="viewPaymentVoucherModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="viewPaymentVoucherModalLabel">Chi tiết phiếu chi</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" v-if="selectedPaymentVoucher">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Mã phiếu chi:</label>
                  <p>{{ selectedPaymentVoucher.code }}</p>
                </div>
                <div class="form-group">
                  <label>Nhà thầu:</label>
                  <p>{{ selectedPaymentVoucher.contractor ? selectedPaymentVoucher.contractor.name : 'N/A' }}</p>
                </div>
                <div class="form-group">
                  <label>Dự án:</label>
                  <p>{{ selectedPaymentVoucher.project ? selectedPaymentVoucher.project.name : 'N/A' }}</p>
                </div>
                <div class="form-group">
                  <label>Gói thầu:</label>
                  <p>{{ selectedPaymentVoucher.bid_package ? selectedPaymentVoucher.bid_package.name : 'N/A' }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Số tiền:</label>
                  <p class="text-primary font-weight-bold">{{ formatCurrency(selectedPaymentVoucher.amount) }}</p>
                </div>
                <div class="form-group">
                  <label>Trạng thái:</label>
                  <p>
                    <span
                      :class="{
                        'badge badge-success': selectedPaymentVoucher.status === 'paid',
                        'badge badge-warning': selectedPaymentVoucher.status === 'unpaid'
                      }"
                    >
                      {{ selectedPaymentVoucher.status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                    </span>
                  </p>
                </div>
                <div class="form-group">
                  <label>Ngày thanh toán:</label>
                  <p>
                    {{
                      selectedPaymentVoucher.payment_date
                        ? formatDate(selectedPaymentVoucher.payment_date)
                        : 'Chưa thanh toán'
                    }}
                  </p>
                </div>
                <div class="form-group">
                  <label>Ngày tạo:</label>
                  <p>{{ formatDate(selectedPaymentVoucher.created_at) }}</p>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label>Mô tả:</label>
                  <p>{{ selectedPaymentVoucher.description || 'Không có mô tả' }}</p>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            <button
              @click="editPaymentVoucher(selectedPaymentVoucher)"
              v-if="selectedPaymentVoucher"
              class="btn btn-primary"
            >
              <i class="fas fa-edit"></i> Sửa phiếu chi
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { formatCurrency, showWarning, formatDate } from '@/utils'

const props = defineProps({
  project: Object
})

const selectedPaymentVoucher = ref(null)

// Lấy phiếu chi tại vị trí index của gói thầu
const getPaymentVoucherAtIndex = (bidPackage, index) => {
  if (!bidPackage.payment_vouchers || bidPackage.payment_vouchers.length <= index) {
    return null
  }
  // Sắp xếp phiếu chi theo ngày tạo
  const sortedVouchers = [...bidPackage.payment_vouchers].sort((a, b) => {
    return new Date(a.created_at) - new Date(b.created_at)
  })
  return sortedVouchers[index]
}

// Tính số tiền còn lại phải chi
const calculateRemainingAmount = (bidPackage) => {
  const totalContractAmount = bidPackage.client_price
  const totalPaid = bidPackage.payment_vouchers
    ? bidPackage.payment_vouchers.reduce((total, voucher) => total + parseInt(voucher.amount || 0), 0)
    : 0
  return totalContractAmount - totalPaid
}

// Xem chi tiết phiếu chi
const viewPaymentVoucher = (voucher) => {
  selectedPaymentVoucher.value = voucher
  window.$('#viewPaymentVoucherModal').modal('show')
}

// Thêm hàm mới để chuyển hướng đến trang sửa phiếu chi
const editPaymentVoucher = (voucher) => {
  if (!voucher) return

  // Đóng modal trước khi chuyển hướng
  window.$('#viewPaymentVoucherModal').modal('hide')

  // Xóa backdrop của modal
  window.$('.modal-backdrop').remove()
  document.body.classList.remove('modal-open')
  document.body.style.paddingRight = ''

  // Chờ 300ms (thời gian hiệu ứng đóng modal) trước khi chuyển hướng
  setTimeout(() => {
    window.location.href = route('payment-vouchers.edit', voucher.id)
  }, 300)
}

// Thêm hàm xử lý khi đóng modal
onMounted(() => {
  window.$('#viewPaymentVoucherModal').on('hidden.bs.modal', function () {
    document.body.classList.remove('modal-open')
    if (document.querySelector('.modal-backdrop')) {
      document.querySelector('.modal-backdrop').remove()
    }
    document.body.style.paddingRight = ''
  })
})

// Xóa event listener khi unmount component
onBeforeUnmount(() => {
  window.$('#viewPaymentVoucherModal').off('hidden.bs.modal')
})

// Sửa thành goToCreatePaymentVoucher để tránh modal backdrop
const goToCreatePaymentVoucher = (bidPackage) => {
  // Nếu gói thầu chưa có nhà thầu được chọn
  if (!bidPackage.selected_contractor_id) {
    showWarning('Gói thầu này chưa có nhà thầu được chọn. Vui lòng chọn nhà thầu trước khi tạo phiếu chi.')
    return
  }

  // Chuyển hướng đến trang tạo phiếu chi với thông tin gói thầu và nhà thầu được chọn
  const url = `/payment-vouchers/create?project_id=${props.project.id}&bid_package_id=${bidPackage.id}&contractor_id=${bidPackage.selected_contractor_id}`
  router.visit(url)
}

// Tổng giá giao thầu của tất cả các gói thầu
const totalContractAmount = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    return total + (parseInt(bidPackage.client_price) || 0)
  }, 0)
})

// Tổng tiền đã chi ở lần chi thứ index
const totalPaymentAmount = (index) => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    const payment = getPaymentVoucherAtIndex(bidPackage, index)
    return total + (payment ? parseInt(payment.amount) || 0 : 0)
  }, 0)
}

// Tổng tiền còn lại phải chi của tất cả gói thầu
const totalRemainingAmount = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    return total + calculateRemainingAmount(bidPackage)
  }, 0)
})
</script>

<style>
.table-responsive {
  overflow-x: auto;
}

thead tr th {
  white-space: nowrap;
}

/* Thêm CSS để xử lý backdrop */
body.modal-open {
  overflow: auto !important;
  padding-right: 0 !important;
}

/* Đảm bảo modal hiển thị đúng */
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

/* Đảm bảo z-index của modal cao hơn backdrop */
.modal-dialog {
  z-index: 1050;
}
</style>
