<template>
  <AdminLayout>
    <template #header>Quản lý loại chi</template>
    <template #breadcrumb>Danh sách loại chi</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách loại chi</h3>
            <div class="card-tools">
              <Link :href="route('payment-categories.create')" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm loại chi mới
              </Link>
            </div>
          </div>
          <div class="card-body">
            <!-- Bộ lọc -->
            <div class="row mb-3">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="search">Tìm kiếm:</label>
                  <input
                    type="text"
                    class="form-control"
                    id="search"
                    placeholder="Tên loại chi"
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
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Ghi chú</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="category in paymentCategories.data" :key="category.id">
                    <td>{{ category.id }}</td>
                    <td>{{ category.name }}</td>
                    <td>{{ truncateText(category.note, 50) || '-' }}</td>
                    <td>
                      <div class="btn-group">
                        <Link :href="route('payment-categories.edit', category.id)" class="btn btn-xs btn-primary">
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button @click="confirmDelete(category)" class="btn btn-xs btn-danger">
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="paymentCategories.data.length === 0">
                    <td colspan="4" class="text-center">Không có loại chi nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="paymentCategories.links" />
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

const props = defineProps({
  paymentCategories: Object,
  filters: Object
})

const search = ref(props.filters?.search || '')

// Hàm cắt ngắn văn bản
const truncateText = (text, length) => {
  if (!text) return null
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Xác nhận xóa loại chi
const confirmDelete = (category) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa loại chi "${category.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('payment-categories.destroy', category.id), {
          onSuccess: () => {
            showSuccess('Loại chi đã được xóa thành công.')
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa loại chi này.')
          }
        })
      }
    }
  )
}

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  router.get(
    route('payment-categories.index'),
    { search: search.value },
    {
      preserveState: true,
      replace: true
    }
  )
}, 300)
</script>
