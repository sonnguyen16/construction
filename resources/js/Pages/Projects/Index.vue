<template>
  <AdminLayout>
    <template #header>Quản lý dự án</template>
    <template #breadcrumb>Danh sách dự án</template>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <Link :href="route('projects.create')" class="btn btn-primary">
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
          <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
              <thead>
                <tr>
                  <th width="5%">STT</th>
                  <th width="10%">Mã dự án</th>
                  <th>Tên dự án</th>
                  <th>Khách hàng</th>
                  <th>Trạng thái</th>
                  <th>Ngày tạo</th>
                  <th width="25%">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(project, index) in projects.data" :key="project.id">
                  <td>{{ getSerialNumber(index) }}</td>
                  <td>{{ project.code }}</td>
                  <td>{{ project.name }}</td>
                  <td>{{ project.customer ? project.customer.name : 'N/A' }}</td>
                  <td>
                    <span :class="getStatusClass(project.status)">
                      {{ getStatusLabel(project.status) }}
                    </span>
                  </td>
                  <td>
                    {{ formatDate(project.created_at) }}
                  </td>
                  <td>
                    <div class="btn-group">
                      <Link :href="route('projects.edit', project.id)" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> Sửa
                      </Link>
                      <Link :href="route('projects.show', project.id)" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Chi tiết
                      </Link>
                      <Link :href="route('projects.expenses', project.id)" class="btn btn-sm btn-warning">
                        <i class="fas fa-money-bill"></i> Chi phí
                      </Link>
                      <Link :href="route('projects.profit', project.id)" class="btn btn-sm btn-success">
                        <i class="fas fa-chart-line"></i> Lợi nhuận
                      </Link>
                    </div>
                  </td>
                </tr>
                <tr v-if="projects.data.length === 0">
                  <td colspan="7" class="text-center">Không có dữ liệu</td>
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
import { formatDate } from '@/utils'

const props = defineProps({
  projects: Object,
  filters: Object,
  statuses: Object
})

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || 'all')
const selectedProject = ref(null)

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
  // Sử dụng jQuery của AdminLTE để hiển thị modal
  window.$('#deleteModal').modal('show')
}

const deleteProject = () => {
  if (selectedProject.value) {
    router.delete(route('projects.destroy', selectedProject.value.id), {
      onSuccess: () => {
        window.$('#deleteModal').modal('hide')
        selectedProject.value = null
      }
    })
  }
}

const filterByStatus = () => {
  router.get(
    route('projects.index'),
    {
      search: search.value,
      status: status.value
    },
    {
      preserveState: true,
      replace: true
    }
  )
}

// Tìm kiếm dự án
watch(search, (value) => {
  router.get(
    route('projects.index'),
    {
      search: value,
      status: status.value
    },
    {
      preserveState: true,
      replace: true
    }
  )
})
</script>
