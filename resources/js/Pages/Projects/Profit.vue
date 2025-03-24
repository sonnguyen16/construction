<template>
  <AdminLayout>
    <template #header>{{ project.name }}</template>
    <template #breadcrumb>Lợi nhuận dự án</template>

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
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover">
              <thead>
                <tr style="white-space: nowrap">
                  <th>STT</th>
                  <th>Mã</th>
                  <th>Tên gói thầu</th>
                  <th>Giá giao thầu</th>
                  <th>%</th>
                  <th>Lợi nhuận</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(bidPackage, index) in project.bid_packages" :key="bidPackage.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ bidPackage.code }}</td>
                  <td>{{ bidPackage.name }}</td>
                  <td>{{ formatCurrency((bidPackage.estimated_price || 0) + (bidPackage.additional_price || 0)) }}</td>

                  <!-- Phần trăm lợi nhuận -->
                  <td>
                    <div class="input-group input-group-sm" style="width: 100px">
                      <input
                        type="number"
                        class="form-control"
                        v-model="profitPercentages[bidPackage.id]"
                        @input="updateProfitPercentage(bidPackage.id)"
                        min="0"
                        max="100"
                        step="0.1"
                      />
                      <div class="input-group-append">
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                  </td>

                  <!-- Lợi nhuận tính theo % -->
                  <td :class="getProfitClass(calculateProfit(bidPackage))">
                    {{ formatCurrency(calculateProfit(bidPackage)) }}
                  </td>
                </tr>
                <tr v-if="project.bid_packages.length === 0">
                  <td colspan="6" class="text-center">Chưa có gói thầu nào</td>
                </tr>
                <tr class="bg-light font-weight-bold">
                  <td colspan="3" class="text-right">Tổng cộng:</td>
                  <td>{{ formatCurrency(totalContractAmount) }}</td>
                  <td>{{ calculateAverageProfitPercentage() }}%</td>
                  <td :class="getProfitClass(totalProfit)">{{ formatCurrency(totalProfit) }}</td>
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
import { ref, computed, onMounted } from 'vue'
import { formatCurrency, parseCurrency } from '@/utils'

const props = defineProps({
  project: Object
})

const profitPercentages = ref({})

// Khởi tạo giá trị phần trăm lợi nhuận cho mỗi gói thầu
onMounted(() => {
  props.project.bid_packages.forEach((bidPackage) => {
    // Nếu đã có tỷ lệ lợi nhuận được lưu, sử dụng nó
    profitPercentages.value[bidPackage.id] = bidPackage.profit_percentage || 10 // Mặc định 10%
  })
})

// Cập nhật phần trăm lợi nhuận cho gói thầu
const updateProfitPercentage = (bidPackageId) => {
  // Đảm bảo giá trị nằm trong khoảng từ 0-100
  if (profitPercentages.value[bidPackageId] < 0) {
    profitPercentages.value[bidPackageId] = 0
  } else if (profitPercentages.value[bidPackageId] > 100) {
    profitPercentages.value[bidPackageId] = 100
  }

  // Gọi API để cập nhật tỷ lệ lợi nhuận
  router.patch(
    route('bid-packages.update-profit-percentage', bidPackageId),
    {
      profit_percentage: profitPercentages.value[bidPackageId]
    },
    {
      preserveState: true,
      preserveScroll: true,
      only: ['project']
    }
  )
}

// Tính lợi nhuận cho một gói thầu dựa trên phần trăm
const calculateProfit = (bidPackage) => {
  // Sử dụng formatCurrency để lấy giá trị đã được điều chỉnh (chia cho 100)
  const totalAmount = parseCurrency(
    formatCurrency((bidPackage.estimated_price || 0) + (bidPackage.additional_price || 0)),
    false
  )
  const percentage = profitPercentages.value[bidPackage.id] || 0

  return Math.round((totalAmount * percentage) / 100) || 0
}

// Tính tổng giá trị hợp đồng
const totalContractAmount = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    // Sử dụng formatCurrency để lấy giá trị đã được điều chỉnh (chia cho 100)
    const amount = parseCurrency(
      formatCurrency((bidPackage.estimated_price || 0) + (bidPackage.additional_price || 0)),
      false
    )
    return total + amount
  }, 0)
})

// Tính tổng lợi nhuận
const totalProfit = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => {
    return total + calculateProfit(bidPackage)
  }, 0)
})

// Tính phần trăm lợi nhuận trung bình (có trọng số)
const calculateAverageProfitPercentage = () => {
  if (totalContractAmount.value === 0) return 0

  // Tính tỷ lệ lợi nhuận trung bình có trọng số
  const weightedSum = props.project.bid_packages.reduce((total, bidPackage) => {
    // Sử dụng formatCurrency để lấy giá trị đã được điều chỉnh
    const amount = parseCurrency(
      formatCurrency((bidPackage.estimated_price || 0) + (bidPackage.additional_price || 0)),
      false
    )
    return total + amount * (profitPercentages.value[bidPackage.id] || 0)
  }, 0)

  return (weightedSum / totalContractAmount.value || 0).toFixed(2)
}

// Lấy class CSS dựa trên lợi nhuận
const getProfitClass = (profit) => {
  if (profit === null || profit === undefined) return ''
  return profit > 0 ? 'text-success' : profit < 0 ? 'text-danger' : ''
}
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
}

thead tr th {
  white-space: nowrap;
}
</style>
