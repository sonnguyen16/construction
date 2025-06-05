<template>
  <AdminLayout>
    <template #header>Chi tiết vai trò</template>
    <template #breadcrumb>Quản lý vai trò / Chi tiết vai trò</template>

    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <!-- Danh sách quyền -->
          <div class="card card-info card-outline" style="max-height: calc(100vh - 200px); overflow-y: auto">
            <div class="card-header sticky-top" style="background-color: #f8f9fa">
              <h3 class="card-title"><i class="fas fa-key mr-2"></i> Danh sách quyền</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div
                  v-for="(modulePermissions, module, index) in groupedPermissions"
                  :key="module"
                  class="col-md-6 mb-3"
                >
                  <div class="card h-100 shadow-sm">
                    <div class="card-header bg-gradient-info text-white" style="cursor: pointer">
                      <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-folder mr-2"></i> {{ formatModuleName(module) }}</h5>
                        <span class="badge badge-light">{{ modulePermissions.length }}</span>
                      </div>
                    </div>
                    <div class="card-body" style="">
                      <div class="list-group">
                        <div
                          v-for="permission in modulePermissions"
                          :key="permission.id"
                          class="list-group-item list-group-item-action d-flex align-items-center py-2"
                        >
                          <i class="fas fa-check-circle text-success mr-2"></i>
                          <span>{{ formatPermissionName(permission.name) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <!-- Người dùng có vai trò này -->
          <div class="card card-success card-outline" style="max-height: calc(100vh - 200px); overflow-y: auto">
            <div class="card-header sticky-top" style="background-color: #f8f9fa">
              <h3 class="card-title"><i class="fas fa-users mr-2"></i> Người dùng có vai trò này</h3>
              <div class="card-tools">
                <button
                  v-if="user.can.includes('permissions.assign')"
                  class="btn btn-success btn-sm"
                  @click="showAssignModal"
                  title="Thêm người dùng vào vai trò"
                >
                  <i class="fas fa-user-plus"></i> Thêm người dùng
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead class="sticky-top" style="background-color: #f8f9fa">
                    <tr>
                      <th style="width: 60px">Ảnh</th>
                      <th>Tên</th>
                      <th>Email</th>
                      <th style="width: 150px">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="roleUser in users" :key="roleUser.id">
                      <td>
                        <img :src="roleUser.avatar" alt="Avatar" class="img-circle" />
                      </td>
                      <td>{{ roleUser.name }}</td>
                      <td>{{ roleUser.email }}</td>
                      <td>
                        <div class="btn-group">
                          <Link :href="route('users.edit', roleUser.id)" class="btn btn-info btn-sm" title="Chỉnh sửa">
                            <i class="fas fa-edit"></i>
                          </Link>
                          <button
                            v-if="role.name !== 'Super Admin' || roleUser.id !== 1"
                            class="btn btn-danger btn-sm"
                            title="Xóa khỏi vai trò"
                            @click="confirmRemoveUser(roleUser)"
                          >
                            <i class="fas fa-user-minus"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="users.length === 0">
                      <td colspan="4" class="text-center py-4">Không có người dùng nào có vai trò này</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal gán người dùng -->
    <div
      class="modal fade"
      id="assignModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="assignModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-success">
            <h5 class="modal-title text-white" id="assignModalLabel">
              <i class="fas fa-user-plus mr-2"></i> Thêm người dùng vào vai trò {{ role.name }}
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input
                  type="text"
                  class="form-control"
                  v-model="searchTerm"
                  placeholder="Tìm kiếm theo tên hoặc email..."
                />
              </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
              <div>
                <div class="custom-control custom-checkbox">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="selectAll"
                    v-model="selectAll"
                    @change="toggleSelectAll"
                  />
                  <label class="custom-control-label" for="selectAll">Chọn tất cả</label>
                </div>
              </div>
              <div>
                <span class="badge badge-info p-2"> Đã chọn: {{ selectedUsers.length }} người dùng </span>
              </div>
            </div>

            <div class="table-responsive mt-3">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th style="width: 40px"></th>
                    <th style="width: 60px">Ảnh</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Vai trò hiện tại</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="availableUser in filteredUsers" :key="availableUser.id">
                    <td class="text-center">
                      <div class="custom-control custom-checkbox">
                        <input
                          type="checkbox"
                          class="custom-control-input"
                          :id="'user-' + availableUser.id"
                          v-model="selectedUsers"
                          :value="availableUser.id"
                        />
                        <label class="custom-control-label" :for="'user-' + availableUser.id"></label>
                      </div>
                    </td>
                    <td>
                      <img
                        :src="availableUser.avatar"
                        alt="Avatar"
                        class="img-circle"
                        style="width: 40px; height: 40px"
                      />
                    </td>
                    <td>{{ availableUser.name }}</td>
                    <td>{{ availableUser.email }}</td>
                    <td>
                      <span
                        v-for="userRole in availableUser.roles"
                        :key="userRole.id"
                        class="badge badge-info mr-1 p-2"
                      >
                        {{ userRole.name }}
                      </span>
                      <span v-if="!availableUser.roles || availableUser.roles.length === 0" class="text-muted">
                        Chưa có vai trò
                      </span>
                    </td>
                  </tr>
                  <tr v-if="filteredUsers.length === 0">
                    <td colspan="5" class="text-center py-4">
                      <i class="fas fa-search fa-2x mb-2 text-muted"></i>
                      <p class="text-muted">Không tìm thấy người dùng nào phù hợp</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              <i class="fas fa-times mr-1"></i> Hủy
            </button>
            <button
              type="button"
              class="btn btn-success"
              @click="assignUsers"
              :disabled="selectedUsers.length === 0 || processing"
            >
              <i class="fas fa-user-plus mr-1"></i> Thêm người dùng
              <span
                v-if="processing"
                class="spinner-border spinner-border-sm ml-1"
                role="status"
                aria-hidden="true"
              ></span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal xác nhận xóa người dùng khỏi vai trò -->
    <div
      class="modal fade"
      id="removeUserModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="removeUserModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-danger">
            <h5 class="modal-title text-white" id="removeUserModalLabel">
              <i class="fas fa-user-minus mr-2"></i> Xóa người dùng khỏi vai trò
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              Bạn có chắc chắn muốn xóa người dùng <strong>{{ selectedUserToRemove?.name }}</strong> khỏi vai trò
              <strong>{{ role.name }}</strong
              >?
            </div>
            <p class="text-muted"><small>Người dùng sẽ không còn các quyền liên quan đến vai trò này.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              <i class="fas fa-times mr-1"></i> Hủy
            </button>
            <button type="button" class="btn btn-danger" @click="removeUserFromRole" :disabled="processing">
              <i class="fas fa-user-minus mr-1"></i> Xóa người dùng
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  role: Object,
  users: Array
})

const user = computed(() => usePage().props.auth.user)
const processing = ref(false)
const selectedUserToRemove = ref(null)

// Nhóm quyền theo module
const groupedPermissions = computed(() => {
  const grouped = {}

  props.role.permissions.forEach((permission) => {
    const moduleName = permission.name.split('.')[0]

    if (!grouped[moduleName]) {
      grouped[moduleName] = []
    }

    grouped[moduleName].push(permission)
  })

  return grouped
})

// Format tên module để hiển thị
const formatModuleName = (module) => {
  // Chuyển đổi tên module thành dạng hiển thị đẹp hơn
  const moduleNames = {
    dashboard: 'Bảng điều khiển',
    users: 'Người dùng',
    contractors: 'Nhà thầu',
    projects: 'Dự án',
    'bid-packages': 'Gói thầu',
    bids: 'Giá dự thầu',
    customers: 'Khách hàng',
    'payment-vouchers': 'Phiếu chi',
    'receipt-vouchers': 'Phiếu thu',
    categories: 'Danh mục',
    units: 'Đơn vị tính',
    'receipt-categories': 'Loại thu',
    'payment-categories': 'Loại chi',
    'project-categories': 'Danh mục dự án',
    products: 'Sản phẩm',
    'import-vouchers': 'Phiếu nhập kho',
    'export-vouchers': 'Phiếu xuất kho',
    reports: 'Báo cáo',
    tasks: 'Công việc',
    roles: 'Vai trò',
    permissions: 'Quyền'
  }

  return moduleNames[module] || module.charAt(0).toUpperCase() + module.slice(1).replace(/-/g, ' ')
}

// Format tên quyền để hiển thị
const formatPermissionName = (permissionName) => {
  const parts = permissionName.split('.')
  const action = parts[1]

  const actionNames = {
    view: 'Xem',
    create: 'Thêm mới',
    edit: 'Chỉnh sửa',
    delete: 'Xóa',
    'update-status': 'Cập nhật trạng thái',
    print: 'In',
    expenses: 'Xem chi phí',
    profit: 'Xem lợi nhuận',
    commission: 'Cập nhật hoa hồng',
    'select-contractor': 'Chọn nhà thầu',
    trash: 'Xem thùng rác',
    restore: 'Khôi phục',
    'force-delete': 'Xóa vĩnh viễn',
    assign: 'Phân quyền',
    Loans: 'Khoản vay'
  }

  return actionNames[action] || action.charAt(0).toUpperCase() + action.slice(1).replace(/-/g, ' ')
}

// Biến cho modal gán người dùng
const allUsers = ref([])
const searchTerm = ref('')
const selectedUsers = ref([])
const selectAll = ref(false)

// Lọc người dùng theo từ khóa tìm kiếm
const filteredUsers = computed(() => {
  if (!searchTerm.value) {
    return allUsers.value
  }

  const term = searchTerm.value.toLowerCase()
  return allUsers.value.filter(
    (user) => user.name.toLowerCase().includes(term) || user.email.toLowerCase().includes(term)
  )
})

// Chọn/bỏ chọn tất cả người dùng
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedUsers.value = filteredUsers.value.map((user) => user.id)
  } else {
    selectedUsers.value = []
  }
}

// Lấy danh sách tất cả người dùng
const fetchAllUsers = async () => {
  try {
    const response = await axios.get('/api/users')
    allUsers.value = response.data
  } catch (error) {
    console.error('Error fetching users:', error)
    // Lỗi sẽ được xử lý trong AdminLayout thông qua flash message
  }
}

// Hiển thị modal gán người dùng
const showAssignModal = async () => {
  // Lấy danh sách người dùng nếu chưa có
  if (allUsers.value.length === 0) {
    await fetchAllUsers()
  }

  // Reset các biến
  selectedUsers.value = []
  selectAll.value = false
  searchTerm.value = ''

  // Hiển thị modal
  $('#assignModal').modal('show')
}

// Gán người dùng cho vai trò
const assignUsers = () => {
  if (selectedUsers.value.length === 0) {
    return
  }

  processing.value = true

  router.post(
    route('roles.assign-users', props.role.id),
    {
      users: selectedUsers.value
    },
    {
      onSuccess: () => {
        $('#assignModal').modal('hide')
        processing.value = false
      },
      onError: () => {
        processing.value = false
      }
    }
  )
}

// Xác nhận xóa người dùng khỏi vai trò
const confirmRemoveUser = (roleUser) => {
  selectedUserToRemove.value = roleUser
  $('#removeUserModal').modal('show')
}

// Xóa người dùng khỏi vai trò
const removeUserFromRole = () => {
  if (!selectedUserToRemove.value) {
    return
  }

  processing.value = true

  router.post(
    route('roles.remove-user', {
      role: props.role.id,
      user: selectedUserToRemove.value.id
    }),
    {},
    {
      onSuccess: () => {
        $('#removeUserModal').modal('hide')
        processing.value = false
      },
      onError: () => {
        processing.value = false
      }
    }
  )
}

// Khởi tạo khi component được mount
onMounted(() => {})
</script>
