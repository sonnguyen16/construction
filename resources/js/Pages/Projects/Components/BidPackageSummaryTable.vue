<template>
  <div class="bid-package-summary-table">
    <div class="table-responsive sticky-table-container">
      <table class="table table-bordered table-hover">
        <thead class="thead-light sticky-header">
          <tr>
            <th style="width: 50px">STT</th>
            <th style="width: 100px">Mã</th>
            <th>Tên gói thầu</th>
            <th style="width: 150px">Giá dự thầu</th>
            <th style="width: 120px">Phát sinh</th>
            <th style="width: 150px">Giá giao thầu</th>
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
              <td class="text-right">{{ formatCurrency(bidPackage.display_estimated_price) }}</td>
              <td class="text-right">{{ formatCurrency(bidPackage.display_additional_price) }}</td>
              <td class="text-right">
                {{ bidPackage.status === 'awarded' || bidPackage.status === 'completed' ? formatCurrency(bidPackage.display_client_price) : '' }}
              </td>
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
                <td class="text-right">{{ formatCurrency(workItem.display_estimated_price) }}</td>
                <td class="text-right">{{ formatCurrency(workItem.display_additional_price) }}</td>
                <td class="text-right">
                  {{ workItem.status === 'awarded' || workItem.status === 'completed' ? formatCurrency(workItem.display_client_price) : '' }}
                </td>
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
        <tfoot class="sticky-footer">
          <tr class="table-secondary font-weight-bold">
            <td colspan="3" class="text-right">Tổng cộng:</td>
            <td class="text-right">{{ formatCurrency(totalEstimatedPrice) }}</td>
            <td class="text-right">{{ formatCurrency(totalAdditionalPrice) }}</td>
            <td class="text-right">{{ formatCurrency(totalClientPrice) }}</td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/utils'

// Tính tổng giá dự thầu
const totalEstimatedPrice = computed(() => {
  let total = 0
  props.bidPackages.forEach(bidPackage => {
    total += parseInt(bidPackage.display_estimated_price) || 0
  })
  return total
})

// Tính tổng giá phát sinh
const totalAdditionalPrice = computed(() => {
  let total = 0
  props.bidPackages.forEach(bidPackage => {
    total += parseInt(bidPackage.display_additional_price) || 0
  })
  return total
})

// Tính tổng giá giao thầu
const totalClientPrice = computed(() => {
  let total = 0
  props.bidPackages.forEach(bidPackage => {
    if (bidPackage.status === 'awarded' || bidPackage.status === 'completed') {
      total += parseInt(bidPackage.display_client_price) || 0
    }
  })
  return total
})

const props = defineProps({
  bidPackages: {
    type: Array,
    required: true
  },
  isCompact: {
    type: Boolean,
    default: false
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
.work-item-row {
  background-color: #f8f9fa;
}

/* CSS cho sticky header và footer */
.sticky-table-container {
  max-height: calc(100vh - 250px);
  overflow-y: auto;
  position: relative;
}

.sticky-header {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: #f8f9fa;
}

.sticky-header th {
  position: sticky;
  top: 0;
  background-color: #f8f9fa;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.sticky-footer {
  position: sticky;
  bottom: 0;
  z-index: 10;
}

.sticky-footer td {
  position: sticky;
  bottom: 0;
  background-color: #fff;
  box-shadow: 0 -1px 2px rgba(0, 0, 0, 0.1);
}

.sticky-footer tr:last-child td {
  position: sticky;
  bottom: 0;
  background-color: #e2f0fb;
}

.sticky-footer tr:first-child td {
  position: sticky;
  background-color: #e9ecef;
}

.table td.text-right {
  text-align: right;
}
</style>
