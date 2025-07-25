<template>
  <AdminLayout>
    <template #header>Quản lý nhà thầu</template>
    <template #breadcrumb>Danh sách nhà thầu</template>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách nhà thầu</h3>
            <div class="card-tools">
              <Link
                v-if="hasGlobalPermission('contractors.create')"
                :href="route('contractors.create')"
                class="btn btn-sm btn-primary"
              >
                <i class="fas fa-plus"></i> Thêm nhà thầu mới
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
                    <th>Tên</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th width="15%">Thao tác</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(contractor, index) in contractors.data" :key="contractor.id">
                    <td>{{ getSerialNumber(index) }}</td>
                    <td>{{ contractor.name }}</td>
                    <td>{{ contractor.phone || '-' }}</td>
                    <td>{{ contractor.address || '-' }}</td>
                    <td>
                      <div class="btn-group">
                        <Link
                          v-if="hasGlobalPermission('contractors.edit')"
                          :href="route('contractors.edit', contractor.id)"
                          class="btn btn-sm btn-primary"
                        >
                          <i class="fas fa-edit"></i> Sửa
                        </Link>
                        <button
                          v-if="hasGlobalPermission('contractors.delete')"
                          @click="confirmDelete(contractor)"
                          class="btn btn-sm btn-danger"
                        >
                          <i class="fas fa-trash"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="contractors.data.length === 0">
                    <td colspan="6" class="text-center">Không có dữ liệu</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer clearfix">
            <pagination :links="contractors.links" />
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
            Bạn có chắc chắn muốn xóa nhà thầu
            <strong>{{ selectedContractor?.name }}</strong
            >?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" @click="deleteContractor">Xóa</button>
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

const props = defineProps({
  contractors: Object,
  filters: Object
})

const { can, hasGlobalPermission } = usePermission()

const search = ref(props.filters?.search || '')
const selectedContractor = ref(null)

// Tính số thứ tự dựa trên trang hiện tại và vị trí trong trang
const getSerialNumber = (index) => {
  const currentPage = props.contractors.current_page
  const perPage = props.contractors.per_page
  return (currentPage - 1) * perPage + index + 1
}

const confirmDelete = (contractor) => {
  selectedContractor.value = contractor
  // Sử dụng hàm showConfirm từ utils.js
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa nhà thầu "${contractor.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        deleteContractor()
      }
    }
  )
}

const deleteContractor = () => {
  if (selectedContractor.value) {
    router.delete(route('contractors.destroy', selectedContractor.value.id), {
      onSuccess: () => {
        selectedContractor.value = null
        router.reload({ preserveState: true })
      }
    })
  }
}

// Tìm kiếm nhà thầu
watch(search, (value) => {
  router.get(
    route('contractors.index'),
    { search: value },
    {
      preserveState: true,
      replace: true
    }
  )
})
</script>
