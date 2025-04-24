<template>
  <AdminLayout title="Báo cáo công nợ nhà cung cấp/nhà thầu">
    <template #header>Báo cáo công nợ nhà cung cấp/nhà thầu</template>
    <template #breadcrumb>Báo cáo công nợ nhà cung cấp/nhà thầu</template>

    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Báo cáo công nợ nhà cung cấp/nhà thầu</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-success" @click="exportToExcel">
                    <i class="fas fa-file-excel mr-1"></i> Xuất Excel
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th class="text-center" style="width: 50px">STT</th>
                        <th>Nhà cung cấp/nhà thầu</th>
                        <th>Điện thoại</th>
                        <th>Địa chỉ</th>
                        <th class="text-right">Tổng mua hàng</th>
                        <th class="text-right">Tổng chi trả</th>
                        <th class="text-right">Còn lại</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in debtData" :key="index">
                        <td class="text-center">{{ index + 1 }}</td>
                        <td>{{ item.contractor.name }}</td>
                        <td>{{ item.contractor.phone }}</td>
                        <td>{{ item.contractor.address }}</td>
                        <td class="text-right">{{ formatCurrency(item.total_purchase) }}</td>
                        <td class="text-right">{{ formatCurrency(item.total_paid) }}</td>
                        <td class="text-right" :class="{'text-danger': item.remaining > 0, 'text-success': item.remaining <= 0}">
                          {{ formatCurrency(item.remaining) }}
                        </td>
                      </tr>
                      <tr v-if="debtData.length === 0">
                        <td colspan="7" class="text-center">Không có dữ liệu</td>
                      </tr>
                    </tbody>
                    <tfoot v-if="debtData.length > 0">
                      <tr>
                        <th colspan="4" class="text-right">Tổng cộng:</th>
                        <th class="text-right">{{ formatCurrency(totalPurchase) }}</th>
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
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import * as XLSX from 'xlsx'

const props = defineProps({
  debtData: Array
})

// Tính tổng các cột
const totalPurchase = computed(() => {
  return props.debtData.reduce((sum, item) => sum + parseInt(item.total_purchase || 0), 0)
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

// Hàm xuất Excel
const exportToExcel = () => {
  // Chuẩn bị dữ liệu cho Excel
  const excelData = [
    ['Báo cáo công nợ nhà cung cấp/ nhà thầu'],
    [],
    ['STT', 'Nhà cung cấp/nhà thầu', 'Điện thoại', 'Địa chỉ', 'Tổng mua hàng', 'Tổng chi trả', 'Còn lại'],
    ...props.debtData.map((item, index) => [
      index + 1,
      item.contractor.name,
      item.contractor.phone,
      item.contractor.address,
      parseInt(item.total_purchase || 0),
      parseInt(item.total_paid || 0),
      parseInt(item.remaining || 0)
    ]),
    ['', '', '', 'Tổng cộng:', totalPurchase.value, totalPaid.value, totalRemaining.value]
  ]

  // Tạo workbook và worksheet
  const worksheet = XLSX.utils.aoa_to_sheet(excelData)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Báo cáo công nợ nhà thầu')

  // Xuất file Excel
  XLSX.writeFile(workbook, 'bao-cao-cong-no-nha-thau.xlsx')
}
</script>
