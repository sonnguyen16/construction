<template>
  <AdminLayout>
    <template #header>Quản lý người dùng</template>
    <template #breadcrumb>Danh sách người dùng</template>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách người dùng</h3>
            <div class="card-tools">
              <Link
                v-if="hasGlobalPermission('users.create')"
                :href="route('users.create')"
                class="btn btn-sm btn-primary"
              >
                <i class="fas fa-plus"></i> Thêm người dùng mới
              </Link>
            </div>
          </div>
          <div class="card-body">
            <!-- Bộ lọc -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="search">Tìm kiếm:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="search"
                    placeholder="Tên, Email, Số điện thoại"
                    v-model="search"
                    @input="debouncedSearch"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover text-nowrap">
                <thead>
                  <tr>
                    <th width="5%">STT</th>
                    <th width="10%">Ảnh đại diện</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Ngày tạo</th>
                    <th width="15%">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(user, index) in users.data" :key="user.id">
                    <td>{{ getSerialNumber(index) }}</td>
                    <td>
                      <img
                        :src="user.avatar || 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg'"
                        class="img-circle elevation-2"
                        alt="User Avatar"
                        style="width: 40px; height: 40px; object-fit: cover"
                      />
                    </td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ formatDate(user.created_at) }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="hasGlobalPermission('users.edit')"
                          :href="`/users/${user.id}/edit`"
                          class="btn btn-sm btn-primary"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button
                          v-if="hasGlobalPermission('users.delete')"
                          @click="confirmDelete(user)"
                          class="btn btn-sm btn-danger"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="users.data.length === 0">
                    <td colspan="6" class="text-center">Không có dữ liệu</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer clearfix">
            <pagination :links="users.links" />
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
            Bạn có chắc chắn muốn xóa người dùng
            <strong>{{ selectedUser?.name }}</strong
            >?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" @click="deleteUser">Xóa</button>
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
import { formatDate, showConfirm } from '@/utils'
import { usePermission } from '@/Composables/usePermission'

const { can, hasGlobalPermission } = usePermission()
const props = defineProps({
  users: Object
})

const search = ref('')
const selectedUser = ref(null)

const confirmDelete = (user) => {
  selectedUser.value = user
  // Sử dụng hàm showConfirm từ utils.js
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa người dùng "${user.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        deleteUser()
      }
    }
  )
}

const deleteUser = () => {
  if (selectedUser.value) {
    router.delete(`/users/${selectedUser.value.id}`, {
      onSuccess: () => {
        selectedUser.value = null
        router.reload({ preserveState: true })
      }
    })
  }
}

// Tìm kiếm người dùng
watch(search, (value) => {
  router.get(
    '/users',
    { search: value },
    {
      preserveState: true,
      replace: true
    }
  )
})

// Tính số thứ tự dựa trên trang hiện tại và vị trí trong trang
const getSerialNumber = (index) => {
  const currentPage = props.users.current_page
  const perPage = props.users.per_page
  return (currentPage - 1) * perPage + index + 1
}
</script>
