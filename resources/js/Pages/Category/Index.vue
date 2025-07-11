<template>
  <AdminLayout>
    <template #header>Quản lý danh mục</template>
    <template #breadcrumb>Danh sách danh mục</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách danh mục</h3>
            <div class="card-tools">
              <Link
                v-if="hasGlobalPermission('categories.create')"
                :href="route('categories.create')"
                class="btn btn-sm btn-primary"
              >
                <i class="fas fa-plus"></i> Thêm danh mục mới
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
                    placeholder="Tên danh mục"
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
                    <th>Tên danh mục</th>
                    <th>Ghi chú</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="category in categories" :key="category.id">
                    <td>{{ category.name }}</td>
                    <td>{{ truncateText(category.note, 50) || '-' }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="hasGlobalPermission('categories.edit')"
                          :href="route('categories.edit', category.id)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button
                          v-if="hasGlobalPermission('categories.delete')"
                          @click="confirmDelete(category)"
                          class="btn btn-xs btn-danger"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="categories.length === 0">
                    <td colspan="4" class="text-center">Không có danh mục nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { showConfirm, showSuccess, showError } from '@/utils'
import debounce from 'lodash/debounce'
import { usePermission } from '@/Composables/usePermission'

const props = defineProps({
  categories: Array,
  filters: Object
})

const { hasGlobalPermission } = usePermission()

const search = ref(props.filters?.search || '')

// Hàm cắt ngắn văn bản
const truncateText = (text, length) => {
  if (!text) return null
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  router.get(
    route('categories.index'),
    { search: search.value },
    {
      preserveState: true,
      replace: true
    }
  )
}, 300)

// Hàm xác nhận xóa danh mục
const confirmDelete = (category) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa danh mục "${category.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('categories.destroy', category.id), {
          onSuccess: () => {
            showSuccess('Danh mục đã được xóa thành công.')
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa danh mục này.')
          }
        })
      }
    }
  )
}
</script>
