<template>
  <AdminLayout>
    <template #header>Quản lý sản phẩm</template>
    <template #breadcrumb>Danh sách sản phẩm</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách sản phẩm</h3>
            <div class="card-tools">
              <Link
                v-if="hasGlobalPermission('products.create')"
                :href="route('products.create')"
                class="btn btn-sm btn-primary"
              >
                <i class="fas fa-plus"></i> Thêm sản phẩm mới
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
                    placeholder="Tên hoặc mã sản phẩm"
                    v-model="search"
                    @input="debouncedSearch"
                  />
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label for="category_id">Danh mục:</label>
                  <select class="form-control" id="category_id" v-model="selectedCategory" @change="debouncedSearch">
                    <option value="">Tất cả danh mục</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Mã</th>
                    <th>Tên</th>
                    <th>Giá nhập</th>
                    <th>Giá xuất</th>
                    <th>Tồn đầu</th>
                    <th>Ngưỡng cảnh báo</th>
                    <th>Danh mục</th>
                    <th>Đơn vị</th>
                    <th>Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="product in products.data" :key="product.id">
                    <td>{{ product.code }}</td>
                    <td>{{ product.name }}</td>
                    <td>{{ formatCurrency(product.import_price) }}</td>
                    <td>{{ formatCurrency(product.export_price) }}</td>
                    <td>{{ product.initial_stock }}</td>
                    <td>
                      <span
                        :class="{ 'text-danger font-weight-bold': product.initial_stock <= product.warning_threshold }"
                      >
                        {{ product.warning_threshold }}
                        <i
                          v-if="product.initial_stock <= product.warning_threshold"
                          class="fas fa-exclamation-triangle text-warning"
                        ></i>
                      </span>
                    </td>
                    <td>{{ product.category ? product.category.name : '-' }}</td>
                    <td>{{ product.unit ? product.unit.name : '-' }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="hasGlobalPermission('products.view')"
                          :href="route('products.show', product.id)"
                          class="btn btn-xs btn-info"
                        >
                          <i class="fas fa-eye"></i> Xem
                        </Link>
                        <Link
                          v-if="hasGlobalPermission('products.edit')"
                          :href="route('products.edit', product.id)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button
                          v-if="hasGlobalPermission('products.delete')"
                          @click="confirmDelete(product)"
                          class="btn btn-xs btn-danger"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="products.data.length === 0">
                    <td colspan="9" class="text-center">Không có sản phẩm nào</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            <Pagination :links="products.links" />
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
import { showConfirm, showSuccess, showError, formatCurrency } from '@/utils'
import debounce from 'lodash/debounce'
import { usePermission } from '@/Composables/usePermission'

const { can, hasGlobalPermission } = usePermission()

const props = defineProps({
  products: Object,
  categories: Array,
  filters: Object
})

const search = ref(props.filters?.search || '')
const selectedCategory = ref(props.filters?.category_id || '')

// Hàm cắt ngắn văn bản
const truncateText = (text, length) => {
  if (!text) return null
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Xác nhận xóa sản phẩm
const confirmDelete = (product) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa sản phẩm "${product.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        router.delete(route('products.destroy', product.id), {
          onSuccess: () => {
            showSuccess('Sản phẩm đã được xóa thành công.')
          },
          onError: (errors) => {
            showError(errors.error || 'Không thể xóa sản phẩm này.')
          }
        })
      }
    }
  )
}

// Hàm tìm kiếm có debounce
const debouncedSearch = debounce(() => {
  router.get(
    route('products.index'),
    {
      search: search.value,
      category_id: selectedCategory.value
    },
    {
      preserveState: true,
      replace: true
    }
  )
}, 300)
</script>
