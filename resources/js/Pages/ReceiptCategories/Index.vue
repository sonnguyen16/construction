<template>
  <AdminLayout>
    <template #header>Quản lý loại thu</template>
    <template #breadcrumb>Danh sách loại thu</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách loại thu</h3>
            <div class="card-tools">
              <Link
                v-if="hasGlobalPermission('receipt-categories.create')"
                :href="route('receipt-categories.create')"
                class="btn btn-sm btn-primary"
              >
                <i class="fas fa-plus"></i> Thêm loại thu mới
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
                    placeholder="Tên loại thu"
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
                  <tr v-for="category in receiptCategories.data" :key="category.id">
                    <td>{{ category.name }}</td>
                    <td>{{ truncateText(category.note, 50) || '-' }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="hasGlobalPermission('receipt-categories.edit')"
                          :href="route('receipt-categories.edit', category.id)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button
                          v-if="hasGlobalPermission('receipt-categories.delete')"
                          @click="confirmDelete(category)"
                          class="btn btn-xs btn-danger"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="receiptCategories.data.length === 0">
                    <td colspan="4" class="text-center">Không có loại thu nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="receiptCategories.links" />
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
  receiptCategories: Object,
  filters: Object
})

const { can, hasGlobalPermission } = usePermission()

const search = ref(props.filters?.search || '')

// Hàm cắt ngắn văn bản
const truncateText = (text, length) => {
  if (!text) return null
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Xác nhận xóa loại thu
const confirmDelete = (category) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa loại thu "${category.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('receipt-categories.destroy', category.id), {
          onSuccess: () => {
            showSuccess('Loại thu đã được xóa thành công.')
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa loại thu này.')
          }
        })
      }
    }
  )
}

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  router.get(
    route('receipt-categories.index'),
    { search: search.value },
    {
      preserveState: true,
      replace: true
    }
  )
}, 300)
</script>
