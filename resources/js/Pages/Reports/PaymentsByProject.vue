<template>
  <AdminLayout>
    <template #header>Báo cáo chi tiết phiếu chi theo dự án</template>
    <template #breadcrumb>Báo cáo chi tiết phiếu chi theo dự án</template>

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
    <div class="row" v-if="Object.keys(groupedPayments).length > 0">
      <div class="col-md-6">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tổng chi theo dự án</h3>
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
            <h3 class="card-title">Tổng chi theo nhà thầu</h3>
          </div>
          <div class="card-body">
            <div class="chart">
              <canvas
                id="contractorChart"
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
            <h3 class="card-title">Báo cáo chi tiết phiếu chi theo dự án</h3>
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
                    <th>Gói thầu</th>
                    <th>Nhà thầu</th>
                    <th>Mã phiếu chi</th>
                    <th>Số tiền</th>
                    <th>Ngày tạo</th>
                    <th>Người tạo</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="(group, projectId) in groupedPayments" :key="projectId">
                    <tr class="bg-light">
                      <td colspan="7">
                        <strong>{{ group.projectName }}</strong>
                      </td>
                    </tr>
                    <template v-for="(bidPackageGroup, bidPackageId) in group.bidPackages" :key="bidPackageId">
                      <tr>
                        <td></td>
                        <td colspan="6">
                          <strong>{{ bidPackageGroup.bidPackageName }}</strong>
                        </td>
                      </tr>
                      <tr v-for="voucher in bidPackageGroup.vouchers" :key="voucher.id">
                        <td></td>
                        <td></td>
                        <td>{{ voucher.contractor.name }}</td>
                        <td>
                          <Link :href="`/payment-vouchers/${voucher.id}`">
                            {{ voucher.code }}
                          </Link>
                        </td>
                        <td>{{ formatCurrency(voucher.amount) }}</td>
                        <td>{{ formatDate(voucher.created_at) }}</td>
                        <td>{{ voucher.creator ? voucher.creator.name : '-' }}</td>
                      </tr>
                      <tr class="table-secondary">
                        <td></td>
                        <td></td>
                        <td colspan="2"><strong>Tổng gói thầu:</strong></td>
                        <td>
                          <strong>{{ formatCurrency(bidPackageGroup.total) }}</strong>
                        </td>
                        <td colspan="2"></td>
                      </tr>
                    </template>
                    <tr class="table-primary">
                      <td></td>
                      <td colspan="3"><strong>Tổng dự án:</strong></td>
                      <td>
                        <strong>{{ formatCurrency(group.total) }}</strong>
                      </td>
                      <td colspan="2"></td>
                    </tr>
                  </template>
                  <tr v-if="Object.keys(groupedPayments).length === 0">
                    <td colspan="7" class="text-center">Không có dữ liệu</td>
                  </tr>
                </tbody>
                <tfoot v-if="Object.keys(groupedPayments).length > 0">
                  <tr class="table-dark">
                    <td colspan="4"><strong>TỔNG CỘNG:</strong></td>
                    <td>
                      <strong>{{ formatCurrency(totalAmount) }}</strong>
                    </td>
                    <td colspan="2"></td>
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
  payments: Array,
  projects: Array,
  filters: Object
})

const filters = ref({
  project_id: props.filters.project_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

// Nhóm phiếu chi theo dự án và gói thầu
const groupedPayments = computed(() => {
  const grouped = {}

  props.payments.forEach((voucher) => {
    if (!voucher.bid_package || !voucher.bid_package.project) return

    const projectId = voucher.bid_package.project.id
    const bidPackageId = voucher.bid_package.id

    if (!grouped[projectId]) {
      grouped[projectId] = {
        projectName: voucher.bid_package.project.name,
        bidPackages: {},
        total: 0
      }
    }

    if (!grouped[projectId].bidPackages[bidPackageId]) {
      grouped[projectId].bidPackages[bidPackageId] = {
        bidPackageName: `${voucher.bid_package.code} - ${voucher.bid_package.name}`,
        vouchers: [],
        total: 0
      }
    }

    grouped[projectId].bidPackages[bidPackageId].vouchers.push(voucher)
    grouped[projectId].bidPackages[bidPackageId].total += parseInt(voucher.amount || 0)
    grouped[projectId].total += parseInt(voucher.amount || 0)
  })

  return grouped
})

// Tính tổng số tiền chi
const totalAmount = computed(() => {
  return props.payments.reduce((total, voucher) => total + parseInt(voucher.amount || 0), 0)
})

// Hàm áp dụng bộ lọc
const applyFilters = () => {
  router.get(
    '/reports/payments-by-project',
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
  doc.text('BÁO CÁO CHI TIẾT PHIẾU CHI THEO DỰ ÁN', 14, 20)

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

  Object.values(groupedPayments.value).forEach((project) => {
    // Thêm dòng dự án
    tableData.push([
      { content: project.projectName, colSpan: 7, styles: { fontStyle: 'bold', fillColor: [240, 240, 240] } }
    ])

    Object.values(project.bidPackages).forEach((bidPackage) => {
      // Thêm dòng gói thầu
      tableData.push(['', { content: bidPackage.bidPackageName, colSpan: 6, styles: { fontStyle: 'bold' } }])

      // Thêm các phiếu chi
      bidPackage.vouchers.forEach((voucher) => {
        tableData.push([
          '',
          '',
          voucher.contractor.name,
          voucher.code,
          formatCurrency(voucher.amount),
          formatDate(voucher.created_at),
          voucher.creator ? voucher.creator.name : '-'
        ])
      })

      // Thêm dòng tổng gói thầu
      tableData.push([
        '',
        '',
        { content: 'Tổng gói thầu:', colSpan: 2, styles: { fontStyle: 'bold' } },
        { content: formatCurrency(bidPackage.total), styles: { fontStyle: 'bold' } },
        '',
        ''
      ])
    })

    // Thêm dòng tổng dự án
    tableData.push([
      '',
      { content: 'Tổng dự án:', colSpan: 3, styles: { fontStyle: 'bold', fillColor: [220, 220, 220] } },
      { content: formatCurrency(project.total), styles: { fontStyle: 'bold', fillColor: [220, 220, 220] } },
      { content: '', colSpan: 2, styles: { fillColor: [220, 220, 220] } }
    ])
  })

  // Thêm dòng tổng cộng
  if (tableData.length > 0) {
    tableData.push([
      { content: 'TỔNG CỘNG:', colSpan: 4, styles: { fontStyle: 'bold', fillColor: [200, 200, 200] } },
      { content: formatCurrency(totalAmount.value), styles: { fontStyle: 'bold', fillColor: [200, 200, 200] } },
      { content: '', colSpan: 2, styles: { fillColor: [200, 200, 200] } }
    ])
  }

  // Tạo bảng sử dụng autoTable
  doc.autoTable({
    startY: 40,
    head: [['Dự án', 'Gói thầu', 'Nhà thầu', 'Mã phiếu chi', 'Số tiền', 'Ngày tạo', 'Người tạo']],
    body: tableData,
    theme: 'grid',
    styles: {
      fontSize: 8,
      cellPadding: 2
    },
    columnStyles: {
      4: { halign: 'right' }
    }
  })

  // Lưu file
  doc.save('bao-cao-phieu-chi-theo-du-an.pdf')
}

// Hàm xuất báo cáo Excel
const exportToExcel = () => {
  // Dữ liệu cho file Excel
  const excelData = []

  // Thêm tiêu đề
  excelData.push(['BÁO CÁO CHI TIẾT PHIẾU CHI THEO DỰ ÁN'])
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
  excelData.push(['Dự án', 'Gói thầu', 'Nhà thầu', 'Mã phiếu chi', 'Số tiền', 'Ngày tạo', 'Người tạo'])

  // Thêm dữ liệu
  Object.values(groupedPayments.value).forEach((project) => {
    // Thêm dòng dự án
    excelData.push([project.projectName, '', '', '', '', '', ''])

    Object.values(project.bidPackages).forEach((bidPackage) => {
      // Thêm dòng gói thầu
      excelData.push(['', bidPackage.bidPackageName, '', '', '', '', ''])

      // Thêm các phiếu chi
      bidPackage.vouchers.forEach((voucher) => {
        excelData.push([
          '',
          '',
          voucher.contractor.name,
          voucher.code,
          parseInt(voucher.amount || 0),
          formatDate(voucher.created_at),
          voucher.creator ? voucher.creator.name : '-'
        ])
      })

      // Thêm dòng tổng gói thầu
      excelData.push(['', '', 'Tổng gói thầu:', '', bidPackage.total, '', ''])
    })

    // Thêm dòng tổng dự án
    excelData.push(['', 'Tổng dự án:', '', '', project.total, '', ''])
  })

  // Thêm dòng tổng cộng
  if (excelData.length > 6) {
    excelData.push(['TỔNG CỘNG:', '', '', '', totalAmount.value, '', ''])
  }

  // Tạo workbook và worksheet
  const wb = XLSX.utils.book_new()
  const ws = XLSX.utils.aoa_to_sheet(excelData)

  // Định dạng các ô trong worksheet
  if (!ws['!cols']) ws['!cols'] = []
  ws['!cols'] = [
    { wch: 20 }, // Dự án
    { wch: 25 }, // Gói thầu
    { wch: 20 }, // Nhà thầu
    { wch: 15 }, // Mã phiếu chi
    { wch: 15 }, // Số tiền
    { wch: 15 }, // Ngày tạo
    { wch: 20 } // Người tạo
  ]

  // Định dạng tiêu đề
  const titleCell = { v: 'BÁO CÁO CHI TIẾT PHIẾU CHI THEO DỰ ÁN', t: 's', s: { font: { bold: true, sz: 16 } } }
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
    // Kiểm tra nếu là dòng tổng gói thầu
    if (excelData[r][2] === 'Tổng gói thầu:') {
      for (let c = 0; c < 7; c++) {
        const cellRef = XLSX.utils.encode_cell({ r, c })
        if (!ws[cellRef]) continue
        ws[cellRef].s = { font: { bold: c >= 2 && c <= 4 }, fill: { fgColor: { rgb: 'F5F5F5' } } }
      }
    }

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
    if (excelData[r][4] && typeof excelData[r][4] === 'number') {
      const cellRef = XLSX.utils.encode_cell({ r, c: 4 })
      ws[cellRef].z = '#,##0'
    }
  }

  // Định dạng các dòng dự án
  for (let r = headerRow + 1; r < excelData.length; r++) {
    if (excelData[r][0] && !excelData[r][1] && !excelData[r][2]) {
      for (let c = 0; c < 7; c++) {
        const cellRef = XLSX.utils.encode_cell({ r, c })
        if (!ws[cellRef]) continue
        ws[cellRef].s = { font: { bold: c === 0 }, fill: { fgColor: { rgb: 'F0F0F0' } } }
      }
    }
  }

  // Định dạng các dòng gói thầu
  for (let r = headerRow + 1; r < excelData.length; r++) {
    if (!excelData[r][0] && excelData[r][1] && !excelData[r][2]) {
      for (let c = 0; c < 7; c++) {
        const cellRef = XLSX.utils.encode_cell({ r, c })
        if (!ws[cellRef]) continue
        ws[cellRef].s = { font: { bold: c === 1 }, fill: { fgColor: { rgb: 'F8F8F8' } } }
      }
    }
  }

  // Gộp ô cho tiêu đề
  if (!ws['!merges']) ws['!merges'] = []
  ws['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }) // Gộp ô tiêu đề

  // Thêm worksheet vào workbook
  XLSX.utils.book_append_sheet(wb, ws, 'Báo cáo phiếu chi')

  // Tạo worksheet thứ hai cho danh sách chi tiết phiếu chi
  const detailData = [
    ['DANH SÁCH CHI TIẾT PHIẾU CHI'],
    [],
    ['Mã phiếu chi', 'Nhà thầu', 'Dự án', 'Gói thầu', 'Số tiền', 'Ngày tạo', 'Người tạo', 'Mô tả']
  ]

  // Thêm dữ liệu chi tiết
  props.payments.forEach((voucher) => {
    detailData.push([
      voucher.code,
      voucher.contractor.name,
      voucher.bid_package && voucher.bid_package.project ? voucher.bid_package.project.name : '-',
      voucher.bid_package ? `${voucher.bid_package.code} - ${voucher.bid_package.name}` : '-',
      parseInt(voucher.amount || 0),
      formatDate(voucher.created_at),
      voucher.creator ? voucher.creator.name : '-',
      voucher.description || '-'
    ])
  })

  // Thêm dòng tổng cộng
  detailData.push(['TỔNG CỘNG', '', '', '', totalAmount.value, '', '', ''])

  // Tạo worksheet chi tiết
  const detailWs = XLSX.utils.aoa_to_sheet(detailData)

  // Định dạng các ô trong worksheet chi tiết
  if (!detailWs['!cols']) detailWs['!cols'] = []
  detailWs['!cols'] = [
    { wch: 15 }, // Mã phiếu chi
    { wch: 20 }, // Nhà thầu
    { wch: 25 }, // Dự án
    { wch: 30 }, // Gói thầu
    { wch: 15 }, // Số tiền
    { wch: 15 }, // Ngày tạo
    { wch: 20 }, // Người tạo
    { wch: 40 } // Mô tả
  ]

  // Định dạng tiêu đề
  detailWs.A1 = { v: 'DANH SÁCH CHI TIẾT PHIẾU CHI', t: 's', s: { font: { bold: true, sz: 16 } } }

  // Định dạng tiêu đề cột
  for (let i = 0; i < 8; i++) {
    const cellRef = XLSX.utils.encode_cell({ r: 2, c: i })
    if (!detailWs[cellRef]) continue
    detailWs[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'EEEEEE' } } }
  }

  // Định dạng cột số tiền
  for (let r = 3; r < detailData.length; r++) {
    if (detailData[r][4] && typeof detailData[r][4] === 'number') {
      const cellRef = XLSX.utils.encode_cell({ r, c: 4 })
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

  // Tạo worksheet thứ ba cho biểu đồ tổng hợp theo nhà thầu
  const contractorData = [['TỔNG HỢP CHI THEO NHÀ THẦU'], [], ['Nhà thầu', 'Tổng chi', 'Tỷ lệ']]

  // Tính tổng chi theo nhà thầu
  const contractorSummary = {}
  props.payments.forEach((voucher) => {
    const contractorId = voucher.contractor.id
    const contractorName = voucher.contractor.name

    if (!contractorSummary[contractorId]) {
      contractorSummary[contractorId] = {
        name: contractorName,
        total: 0
      }
    }

    contractorSummary[contractorId].total += parseInt(voucher.amount || 0)
  })

  // Sắp xếp nhà thầu theo tổng chi giảm dần
  const sortedContractors = Object.values(contractorSummary).sort((a, b) => b.total - a.total)

  // Thêm dữ liệu tổng hợp theo nhà thầu
  sortedContractors.forEach((contractor) => {
    const percentage = totalAmount.value > 0 ? ((contractor.total / totalAmount.value) * 100).toFixed(2) : 0
    contractorData.push([contractor.name, contractor.total, `${percentage}%`])
  })

  // Thêm dòng tổng cộng
  contractorData.push(['TỔNG CỘNG', totalAmount.value, '100.00%'])

  // Tạo worksheet tổng hợp theo nhà thầu
  const contractorWs = XLSX.utils.aoa_to_sheet(contractorData)

  // Định dạng các ô trong worksheet tổng hợp theo nhà thầu
  if (!contractorWs['!cols']) contractorWs['!cols'] = []
  contractorWs['!cols'] = [
    { wch: 30 }, // Nhà thầu
    { wch: 20 }, // Tổng chi
    { wch: 15 } // Tỷ lệ
  ]

  // Định dạng tiêu đề
  contractorWs.A1 = { v: 'TỔNG HỢP CHI THEO NHÀ THẦU', t: 's', s: { font: { bold: true, sz: 16 } } }

  // Định dạng tiêu đề cột
  for (let i = 0; i < 3; i++) {
    const cellRef = XLSX.utils.encode_cell({ r: 2, c: i })
    if (!contractorWs[cellRef]) continue
    contractorWs[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'EEEEEE' } } }
  }

  // Định dạng cột số tiền
  for (let r = 3; r < contractorData.length; r++) {
    if (contractorData[r][1] && typeof contractorData[r][1] === 'number') {
      const cellRef = XLSX.utils.encode_cell({ r, c: 1 })
      contractorWs[cellRef].z = '#,##0'
    }
  }

  // Định dạng dòng tổng cộng
  const contractorTotalRow = contractorData.length - 1
  for (let i = 0; i < 3; i++) {
    const cellRef = XLSX.utils.encode_cell({ r: contractorTotalRow, c: i })
    if (!contractorWs[cellRef]) continue
    contractorWs[cellRef].s = { font: { bold: true }, fill: { fgColor: { rgb: 'D0D0D0' } } }
  }

  // Gộp ô cho tiêu đề
  if (!contractorWs['!merges']) contractorWs['!merges'] = []
  contractorWs['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: 2 } }) // Gộp ô tiêu đề

  // Thêm worksheet tổng hợp theo nhà thầu vào workbook
  XLSX.utils.book_append_sheet(wb, contractorWs, 'Tổng hợp theo nhà thầu')

  // Lưu file
  XLSX.writeFile(wb, 'bao-cao-phieu-chi-theo-du-an.xlsx')
}

onMounted(() => {
  // Khởi tạo biểu đồ nếu có dữ liệu
  if (Object.keys(groupedPayments.value).length > 0) {
    // Biểu đồ tổng chi theo dự án
    const projectCtx = document.getElementById('projectChart')
    const projectData = Object.values(groupedPayments.value).map((project) => ({
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
              'rgba(54, 162, 235, 0.7)',
              'rgba(255, 99, 132, 0.7)',
              'rgba(255, 206, 86, 0.7)',
              'rgba(75, 192, 192, 0.7)',
              'rgba(153, 102, 255, 0.7)',
              'rgba(255, 159, 64, 0.7)'
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

    // Biểu đồ tổng chi theo nhà thầu
    const contractorCtx = document.getElementById('contractorChart')

    // Tính tổng chi theo nhà thầu
    const contractorData = {}
    props.payments.forEach((voucher) => {
      const contractorId = voucher.contractor.id
      const contractorName = voucher.contractor.name

      if (!contractorData[contractorId]) {
        contractorData[contractorId] = {
          name: contractorName,
          total: 0
        }
      }

      contractorData[contractorId].total += parseInt(voucher.amount || 0)
    })

    new Chart(contractorCtx, {
      type: 'bar',
      data: {
        labels: Object.values(contractorData).map((item) => item.name),
        datasets: [
          {
            label: 'Tổng chi (VNĐ)',
            data: Object.values(contractorData).map((item) => item.total),
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
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
})
</script>
