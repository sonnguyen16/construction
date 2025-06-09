<template>
  <AdminLayout>
    <template #header>Quản lý vai trò</template>
    <template #breadcrumb>Quản lý vai trò</template>

    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Danh sách vai trò</h3>
              <div class="card-tools">
                <Link
                  v-if="hasGlobalPermission('roles.create')"
                  :href="route('roles.create')"
                  class="btn btn-primary btn-sm"
                >
                  <i class="fas fa-plus"></i> Thêm vai trò mới
                </Link>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Tên vai trò</th>
                      <th>Số quyền</th>
                      <th>Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="role in roles" :key="role.id">
                      <td>{{ role.name }}</td>
                      <td>
                        <span class="badge badge-info">{{ role.permissions.length }}</span>
                      </td>
                      <td>
                        <div class="btn-group">
                          <!-- <Link
                            v-if="user.can.includes('roles.view')"
                            :href="route('roles.show', role.id)"
                            class="btn btn-info btn-sm"
                            title="Xem chi tiết"
                          >
                            <i class="fas fa-eye"></i>
                          </Link> -->
                          <Link
                            v-if="hasGlobalPermission('roles.edit') && role.name !== 'Super Admin'"
                            :href="route('roles.edit', role.id)"
                            class="btn btn-primary btn-sm"
                            title="Chỉnh sửa"
                          >
                            <i class="fas fa-edit"></i>
                          </Link>
                          <button
                            v-if="hasGlobalPermission('roles.delete') && role.name !== 'Super Admin'"
                            @click="confirmDelete(role)"
                            class="btn btn-danger btn-sm"
                            title="Xóa"
                          >
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="roles.length === 0">
                      <td colspan="4" class="text-center">Không có vai trò nào</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
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
            Bạn có chắc chắn muốn xóa vai trò <strong>{{ selectedRole?.name }}</strong
            >?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" @click="deleteRole">Xóa</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, onMounted, computed } from 'vue'
import { usePermission } from '@/Composables/usePermission'

const props = defineProps({
  roles: Array
})

const user = computed(() => usePage().props.auth.user)

const { hasGlobalPermission } = usePermission()

const selectedRole = ref(null)

// Hiển thị modal xác nhận xóa
const confirmDelete = (role) => {
  selectedRole.value = role
  $('#deleteModal').modal('show')
}

// Xóa vai trò
const deleteRole = () => {
  router.delete(route('roles.destroy', selectedRole.value.id), {
    onSuccess: () => {
      $('#deleteModal').modal('hide')
    },
    onError: () => {
      $('#deleteModal').modal('hide')
    }
  })
}

// Không cần xử lý flash messages vì đã được xử lý trong AdminLayout
</script>
