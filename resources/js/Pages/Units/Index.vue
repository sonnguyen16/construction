<template>
  <AdminLayout>
    <template #header>Quản lý đơn vị</template>
    <template #breadcrumb>Danh sách đơn vị</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách đơn vị</h3>
            <div class="card-tools">
              <Link
                v-if="hasGlobalPermission('units.create')"
                :href="route('units.create')"
                class="btn btn-sm btn-primary"
              >
                <i class="fas fa-plus"></i> Thêm đơn vị mới
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
                    placeholder="Tên đơn vị"
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
                    <th>Tên</th>
                    <th>Ghi chú</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="unit in units.data" :key="unit.id">
                    <td>{{ unit.name }}</td>
                    <td>{{ truncateText(unit.note, 50) || '-' }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="hasGlobalPermission('units.edit')"
                          :href="route('units.edit', unit.id)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button
                          v-if="hasGlobalPermission('units.delete')"
                          @click="confirmDelete(unit)"
                          class="btn btn-xs btn-danger"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="units.data.length === 0">
                    <td colspan="4" class="text-center">Không có đơn vị nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="units.links" />
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
  units: Object,
  filters: Object
})

const { can, hasGlobalPermission } = usePermission()

const search = ref(props.filters?.search || '')

// Hàm cắt ngắn văn bản
const truncateText = (text, length) => {
  if (!text) return null
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Xác nhận xóa đơn vị
const confirmDelete = (unit) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa đơn vị "${unit.name}" không?`, 'Xóa', 'Hủy').then((result) => {
    if (result.isConfirmed) {
      router.delete(route('units.destroy', unit.id), {
        onSuccess: () => {
          showSuccess('Đơn vị đã được xóa thành công.')
        },
        onError: (errors) => {
          showError(errors.error || 'Không thể xóa đơn vị này.')
        }
      })
    }
  })
}

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  router.get(
    route('units.index'),
    { search: search.value },
    {
      preserveState: true,
      replace: true
    }
  )
}, 300)
</script>
