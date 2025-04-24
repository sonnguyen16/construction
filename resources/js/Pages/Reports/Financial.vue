<template>
  <AdminLayout>
    <template #header>Báo cáo thu chi</template>
    <template #breadcrumb>Báo cáo thu chi</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card card-outline card-primary">
          <div class="card-header">
            <div class="card-title">
                {{ selectedProject?.name }}
            </div>
            <div class="card-tools">
               <select
                  style="width: 300px;"
                  id="project"
                  v-model="selectedProjectId"
                  @change="loadReport"
                  class="form-control"
                >
                  <option v-for="project in projects" :key="project.id" :value="project.id">
                    {{ project.name }}
                  </option>
                </select>
            </div>
          </div>
          <div v-if="reportData" class="card-body p-0">
            <div class="d-flex justify-content-between p-3 bg-light">
              <div class="text-center flex-fill">
                <div class="text-muted small">Tổng dự toán</div>
                <div class="h5 mb-0 font-weight-bold">{{ formatCurrency(reportData.totalEstimatedPrice) }}</div>
              </div>
              <div class="text-center flex-fill">
                <div class="text-muted small">Phát sinh</div>
                <div class="h5 mb-0 font-weight-bold">{{ formatCurrency(reportData.totalAdditionalPrice) }}</div>
              </div>
              <div class="text-center flex-fill">
                <div class="text-muted small">Tổng giao thầu</div>
                <div class="h5 mb-0 font-weight-bold">{{ formatCurrency(reportData.totalClientPrice) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="reportData">

      <!-- Bảng thu chi -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Bảng thu chi</h3>
            </div>
            <div class="card-body table-responsive p-0">
              <table class="table table-hover text-nowrap">
                <thead>
                  <tr>
                    <th style="width: 50px" class="text-center">STT</th>
                    <th>Loại thu chi</th>
                    <th class="text-right">Thu</th>
                    <th class="text-right">Chi</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Danh sách thu -->
                  <template v-for="(item, index) in reportData.receiptItems" :key="`receipt-${item.id}`">
                    <tr>
                      <td class="text-center">{{ index + 1 }}</td>
                      <td>{{ item.name }}</td>
                      <td class="text-right">{{ formatCurrency(item.amount) }}</td>
                      <td class="text-right"></td>
                    </tr>
                  </template>

                  <!-- Danh sách chi -->
                  <template v-for="(item, index) in reportData.paymentItems" :key="`payment-${item.id}`">
                    <tr>
                      <td class="text-center">{{ reportData.receiptItems.length + index + 1 }}</td>
                      <td>{{ item.name }}</td>
                      <td class="text-right"></td>
                      <td class="text-right">{{ formatCurrency(item.amount) }}</td>
                    </tr>
                  </template>

                  <!-- Tổng cộng -->
                  <tr class="bg-light font-weight-bold">
                    <td class="text-center" colspan="2">Tổng cộng</td>
                    <td class="text-right">{{ formatCurrency(reportData.totalReceipt) }}</td>
                    <td class="text-right">{{ formatCurrency(reportData.totalPayment) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Tổng kết -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Tổng kết</h3>
            </div>
            <div class="card-body table-responsive p-0">
              <table class="table table-hover text-nowrap">
                <thead>
                  <tr>
                    <th>Thông tin</th>
                    <th class="text-right">Giá trị</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Phải thu (Tổng dự toán - Tổng thu)</td>
                    <td class="text-right font-weight-bold" :class="reportData.receivables >= 0 ? 'text-danger' : 'text-success'">
                      {{ formatCurrency(reportData.receivables) }}
                    </td>
                  </tr>
                  <tr>
                    <td>Phải chi (Tổng giao thầu - Tổng chi)</td>
                    <td class="text-right font-weight-bold" :class="reportData.payables >= 0 ? 'text-danger' : 'text-success'">
                      {{ formatCurrency(reportData.payables) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body text-center py-5">
            <p class="text-muted">Vui lòng chọn dự án để xem báo cáo thu chi</p>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  projects: {
    type: Array,
    default: () => [],
  },
  selectedProject: {
    type: Object,
    default: null,
  },
  reportData: {
    type: Object,
    default: null,
  },
})

const selectedProjectId = ref(props.selectedProject?.id || null)

const loadReport = () => {
  router.get(route('reports.financial'), { project_id: selectedProjectId.value }, {
    preserveState: true,
    preserveScroll: true,
    only: ['reportData', 'selectedProject'],
  })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    maximumFractionDigits: 0,
  }).format(value || 0)
}
</script>
