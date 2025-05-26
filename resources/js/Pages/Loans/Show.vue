<template>
  <AdminLayout>
    <template #header>Chi tiết khoản vay</template>
    <template #breadcrumb>Quản lý khoản vay / Chi tiết khoản vay</template>

    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card card-info card-outline">
            <div class="card-header">
              <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                  <i class="fas fa-money-bill-wave mr-2"></i> Thông tin khoản vay
                </h3>
                <div>
                  <Link :href="route('loans.edit', loan.id)" class="btn btn-warning mr-2">
                    <i class="fas fa-edit mr-1"></i> Chỉnh sửa
                  </Link>
                  <button
                    v-if="loan.status === 'active'"
                    @click="updateStatus('completed')"
                    class="btn btn-success mr-2"
                  >
                    <i class="fas fa-check-circle mr-1"></i> Đánh dấu hoàn thành
                  </button>
                  <button
                    v-if="loan.status === 'completed'"
                    @click="updateStatus('active')"
                    class="btn btn-primary mr-2"
                  >
                    <i class="fas fa-undo mr-1"></i> Đánh dấu đang vay
                  </button>
                  <Link :href="route('loans.index')" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Quay lại
                  </Link>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <div class="info-box-content">
                      <span class="info-box-text text-muted">Tên khoản vay</span>
                      <span class="info-box-number text-bold">{{ loan.name }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <div class="info-box-content">
                      <span class="info-box-text text-muted">Nhà cung cấp</span>
                      <span class="info-box-number text-bold">{{ loan.contractor?.name || 'N/A' }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <div class="info-box-content">
                      <span class="info-box-text text-muted">Dự án</span>
                      <span class="info-box-number text-bold">{{ loan.project?.name || 'Không có' }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <div class="info-box-content">
                      <span class="info-box-text text-muted">Số tiền vay</span>
                      <span class="info-box-number text-bold">{{ formatCurrency(loan.amount) }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <div class="info-box-content">
                      <span class="info-box-text text-muted">Lãi suất</span>
                      <span class="info-box-number text-bold">{{ loan.interest_rate }}% / năm</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <div class="info-box-content">
                      <span class="info-box-text text-muted">Thời gian vay</span>
                      <span class="info-box-number text-bold">
                        {{ formatDate(loan.start_date) }} - {{ formatDate(loan.end_date) }}
                      </span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <div class="info-box-content">
                      <span class="info-box-text text-muted">Trạng thái</span>
                      <span class="info-box-number">
                        <span
                          class="badge"
                          :class="loan.status === 'active' ? 'badge-primary' : 'badge-success'"
                          style="font-size: 14px; padding: 5px 10px;"
                        >
                          {{ loan.status === 'active' ? 'Đang vay' : 'Đã hoàn thành' }}
                        </span>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-box bg-light">
                    <div class="info-box-content">
                      <span class="info-box-text text-muted">File hợp đồng</span>
                      <span class="info-box-number">
                        <a
                          v-if="loan.contract_file"
                          :href="'/storage/' + loan.contract_file"
                          target="_blank"
                          class="btn btn-sm btn-info"
                        >
                          <i class="fas fa-download mr-1"></i> Tải xuống
                        </a>
                        <span v-else class="text-muted">Không có file</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-12">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">Thông tin thêm</h3>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-12">
                          <h5>Ghi chú:</h5>
                          <p>{{ loan.notes || 'Không có ghi chú' }}</p>
                        </div>
                      </div>
                      <hr />
                      <div class="row">
                        <div class="col-md-6">
                          <p><strong>Người tạo:</strong> {{ loan.creator?.name || 'N/A' }}</p>
                          <p><strong>Ngày tạo:</strong> {{ formatDate(loan.created_at) }}</p>
                        </div>
                        <div class="col-md-6">
                          <p><strong>Người cập nhật:</strong> {{ loan.updater?.name || 'N/A' }}</p>
                          <p><strong>Ngày cập nhật:</strong> {{ formatDate(loan.updated_at) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <div class="d-flex justify-content-between">
                <button @click="confirmDelete" class="btn btn-danger">
                  <i class="fas fa-trash mr-1"></i> Xóa khoản vay
                </button>
                <Link :href="route('loans.index')" class="btn btn-secondary">
                  <i class="fas fa-arrow-left mr-1"></i> Quay lại danh sách
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { formatCurrency, formatDate, showConfirm, showSuccess } from '@/utils'

const props = defineProps({
  loan: Object
})

// Cập nhật trạng thái khoản vay
const updateStatus = (status) => {
  const statusText = status === 'active' ? 'đang vay' : 'đã hoàn thành'
  
  showConfirm(
    'Xác nhận thay đổi trạng thái',
    `Bạn có chắc chắn muốn đánh dấu khoản vay này là ${statusText}?`,
    'Xác nhận',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      router.put(route('loans.update-status', props.loan.id), {
        status: status
      }, {
        onSuccess: () => {
          showSuccess(`Đã cập nhật trạng thái khoản vay thành ${statusText}!`)
        }
      })
    }
  })
}

// Xác nhận xóa khoản vay
const confirmDelete = () => {
  showConfirm(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa khoản vay "${props.loan.name}" không?`,
    'Xóa',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('loans.destroy', props.loan.id), {
        onSuccess: () => {
          showSuccess('Xóa khoản vay thành công!')
          router.visit(route('loans.index'))
        }
      })
    }
  })
}
</script>

<style scoped>
.info-box {
  min-height: 100px;
  margin-bottom: 20px;
}
.info-box-content {
  padding: 15px 10px;
  margin-left: 0;
}
.info-box-text {
  display: block;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.info-box-number {
  display: block;
  font-weight: 700;
  font-size: 18px;
}
</style>
