<template>
  <AdminLayout title="Báo cáo công nợ khách hàng">
    <template #header>Báo cáo công nợ khách hàng</template>
    <template #breadcrumb>Báo cáo công nợ khách hàng</template>

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Báo cáo công nợ khách hàng</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-success" @click="exportToExcel">
                    <i class="fas fa-file-excel mr-1"></i> Xuất Excel
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead class="sticky-top bg-white">
                      <tr>
                        <th class="text-center" style="width: 50px">STT</th>
                        <th>Khách hàng</th>
                        <th>Điện thoại</th>
                        <th>Địa chỉ</th>
                        <th class="text-right">Tổng dự án</th>
                        <th class="text-right">Tổng chi trả</th>
                        <th class="text-right">Còn lại</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template v-for="(item, index) in debtData" :key="index">
                        <!-- Dòng khách hàng chính -->
                        <tr :class="{ 'bg-light': hasExpandedDetails(item.customer.id) }">
                          <td class="text-center">{{ index + 1 }}</td>
                          <td>
                            <div class="d-flex align-items-center">
                              <button
                                @click="toggleDetails(item.customer.id)"
                                class="btn btn-xs mr-2"
                                :class="hasExpandedDetails(item.customer.id) ? 'btn-info' : 'btn-secondary'"
                              >
                                <i :class="hasExpandedDetails(item.customer.id) ? 'fas fa-minus' : 'fas fa-plus'"></i>
                              </button>
                              {{ item.customer.name }}
                            </div>
                          </td>
                          <td>{{ item.customer.phone }}</td>
                          <td>{{ item.customer.address }}</td>
                          <td class="text-right">
                            <div class="d-flex justify-content-end align-items-center">
                              <span>{{ formatCurrency(item.total_project) }}</span>
                            </div>
                          </td>
                          <td class="text-right">
                            <div class="d-flex justify-content-end align-items-center">
                              <span>{{ formatCurrency(item.total_paid) }}</span>
                              <button
                                @click="viewPaidVouchers(item.customer.id)"
                                class="btn btn-xs btn-outline-success ml-2"
                                title="Xem phiếu đã thanh toán"
                                style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"
                              >
                                <i class="fas fa-eye"></i>
                              </button>
                            </div>
                          </td>
                          <td class="text-right" :class="{'text-danger': item.remaining > 0, 'text-success': item.remaining <= 0}">
                            <div class="d-flex justify-content-end align-items-center">
                              <span>{{ formatCurrency(item.remaining) }}</span>
                              <button
                                @click="viewUnpaidVouchers(item.customer.id)"
                                class="btn btn-xs btn-outline-primary ml-2"
                                title="Xem phiếu chưa thanh toán"
                                style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"
                              >
                                <i class="fas fa-eye"></i>
                              </button>
                            </div>
                          </td>
                        </tr>

                        <!-- Chi tiết theo dự án -->
                        <template v-if="hasExpandedDetails(item.customer.id) && item.project_details && item.project_details.length > 0">
                          <tr class="project-detail-header bg-light">
                            <td></td>
                            <td colspan="3" class="font-weight-bold">Dự án</td>
                            <td class="text-right font-weight-bold">Tổng dự án</td>
                            <td class="text-right font-weight-bold">Tổng chi trả</td>
                            <td class="text-right font-weight-bold">Còn lại</td>
                          </tr>
                          <tr v-for="(project, pIndex) in item.project_details" :key="`${item.customer.id}-${project.project.id}`" class="project-detail-row">
                            <td></td>
                            <td colspan="3">
                              <div class="d-flex align-items-center">
                                <Link :href="route('projects.show', project.project.id)" class="ml-4">
                                  {{ project.project.name }}
                                </Link>
                              </div>
                            </td>
                            <td class="text-right">
                              <div class="d-flex justify-content-end align-items-center">
                                <span>{{ formatCurrency(project.total_project) }}</span>
                                <Link
                                  :href="route('projects.show', project.project.id)"
                                  class="btn btn-xs btn-outline-primary ml-2"
                                  title="Xem chi tiết dự án"
                                  style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"
                                >
                                  <i class="fas fa-eye"></i>
                                </Link>
                              </div>
                            </td>
                            <td class="text-right">
                              <div class="d-flex justify-content-end align-items-center">
                                <span>{{ formatCurrency(project.total_paid) }}</span>
                                <button
                                  @click="viewProjectPaidVouchers(item.customer.id, project.project.id)"
                                  class="btn btn-xs btn-outline-success ml-2"
                                  title="Xem phiếu đã thanh toán của dự án"
                                  style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"
                                >
                                  <i class="fas fa-eye"></i>
                                </button>
                              </div>
                            </td>
                            <td class="text-right" :class="{'text-danger': project.remaining > 0, 'text-success': project.remaining <= 0}">
                              <div class="d-flex justify-content-end align-items-center">
                                <span>{{ formatCurrency(project.remaining) }}</span>
                                <button
                                  @click="viewProjectUnpaidVouchers(item.customer.id, project.project.id)"
                                  class="btn btn-xs btn-outline-primary ml-2"
                                  title="Xem phiếu chưa thanh toán của dự án"
                                  style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"
                                >
                                  <i class="fas fa-eye"></i>
                                </button>
                              </div>
                            </td>
                          </tr>
                        </template>
                      </template>
                      <tr v-if="debtData.length === 0">
                        <td colspan="7" class="text-center">Không có dữ liệu</td>
                      </tr>
                    </tbody>
                    <tfoot v-if="debtData.length > 0" class="sticky-bottom bg-white">
                      <tr>
                        <th colspan="4" class="text-right">Tổng cộng:</th>
                        <th class="text-right">{{ formatCurrency(totalProject) }}</th>
                        <th class="text-right">{{ formatCurrency(totalPaid) }}</th>
                        <th class="text-right" :class="{'text-danger': totalRemaining > 0, 'text-success': totalRemaining <= 0}">
                          {{ formatCurrency(totalRemaining) }}
                        </th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
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
import { Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import * as XLSX from 'xlsx'

const props = defineProps({
  debtData: Array
})

// Quản lý trạng thái mở rộng chi tiết
const expandedDetails = ref([])

// Kiểm tra xem chi tiết của khách hàng có đang được mở rộng không
const hasExpandedDetails = (customerId) => {
  return expandedDetails.value.includes(customerId)
}

// Bật/tắt hiển thị chi tiết
const toggleDetails = (customerId) => {
  const index = expandedDetails.value.indexOf(customerId)
  if (index === -1) {
    expandedDetails.value.push(customerId)
  } else {
    expandedDetails.value.splice(index, 1)
  }
}

// Tính tổng các cột
const totalProject = computed(() => {
  return props.debtData.reduce((sum, item) => sum + parseInt(item.total_project || 0), 0)
})

const totalPaid = computed(() => {
  return props.debtData.reduce((sum, item) => sum + parseInt(item.total_paid || 0), 0)
})

const totalRemaining = computed(() => {
  return props.debtData.reduce((sum, item) => sum + parseInt(item.remaining || 0), 0)
})

// Hàm định dạng tiền tệ
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value)
}

// Hàm xem phiếu chưa thanh toán
const viewAll = (customerId) => {
  router.visit(route('receipt-vouchers.index'), {
    data: {
      customer_id: customerId,
      status: ''
    },
    replace: false,
    preserveState: false
  })
}

// Hàm xem phiếu chưa thanh toán
const viewUnpaidVouchers = (customerId) => {
  router.visit(route('receipt-vouchers.index'), {
    data: {
      customer_id: customerId,
      status: 'unpaid'
    },
    replace: false,
    preserveState: false
  })
}

// Hàm xem phiếu chưa thanh toán theo dự án
const viewProjectUnpaidVouchers = (customerId, projectId) => {
  router.visit(route('receipt-vouchers.index'), {
    data: {
      customer_id: customerId,
      project_id: projectId,
      status: 'unpaid'
    },
    replace: false,
    preserveState: false
  })
}

// Hàm xem phiếu đã thanh toán theo dự án
const viewProjectPaidVouchers = (customerId, projectId) => {
  router.visit(route('receipt-vouchers.index'), {
    data: {
      customer_id: customerId,
      project_id: projectId,
      status: 'paid'
    },
    replace: false,
    preserveState: false
  })
}

// Hàm xem phiếu đã thanh toán
const viewPaidVouchers = (customerId) => {
  router.visit(route('receipt-vouchers.index'), {
    data: {
      customer_id: customerId,
      status: 'paid'
    },
    replace: false,
    preserveState: false
  })
}

// Hàm xuất Excel
const exportToExcel = () => {
  // Chuẩn bị dữ liệu cho Excel
  let excelData = [
    ['Báo cáo công nợ khách hàng'],
    [],
    ['STT', 'Khách hàng', 'Điện thoại', 'Địa chỉ', 'Tổng dự án', 'Tổng chi trả', 'Còn lại']
  ]

  // Thêm dữ liệu chi tiết
  props.debtData.forEach((item, index) => {
    // Thêm dòng khách hàng
    excelData.push([
      index + 1,
      item.customer.name,
      item.customer.phone,
      item.customer.address,
      parseInt(item.total_project || 0),
      parseInt(item.total_paid || 0),
      parseInt(item.remaining || 0)
    ])

    // Thêm chi tiết dự án
    if (item.project_details && item.project_details.length > 0) {
      excelData.push(['', 'Dự án', '', '', 'Tổng dự án', 'Tổng chi trả', 'Còn lại'])

      item.project_details.forEach(project => {
        excelData.push([
          '',
          project.project.name,
          '',
          '',
          parseInt(project.total_project || 0),
          parseInt(project.total_paid || 0),
          parseInt(project.remaining || 0)
        ])
      })

      // Thêm dòng trống sau chi tiết
      excelData.push([])
    }
  })

  // Thêm dòng tổng cộng
  excelData.push(['', '', '', 'Tổng cộng:', totalProject.value, totalPaid.value, totalRemaining.value])

  // Tạo workbook và worksheet
  const worksheet = XLSX.utils.aoa_to_sheet(excelData)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Báo cáo công nợ khách hàng')

  // Xuất file Excel
  XLSX.writeFile(workbook, 'bao-cao-cong-no-khach-hang.xlsx')
}
</script>

<style>
.sticky-top {
  position: sticky;
  top: 0;
  z-index: 1;
}

.sticky-bottom {
  position: sticky;
  bottom: 0;
  z-index: 1;
}

.table-responsive {
  max-height: calc(100vh - 250px);
  overflow-y: auto;
}
</style>
