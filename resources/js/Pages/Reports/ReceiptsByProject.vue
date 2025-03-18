<template>
  <AdminLayout>
    <template #header>Báo cáo chi tiết phiếu thu theo dự án</template>
    <template #breadcrumb>Báo cáo chi tiết phiếu thu theo dự án</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Lọc báo cáo</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="project_id">Dự án:</label>
                  <select class="form-control" id="project_id" v-model="filters.project_id" @change="applyFilters">
                    <option value="">Tất cả dự án</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.code }} - {{ project.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="date_from">Từ ngày:</label>
                  <input
                    type="date"
                    class="form-control"
                    id="date_from"
                    v-model="filters.date_from"
                    @change="applyFilters"
                  />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="date_to">Đến ngày:</label>
                  <input
                    type="date"
                    class="form-control"
                    id="date_to"
                    v-model="filters.date_to"
                    @change="applyFilters"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Biểu đồ tổng hợp -->
    <div class="row" v-if="Object.keys(groupedReceipts).length > 0">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tổng thu theo dự án</h3>
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas
                id="projectChart"
                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%"
              ></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tổng thu theo khách hàng</h3>
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas
                id="customerChart"
                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%"
              ></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Báo cáo chi tiết phiếu thu theo dự án</h3>
            <div class="card-tools">
              <button @click="exportToPDF" class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf"></i> Xuất PDF
              </button>
              <button @click="exportToExcel" class="btn btn-sm btn-success ml-2">
                <i class="fas fa-file-excel"></i> Xuất Excel
              </button>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered table-hover" id="report-table">
                <thead>
                  <tr>
                    <th>Dự án</th>
                    <th>Khách hàng</th>
                    <th>Mã phiếu thu</th>
                    <th>Số tiền</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th>Người tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="(group, projectId) in groupedReceipts" :key="projectId">
                    <tr class="bg-light">
                      <td colspan="7">
                        <strong>{{ group.projectName }}</strong>
                      </td>
                    </tr>
                    <template v-for="voucher in group.vouchers" :key="voucher.id">
                      <tr>
                        <td></td>
                        <td>{{ voucher.customer.name }}</td>
                        <td>
                          <Link :href="`/receipt-vouchers/${voucher.id}`">
                            {{ voucher.code }}
                          </Link>
                        </td>
                        <td>{{ formatCurrency(voucher.amount) }}</td>
                        <td>{{ formatDate(voucher.created_at) }}</td>
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
                        <td>{{ voucher.creator ? voucher.creator.name : '-' }}</td>
                      </tr>
                    </template>
                    <tr class="table-primary">
                      <td></td>
                      <td colspan="2"><strong>Tổng dự án:</strong></td>
                      <td>
                        <strong>{{ formatCurrency(group.total) }}</strong>
                      </td>
                      <td colspan="3"></td>
                    </tr>
                  </template>
                  <tr v-if="Object.keys(groupedReceipts).length === 0">
                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                  </tr>
                </tbody>
                <tfoot v-if="Object.keys(groupedReceipts).length > 0">
                  <tr class="table-dark">
                    <td colspan="3"><strong>TỔNG CỘNG:</strong></td>
                    <td>
                      <strong>{{ formatCurrency(totalAmount) }}</strong>
                    </td>
                    <td colspan="3"></td>
                  </tr>
                </tfoot>
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
import { formatCurrency, formatDate } from '@/utils'
import { ref, computed, onMounted } from 'vue'
import Chart from 'chart.js/auto'
import jsPDF from 'jspdf'
import 'jspdf-autotable'
import * as XLSX from 'xlsx'

const props = defineProps({
  receipts: Array,
  projects: Array,
  filters: Object
})

const filters = ref({
  project_id: props.filters.project_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

// Nhóm phiếu thu theo dự án
const groupedReceipts = computed(() => {
  const grouped = {}

  props.receipts.forEach((voucher) => {
    if (!voucher.project) return

    const projectId = voucher.project.id

    if (!grouped[projectId]) {
      grouped[projectId] = {
        projectName: voucher.project.name,
        vouchers: [],
        total: 0
      }
    }

    grouped[projectId].vouchers.push(voucher)
    grouped[projectId].total += parseInt(voucher.amount || 0)
  })

  return grouped
})

// Tính tổng số tiền thu
const totalAmount = computed(() => {
  return props.receipts.reduce((total, voucher) => total + parseInt(voucher.amount || 0), 0)
})

// Hàm áp dụng bộ lọc
const applyFilters = () => {
  router.get(
    '/reports/receipts-by-project',
    {
      project_id: filters.value.project_id,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to
    },
    {
      preserveState: true,
      replace: true
    }
  )
}

// Hàm xuất báo cáo PDF
const exportToPDF = () => {
  const doc = new jsPDF()

  // Tiêu đề báo cáo
  doc.setFontSize(16)
  doc.text('BÁO CÁO CHI TIẾT PHIẾU THU THEO DỰ ÁN', 14, 20)

  // Thông tin lọc
  doc.setFontSize(10)
  let filterText = 'Bộ lọc: '
  if (filters.value.project_id) {
    const project = props.projects.find((p) => p.id == filters.value.project_id)
    filterText += `Dự án: ${project ? project.name : ''}, `
  }
  if (filters.value.date_from) filterText += `Từ ngày: ${filters.value.date_from}, `
  if (filters.value.date_to) filterText += `Đến ngày: ${filters.value.date_to}, `
  doc.text(filterText, 14, 30)

  // Ngày xuất báo cáo
  doc.text(`Ngày xuất báo cáo: ${formatDate(new Date())}`, 14, 35)

  // Dữ liệu cho bảng
  const tableData = []

  Object.values(groupedReceipts.value).forEach((project) => {
    // Thêm dòng dự án
    tableData.push([
      { content: project.projectName, colSpan: 7, styles: { fontStyle: 'bold', fillColor: [240, 240, 240] } }
    ])

    // Thêm các phiếu thu
    project.vouchers.forEach((voucher) => {
      tableData.push([
        '',
        voucher.customer.name,
        voucher.code,
        formatCurrency(voucher.amount),
        formatDate(voucher.created_at),
        voucher.status === 'pending' ? 'Chưa thanh toán' : 'Đã thanh toán',
        voucher.creator ? voucher.creator.name : '-'
      ])
    })

    // Thêm dòng tổng dự án
    tableData.push([
      '',
      { content: 'Tổng dự án:', colSpan: 2, styles: { fontStyle: 'bold', fillColor: [220, 220, 220] } },
      { content: formatCurrency(project.total), styles: { fontStyle: 'bold', fillColor: [220, 220, 220] } },
      { content: '', colSpan: 3, styles: { fillColor: [220, 220, 220] } }
    ])
  })

  // Thêm dòng tổng cộng
  if (tableData.length > 0) {
    tableData.push([
      { content: 'TỔNG CỘNG:', colSpan: 3, styles: { fontStyle: 'bold', fillColor: [200, 200, 200] } },
      { content: formatCurrency(totalAmount.value), styles: { fontStyle: 'bold', fillColor: [200, 200, 200] } },
      { content: '', colSpan: 3, styles: { fillColor: [200, 200, 200] } }
    ])
  }

  // Tạo bảng sử dụng autoTable
  doc.autoTable({
    startY: 40,
    head: [['Dự án', 'Khách hàng', 'Mã phiếu thu', 'Số tiền', 'Ngày tạo', 'Trạng thái', 'Người tạo']],
    body: tableData,
    theme: 'grid',
    styles: {
      fontSize: 8,
      cellPadding: 2
    },
    columnStyles: {
      3: { halign: 'right' }
    }
  })

  // Lưu file
  doc.save('bao-cao-phieu-thu-theo-du-an.pdf')
}

// Hàm xuất báo cáo Excel
const exportToExcel = () => {
  // Dữ liệu cho file Excel
  const excelData = []

  // Thêm tiêu đề
  excelData.push(['BÁO CÁO CHI TIẾT PHIẾU THU THEO DỰ ÁN'])
  excelData.push([])

  // Thêm thông tin lọc
  let filterText = 'Bộ lọc: '
  if (filters.value.project_id) {
    const project = props.projects.find((p) => p.id == filters.value.project_id)
    filterText += `Dự án: ${project ? project.name : ''}, `
  }
  if (filters.value.date_from) filterText += `Từ ngày: ${filters.value.date_from}, `
  if (filters.value.date_to) filterText += `Đến ngày: ${filters.value.date_to}, `
  excelData.push([filterText])

  // Thêm ngày xuất báo cáo
  excelData.push([`Ngày xuất báo cáo: ${formatDate(new Date())}`])
  excelData.push([])

  // Thêm tiêu đề cột
  excelData.push(['Dự án', 'Khách hàng', 'Mã phiếu thu', 'Số tiền', 'Ngày tạo', 'Trạng thái', 'Người tạo'])

  // Thêm dữ liệu
  Object.values(groupedReceipts.value).forEach((project) => {
    // Thêm dòng dự án
    excelData.push([project.projectName, '', '', '', '', '', ''])

    // Thêm các phiếu thu
    project.vouchers.forEach((voucher) => {
      excelData.push([
        '',
        voucher.customer.name,
        voucher.code,
        parseInt(voucher.amount || 0),
        formatDate(voucher.created_at),
        voucher.status === 'pending' ? 'Chưa thanh toán' : 'Đã thanh toán',
        voucher.creator ? voucher.creator.name : '-'
      ])
    })

    // Thêm dòng tổng dự án
    excelData.push(['', 'Tổng dự án:', '', project.total, '', '', ''])
  })

  // Thêm dòng tổng cộng
  if (excelData.length > 6) {
    excelData.push(['TỔNG CỘNG:', '', '', totalAmount.value, '', '', ''])
  }

  // Tạo workbook và worksheet
  const wb = XLSX.utils.book_new()
  const ws = XLSX.utils.aoa_to_sheet(excelData)

  // Định dạng các ô trong worksheet
  if (!ws['!cols']) ws['!cols'] = []
  ws['!cols'] = [
    { wch: 20 }, // Dự án
    { wch: 25 }, // Khách hàng
    { wch: 15 }, // Mã phiếu thu
    { wch: 15 }, // Số tiền
    { wch: 15 }, // Ngày tạo
    { wch: 15 }, // Trạng thái
    { wch: 20 } // Người tạo
  ]

  // Định dạng tiêu đề
  const titleCell = { v: 'BÁO CÁO CHI TIẾT PHIẾU THU THEO DỰ ÁN', t: 's', s: { font: { bold: true, sz: 16 } } }
  ws.A1 = titleCell

  // Định dạng tiêu đề cột
  const headerRow = 6
  for (let i = 0; i < 7; i++) {
    const cellRef = XLSX.utils.encode_cell({ r: headerRow, c: i })
    if (!ws[cellRef]) continue
    ws[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'EEEEEE' } } }
  }

  // Định dạng các dòng tổng
  for (let r = headerRow + 1; r < excelData.length; r++) {
    // Kiểm tra nếu là dòng tổng dự án
    if (excelData[r][1] === 'Tổng dự án:') {
      for (let c = 0; c < 7; c++) {
        const cellRef = XLSX.utils.encode_cell({ r, c })
        if (!ws[cellRef]) continue
        ws[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'E0E0E0' } } }
      }
    }

    // Kiểm tra nếu là dòng tổng cộng
    if (excelData[r][0] === 'TỔNG CỘNG:') {
      for (let c = 0; c < 7; c++) {
        const cellRef = XLSX.utils.encode_cell({ r, c })
        if (!ws[cellRef]) continue
        ws[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'D0D0D0' } } }
      }
    }

    // Định dạng cột số tiền
    if (excelData[r][3] && typeof excelData[r][3] === 'number') {
      const cellRef = XLSX.utils.encode_cell({ r, c: 3 })
      ws[cellRef].z = '#,##0'
    }
  }

  // Định dạng các dòng dự án
  for (let r = headerRow + 1; r < excelData.length; r++) {
    if (excelData[r][0] && !excelData[r][1]) {
      for (let c = 0; c < 7; c++) {
        const cellRef = XLSX.utils.encode_cell({ r, c })
        if (!ws[cellRef]) continue
        ws[cellRef].s = { font: { bold: c === 0 }, fill: { fgColor: { rgb: 'F0F0F0' } } }
      }
    }
  }

  // Gộp ô cho tiêu đề
  if (!ws['!merges']) ws['!merges'] = []
  ws['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }) // Gộp ô tiêu đề

  // Thêm worksheet vào workbook
  XLSX.utils.book_append_sheet(wb, ws, 'Báo cáo phiếu thu')

  // Tạo worksheet thứ hai cho danh sách chi tiết phiếu thu
  const detailData = [
    ['DANH SÁCH CHI TIẾT PHIẾU THU'],
    [],
    ['Mã phiếu thu', 'Khách hàng', 'Dự án', 'Số tiền', 'Ngày tạo', 'Trạng thái', 'Người tạo', 'Mô tả']
  ]

  // Thêm dữ liệu chi tiết
  props.receipts.forEach((voucher) => {
    detailData.push([
      voucher.code,
      voucher.customer.name,
      voucher.project ? voucher.project.name : '-',
      parseInt(voucher.amount || 0),
      formatDate(voucher.created_at),
      voucher.status === 'pending' ? 'Chưa thanh toán' : 'Đã thanh toán',
      voucher.creator ? voucher.creator.name : '-',
      voucher.description || '-'
    ])
  })

  // Thêm dòng tổng cộng
  detailData.push(['TỔNG CỘNG', '', '', totalAmount.value, '', '', '', ''])

  // Tạo worksheet chi tiết
  const detailWs = XLSX.utils.aoa_to_sheet(detailData)

  // Định dạng các ô trong worksheet chi tiết
  if (!detailWs['!cols']) detailWs['!cols'] = []
  detailWs['!cols'] = [
    { wch: 15 }, // Mã phiếu thu
    { wch: 25 }, // Khách hàng
    { wch: 25 }, // Dự án
    { wch: 15 }, // Số tiền
    { wch: 15 }, // Ngày tạo
    { wch: 15 }, // Trạng thái
    { wch: 20 }, // Người tạo
    { wch: 40 } // Mô tả
  ]

  // Định dạng tiêu đề
  detailWs.A1 = { v: 'DANH SÁCH CHI TIẾT PHIẾU THU', t: 's', s: { font: { bold: true, sz: 16 } } }

  // Định dạng tiêu đề cột
  for (let i = 0; i < 8; i++) {
    const cellRef = XLSX.utils.encode_cell({ r: 2, c: i })
    if (!detailWs[cellRef]) continue
    detailWs[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'EEEEEE' } } }
  }

  // Định dạng cột số tiền
  for (let r = 3; r < detailData.length; r++) {
    if (detailData[r][3] && typeof detailData[r][3] === 'number') {
      const cellRef = XLSX.utils.encode_cell({ r, c: 3 })
      detailWs[cellRef].z = '#,##0'
    }
  }

  // Định dạng dòng tổng cộng
  const detailTotalRow = detailData.length - 1
  for (let i = 0; i < 8; i++) {
    const cellRef = XLSX.utils.encode_cell({ r: detailTotalRow, c: i })
    if (!detailWs[cellRef]) continue
    detailWs[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'D0D0D0' } } }
  }

  // Gộp ô cho tiêu đề
  if (!detailWs['!merges']) detailWs['!merges'] = []
  detailWs['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: 7 } }) // Gộp ô tiêu đề

  // Thêm worksheet chi tiết vào workbook
  XLSX.utils.book_append_sheet(wb, detailWs, 'Danh sách chi tiết')

  // Tạo worksheet thứ ba cho biểu đồ tổng hợp theo khách hàng
  const customerData = [['TỔNG HỢP THU THEO KHÁCH HÀNG'], [], ['Khách hàng', 'Tổng thu', 'Tỷ lệ']]

  // Tính tổng thu theo khách hàng
  const customerSummary = {}
  props.receipts.forEach((voucher) => {
    const customerId = voucher.customer.id
    const customerName = voucher.customer.name

    if (!customerSummary[customerId]) {
      customerSummary[customerId] = {
        name: customerName,
        total: 0
      }
    }

    customerSummary[customerId].total += parseInt(voucher.amount || 0)
  })

  // Sắp xếp khách hàng theo tổng thu giảm dần
  const sortedCustomers = Object.values(customerSummary).sort((a, b) => b.total - a.total)

  // Thêm dữ liệu tổng hợp theo khách hàng
  sortedCustomers.forEach((customer) => {
    const percentage = totalAmount.value > 0 ? ((customer.total / totalAmount.value) * 100).toFixed(2) : 0
    customerData.push([customer.name, customer.total, `${percentage}%`])
  })

  // Thêm dòng tổng cộng
  customerData.push(['TỔNG CỘNG', totalAmount.value, '100.00%'])

  // Tạo worksheet tổng hợp theo khách hàng
  const customerWs = XLSX.utils.aoa_to_sheet(customerData)

  // Định dạng các ô trong worksheet tổng hợp theo khách hàng
  if (!customerWs['!cols']) customerWs['!cols'] = []
  customerWs['!cols'] = [
    { wch: 30 }, // Khách hàng
    { wch: 20 }, // Tổng thu
    { wch: 15 } // Tỷ lệ
  ]

  // Định dạng tiêu đề
  customerWs.A1 = { v: 'TỔNG HỢP THU THEO KHÁCH HÀNG', t: 's', s: { font: { bold: true, sz: 16 } } }

  // Định dạng tiêu đề cột
  for (let i = 0; i < 3; i++) {
    const cellRef = XLSX.utils.encode_cell({ r: 2, c: i })
    if (!customerWs[cellRef]) continue
    customerWs[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'EEEEEE' } } }
  }

  // Định dạng cột số tiền
  for (let r = 3; r < customerData.length; r++) {
    if (customerData[r][1] && typeof customerData[r][1] === 'number') {
      const cellRef = XLSX.utils.encode_cell({ r, c: 1 })
      customerWs[cellRef].z = '#,##0'
    }
  }

  // Định dạng dòng tổng cộng
  const customerTotalRow = customerData.length - 1
  for (let i = 0; i < 3; i++) {
    const cellRef = XLSX.utils.encode_cell({ r: customerTotalRow, c: i })
    if (!customerWs[cellRef]) continue
    customerWs[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'D0D0D0' } } }
  }

  // Gộp ô cho tiêu đề
  if (!customerWs['!merges']) customerWs['!merges'] = []
  customerWs['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: 2 } }) // Gộp ô tiêu đề

  // Thêm worksheet tổng hợp theo khách hàng vào workbook
  XLSX.utils.book_append_sheet(wb, customerWs, 'Tổng hợp theo khách hàng')

  // Lưu file
  XLSX.writeFile(wb, 'bao-cao-phieu-thu-theo-du-an.xlsx')
}

onMounted(() => {
  // Khởi tạo biểu đồ nếu có dữ liệu
  if (Object.keys(groupedReceipts.value).length > 0) {
    // Biểu đồ tổng thu theo dự án
    const projectCtx = document.getElementById('projectChart')
    const projectData = Object.values(groupedReceipts.value).map((project) => ({
      name: project.projectName,
      total: project.total
    }))

    new Chart(projectCtx, {
      type: 'pie',
      data: {
        labels: projectData.map((item) => item.name),
        datasets: [
          {
            data: projectData.map((item) => item.total),
            backgroundColor: [
              'rgba(40, 167, 69, 0.7)', // success green
              'rgba(23, 162, 184, 0.7)', // info blue
              'rgba(255, 193, 7, 0.7)', // warning yellow
              'rgba(220, 53, 69, 0.7)', // danger red
              'rgba(108, 117, 125, 0.7)', // secondary gray
              'rgba(0, 123, 255, 0.7)' // primary blue
            ]
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                const label = context.label || ''
                const value = formatCurrency(context.raw)
                const percentage = ((context.raw / totalAmount.value) * 100).toFixed(2) + '%'
                return `${label}: ${value} (${percentage})`
              }
            }
          }
        }
      }
    })

    // Biểu đồ tổng thu theo khách hàng
    const customerCtx = document.getElementById('customerChart')

    // Tính tổng thu theo khách hàng
    const customerData = {}
    props.receipts.forEach((voucher) => {
      const customerId = voucher.customer.id
      const customerName = voucher.customer.name

      if (!customerData[customerId]) {
        customerData[customerId] = {
          name: customerName,
          total: 0
        }
      }

      customerData[customerId].total += parseInt(voucher.amount || 0)
    })

    // Chỉ lấy top 10 khách hàng có tổng thu cao nhất
    const top10Customers = Object.values(customerData)
      .sort((a, b) => b.total - a.total)
      .slice(0, 10)

    new Chart(customerCtx, {
      type: 'bar',
      data: {
        labels: top10Customers.map((item) => item.name),
        datasets: [
          {
            label: 'Tổng thu (VNĐ)',
            data: top10Customers.map((item) => item.total),
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: 'rgba(40, 167, 69, 1)',
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
          },
          title: {
            display: true,
            text: 'Top 10 khách hàng có tổng thu cao nhất',
            font: {
              size: 14
            }
          }
        }
      }
    })
  }
})
</script>
