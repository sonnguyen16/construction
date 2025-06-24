<template>
  <AdminLayout>
    <template #header>Danh mục dự án</template>
    <template #breadcrumb>Danh mục dự án</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between">
              <h3 class="card-title">Danh sách danh mục dự án</h3>
              <Link
                v-if="hasGlobalPermission('project-categories.create')"
                :href="route('project-categories.create')"
                class="btn btn-primary btn-sm"
              >
                <i class="fas fa-plus mr-1"></i> Thêm mới
              </Link>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="input-group">
                  <input
                    type="text"
                    class="form-control"
                    placeholder="Tìm kiếm theo tên..."
                    v-model="search"
                    @input="debouncedSearch"
                  />
                  <div class="input-group-append">
                    <button class="btn btn-default" @click="applySearch">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Tên danh mục</th>
                    <th>Ghi chú</th>
                    <th>Ngày tạo</th>
                    <th style="width: 150px">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="category in projectCategories.data" :key="category.id">
                    <td>{{ category.name }}</td>
                    <td>{{ category.note || '-' }}</td>
                    <td>{{ formatDate(category.created_at) }}</td>
                    <td>
                      <Link
                        v-if="hasGlobalPermission('project-categories.edit')"
                        :href="route('project-categories.edit', category.id)"
                        class="btn btn-sm btn-info mr-1"
                      >
                        <i class="fas fa-edit"></i>
                      </Link>
                      <button
                        v-if="hasGlobalPermission('project-categories.delete')"
                        class="btn btn-sm btn-danger"
                        @click="confirmDelete(category)"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="projectCategories.data.length === 0">
                    <td colspan="5" class="text-center">Không có dữ liệu</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <Pagination :links="projectCategories.links" />
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
import { formatDate, showConfirm, showSuccess } from '@/utils'
import Pagination from '@/Components/Pagination.vue'
import debounce from 'lodash/debounce'
import { usePermission } from '@/Composables/usePermission'

const props = defineProps({
  projectCategories: Object,
  filters: Object
})

const { can, hasGlobalPermission } = usePermission()

const search = ref(props.filters.search || '')

const debouncedSearch = debounce(() => {
  applySearch()
}, 300)

const applySearch = () => {
  router.get(
    route('project-categories.index'),
    { search: search.value },
    {
      preserveState: true,
      replace: true
    }
  )
}

const confirmDelete = (category) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa danh mục "${category.name}"?`, () => {
    router.delete(route('project-categories.destroy', category.id), {
      onSuccess: () => {
        showSuccess('Danh mục đã được xóa thành công.')
      }
    })
  })
}
</script>
