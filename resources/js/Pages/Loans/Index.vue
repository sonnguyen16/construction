<template>
  <AdminLayout>
    <template #header>Quản lý khoản vay</template>
    <template #breadcrumb>Danh sách khoản vay</template>

    <!-- Tổng hợp thống kê -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Tổng hợp thống kê</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Tổng số khoản vay</span>
                    <span class="info-box-number">{{ totalLoanCount }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Tổng số tiền vay</span>
                    <span class="info-box-number">{{ formatCurrency(totalLoanAmount) }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="info-box bg-light">
                  <div class="info-box-content">
                    <span class="info-box-text">Số khoản vay đang hoạt động</span>
                    <span class="info-box-number">{{ activeLoanCount }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Danh sách khoản vay -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách khoản vay</h3>
            <div class="card-tools">
              <Link :href="route('loans.create')" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm khoản vay mới
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
                    v-model="filters.search"
                    placeholder="Tìm theo tên khoản vay"
                    @input="debouncedSearch"
                  />
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="contractor_id">Nhà cung cấp:</label>
                  <select
                    class="form-control"
                    id="contractor_id"
                    v-model="filters.contractor_id"
                    @change="applyFilters"
                  >
                    <option value="">-- Tất cả nhà cung cấp --</option>
                    <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                      {{ contractor.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="project_id">Dự án:</label>
                  <select
                    class="form-control"
                    id="project_id"
                    v-model="filters.project_id"
                    @change="applyFilters"
                  >
                    <option value="">-- Tất cả dự án --</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="status">Trạng thái:</label>
                  <select
                    class="form-control"
                    id="status"
                    v-model="filters.status"
                    @change="applyFilters"
                  >
                    <option value="">-- Tất cả trạng thái --</option>
                    <option v-for="status in statuses" :key="status.value" :value="status.value">
                      {{ status.label }}
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
                    <th>Tên khoản vay</th>
                    <th>Nhà cung cấp</th>
                    <th>Dự án</th>
                    <th>Số tiền</th>
                    <th>Lãi suất</th>
                    <th>Ngày bắt đầu</th>
                    <th>Ngày kết thúc</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loans.data.length === 0">
                    <td colspan="9" class="text-center">Không có dữ liệu</td>
                  </tr>
                  <tr v-for="loan in loans.data" :key="loan.id">
                    <td>{{ loan.name }}</td>
                    <td>{{ loan.contractor?.name || 'N/A' }}</td>
                    <td>{{ loan.project?.name || 'Không có' }}</td>
                    <td>{{ formatCurrency(loan.amount) }}</td>
                    <td>{{ loan.interest_rate }}%</td>
                    <td>{{ formatDate(loan.start_date) }}</td>
                    <td>{{ formatDate(loan.end_date) }}</td>
                    <td>
                      <span
                        class="badge"
                        :class="loan.status === 'active' ? 'badge-primary' : 'badge-success'"
                      >
                        {{ loan.status === 'active' ? 'Đang vay' : 'Đã hoàn thành' }}
                      </span>
                    </td>
                    <td>
                      <div class="btn-group">
                        <Link :href="route('loans.show', loan.id)" class="btn btn-info btn-sm">
                          <i class="fas fa-eye"></i>
                        </Link>
                        <Link :href="route('loans.edit', loan.id)" class="btn btn-warning btn-sm">
                          <i class="fas fa-edit"></i>
                        </Link>
                        <button @click="confirmDelete(loan)" class="btn btn-danger btn-sm">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <div class="d-flex justify-content-center">
              <Pagination :links="loans.links" />
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
import { ref, computed, onMounted } from 'vue'
import { formatCurrency, formatDate, showConfirm, showSuccess } from '@/utils'
import Pagination from '@/Components/Pagination.vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  loans: Object,
  contractors: Array,
  projects: Array,
  statuses: Array,
  filters: Object
})

// Tính toán tổng số khoản vay
const totalLoanCount = computed(() => props.loans.total || 0)

// Tính toán tổng số tiền vay
const totalLoanAmount = computed(() => {
  return props.loans.data.reduce((total, loan) => total + parseFloat(loan.amount || 0), 0)
})

// Tính toán số khoản vay đang hoạt động
const activeLoanCount = computed(() => {
  return props.loans.data.filter(loan => loan.status === 'active').length
})

// Bộ lọc
const filters = ref({
  search: props.filters.search || '',
  contractor_id: props.filters.contractor_id || '',
  project_id: props.filters.project_id || '',
  status: props.filters.status || ''
})

// Hàm cắt ngắn văn bản
const truncateText = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Hàm xác nhận xóa khoản vay
const confirmDelete = (loan) => {
  showConfirm(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa khoản vay "${loan.name}" không?`,
    'Xóa',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('loans.destroy', loan.id), {
        onSuccess: () => {
          showSuccess('Xóa khoản vay thành công!')
        }
      })
    }
  })
}

// Hàm áp dụng bộ lọc
const applyFilters = () => {
  router.get(
    route('loans.index'),
    {
      search: filters.value.search,
      contractor_id: filters.value.contractor_id,
      project_id: filters.value.project_id,
      status: filters.value.status
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
    contractor_id: '',
    project_id: '',
    status: ''
  }
  applyFilters()
}

// Debounce tìm kiếm để tránh gửi quá nhiều request
const debouncedSearch = debounce(() => {
  applyFilters()
}, 500)

// Khởi tạo bộ lọc từ URL khi trang được tải
onMounted(() => {
  filters.value = {
    search: props.filters.search || '',
    contractor_id: props.filters.contractor_id || '',
    project_id: props.filters.project_id || '',
    status: props.filters.status || ''
  }
})
</script>
