<template>
  <AdminLayout>
    <template #header>Quản lý phiếu nhập kho</template>
    <template #breadcrumb>Danh sách phiếu nhập kho</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách phiếu nhập kho</h3>
            <div class="card-tools">
              <Link :href="route('import-vouchers.create')" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm phiếu nhập kho mới
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
                    placeholder="Mã phiếu, dự án, nhà thầu"
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
                    <th>Ngày nhập</th>
                    <th>Dự án</th>
                    <th>Nhà thầu</th>
                    <th>Tổng tiền</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="voucher in importVouchers.data" :key="voucher.id">
                    <td>{{ voucher.code }}</td>
                    <td>{{ formatDate(voucher.import_date) }}</td>
                    <td>{{ voucher.project ? voucher.project.name : '-' }}</td>
                    <td>{{ voucher.contractor ? voucher.contractor.name : '-' }}</td>
                    <td>{{ formatCurrency(voucher.total_amount) }}</td>
                    <td>{{ formatDateTime(voucher.created_at) }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="canInProject('import-vouchers.view', voucher.project_id)"
                          :href="route('import-vouchers.show', voucher.id)"
                          class="btn btn-xs btn-info"
                        >
                          <i class="fas fa-eye"></i> Xem
                        </Link>
                        <Link
                          v-if="canInProject('import-vouchers.edit', voucher.project_id)"
                          :href="route('import-vouchers.edit', voucher.id)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button
                          v-if="canInProject('import-vouchers.delete', voucher.project_id)"
                          @click="confirmDelete(voucher)"
                          class="btn btn-xs btn-danger"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="importVouchers.data.length === 0">
                    <td colspan="7" class="text-center">Không có phiếu nhập kho nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="importVouchers.links" />
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
  importVouchers: Object,
  projects: Array,
  filters: Object
})

const { canInProject } = usePermission()

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

// Xác nhận xóa phiếu nhập kho
const confirmDelete = (voucher) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa phiếu nhập kho "${voucher.code}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('import-vouchers.destroy', voucher.id), {
          onSuccess: () => {
            showSuccess('Phiếu nhập kho đã được xóa thành công.')
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa phiếu nhập kho này.')
          }
        })
      }
    }
  )
}

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  router.get(
    route('import-vouchers.index'),
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
