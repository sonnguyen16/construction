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
                      <tr v-for="(item, index) in debtData" :key="index">
                        <td class="text-center">{{ index + 1 }}</td>
                        <td>{{ item.customer.name }}</td>
                        <td>{{ item.customer.phone }}</td>
                        <td>{{ item.customer.address }}</td>
                        <td class="text-right">
                          <div class="d-flex justify-content-end align-items-center">
                            <span>{{ formatCurrency(item.total_project) }}</span>
                            <button
                              @click="viewAll(item.customer.id)"
                              class="btn btn-xs btn-outline-primary ml-2"
                              title="Xem tất cả phiếu"
                              style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"
                            >
                              <i class="fas fa-eye"></i>
                            </button>
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
import { computed } from 'vue'
import * as XLSX from 'xlsx'

const props = defineProps({
  debtData: Array
})

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
  const excelData = [
    ['Báo cáo công nợ khách hàng'],
    [],
    ['STT', 'Khách hàng', 'Điện thoại', 'Địa chỉ', 'Tổng dự án', 'Tổng chi trả', 'Còn lại'],
    ...props.debtData.map((item, index) => [
      index + 1,
      item.customer.name,
      item.customer.phone,
      item.customer.address,
      parseInt(item.total_project || 0),
      parseInt(item.total_paid || 0),
      parseInt(item.remaining || 0)
    ]),
    ['', '', '', 'Tổng cộng:', totalProject.value, totalPaid.value, totalRemaining.value]
  ]

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
