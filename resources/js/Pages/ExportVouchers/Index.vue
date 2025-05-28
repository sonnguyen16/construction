<template>
  <AdminLayout>
    <template #header>Quản lý phiếu xuất kho</template>
    <template #breadcrumb>Danh sách phiếu xuất kho</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách phiếu xuất kho</h3>
            <div class="card-tools">
              <Link v-if="can('export_vouchers.create')" :href="route('export-vouchers.create')" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm phiếu xuất kho mới
              </Link>
            </div>
          </div>
          <div class="card-body">
            <!-- Bộ lọc -->
            <div class="row mb-3">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="search">Tìm kiếm:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="search"
                    placeholder="Mã phiếu, dự án, khách hàng"
                    v-model="search"
                    @input="debouncedSearch"
                  />
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="project_id">Dự án:</label>
                  <select class="form-control" id="project_id" v-model="selectedProject" @change="debouncedSearch">
                    <option value="">Tất cả dự án</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="date_from">Từ ngày:</label>
                  <input type="date" class="form-control" id="date_from" v-model="dateFrom" @change="debouncedSearch" />
                </div>
              </div>
              <div class="col-md-2">
                <div class="form-group">
                  <label for="date_to">Đến ngày:</label>
                  <input type="date" class="form-control" id="date_to" v-model="dateTo" @change="debouncedSearch" />
                </div>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Mã phiếu</th>
                    <th>Ngày xuất</th>
                    <th>Dự án</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="voucher in exportVouchers.data" :key="voucher.id">
                    <td>{{ voucher.code }}</td>
                    <td>{{ formatDate(voucher.export_date) }}</td>
                    <td>{{ voucher.project ? voucher.project.name : '-' }}</td>
                    <td>{{ voucher.customer ? voucher.customer.name : '-' }}</td>
                    <td>{{ formatCurrency(voucher.total_amount) }}</td>
                    <td>{{ formatDateTime(voucher.created_at) }}</td>
                    <td>
                      <div class="btn-group">
                        <Link v-if="can('export_vouchers.view')" :href="route('export-vouchers.show', voucher.id)" class="btn btn-xs btn-info">
                          <i class="fas fa-eye"></i> Xem
                        </Link>
                        <Link v-if="can('export_vouchers.edit')" :href="route('export-vouchers.edit', voucher.id)" class="btn btn-xs btn-primary">
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button v-if="can('export_vouchers.delete')" @click="confirmDelete(voucher)" class="btn btn-xs btn-danger">
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="exportVouchers.data.length === 0">
                    <td colspan="7" class="text-center">Không có phiếu xuất kho nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="exportVouchers.links" />
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import Pagination from '@/Components/Pagination.vue'
import { formatCurrency } from '@/utils'
import debounce from 'lodash/debounce'
import { usePermission } from '@/Composables/usePermission'

const props = defineProps({
  exportVouchers: Object,
  projects: Array,
  filters: Object
})

const { can } = usePermission()

const search = ref(props.filters?.search || '')
const selectedProject = ref(props.filters?.project_id || '')
const dateFrom = ref(props.filters?.date_from || '')
const dateTo = ref(props.filters?.date_to || '')

// Format ngày tháng
const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }).format(date)
}

// Định dạng ngày giờ
const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

// Xác nhận xóa phiếu xuất kho
const confirmDelete = (voucher) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa phiếu xuất kho "${voucher.code}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('export-vouchers.destroy', voucher.id), {
          onSuccess: () => {
            showSuccess('Phiếu xuất kho đã được xóa thành công.')
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa phiếu xuất kho này.')
          }
        })
      }
    }
  )
}

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  router.get(
    route('export-vouchers.index'),
    {
      search: search.value,
      project_id: selectedProject.value,
      date_from: dateFrom.value,
      date_to: dateTo.value
    },
    {
      preserveState: true,
      replace: true
    }
  )
}, 300)
</script>
