<template>
  <AdminLayout>
    <template #header>Thêm vai trò mới</template>
    <template #breadcrumb>Quản lý vai trò / Thêm vai trò mới</template>

    <form @submit.prevent="submitForm">
      <div class="">
        <div class="">
          <div class="form-group">
            <label for="name" class="font-weight-bold">Tên vai trò <span class="text-danger">*</span></label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-tag"></i></span>
              </div>
              <input
                type="text"
                class="form-control"
                id="name"
                v-model="form.name"
                :class="{ 'is-invalid': errors.name }"
                placeholder="Nhập tên vai trò"
              />
            </div>
            <div class="invalid-feedback" v-if="errors.name">{{ errors.name }}</div>
          </div>

          <div class="form-group mt-2">
            <label class="font-weight-bold">Quyền <span class="text-danger">*</span></label>
            <div v-if="errors.permissions" class="alert alert-danger">
              <i class="fas fa-exclamation-triangle mr-1"></i> {{ errors.permissions }}
            </div>

            <div class="card card-info card-outline" style="height: calc(100vh - 360px); overflow-y: auto">
              <div class="card-header bg-light sticky-top">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="form-check">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      id="selectAll"
                      v-model="selectAllPermissions"
                      @change="toggleAllPermissions"
                    />
                    <label class="form-check-label font-weight-bold" for="selectAll">
                      <i class="fas fa-check-double mr-1"></i> Chọn tất cả quyền
                    </label>
                  </div>
                  <span class="badge badge-info p-2">
                    <i class="fas fa-shield-alt mr-1"></i> Tổng số quyền: {{ getTotalPermissions() }}
                  </span>
                </div>
              </div>
              <div class="card-body">
                <div class="row">
                  <div v-for="(modulePermissions, module) in permissions" :key="module" class="col-md-3 mb-3">
                    <div class="card h-100 shadow-sm">
                      <div class="card-header bg-gradient-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                          <div class="form-check">
                            <input
                              type="checkbox"
                              class="form-check-input"
                              :id="'module-' + module"
                              v-model="moduleSelections[module]"
                              @change="toggleModulePermissions(module)"
                            />
                            <label class="form-check-label" :for="'module-' + module">
                              <strong>{{ formatModuleName(module) }}</strong>
                            </label>
                          </div>
                          <span class="badge badge-light">
                            {{ modulePermissions.length }}
                          </span>
                        </div>
                      </div>
                      <div class="card-body" style="max-height: 180px; overflow-y: auto">
                        <div
                          v-for="permission in modulePermissions"
                          :key="permission.id"
                          class="custom-control custom-checkbox mb-2"
                        >
                          <input
                            type="checkbox"
                            class="custom-control-input"
                            :id="'permission-' + permission.id"
                            v-model="form.permissions"
                            :value="permission.name"
                            @change="updateModuleSelection(module)"
                          />
                          <label class="custom-control-label" :for="'permission-' + permission.id">
                            {{ formatPermissionName(permission.name) }}
                          </label>
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
      <div class="">
        <button type="submit" class="btn btn-primary" :disabled="processing">
          <i class="fas fa-save mr-1"></i> Lưu
        </button>
        <Link :href="route('roles.index')" class="btn btn-secondary ms-3">
          <i class="fas fa-arrow-left mr-1"></i> Trở về
        </Link>
      </div>
    </form>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const props = defineProps({
  permissions: Object,
  modules: Array
})

const form = useForm({
  name: '',
  permissions: []
})

const errors = computed(() => form.errors)
const processing = computed(() => form.processing)

// Quản lý trạng thái chọn cho từng module
const moduleSelections = ref({})
const selectAllPermissions = ref(false)

// Khởi tạo trạng thái chọn cho từng module
props.modules.forEach((module) => {
  moduleSelections.value[module] = false
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
    categories: 'Danh mục sản phẩm',
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
    permissions: 'Quyền',
    loans: 'Khoản vay'
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
    commission: 'Xem gói thầu',
    'select-contractor': 'Chọn nhà thầu',
    trash: 'Công việc đã xóa',
    assign: 'Phân quyền',
    files: 'Files'
  }

  return actionNames[action] || action.charAt(0).toUpperCase() + action.slice(1).replace(/-/g, ' ')
}

// Chọn/bỏ chọn tất cả quyền của một module
const toggleModulePermissions = (module) => {
  const isSelected = moduleSelections.value[module]
  const modulePermissionNames = props.permissions[module].map((p) => p.name)

  if (isSelected) {
    // Thêm tất cả quyền của module vào danh sách đã chọn
    modulePermissionNames.forEach((permName) => {
      if (!form.permissions.includes(permName)) {
        form.permissions.push(permName)
      }
    })
  } else {
    // Loại bỏ tất cả quyền của module khỏi danh sách đã chọn
    form.permissions = form.permissions.filter((permName) => !modulePermissionNames.includes(permName))
  }

  // Cập nhật trạng thái chọn tất cả
  updateSelectAllStatus()
}

// Cập nhật trạng thái chọn của module dựa trên các quyền đã chọn
const updateModuleSelection = (module) => {
  const modulePermissionNames = props.permissions[module].map((p) => p.name)
  const allSelected = modulePermissionNames.every((permName) => form.permissions.includes(permName))
  const someSelected = modulePermissionNames.some((permName) => form.permissions.includes(permName))

  moduleSelections.value[module] = allSelected

  // Cập nhật trạng thái chọn tất cả
  updateSelectAllStatus()
}

// Cập nhật trạng thái chọn tất cả
const updateSelectAllStatus = () => {
  selectAllPermissions.value = Object.values(moduleSelections.value).every((selected) => selected)
}

// Đếm tổng số quyền
const getTotalPermissions = () => {
  let total = 0
  Object.values(props.permissions).forEach((modulePermissions) => {
    total += modulePermissions.length
  })
  return total
}

// Chọn/bỏ chọn tất cả quyền
const toggleAllPermissions = () => {
  const allPermissionNames = Object.values(props.permissions)
    .flat()
    .map((p) => p.name)

  if (selectAllPermissions.value) {
    // Chọn tất cả quyền
    form.permissions = [...allPermissionNames]

    // Cập nhật trạng thái chọn của từng module
    Object.keys(moduleSelections.value).forEach((module) => {
      moduleSelections.value[module] = true
    })
  } else {
    // Bỏ chọn tất cả quyền
    form.permissions = []

    // Cập nhật trạng thái chọn của từng module
    Object.keys(moduleSelections.value).forEach((module) => {
      moduleSelections.value[module] = false
    })
  }
}

// Theo dõi sự thay đổi của form.permissions để cập nhật trạng thái chọn của từng module
watch(
  () => form.permissions,
  () => {
    props.modules.forEach((module) => {
      updateModuleSelection(module)
    })
  },
  { deep: true }
)

// Gửi form
const submitForm = () => {
  form.post(route('roles.store'), {
    onSuccess: () => {
      // Chuyển hướng đến trang danh sách vai trò
      router.visit(route('roles.index'))
    }
  })
}
</script>
