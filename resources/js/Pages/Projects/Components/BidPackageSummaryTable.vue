<template>
  <div class="bid-package-summary-table">
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="thead-light">
          <tr>
            <th style="width: 50px">STT</th>
            <th style="width: 100px">Mã</th>
            <th>Tên gói thầu</th>
            <th style="width: 150px">Giá dự thầu</th>
            <th style="width: 120px">Phát sinh</th>
            <th>Nhà thầu</th>
            <th style="width: 120px">Trạng thái</th>
            <th>Ghi chú</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(bidPackage, index) in bidPackages" :key="bidPackage.id">
            <!-- Dòng gói thầu chính -->
            <tr :class="{ 'table-primary': bidPackage.children && bidPackage.children.length > 0 }">
              <td>{{ index + 1 }}</td>
              <td>{{ bidPackage.code }}</td>
              <td>{{ bidPackage.name }}</td>
              <td class="text-right">{{ formatCurrency(bidPackage.estimated_price) }}</td>
              <td class="text-right">{{ formatCurrency(bidPackage.additional_price) }}</td>
              <td>
                {{ getWinningBidContractor(bidPackage) }}
              </td>
              <td>
                <span :class="getStatusBadgeClass(bidPackage.status)">
                  {{ getStatusText(bidPackage.status) }}
                </span>
              </td>
              <td>{{ bidPackage.description }}</td>
            </tr>

            <!-- Dòng các hạng mục con -->
            <template v-if="bidPackage.children && bidPackage.children.length > 0">
              <tr v-for="(workItem, wIndex) in bidPackage.children" :key="workItem.id" class="work-item-row">
                <td></td>
                <td>+ {{ workItem.code || `HM${wIndex + 1}` }}</td>
                <td>{{ workItem.name }}</td>
                <td class="text-right">{{ formatCurrency(workItem.estimated_price) }}</td>
                <td class="text-right">{{ formatCurrency(workItem.additional_price) }}</td>
                <td>{{ getSelectedContractorName(workItem) }}</td>
                <td>
                  <span :class="getWorkItemStatusBadgeClass(workItem.status)">
                    {{ getWorkItemStatusText(workItem.status) }}
                  </span>
                </td>
                <td>{{ workItem.description }}</td>
              </tr>
            </template>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/utils'

const props = defineProps({
  bidPackages: {
    type: Array,
    required: true
  }
})

// Lấy nhà thầu trúng thầu (nhà thầu có giá thầu thấp nhất hoặc được chọn)
const getWinningBidContractor = (bidPackage) => {
  // Nếu có nhà thầu được chọn trực tiếp
  if (bidPackage.selected_contractor_id && bidPackage.selectedContractor) {
    return bidPackage.selectedContractor.name
  }

  // Nếu không có nhà thầu được chọn trực tiếp, kiểm tra trong danh sách bids
  if (bidPackage.bids && bidPackage.bids.length > 0) {
    // Nếu có nhà thầu được chọn
    const selectedBid = bidPackage.bids.find((bid) => bid.is_selected)
    if (selectedBid && selectedBid.contractor) {
      return selectedBid.contractor.name
    }

    // Nếu không, lấy nhà thầu có giá thấp nhất
    const lowestBid = [...bidPackage.bids].sort((a, b) => a.price - b.price)[0]
    if (lowestBid && lowestBid.contractor) {
      return lowestBid.contractor.name
    }
  }

  return ''
}

// Lấy tên nhà thầu được chọn cho hạng mục
const getSelectedContractorName = (workItem) => {
  // Nếu có nhà thầu được chọn trực tiếp
  if (workItem.selected_contractor_id && workItem.selectedContractor) {
    return workItem.selectedContractor.name
  }

  // Nếu không có nhà thầu được chọn trực tiếp, kiểm tra trong danh sách bids
  if (workItem.bids && workItem.bids.length > 0) {
    // Nếu có nhà thầu được chọn
    const selectedBid = workItem.bids.find((bid) => bid.is_selected)
    if (selectedBid && selectedBid.contractor) {
      return selectedBid.contractor.name
    }
  }

  return ''
}

// Lấy class cho badge trạng thái gói thầu
const getStatusBadgeClass = (status) => {
  const classes = {
    open: 'badge badge-info',
    awarded: 'badge badge-primary',
    completed: 'badge badge-success',
    cancelled: 'badge badge-danger'
  }
  return classes[status] || 'badge badge-secondary'
}

// Lấy text hiển thị cho trạng thái gói thầu
const getStatusText = (status) => {
  const texts = {
    open: 'Đang mở thầu',
    awarded: 'Đã chọn nhà thầu',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  }
  return texts[status] || status
}

// Lấy class cho badge trạng thái hạng mục
const getWorkItemStatusBadgeClass = (status) => {
  const classes = {
    open: 'badge badge-info',
    awarded: 'badge badge-primary',
    completed: 'badge badge-success',
    cancelled: 'badge badge-danger'
  }
  return classes[status] || 'badge badge-secondary'
}

// Lấy text hiển thị cho trạng thái hạng mục
const getWorkItemStatusText = (status) => {
  const texts = {
    open: 'Đang mở thầu',
    awarded: 'Đã chọn nhà thầu',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  }
  return texts[status] || status
}
</script>

<style scoped>
.bid-package-summary-table {
  margin-bottom: 2rem;
}

.work-item-row {
  background-color: #f8f9fa;
}

.work-item-row td {
  padding-left: 1.5rem;
}

.table td.text-right {
  text-align: right;
}
</style>
