<template>
  <AdminLayout>
    <template #header>{{ project.name }}</template>
    <template #breadcrumb>Lợi nhuận dự án</template>

    <!-- Thông tin hoa hồng dự án -->
    <div class="row mb-3">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin hoa hồng</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label>Phần trăm hoa hồng (%)</label>
                  <div class="input-group">
                    <input 
                      type="number" 
                      class="form-control" 
                      v-model="commissionPercentage" 
                      min="0" 
                      max="100" 
                      step="0.01"
                    />
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Tổng doanh thu dự án</label>
                  <input type="text" class="form-control" :value="formatCurrency(totalRevenue)" disabled />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label>Giá trị hoa hồng</label>
                  <input type="text" class="form-control" :value="formatCurrency(commissionAmount)" disabled />
                </div>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-12">
                <button @click="updateCommissionPercentage" class="btn btn-primary" :disabled="isUpdatingCommission">
                  <i class="fas fa-save mr-1"></i> Cập nhật hoa hồng
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Thông tin dự án -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Lợi nhuận dự án</h3>
            <div class="card-tools">
              <Link :href="route('projects.show', project.id)" class="btn btn-sm btn-info">
                <i class="fas fa-arrow-left"></i> Quay lại
              </Link>
            </div>
          </div>
          <div class="card-body p-0 overflow-y-auto overflow-x-auto" style="max-height: calc(100vh - 250px)">
            <table class="table table-hover">
              <thead class="sticky top-0 bg-light">
                <tr style="white-space: nowrap">
                  <th>STT</th>
                  <th>Mã</th>
                  <th>Tên gói thầu</th>
                  <th>Tên nhà thầu</th>
                  <th>Giá dự thầu</th>
                  <th>Giá giao thầu</th>
                  <th>Tổng thu</th>
                  <th>Tổng chi</th>
                  <th>Lợi nhuận</th>
                  <th>%</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(bidPackage, index) in project.bid_packages" :key="bidPackage.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ bidPackage.code }}</td>
                  <td>{{ bidPackage.name }}</td>
                  <td>{{ bidPackage.selected_contractor ? bidPackage.selected_contractor.name : '-' }}</td>
                  <td>{{ formatCurrency(bidPackage.display_estimated_price || 0) }}</td>
                  <td>{{ formatCurrency(bidPackage.display_client_price || 0) }}</td>
                  <td></td>
                  <td>{{ formatCurrency(getTotalPaymentAmount(bidPackage)) }}</td>
                  <!-- Lợi nhuận = giá dự thầu - giá giao thầu -->
                  <td :class="getProfitClass(calculateProfit(bidPackage))">
                    {{ formatCurrency(calculateProfit(bidPackage)) }}
                  </td>
                  <!-- Phần trăm lợi nhuận = lợi nhuận / giá dự thầu * 100 -->
                  <td :class="getProfitClass(calculateProfit(bidPackage))">
                    {{ calculateProfitPercentage(bidPackage) }}%
                  </td>
                </tr>
                <tr v-if="project.bid_packages.length === 0">
                  <td colspan="10" class="text-center">Chưa có gói thầu nào</td>
                </tr>
                <tr class="bg-light font-weight-bold sticky bottom-0">
                  <td colspan="4" class="text-right">Tổng cộng:</td>
                  <td>{{ formatCurrency(totalEstimatedPrice) }}</td>
                  <td>{{ formatCurrency(totalContractAmount) }}</td>
                  <td>{{ formatCurrency(totalReceiptAmount) }}</td>
                  <td>{{ formatCurrency(totalPaidAmount) }}</td>
                  <td>{{ formatCurrency(totalProfit) }}</td>
                  <td>{{ calculateAverageProfitPercentage() }}%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { showSuccess, showError } from '@/utils'
import { ref, computed, onMounted } from 'vue'
import { formatCurrency, parseCurrency } from '@/utils'

const props = defineProps({
  project: Object
})

// Biến lưu trữ % hoa hồng
const commissionPercentage = ref(props.project.commission_percentage || 0)
const isUpdatingCommission = ref(false)

// Tổng doanh thu dự án (tổng giá dự thầu - tổng giá giao thầu)
const totalRevenue = computed(() => {
  return totalEstimatedPrice.value - totalContractAmount.value
})

// Tính giá trị hoa hồng
const commissionAmount = computed(() => {
  return (totalRevenue.value * commissionPercentage.value) / 100
})

// Cập nhật % hoa hồng
const updateCommissionPercentage = () => {
  isUpdatingCommission.value = true
  
  router.put(route('projects.update-commission', props.project.id), {
    commission_percentage: commissionPercentage.value
  })
}


// Tính lợi nhuận cho một gói thầu: Lợi nhuận = giá dự thầu - giá giao thầu
const calculateProfit = (bidPackage) => {
  const estimatedPrice = parseInt(bidPackage.display_estimated_price || 0)
  const clientPrice = parseInt(bidPackage.display_client_price || 0)
  return estimatedPrice - clientPrice
}

// Tính phần trăm lợi nhuận cho một gói thầu: % = lợi nhuận / giá dự thầu * 100
const calculateProfitPercentage = (bidPackage) => {
  const estimatedPrice = parseInt(bidPackage.display_estimated_price || 0)
  if (estimatedPrice === 0) return 0

  const profit = calculateProfit(bidPackage)
  return ((profit / estimatedPrice) * 100).toFixed(2)
}

// Tính tổng giá trị hợp đồng
const totalContractAmount = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    // Sử dụng formatCurrency để lấy giá trị đã được điều chỉnh (chia cho 100)
    const amount = parseInt(bidPackage.display_client_price || 0)
    return total + amount
  }, 0)
})

const totalEstimatedPrice = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    return total + parseInt(bidPackage.display_estimated_price || 0)
  }, 0)
})

// Tính tổng lợi nhuận
const totalProfit = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    return total + calculateProfit(bidPackage)
  }, 0)
})

// Tính phần trăm lợi nhuận trung bình
const calculateAverageProfitPercentage = () => {
  if (totalEstimatedPrice.value === 0) return 0
  return ((totalProfit.value / totalEstimatedPrice.value) * 100).toFixed(2)
}

// Lấy class CSS dựa trên lợi nhuận
const getProfitClass = (profit) => {
  if (profit === null || profit === undefined) return ''
  return profit > 0 ? 'text-success' : profit < 0 ? 'text-danger' : ''
}

// Tính tổng chi cho một gói thầu
const getTotalPaymentAmount = (bidPackage) => {
  const totalPaid = bidPackage.payment_vouchers
    ? bidPackage.payment_vouchers.filter(voucher => voucher.status === 'paid').reduce((total, voucher) => total + parseInt(voucher.amount || 0), 0)
    : 0
  return totalPaid
}

// Tổng tiền đã chi của tất cả gói thầu
const totalPaidAmount = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    return total + getTotalPaymentAmount(bidPackage)
  }, 0)
})

// Tính tổng thu từ phiếu thu của dự án
const getTotalReceiptAmount = () => {
  if (!props.project.receipt_vouchers) return 0
  return props.project.receipt_vouchers.filter(receipt => receipt.status === 'paid').reduce((total, receipt) => {
    return total + parseInt(receipt.amount || 0)
  }, 0)
}

// Tổng thu từ phiếu thu của dự án
const totalReceiptAmount = computed(() => {
  return getTotalReceiptAmount()
})
</script>

<style>
.table-responsive {
  overflow-x: auto;
}

.form-control::-webkit-inner-spin-button,
.form-control::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.form-control[type='number'] {
  -moz-appearance: textfield;
  appearance: textfield;
}

thead tr th {
  white-space: nowrap;
}
</style>
