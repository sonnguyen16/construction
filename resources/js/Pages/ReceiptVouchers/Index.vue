<template>
  <AdminLayout>
    <template #header>Quản lý phiếu thu</template>
    <template #breadcrumb>Danh sách phiếu thu</template>

    <!-- Tổng hợp thống kê -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tổng hợp thống kê</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Tổng số phiếu thu</span>
                    <span class="info-box-number">{{ totalReceiptCount }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Tổng số tiền đã thu</span>
                    <span class="info-box-number">{{ formatCurrency(totalReceiptAmount) }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Số tiền dự thu</span>
                    <span class="info-box-number">
                      {{ formatCurrency(totalReceiptAmountUnpaid) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách phiếu thu</h3>
            <div class="card-tools">
              <Link :href="route('receipt-vouchers.create')" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tạo phiếu thu mới
              </Link>
            </div>
          </div>
          <div class="card-body">
            <!-- Bộ lọc -->
            <div class="row mb-3">
              <div class="col-md-3">
                <div class="form-group">
                  <label for="search">Tìm kiếm:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="search"
                    placeholder="Mã phiếu thu, tên khách hàng..."
                    v-model="filters.search"
                    @input="debouncedSearch"
                  />
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="customer_id">Khách hàng:</label>
                  <select class="form-control" id="customer_id" v-model="filters.customer_id" @change="applyFilters">
                    <option value="">Tất cả khách hàng</option>
                    <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                      {{ customer.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="project_id">Dự án:</label>
                  <select class="form-control" id="project_id" v-model="filters.project_id" @change="applyFilters">
                    <option value="">Tất cả dự án</option>
                    <option value="null">Ngoài dự án</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="date_range">Khoảng thời gian:</label>
                  <div class="input-group">
                    <input
                      type="date"
                      class="form-control"
                      id="date_from"
                      v-model="filters.date_from"
                      @change="applyFilters"
                    />
                    <div class="input-group-append input-group-prepend">
                      <span class="input-group-text">đến</span>
                    </div>
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

              <div class="col-md-3">
                <div class="form-group">
                  <label for="receipt_category_id">Loại thu:</label>
                  <select
                    class="form-control"
                    id="receipt_category_id"
                    v-model="filters.receipt_category_id"
                    @change="applyFilters"
                  >
                    <option value="">Tất cả loại thu</option>
                    <option v-for="category in receiptCategories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="status">Trạng thái:</label>
                  <select class="form-control" id="status" v-model="filters.status" @change="applyFilters">
                    <option value="">Tất cả trạng thái</option>
                    <option v-for="(label, value) in statuses" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <button @click="resetFilters" class="btn btn-default mb-3">
                  <i class="fas fa-redo mr-1"></i> Đặt lại bộ lọc
                </button>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Mã phiếu thu</th>
                    <th>Khách hàng</th>
                    <th>Dự án</th>
                    <th>Loại thu</th>
                    <th>Số tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th style="width: 150px">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="voucher in receiptVouchers.data" :key="voucher.id">
                    <td>{{ voucher.code }}</td>
                    <td>{{ voucher.customer.name }}</td>
                    <td>
                      <span v-if="voucher.project">
                        {{ voucher.project.name }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td>
                      <span v-if="voucher.receipt_category">
                        {{ voucher.receipt_category.name }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td>{{ formatCurrency(voucher.amount) }}</td>
                    <td>
                      <span
                        :class="{
                          'badge badge-warning': voucher.status === 'unpaid',
                          'badge badge-success': voucher.status === 'paid'
                        }"
                      >
                        {{ voucher.status === 'unpaid' ? 'Dự thu' : 'Đã thu' }}
                      </span>
                    </td>
                    <td>{{ formatDate(voucher.created_at) }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="voucher.project_id ? canInProject('receipt-vouchers.view', voucher.project_id) : true"
                          :href="route('receipt-vouchers.show', voucher.id)"
                          class="btn btn-xs btn-info"
                        >
                          <i class="fas fa-eye"></i>
                        </Link>
                        <Link
                          v-if="voucher.project_id ? canInProject('receipt-vouchers.edit', voucher.project_id) : true"
                          :href="route('receipt-vouchers.edit', voucher.id)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit"></i>
                        </Link>
                        <button
                          v-if="voucher.project_id ? canInProject('receipt-vouchers.delete', voucher.project_id) : true"
                          @click="confirmDelete(voucher)"
                          class="btn btn-xs btn-danger"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                        <Link
                          v-if="voucher.project_id ? canInProject('receipt-vouchers.print', voucher.project_id) : true"
                          :href="route('receipt-vouchers.print', voucher.id)"
                          target="_blank"
                          class="btn btn-xs btn-secondary"
                        >
                          <i class="fas fa-print"></i>
                        </Link>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="receiptVouchers.data.length === 0">
                    <td colspan="8" class="text-center">Không có phiếu thu nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="receiptVouchers.links" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { formatCurrency, formatDate, showConfirm, showSuccess } from '@/utils'
import Pagination from '@/Components/Pagination.vue'
import debounce from 'lodash/debounce'
import { usePermission } from '@/Composables/usePermission'

// Sử dụng composable phân quyền
const { canInProject } = usePermission()

const props = defineProps({
  receiptVouchers: Object,
  customers: Array,
  projects: Array,
  bidPackages: Array,
  receiptCategories: Array,
  statuses: Object,
  filters: Object,
  totalReceiptCount: Number,
  totalReceiptAmount: Number,
  totalReceiptAmountUnpaid: Number
})

const filters = ref({
  search: props.filters.search || '',
  customer_id: props.filters.customer_id || '',
  project_id: props.filters.project_id || '',
  bid_package_id: props.filters.bid_package_id || '',
  receipt_category_id: props.filters.receipt_category_id || '',
  status: props.filters.status || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  applyFilters()
}, 300)

// Hàm áp dụng bộ lọc
const applyFilters = () => {
  router.get(
    route('receipt-vouchers.index'),
    {
      search: filters.value.search,
      customer_id: filters.value.customer_id,
      project_id: filters.value.project_id,
      bid_package_id: filters.value.bid_package_id,
      receipt_category_id: filters.value.receipt_category_id,
      status: filters.value.status,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to
    },
    {
      preserveState: true,
      replace: true
    }
  )
}

// Hàm đặt lại bộ lọc
const resetFilters = () => {
  filters.value = {
    search: '',
    customer_id: '',
    project_id: '',
    bid_package_id: '',
    receipt_category_id: '',
    status: '',
    date_from: '',
    date_to: ''
  }
  applyFilters()
}

// Hàm xác nhận xóa phiếu thu
const confirmDelete = (voucher) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa phiếu thu "${voucher.code}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('receipt-vouchers.destroy', voucher.id), {
          onSuccess: () => {
            showSuccess('Phiếu thu đã được xóa thành công.')
          }
        })
      }
    }
  )
}
</script>
