<template>
  <AdminLayout>
    <template #header>Quản lý khách hàng</template>
    <template #breadcrumb>Danh sách khách hàng</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách khách hàng</h3>
            <div class="card-tools">
              <Link
                v-if="hasGlobalPermission('customers.create')"
                :href="route('customers.create')"
                class="btn btn-sm btn-primary"
              >
                <i class="fas fa-plus"></i> Thêm khách hàng mới
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
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Tên khách hàng</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="customer in customers.data" :key="customer.id">
                    <td>{{ customer.name }}</td>
                    <td>{{ customer.email || '-' }}</td>
                    <td>{{ customer.phone || '-' }}</td>
                    <td>{{ truncateText(customer.address, 30) }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="hasGlobalPermission('customers.view')"
                          :href="route('customers.show', customer.id)"
                          class="btn btn-xs btn-info"
                        >
                          <i class="fas fa-eye"></i> Xem
                        </Link>
                        <Link
                          v-if="hasGlobalPermission('customers.edit')"
                          :href="route('customers.edit', customer.id)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button
                          v-if="hasGlobalPermission('customers.delete')"
                          @click="confirmDelete(customer)"
                          class="btn btn-xs btn-danger"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="customers.data.length === 0">
                    <td colspan="6" class="text-center">Không có khách hàng nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="customers.links" />
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
import { showConfirm, showSuccess, showError } from '@/utils'
import debounce from 'lodash/debounce'
import { usePermission } from '@/Composables/usePermission'

const props = defineProps({
  customers: Object,
  filters: Object
})

const { can, hasGlobalPermission } = usePermission()

const search = ref(props.filters.search || '')

// Hàm cắt ngắn văn bản
const truncateText = (text, length) => {
  if (!text) return '-'
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  router.get(
    route('customers.index'),
    { search: search.value },
    {
      preserveState: true,
      replace: true
    }
  )
}, 300)

// Hàm xác nhận xóa khách hàng
const confirmDelete = (customer) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa khách hàng "${customer.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('customers.destroy', customer.id), {
          onSuccess: () => {
            showSuccess('Khách hàng đã được xóa thành công.')
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa khách hàng này.')
          }
        })
      }
    }
  )
}
</script>
