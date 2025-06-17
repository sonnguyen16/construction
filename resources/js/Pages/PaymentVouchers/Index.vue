<template>
  <AdminLayout>
    <template #header>Quản lý phiếu chi</template>
    <template #breadcrumb>Danh sách phiếu chi</template>

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
                    <span class="info-box-text">Tổng số phiếu chi</span>
                    <span class="info-box-number">{{ totalPaymentCount }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Tổng số tiền chi</span>
                    <span class="info-box-number">{{ formatCurrency(totalPaymentAmount) }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Đang chờ duyệt</span>
                    <span class="info-box-number">
                      {{ formatCurrency(totalPaymentAmountProposed) }}
                    </span>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Đã duyệt</span>
                    <span class="info-box-number">
                      {{ formatCurrency(totalPaymentAmountApproved) }}
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
            <h3 class="card-title">Danh sách phiếu chi</h3>
            <div class="card-tools">
              <Link :href="route('payment-vouchers.create')" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tạo phiếu chi mới
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
                    placeholder="Mã phiếu chi, tên nhà thầu..."
                    v-model="filters.search"
                    @input="debouncedSearch"
                  />
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="contractor_id">Nhà thầu:</label>
                  <select
                    class="form-control"
                    id="contractor_id"
                    v-model="filters.contractor_id"
                    @change="applyFilters"
                  >
                    <option value="">Tất cả nhà thầu</option>
                    <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                      {{ contractor.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="project_id">Dự án:</label>
                  <select class="form-control" id="project_id" v-model="filters.project_id" @change="applyFilters" disabled>
                    <option value="">Tất cả dự án</option>
                    <option value="null">Ngoài dự án</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.name }}
                    </option>
                  </select>
                  <small class="form-text text-muted">Dự án được điều chỉnh từ dropdown chọn dự án chính</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="bid_package_id">Gói thầu:</label>
                  <select
                    class="form-control"
                    id="bid_package_id"
                    v-model="filters.bid_package_id"
                    @change="applyFilters"
                  >
                    <option value="">Tất cả gói thầu</option>
                    <option value="null">Không thuộc gói thầu nào</option>
                    <option v-for="bidPackage in bidPackagesFiltered" :key="bidPackage.id" :value="bidPackage.id">
                      {{ bidPackage.code }} - {{ bidPackage.name }}
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
                  <label for="payment_category_id">Loại chi:</label>
                  <select
                    class="form-control"
                    id="payment_category_id"
                    v-model="filters.payment_category_id"
                    @change="applyFilters"
                  >
                    <option value="">Tất cả loại chi</option>
                    <option v-for="category in paymentCategories" :key="category.id" :value="category.id">
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
                    <th>Mã phiếu chi</th>
                    <th>Nhà thầu</th>
                    <th>Dự án</th>
                    <th>Gói thầu</th>
                    <th>Loại chi</th>
                    <th>Số tiền</th>
                    <th>Mô tả</th>
                    <th>Người tạo</th>
                    <th>Ngày tạo</th>
                    <th>Trạng thái</th>
                    <th style="width: 200px">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="voucher in paymentVouchers.data" :key="voucher.id">
                    <td>{{ voucher.code }}</td>
                    <td>{{ voucher.contractor.name }}</td>
                    <td>
                      <span v-if="voucher.bid_package && voucher.bid_package.project">
                        {{ voucher.bid_package.project.name }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td>
                      <span v-if="voucher.bid_package">
                        {{ voucher.bid_package.code }} - {{ voucher.bid_package.name }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td>
                      <span v-if="voucher.payment_category">
                        {{ voucher.payment_category.name }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td>{{ formatCurrency(voucher.amount) }}</td>
                    <td>{{ truncateText(voucher.description, 30) }}</td>
                    <td>{{ voucher.creator ? voucher.creator.name : '-' }}</td>
                    <td>{{ formatDate(voucher.created_at) }}</td>
                    <td>
                      <span
                        :class="{
                          'badge badge-secondary': voucher.status === 'proposed',
                          'badge badge-warning': voucher.status === 'approved',
                          'badge badge-success': voucher.status === 'paid'
                        }"
                      >
                        {{ statuses[voucher.status] }}
                      </span>
                    </td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="
                            voucher.bid_package && voucher.bid_package.project
                              ? canInProject('payment-vouchers.view', voucher.bid_package.project.id)
                              : true
                          "
                          :href="route('payment-vouchers.show', voucher.id)"
                          class="btn btn-xs btn-info"
                        >
                          <i class="fas fa-eye"></i>
                        </Link>
                        <Link
                          v-if="
                            voucher.bid_package && voucher.bid_package.project
                              ? canInProject('payment-vouchers.edit', voucher.bid_package.project.id)
                              : true
                          "
                          :href="route('payment-vouchers.edit', voucher.id)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit"></i>
                        </Link>
                        <button
                          v-if="
                            voucher.bid_package && voucher.bid_package.project
                              ? canInProject('payment-vouchers.delete', voucher.bid_package.project.id)
                              : true
                          "
                          @click="confirmDelete(voucher)"
                          class="btn btn-xs btn-danger"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                        <a
                          v-if="
                            voucher.bid_package && voucher.bid_package.project
                              ? canInProject('payment-vouchers.print', voucher.bid_package.project.id)
                              : true
                          "
                          :href="`/payment-vouchers/${voucher.id}/print`"
                          target="_blank"
                          class="btn btn-xs btn-secondary"
                        >
                          <i class="fas fa-print"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="paymentVouchers.data.length === 0">
                    <td colspan="11" class="text-center">Không có phiếu chi nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="paymentVouchers.links" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { usePermission } from '@/Composables/usePermission'
import { useCurrentProject } from '@/Composables/useCurrentProject'

// Sử dụng composable phân quyền
const { canInProject } = usePermission()

// Sử dụng composable dự án hiện tại
const { currentProject } = useCurrentProject()
import { formatCurrency, formatDate, showConfirm, showSuccess } from '@/utils'
import { ref, computed, watch } from 'vue'
import Pagination from '@/Components/Pagination.vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  paymentVouchers: Object,
  contractors: Array,
  projects: Array,
  bidPackages: Array,
  paymentCategories: Array,
  filters: Object,
  statuses: Object,
  totalPaymentCount: Number,
  totalPaymentAmount: Number,
  totalPaymentAmountProposed: Number,
  totalPaymentAmountApproved: Number
})

const filters = ref({
  search: props.filters.search || '',
  contractor_id: props.filters.contractor_id || '',
  project_id: props.filters.project_id || (currentProject.value ? currentProject.value.id : ''),
  bid_package_id: props.filters.bid_package_id || '',
  payment_category_id: props.filters.payment_category_id || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
  status: props.filters.status || ''
})

// Hàm cắt ngắn văn bản
const truncateText = (text, length) => {
  if (!text) return '-'
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Hàm xác nhận xóa phiếu chi
const confirmDelete = (voucher) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa phiếu chi "${voucher.code}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(`/payment-vouchers/${voucher.id}`, {
          onSuccess: () => {
            showSuccess('Phiếu chi đã được xóa thành công.')
          }
        })
      }
    }
  )
}

// Hàm áp dụng bộ lọc
const applyFilters = () => {
  router.get(
    '/payment-vouchers',
    {
      search: filters.value.search,
      contractor_id: filters.value.contractor_id,
      project_id: filters.value.project_id,
      bid_package_id: filters.value.bid_package_id,
      payment_category_id: filters.value.payment_category_id,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to,
      status: filters.value.status
    },
    {
      preserveState: true,
      replace: true
    }
  )
}

const bidPackagesFiltered = computed(() => {
  return props.bidPackages.filter((bidPackage) => bidPackage.project_id == filters.value.project_id)
})

// Hàm đặt lại bộ lọc
const resetFilters = () => {
  filters.value = {
    search: '',
    contractor_id: '',
    project_id: '',
    bid_package_id: '',
    payment_category_id: '',
    date_from: '',
    date_to: '',
    status: ''
  }
  applyFilters()
}

// Debounce tìm kiếm để tránh gửi quá nhiều request
const debouncedSearch = debounce(() => {
  applyFilters()
}, 500)

// Theo dõi thay đổi của bộ lọc
watch(
  () => filters.value,
  (newFilters) => {
    // Không cần làm gì ở đây vì đã có các hàm xử lý riêng
  },
  { deep: true }
)

// Theo dõi thay đổi của dự án hiện tại
watch(
  () => currentProject.value,
  (newProject) => {
    if (newProject) {
      filters.value.project_id = newProject.id
      // Áp dụng bộ lọc ngay lập tức khi dự án thay đổi
      applyFilters()
    }
  },
  { immediate: true }
)
</script>
