<template>
  <AdminLayout>
    <template #header>
      <h1 style="font-size: 1.4rem">Quản lý dự án</h1>
    </template>
    <template #breadcrumb>Danh sách dự án</template>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <Link
                v-if="hasGlobalPermission('projects.create')"
                :href="route('projects.create')"
                class="btn btn-primary"
              >
                <i class="fas fa-plus mr-1"></i> Thêm dự án
              </Link>
              <div class="d-flex">
                <div class="mr-2">
                  <select class="form-control" v-model="status" @change="filterByStatus">
                    <option v-for="(label, value) in statuses" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                </div>
                <div class="input-group" style="width: 200px">
                  <input
                    type="text"
                    name="table_search"
                    class="form-control float-right"
                    placeholder="Tìm kiếm"
                    v-model="search"
                  />
                  <div class="input-group-append">
                    <button type="submit" class="btn btn-info">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div style="height: calc(100vh - 300px)" class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
              <thead>
                <tr>
                  <th width="5%">STT</th>
                  <th width="10%" @click="sortBy('code')" class="sortable">
                    Mã dự án
                    <i v-if="sort.field === 'code'" :class="getSortIcon('code')"></i>
                    <i v-else class="fas fa-sort text-muted"></i>
                  </th>
                  <th @click="sortBy('name')" class="sortable">
                    Tên dự án
                    <i v-if="sort.field === 'name'" :class="getSortIcon('name')"></i>
                    <i v-else class="fas fa-sort text-muted"></i>
                  </th>
                  <th @click="sortBy('customer_id')" class="sortable">
                    Khách hàng
                    <i v-if="sort.field === 'customer_id'" :class="getSortIcon('customer_id')"></i>
                    <i v-else class="fas fa-sort text-muted"></i>
                  </th>
                  <th @click="sortBy('total_estimated_price')" class="sortable">
                    Tổng dự toán
                    <i v-if="sort.field === 'total_estimated_price'" :class="getSortIcon('total_estimated_price')"></i>
                    <i v-else class="fas fa-sort text-muted"></i>
                  </th>
                  <th @click="sortBy('total_additional_price')" class="sortable">
                    Tổng phát sinh
                    <i
                      v-if="sort.field === 'total_additional_price'"
                      :class="getSortIcon('total_additional_price')"
                    ></i>
                    <i v-else class="fas fa-sort text-muted"></i>
                  </th>
                  <th @click="sortBy('total_client_price')" class="sortable">
                    Tổng giao thầu
                    <i v-if="sort.field === 'total_client_price'" :class="getSortIcon('total_client_price')"></i>
                    <i v-else class="fas fa-sort text-muted"></i>
                  </th>
                  <th @click="sortBy('status')" class="sortable">
                    Trạng thái
                    <i v-if="sort.field === 'status'" :class="getSortIcon('status')"></i>
                    <i v-else class="fas fa-sort text-muted"></i>
                  </th>
                  <th @click="sortBy('description')" class="sortable">
                    Ghi chú
                    <i v-if="sort.field === 'description'" :class="getSortIcon('description')"></i>
                    <i v-else class="fas fa-sort text-muted"></i>
                  </th>
                  <th width="30%">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(project, index) in projects.data" :key="project.id">
                  <td>{{ getSerialNumber(index) }}</td>
                  <td>{{ project.code }}</td>
                  <td>{{ project.name }}</td>
                  <td>{{ project.customer ? project.customer.name : 'N/A' }}</td>
                  <td class="text-right">{{ formatCurrency(project.total_estimated_price) }}</td>
                  <td class="text-right">{{ formatCurrency(project.total_additional_price) }}</td>
                  <td class="text-right">{{ formatCurrency(project.total_client_price) }}</td>
                  <td>
                    <span :class="getStatusClass(project.status)">
                      {{ getStatusLabel(project.status) }}
                    </span>
                  </td>
                  <td>
                    {{ project.description }}
                  </td>
                  <td>
                    <div class="dropdown">
                      <button
                        class="btn btn-sm btn-secondary dropdown-toggle"
                        type="button"
                        id="dropdownMenuButton"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                      >
                        Thao tác
                      </button>
                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <Link
                          v-if="canInProject('projects.edit', project.id)"
                          :href="route('projects.edit', project.id)"
                          class="dropdown-item"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <Link
                          v-if="canInProject('projects.commission', project.id)"
                          :href="route('projects.show', project.id)"
                          class="dropdown-item"
                        >
                          <i class="fas fa-eye"></i> Chi tiết
                        </Link>
                        <Link
                          v-if="canInProject('projects.expenses', project.id)"
                          :href="route('projects.expenses', project.id)"
                          class="dropdown-item"
                        >
                          <i class="fas fa-money-bill"></i> Chi phí
                        </Link>
                        <Link
                          v-if="canInProject('projects.profit', project.id)"
                          :href="route('projects.profit', project.id)"
                          class="dropdown-item"
                        >
                          <i class="fas fa-chart-line"></i> Lợi nhuận
                        </Link>
                        <Link
                          v-if="canInProject('projects.files', project.id)"
                          :href="route('projects.files', project.id)"
                          class="dropdown-item"
                        >
                          <i class="fas fa-file"></i> Files
                        </Link>
                        <Link
                          v-if="canInProject('permissions.view', project.id)"
                          :href="route('projects.roles.index', project.id)"
                          class="dropdown-item"
                        >
                          <i class="fas fa-user-shield"></i> Phân quyền
                        </Link>
                        <button
                          v-if="canInProject('projects.delete', project.id)"
                          @click="confirmDelete(project)"
                          class="dropdown-item"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr v-if="projects.data.length === 0">
                  <td colspan="10" class="text-center">Không có dữ liệu</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer clearfix">
            <pagination :links="projects.links" />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div
      class="modal fade"
      id="deleteModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="deleteModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Bạn có chắc chắn muốn xóa dự án
            <strong>{{ selectedProject?.name }}</strong
            >?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" @click="deleteProject">Xóa</button>
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
import { formatDate, formatCurrency, showConfirm } from '@/utils'
import { usePermission } from '@/Composables/usePermission'

const props = defineProps({
  projects: Object,
  filters: Object,
  statuses: Object
})

// Sử dụng composable usePermission với các hàm kiểm tra quyền theo dự án
const { canInProject, hasGlobalPermission } = usePermission()

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || 'all')
const selectedProject = ref(null)

// Biến quản lý sắp xếp
const sort = ref({
  field: props.filters?.sort_field || 'id',
  direction: props.filters?.sort_direction || 'desc'
})

// Tính số thứ tự dựa trên trang hiện tại và vị trí trong trang
const getSerialNumber = (index) => {
  const currentPage = props.projects.current_page
  const perPage = props.projects.per_page
  return (currentPage - 1) * perPage + index + 1
}

const getStatusLabel = (status) => {
  const statusMap = {
    active: 'Đang hoạt động',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    active: 'badge badge-success',
    completed: 'badge badge-info',
    cancelled: 'badge badge-danger'
  }
  return classMap[status] || 'badge badge-secondary'
}

const confirmDelete = (project) => {
  selectedProject.value = project
  // Sử dụng hàm showConfirm từ utils.js
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa dự án "${project.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        deleteProject()
      }
    }
  )
}

const deleteProject = () => {
  if (selectedProject.value) {
    router.delete(route('projects.destroy', selectedProject.value.id), {
      onSuccess: () => {
        selectedProject.value = null
        router.reload({ preserveState: true })
      }
    })
  }
}

// Hàm xử lý sắp xếp
const sortBy = (field) => {
  if (sort.value.field === field) {
    // Nếu đang sắp xếp theo field này rồi thì đổi hướng sắp xếp
    sort.value.direction = sort.value.direction === 'asc' ? 'desc' : 'asc'
  } else {
    // Nếu chưa sắp xếp theo field này thì mặc định sắp xếp tăng dần
    sort.value.field = field
    sort.value.direction = 'asc'
  }

  // Gọi API để lấy dữ liệu mới
  applyFilters()
}

// Lấy icon cho cột đang sắp xếp
const getSortIcon = (field) => {
  if (sort.value.field !== field) return ''
  return sort.value.direction === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down'
}

// Hàm áp dụng tất cả các bộ lọc và sắp xếp
const applyFilters = () => {
  router.get(
    route('projects.index'),
    {
      search: search.value,
      status: status.value,
      sort_field: sort.value.field,
      sort_direction: sort.value.direction
    },
    {
      preserveState: true,
      replace: true
    }
  )
}

const filterByStatus = () => {
  applyFilters()
}

// Tìm kiếm dự án
watch(
  search,
  (value) => {
    router.get(
      route('projects.index'),
      {
        search: value,
        status: status.value,
        sort_field: sort.value.field,
        sort_direction: sort.value.direction
      },
      {
        preserveState: true,
        replace: true
      }
    )
  },
  { debounce: 300 }
)
</script>

<style scoped>
.dropdown-item {
  cursor: pointer;
  padding: 5px 10px;
}

.sortable {
  cursor: pointer;
  position: relative;
  user-select: none;
}

.sortable:hover {
  background-color: #f8f9fa;
}

.sortable i {
  margin-left: 5px;
  font-size: 0.85em;
  color: #007bff;
}

.sortable i.text-muted {
  color: #adb5bd !important;
  font-size: 0.75em;
  opacity: 0.5;
}

.sortable:hover i.text-muted {
  opacity: 0.8;
}

.sortable:hover i {
  opacity: 1;
}

.sortable i.fas.fa-sort-up {
  position: relative;
  top: 2px;
}

.sortable i.fas.fa-sort-down {
  position: relative;
  top: -2px;
}
.dropdown-item i {
  width: 20px;
  margin-left: 10px;
  margin-right: 10px;
}
</style>
