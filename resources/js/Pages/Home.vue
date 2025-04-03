<template>
  <AdminLayout>
    <template #header>Bảng điều khiển</template>
    <template #breadcrumb>Bảng điều khiển</template>

    <!-- Thống kê tổng quan -->
    <div class="row">
      <!-- Thông tin dự án -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ stats.totalProjects }}</h3>
            <p>Tổng số dự án</p>
          </div>
          <div class="icon">
            <i class="fas fa-project-diagram"></i>
          </div>
          <Link :href="route('projects.index')" class="small-box-footer">
            Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
          </Link>
        </div>
      </div>
      <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ stats.completedProjects }}</h3>
            <p>Dự án đã hoàn thành</p>
          </div>
          <div class="icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <Link :href="route('projects.index') + '?status=completed'" class="small-box-footer">
            Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
          </Link>
        </div>
      </div>
      <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ stats.inProgressProjects }}</h3>
            <p>Dự án đang thực hiện</p>
          </div>
          <div class="icon">
            <i class="fas fa-spinner"></i>
          </div>
          <Link :href="route('projects.index') + '?status=active'" class="small-box-footer">
            Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
          </Link>
        </div>
      </div>
    </div>

    <!-- Thống kê tài chính -->
    <div class="row">
      <!-- Doanh thu -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3>{{ formatCurrency(stats.totalRevenue) }}</h3>
            <p>Doanh thu</p>
          </div>
          <div class="icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="small-box-footer">
            <i class="fas fa-info-circle"></i>
          </div>
        </div>
      </div>
      <!-- Tổng thu -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ formatCurrency(stats.totalReceiptAmount) }}</h3>
            <p>Tổng thu</p>
          </div>
          <div class="icon">
            <i class="fas fa-money-bill"></i>
          </div>
          <Link :href="route('receipt-vouchers.index') + '?status=paid'" class="small-box-footer">
            Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
          </Link>
        </div>
      </div>
      <!-- Phải thu -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>{{ formatCurrency(stats.receivables) }}</h3>
            <p>Phải thu</p>
          </div>
          <div class="icon">
            <i class="fas fa-hand-holding-usd"></i>
          </div>
          <div class="small-box-footer">
            <i class="fas fa-info-circle"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Chi phí -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-secondary">
          <div class="inner">
            <h3>{{ formatCurrency(stats.totalExpense) }}</h3>
            <p>Chi phí</p>
          </div>
          <div class="icon">
            <i class="fas fa-chart-pie"></i>
          </div>
          <div class="small-box-footer">
            <i class="fas fa-info-circle"></i>
          </div>
        </div>
      </div>
      <!-- Tổng chi -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-danger">
          <div class="inner">
            <h3>{{ formatCurrency(stats.totalPaymentAmount) }}</h3>
            <p>Tổng chi</p>
          </div>
          <div class="icon">
            <i class="fas fa-money-bill-wave"></i>
          </div>
          <Link :href="route('payment-vouchers.index') + '?status=paid'" class="small-box-footer">
            Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
          </Link>
        </div>
      </div>
      <!-- Phải chi -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-dark">
          <div class="inner">
            <h3>{{ formatCurrency(stats.payables) }}</h3>
            <p>Phải chi</p>
          </div>
          <div class="icon">
            <i class="fas fa-credit-card"></i>
          </div>
          <div class="small-box-footer">
            <i class="fas fa-info-circle"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Lợi nhuận và Cân đối -->
    <div class="row">
      <div class="col-lg-4 col-12">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ formatCurrency(stats.profit) }}</h3>
            <p>Lợi nhuận (Doanh thu - Chi phí)</p>
          </div>
          <div class="icon">
            <i class="fas fa-chart-bar"></i>
          </div>
          <div class="small-box-footer">
            <i class="fas fa-info-circle"></i>
          </div>
        </div>
      </div>
      <!-- Đề xuất thu -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ formatCurrency(stats.pendingReceiptAmount) }}</h3>
            <p>Đề xuất thu ({{ stats.pendingReceiptCount }})</p>
          </div>
          <div class="icon">
            <i class="fas fa-file-invoice-dollar"></i>
          </div>
          <Link :href="route('receipt-vouchers.index') + '?status=unpaid'" class="small-box-footer">
            Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
          </Link>
        </div>
      </div>
      <!-- Đề xuất chi -->
      <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>{{ formatCurrency(stats.pendingPaymentAmount) }}</h3>
            <p>Đề xuất chi ({{ stats.pendingPaymentCount }})</p>
          </div>
          <div class="icon">
            <i class="fas fa-file-invoice"></i>
          </div>
          <Link :href="route('payment-vouchers.index') + '?status=proposed'" class="small-box-footer">
            Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
          </Link>
        </div>
      </div>
    </div>

    <!-- Biểu đồ thu chi -->
    <div class="row">
      <!-- Thêm biểu đồ tổng thu theo khách hàng -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tổng thu theo khách hàng</h3>
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas
                id="receiptsByCustomerChart"
                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%"
              ></canvas>
            </div>
          </div>
        </div>
      </div>
      <!-- Thêm chi tiết thu theo khách hàng -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Chi tiết thu theo khách hàng</h3>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Khách hàng</th>
                  <th>Tổng thu</th>
                  <th>Tỷ lệ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="receipt in receiptsByCustomer" :key="receipt.customer_id">
                  <td>{{ receipt.customer.name }}</td>
                  <td>{{ formatCurrency(receipt.total_amount) }}</td>
                  <td>
                    <div class="progress progress-xs">
                      <div
                        class="progress-bar bg-success"
                        :style="`width: ${((receipt.total_amount / stats.totalReceiptAmount) * 100).toFixed(2)}%`"
                      ></div>
                    </div>
                    <span class="badge bg-success">
                      {{ ((receipt.total_amount / stats.totalReceiptAmount) * 100).toFixed(2) }}%
                    </span>
                  </td>
                </tr>
                <tr v-if="receiptsByCustomer.length === 0">
                  <td colspan="3" class="text-center">Không có dữ liệu</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tổng chi theo nhà thầu</h3>
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas
                id="paymentsByContractorChart"
                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%"
              ></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Chi tiết chi theo nhà thầu</h3>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Nhà thầu</th>
                  <th>Tổng chi</th>
                  <th>Tỷ lệ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="payment in paymentsByContractor" :key="payment.contractor_id">
                  <td>{{ payment.contractor.name }}</td>
                  <td>{{ formatCurrency(payment.total_amount) }}</td>
                  <td>
                    <div class="progress progress-xs">
                      <div
                        class="progress-bar bg-primary"
                        :style="`width: ${((payment.total_amount / stats.totalPaymentAmount) * 100).toFixed(2)}%`"
                      ></div>
                    </div>
                    <span class="badge bg-primary">
                      {{ ((payment.total_amount / stats.totalPaymentAmount) * 100).toFixed(2) }}%
                    </span>
                  </td>
                </tr>
                <tr v-if="paymentsByContractor.length === 0">
                  <td colspan="3" class="text-center">Không có dữ liệu</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Biểu đồ thu chi theo tháng -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Biểu đồ thu chi theo tháng ({{ currentYear }})</h3>
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas
                id="incomeExpenseChart"
                style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%"
              ></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'
import { formatCurrency, formatDate } from '@/utils'
import { onMounted } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  stats: Object,
  recentPaymentVouchers: Array,
  recentReceiptVouchers: Array,
  paymentsByContractor: Array,
  receiptsByCustomer: Array,
  paymentsByMonth: Object,
  receiptsByMonth: Object,
  currentYear: Number
})

onMounted(() => {
  // Biểu đồ tổng chi theo nhà thầu
  if (props.paymentsByContractor && props.paymentsByContractor.length > 0) {
    const ctx = document.getElementById('paymentsByContractorChart')

    // Tạo mảng màu ngẫu nhiên cho các cột
    const backgroundColors = [
      'rgba(54, 162, 235, 0.7)',
      'rgba(255, 99, 132, 0.7)',
      'rgba(255, 206, 86, 0.7)',
      'rgba(75, 192, 192, 0.7)',
      'rgba(153, 102, 255, 0.7)',
      'rgba(255, 159, 64, 0.7)',
      'rgba(199, 199, 199, 0.7)',
      'rgba(83, 102, 255, 0.7)',
      'rgba(40, 159, 64, 0.7)',
      'rgba(210, 199, 199, 0.7)'
    ]

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: props.paymentsByContractor.map((item) => item.contractor.name),
        datasets: [
          {
            label: 'Tổng chi (VNĐ)',
            data: props.paymentsByContractor.map((item) => item.total_amount),
            backgroundColor: backgroundColors.slice(0, props.paymentsByContractor.length),
            borderColor: backgroundColors
              .slice(0, props.paymentsByContractor.length)
              .map((color) => color.replace('0.7', '1')),
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return formatCurrency(value)
              }
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                return formatCurrency(context.raw)
              }
            }
          }
        }
      }
    })
  }

  // Thêm biểu đồ tổng thu theo khách hàng
  if (props.receiptsByCustomer && props.receiptsByCustomer.length > 0) {
    const ctxReceipts = document.getElementById('receiptsByCustomerChart')

    // Tạo mảng màu cho các cột
    const backgroundColors = [
      'rgba(40, 167, 69, 0.7)',
      'rgba(23, 162, 184, 0.7)',
      'rgba(108, 117, 125, 0.7)',
      'rgba(255, 193, 7, 0.7)',
      'rgba(220, 53, 69, 0.7)',
      'rgba(111, 66, 193, 0.7)',
      'rgba(253, 126, 20, 0.7)',
      'rgba(32, 201, 151, 0.7)',
      'rgba(102, 16, 242, 0.7)',
      'rgba(0, 123, 255, 0.7)'
    ]

    new Chart(ctxReceipts, {
      type: 'bar',
      data: {
        labels: props.receiptsByCustomer.map((item) => item.customer.name),
        datasets: [
          {
            label: 'Tổng thu (VNĐ)',
            data: props.receiptsByCustomer.map((item) => item.total_amount),
            backgroundColor: backgroundColors.slice(0, props.receiptsByCustomer.length),
            borderColor: backgroundColors
              .slice(0, props.receiptsByCustomer.length)
              .map((color) => color.replace('0.7', '1')),
            borderWidth: 1
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return formatCurrency(value)
              }
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                return formatCurrency(context.raw)
              }
            }
          }
        }
      }
    })
  }

  // Biểu đồ thu chi theo tháng
  const incomeExpenseCtx = document.getElementById('incomeExpenseChart')

  // Dữ liệu cho biểu đồ
  const months = Array.from({ length: 12 }, (_, i) => i + 1)
  const monthLabels = months.map((month) => `Tháng ${month}`)

  new Chart(incomeExpenseCtx, {
    type: 'bar',
    data: {
      labels: monthLabels,
      datasets: [
        {
          label: 'Phiếu thu',
          data: months.map((month) => props.receiptsByMonth[month] || 0),
          backgroundColor: 'rgba(40, 167, 69, 0.7)',
          borderColor: 'rgba(40, 167, 69, 1)',
          borderWidth: 1
        },
        {
          label: 'Phiếu chi',
          data: months.map((month) => props.paymentsByMonth[month] || 0),
          backgroundColor: 'rgba(220, 53, 69, 0.7)',
          borderColor: 'rgba(220, 53, 69, 1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return formatCurrency(value)
            }
          }
        }
      },
      plugins: {
        tooltip: {
          callbacks: {
            label: function (context) {
              return `${context.dataset.label}: ${formatCurrency(context.raw)}`
            }
          }
        }
      }
    }
  })
})
</script>
