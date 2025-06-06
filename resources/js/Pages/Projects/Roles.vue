<template>
  <AdminLayout>
    <template #header>Phân quyền dự án: {{ project.name }}</template>
    <template #breadcrumb>
      <Link :href="route('projects.index')">Quản lý dự án</Link> /
      <Link :href="route('projects.show', project.id)">{{ project.name }}</Link> /
      <span>Phân quyền</span>
    </template>

    <div class="container-fluid">
      <div class="row">
        <div class="col-md-6">
          <!-- Danh sách người dùng có vai trò trong dự án -->
          <div class="card card-success card-outline" style="max-height: calc(100vh - 200px); overflow-y: auto">
            <div class="card-header sticky-top" style="background-color: #f8f9fa">
              <h3 class="card-title"><i class="fas fa-users mr-2"></i> Người dùng có vai trò trong dự án</h3>
              <div class="card-tools">
                <button
                  v-if="canInProject('permissions.assign', project.id)"
                  type="button"
                  class="btn btn-sm btn-success"
                  data-toggle="modal"
                  data-target="#assignModal"
                  @click="showAssignModal"
                >
                  <i class="fas fa-user-plus"></i> Thêm người dùng
                </button>
              </div>
            </div>
            <div class="card-body">
              <div v-if="projectRoles.length === 0" class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> Chưa có người dùng nào được phân quyền trong dự án này.
              </div>
              <div v-else class="table-responsive">
                <table class="table table-hover table-striped">
                  <thead>
                    <tr>
                      <th>Người dùng</th>
                      <th>Vai trò</th>
                      <th>Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="projectRole in projectRoles" :key="projectRole.id">
                      <td>{{ projectRole.user_name }}</td>
                      <td>
                        <span class="badge badge-primary">{{ projectRole.role_name }}</span>
                      </td>
                      <td>
                        <button
                          v-if="
                            projectRole.role_name !== 'Super Admin' && canInProject('permissions.assign', project.id)
                          "
                          class="btn btn-sm btn-danger"
                          @click="confirmRemoveUser(projectRole)"
                          data-toggle="modal"
                          data-target="#removeModal"
                        >
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <!-- Thông tin về vai trò và quyền -->
          <div class="card card-info card-outline" style="max-height: calc(100vh - 200px); overflow-y: auto">
            <div class="card-header sticky-top" style="background-color: #f8f9fa">
              <h3 class="card-title"><i class="fas fa-key mr-2"></i> Thông tin vai trò</h3>
            </div>
            <div class="card-body">
              <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> Vai trò và quyền được định nghĩa toàn cục trong hệ thống. Tại
                đây bạn chỉ có thể gán vai trò cho người dùng trong phạm vi dự án này.
              </div>
              <div class="row">
                <div v-for="role in roles" :key="role.id" class="col-md-6 mb-3">
                  <div class="card h-100 shadow-sm">
                    <div class="card-header bg-gradient-info text-white">
                      <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ role.name }}</h5>
                      </div>
                    </div>
                    <div class="card-body">
                      <p class="text-muted">{{ role.permissions.length }} quyền</p>
                      <div class="mt-2">
                        <Link
                          v-if="canInProject('roles.edit', project.id)"
                          :href="route('roles.edit', role.id)"
                          class="btn btn-sm btn-outline-info"
                        >
                          <i class="fas fa-edit"></i> Chỉnh sửa quyền
                        </Link>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal thêm người dùng vào vai trò -->
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
              <i class="fas fa-user-plus mr-2"></i> Thêm người dùng vào dự án
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="userSelect">Chọn người dùng:</label>
              <input
                type="text"
                class="form-control"
                id="userSelect"
                placeholder="Chọn người dùng"
                data-role="inputpicker"
              />
            </div>
            <div class="form-group">
              <label for="roleSelect">Chọn vai trò:</label>
              <input
                type="text"
                class="form-control"
                id="roleSelect"
                placeholder="Chọn vai trò"
                data-role="inputpicker"
              />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            <button
              type="button"
              class="btn btn-success"
              @click="assignRole"
              :disabled="!selectedUser || !selectedRole"
            >
              <i class="fas fa-save mr-1"></i> Lưu
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal xác nhận xóa người dùng khỏi vai trò -->
    <div
      class="modal fade"
      id="removeModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="removeModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header bg-danger">
            <h5 class="modal-title text-white" id="removeModalLabel">
              <i class="fas fa-exclamation-triangle mr-2"></i> Xác nhận xóa
            </h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>
              Bạn có chắc chắn muốn xóa người dùng
              <strong v-if="userToRemove">{{ userToRemove.user_name }}</strong>
              khỏi vai trò trong dự án này?
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" @click="removeUserFromRole">
              <i class="fas fa-trash mr-1"></i> Xóa
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePermission } from '@/composables/usePermission'

const props = defineProps({
  project: Object,
  roles: Array,
  users: Array,
  projectRoles: Array
})

const { canInProject } = usePermission()

// Biến cho modal gán người dùng
const selectedUser = ref('')
const selectedRole = ref('')
const userToRemove = ref(null)

// InputPicker instances để có thể hủy khi component unmount
let userPicker = null
let rolePicker = null

// Lọc người dùng chưa có trong dự án
const filteredUsers = computed(() => {
  const assignedUserIds = props.projectRoles.map((pr) => pr.user_id)
  return props.users.filter((user) => !assignedUserIds.includes(user.id))
})

// Format tên quyền để hiển thị
function formatPermissionName(permissionName) {
  // Xóa phần module.
  const nameParts = permissionName.split('.')
  let name = nameParts.length > 1 ? nameParts[1] : nameParts[0]

  // Chuyển đổi các dấu gạch ngang thành khoảng trắng
  name = name.replace(/-/g, ' ')

  // Viết hoa chữ cái đầu tiên của mỗi từ
  name = name
    .split(' ')
    .map((word) => {
      return word.charAt(0).toUpperCase() + word.slice(1)
    })
    .join(' ')

  return name
}

// Hiển thị modal gán người dùng
function showAssignModal() {
  selectedUser.value = ''
  selectedRole.value = ''
  
  // Reset các input picker
  if (userPicker) {
    window.$('#userSelect').inputpicker('val', '')
  }
  
  if (rolePicker) {
    window.$('#roleSelect').inputpicker('val', '')
  }
}

// Gán vai trò cho người dùng
function assignRole() {
  if (!selectedUser.value || !selectedRole.value) return

  router.post(
    route('projects.roles.store', props.project.id),
    {
      user_id: selectedUser.value,
      role_id: selectedRole.value
    },
    {
      onSuccess: () => {
        // Đóng modal
        document.getElementById('assignModal').querySelector('[data-dismiss="modal"]').click()
      }
    }
  )
}

// Xác nhận xóa người dùng khỏi vai trò
function confirmRemoveUser(projectRole) {
  userToRemove.value = projectRole
}

// Xóa người dùng khỏi vai trò
function removeUserFromRole() {
  if (!userToRemove.value) return

  router.delete(route('projects.roles.destroy', [props.project.id, userToRemove.value.id]), {
    onSuccess: () => {
      // Đóng modal
      document.getElementById('removeModal').querySelector('[data-dismiss="modal"]').click()
      userToRemove.value = null
    }
  })
}

// Hàm hủy input picker an toàn
const safeDestroyInputPicker = (selector) => {
  try {
    const $el = window.$(selector)

    // Kiểm tra xem phần tử có tồn tại không
    if ($el.length === 0) return

    // Hủy sự kiện trước
    $el.off('change')

    // Kiểm tra xem inputpicker đã được khởi tạo chưa
    const instance = $el.data('inputpicker')
    if (instance) {
      // Hủy các dropdown mở
      window.$('.inputpicker-div').remove()

      // Xóa data
      $el.removeData('inputpicker')

      // Xóa các thuộc tính liên quan đến inputpicker
      $el.removeAttr('data-inputpicker-uuid')
      $el.removeAttr('data-value')
    }
  } catch (e) {
    console.error(`Lỗi khi hủy InputPicker ${selector}:`, e)
  }
}

// Khởi tạo input picker khi component được mount
onMounted(() => {
  // Khởi tạo input picker cho người dùng
  userPicker = window.$('#userSelect').inputpicker({
    data: filteredUsers.value.map((user) => ({
      value: user.id,
      text: user.name,
      email: user.email || ''
    })),
    fields: [
      { name: 'text', text: 'Tên người dùng' },
      { name: 'email', text: 'Email' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: true,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi người dùng
  window.$('#userSelect').on('change', function () {
    selectedUser.value = window.$(this).val()
  })

  // Khởi tạo input picker cho vai trò
  rolePicker = window.$('#roleSelect').inputpicker({
    data: props.roles.map((role) => ({
      value: role.id,
      text: role.name
    })),
    fields: [
      { name: 'text', text: 'Tên vai trò' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: true,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })

  // Sự kiện thay đổi vai trò
  window.$('#roleSelect').on('change', function () {
    selectedRole.value = window.$(this).val()
  })
})

// Hủy input picker khi component unmount
onBeforeUnmount(() => {
  safeDestroyInputPicker('#userSelect')
  safeDestroyInputPicker('#roleSelect')
})
</script>
