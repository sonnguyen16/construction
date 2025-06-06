<template>
  <AdminLayout>
    <template #header>Bảng điều khiển</template>
    <template #breadcrumb>Bảng điều khiển</template>

    <div>
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

      <!-- Biểu đồ dòng tiền tổng hợp -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Biểu đồ dòng tiền tổng hợp</h3>
              <div class="card-tools d-flex align-items-center">
                <!-- Combo box chọn tháng/năm -->
                <div class="mr-2" v-if="chartView === 'day'">
                  <select class="form-control form-control-sm" v-model="selectedMonth" @change="updateCharts">
                    <option v-for="(month, index) in monthNames" :key="index" :value="index + 1">{{ month }}</option>
                  </select>
                </div>
                <div class="mr-2" v-if="chartView === 'day' || chartView === 'month'">
                  <select class="form-control form-control-sm" v-model="selectedYear" @change="updateCharts">
                    <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                  </select>
                </div>
                <!-- Nút chọn chế độ xem -->
                <div class="btn-group">
                  <button
                    @click="changeChartView('day')"
                    :class="['btn', 'btn-sm', chartView === 'day' ? 'btn-primary' : 'btn-default']"
                  >
                    Ngày
                  </button>
                  <button
                    @click="changeChartView('month')"
                    :class="['btn', 'btn-sm', chartView === 'month' ? 'btn-primary' : 'btn-default']"
                  >
                    Tháng
                  </button>
                  <button
                    @click="changeChartView('year')"
                    :class="['btn', 'btn-sm', chartView === 'year' ? 'btn-primary' : 'btn-default']"
                  >
                    Năm
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="chart">
                <canvas
                  id="cashflowChart"
                  style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%"
                ></canvas>
              </div>
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
import { onMounted, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'
import annotationPlugin from 'chartjs-plugin-annotation'
import { usePermission } from '@/Composables/usePermission'

// Đăng ký plugin annotation
Chart.register(annotationPlugin)

const props = defineProps({
  stats: Object,
  recentPaymentVouchers: Array,
  recentReceiptVouchers: Array,
  paymentsByContractor: Array,
  receiptsByCustomer: Array,
  paymentsByMonth: Object,
  receiptsByMonth: Object,
  currentYear: Number,
  currentMonth: Number,
  dailyData: Object,
  monthlyData: Object,
  yearlyData: Object
})

const { can } = usePermission()

// Biến để theo dõi chế độ xem biểu đồ đường (ngày/tháng/năm)
const chartView = ref('month') // Mặc định xem theo tháng

// Biến lưu trữ instance của biểu đồ dòng tiền
let cashflowChartInstance = null

// Các biến cho combo box chọn tháng/năm
const selectedMonth = ref(props.currentMonth)
const selectedYear = ref(props.currentYear)

// Danh sách các tháng trong năm
const monthNames = [
  'Tháng 1',
  'Tháng 2',
  'Tháng 3',
  'Tháng 4',
  'Tháng 5',
  'Tháng 6',
  'Tháng 7',
  'Tháng 8',
  'Tháng 9',
  'Tháng 10',
  'Tháng 11',
  'Tháng 12'
]

// Tạo danh sách các năm có sẵn (từ 2020 đến năm hiện tại)
const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let i = 2020; i <= currentYear; i++) {
    years.push(i)
  }
  return years
})

// Hàm thay đổi chế độ xem biểu đồ đường
function changeChartView(view) {
  chartView.value = view
  updateCharts()
}

// Hàm cập nhật biểu đồ dòng tiền khi thay đổi tháng hoặc năm
function updateCharts() {
  // Lưu lại chế độ xem hiện tại
  const currentView = chartView.value

  // Gọi API để lấy dữ liệu mới dựa trên tháng và năm đã chọn
  if (currentView === 'day') {
    // Nếu đang xem theo ngày, lấy dữ liệu cho tháng đã chọn
    router.reload({
      only: ['dailyData'],
      data: {
        month: selectedMonth.value,
        year: selectedYear.value,
        view: 'day'
      },
      onSuccess: () => {
        // Khôi phục chế độ xem sau khi tải lại
        chartView.value = currentView
        updateCashflowChart()
      }
    })
  } else if (currentView === 'month') {
    // Nếu đang xem theo tháng, lấy dữ liệu cho năm đã chọn
    router.reload({
      only: ['monthlyData'],
      data: {
        year: selectedYear.value,
        view: 'month'
      },
      onSuccess: () => {
        // Khôi phục chế độ xem sau khi tải lại
        chartView.value = currentView
        updateCashflowChart()
      }
    })
  } else {
    // Nếu đang xem theo năm, không cần lấy dữ liệu mới vì đã có sẵn
    updateCashflowChart()
  }
}

// Hàm xử lý khi nhấp vào điểm trên biểu đồ dòng tiền
function handleCashflowChartClick(event, chartElements, chart) {
  if (chartElements.length === 0) return

  const clickedElement = chartElements[0]
  const datasetIndex = clickedElement.datasetIndex
  const index = clickedElement.index

  // Xác định loại dữ liệu dựa vào dataset
  const datasetLabel = chart.data.datasets[datasetIndex].label
  let routeName, status

  // Xác định thời gian tương ứng
  let date_from, date_to

  if (chartView.value === 'day') {
    // Nếu xem theo ngày, cả date_from và date_to đều là cùng một ngày
    const selectedDay = props.dailyData.labels[index]
    date_from = `${selectedYear.value}-${String(selectedMonth.value).padStart(2, '0')}-${String(selectedDay).padStart(
      2,
      '0'
    )}`
    date_to = date_from
  } else if (chartView.value === 'month') {
    // Nếu xem theo tháng, date_from là ngày đầu tháng, date_to là ngày cuối tháng
    const monthValue = index + 1 // Chuyển từ index 0-11 sang tháng 1-12
    const daysInMonth = new Date(selectedYear.value, monthValue, 0).getDate()
    date_from = `${selectedYear.value}-${String(monthValue).padStart(2, '0')}-01`
    date_to = `${selectedYear.value}-${String(monthValue).padStart(2, '0')}-${String(daysInMonth).padStart(2, '0')}`
  } else {
    // chartView.value === 'year'
    // Nếu xem theo năm, date_from là ngày đầu năm, date_to là ngày cuối năm
    const selectedYear = props.yearlyData.labels[index]
    date_from = `${selectedYear}-01-01`
    date_to = `${selectedYear}-12-31`
  }

  // Xác định trang đích dựa vào loại dữ liệu
  if (datasetLabel.includes('Dự thu chi')) {
    // Hiển thị trang phiếu thu/chi chưa thanh toán
    const queryParams = {
      status: 'unpaid',
      date_from,
      date_to
    }

    // Mở tab mới cho cả phiếu thu và phiếu chi
    window.open(route('receipt-vouchers.index', queryParams), '_blank')
    window.open(route('payment-vouchers.index', queryParams), '_blank')
  } else if (datasetLabel.includes('Thu chi thực')) {
    // Hiển thị trang phiếu thu/chi đã thanh toán
    const queryParams = {
      status: 'paid',
      date_from,
      date_to
    }

    // Mở tab mới cho cả phiếu thu và phiếu chi
    window.open(route('receipt-vouchers.index', queryParams), '_blank')
    window.open(route('payment-vouchers.index', queryParams), '_blank')
  } else if (datasetLabel.includes('Dòng tiền')) {
    // Hiển thị trang phiếu thu/chi đã thanh toán và khoản vay
    const queryParams = {
      status: 'paid',
      date_from,
      date_to
    }

    // Mở tab mới cho cả phiếu thu, phiếu chi và khoản vay
    window.open(route('receipt-vouchers.index', queryParams), '_blank')
    window.open(route('payment-vouchers.index', queryParams), '_blank')
    window.open(route('loans.index', { date_from, date_to }), '_blank')
  }
}

// Hàm lấy vị trí đánh dấu thời gian hiện tại
function getTimeMarkerPosition(chartData) {
  if (!chartData) return null

  // Xử lý theo chế độ xem
  if (chartView.value === 'day' && chartData.currentDay) {
    // Trả về vị trí ngày hiện tại trong mảng labels
    return chartData.currentDay - 1 // Trừ 1 vì mảng bắt đầu từ 0
  } else if (chartView.value === 'month' && chartData.currentMonth) {
    // Trả về vị trí tháng hiện tại trong mảng labels
    return chartData.currentMonth - 1 // Trừ 1 vì mảng bắt đầu từ 0
  } else if (chartView.value === 'year' && chartData.currentYearIndex !== undefined) {
    // Trả về vị trí năm hiện tại trong mảng labels
    return chartData.currentYearIndex
  }

  return null // Không có đánh dấu nếu không phải thời gian hiện tại
}

// Hàm cập nhật biểu đồ dòng tiền tổng hợp
function updateCashflowChart() {
  // Hủy biểu đồ cũ nếu có
  if (cashflowChartInstance) {
    cashflowChartInstance.destroy()
  }

  // Lấy dữ liệu tương ứng với chế độ xem
  let chartData
  let chartTitle

  if (chartView.value === 'day') {
    chartData = props.dailyData
    chartTitle = `Dòng tiền theo ngày (Tháng ${selectedMonth.value}/${selectedYear.value})`
  } else if (chartView.value === 'month') {
    chartData = props.monthlyData
    chartTitle = `Dòng tiền theo tháng (${selectedYear.value})`
  } else {
    // chartView.value === 'year'
    chartData = props.yearlyData
    chartTitle = 'Dòng tiền theo năm'
  }

  // Kiểm tra nếu chartData hoặc các thuộc tính cần thiết không tồn tại
  if (!chartData || !chartData.labels || !chartData.cashflow) {
    console.error('Dữ liệu biểu đồ không hợp lệ:', chartData)
    return // Thoát khỏi hàm nếu dữ liệu không hợp lệ
  }

  // Tạo biểu đồ đường mới cho dòng tiền
  const ctx = document.getElementById('cashflowChart')
  if (!ctx) {
    console.error('Không tìm thấy phần tử canvas có id "cashflowChart"')
    return
  }

  cashflowChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels: chartData.labels,
      datasets: [
        {
          label: 'Dự thu chi (dự thu - dự chi)',
          data: chartData.cashflow.expected,
          borderColor: 'rgba(0, 128, 255, 1)', // Màu xanh dương đậm
          backgroundColor: 'rgba(0, 128, 255, 0.1)',
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: false,
          tension: 0.1
        },
        {
          label: 'Thu chi thực (đã thu + vay - đã chi)',
          data: chartData.cashflow.actual,
          borderColor: 'rgba(255, 102, 0, 1)', // Màu cam đỏ
          backgroundColor: 'rgba(255, 102, 0, 0.1)',
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: false,
          tension: 0.1
        },
        {
          label: 'Dòng tiền (đã thu - vay - đã chi)',
          data: chartData.cashflow.flow,
          borderColor: 'rgba(0, 153, 0, 1)', // Màu xanh lá đậm
          backgroundColor: 'rgba(0, 153, 0, 0.1)',
          borderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: false,
          tension: 0.1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      onClick: handleCashflowChartClick,

      scales: {
        y: {
          beginAtZero: false, // Cho phép giá trị âm
          ticks: {
            callback: function (value) {
              return formatCurrency(value)
            }
          }
        }
      },
      plugins: {
        title: {
          display: true,
          text: chartTitle,
          font: {
            size: 16
          }
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              return `${context.dataset.label}: ${formatCurrency(context.raw)}`
            }
          }
        },
        legend: {
          position: 'bottom'
        },
        // Thêm cấu hình đường dọc đánh dấu thời gian hiện tại
        annotation: {
          annotations: {
            line1: {
              type: 'line',
              mode: 'vertical',
              scaleID: 'x',
              value: getTimeMarkerPosition(chartData),
              borderColor: 'rgba(255, 0, 0, 0.8)',
              borderWidth: 2,
              borderDash: [6, 6], // Thêm đường nét đứt (6px đường, 6px khoảng trống),
              label: {
                content: 'Hiện tại',
                enabled: true,
                position: 'top',
                backgroundColor: 'rgba(255, 0, 0, 0.8)',
                color: 'white',
                font: {
                  weight: 'bold'
                }
              }
            }
          }
        }
      }
    }
  })
}

onMounted(() => {
  // Khởi tạo biểu đồ dòng tiền
  updateCashflowChart()

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
          label: 'Phiếu thu đã thanh toán',
          data: months.map((month) => props.receiptsByMonth[month]?.paid || 0),
          backgroundColor: 'rgba(40, 167, 69, 0.9)',
          borderColor: 'rgba(40, 167, 69, 1)',
          borderWidth: 1,
          stack: 'thu'
        },
        {
          label: 'Phiếu thu chưa thanh toán',
          data: months.map((month) => props.receiptsByMonth[month]?.unpaid || 0),
          backgroundColor: 'rgba(23, 162, 184, 0.9)',
          borderColor: 'rgba(23, 162, 184, 1)',
          borderWidth: 1,
          stack: 'thu'
        },
        {
          label: 'Phiếu chi đã thanh toán',
          data: months.map((month) => props.paymentsByMonth[month]?.paid || 0),
          backgroundColor: 'rgba(220, 53, 69, 0.9)',
          borderColor: 'rgba(220, 53, 69, 1)',
          borderWidth: 1,
          stack: 'chi'
        },
        {
          label: 'Phiếu chi chưa thanh toán',
          data: months.map((month) => props.paymentsByMonth[month]?.unpaid || 0),
          backgroundColor: 'rgba(255, 193, 7, 0.9)',
          borderColor: 'rgba(255, 193, 7, 1)',
          borderWidth: 1,
          stack: 'chi'
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
