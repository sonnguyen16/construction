<template>
  <AdminLayout>
    <template #header>Tạo phiếu chi mới</template>
    <template #breadcrumb>Tạo phiếu chi mới</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu chi</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="code">Mã phiếu chi</label>
                    <input
                      type="text"
                      class="form-control"
                      id="code"
                      v-model="form.code"
                      placeholder="Mã sẽ được tạo tự động"
                      disabled
                    />
                  </div>

                  <!-- Select cho nhà thầu -->
                  <div class="form-group">
                    <label for="contractor_id">Nhà thầu <span class="text-danger">*</span></label>
                    <select
                      class="form-control"
                      id="contractor_id"
                      v-model="form.contractor_id"
                      :class="{ 'is-invalid': form.errors.contractor_id }"
                    >
                      <option value="">Chọn nhà thầu</option>
                      <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                        {{ contractor.name }} {{ contractor.phone ? '- ' + contractor.phone : '' }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.contractor_id">{{ form.errors.contractor_id }}</div>
                  </div>

                  <!-- Select cho dự án -->
                  <div class="form-group">
                    <label for="project_id">Dự án</label>
                    <select
                      class="form-control"
                      id="project_id"
                      v-model="form.project_id"
                      :class="{ 'is-invalid': form.errors.project_id }"
                      @change="onProjectChange"
                    >
                      <option value="">Chọn dự án</option>
                      <option v-for="project in projects" :key="project.id" :value="project.id">
                        {{ project.name }} {{ project.code ? '- ' + project.code : '' }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.project_id">{{ form.errors.project_id }}</div>
                  </div>

                  <!-- Select cho gói thầu -->
                  <div class="form-group" v-if="form.project_id">
                    <label for="bid_package_id">Gói thầu</label>
                    <select
                      class="form-control"
                      id="bid_package_id"
                      v-model="form.bid_package_id"
                      :class="{ 'is-invalid': form.errors.bid_package_id }"
                      @change="onBidPackageChange"
                    >
                      <option value="">Chọn gói thầu</option>
                      <option v-for="bidPackage in filteredBidPackages" :key="bidPackage.id" :value="bidPackage.id">
                        {{ bidPackage.code ? bidPackage.code + ' - ' : '' }}{{ bidPackage.name }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.bid_package_id">
                      {{ form.errors.bid_package_id }}
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label for="amount">Số tiền <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="amount"
                      v-model="form.amount"
                      placeholder="Nhập số tiền"
                      v-currency
                      :class="{ 'is-invalid': form.errors.amount }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.amount">{{ form.errors.amount }}</div>
                  </div>

                  <!-- Select cho trạng thái -->
                  <div class="form-group">
                    <label for="status">Trạng thái thanh toán</label>
                    <select
                      class="form-control"
                      id="status"
                      v-model="form.status"
                      :class="{ 'is-invalid': form.errors.status }"
                      @change="onStatusChange"
                    >
                      <option v-for="(text, value) in statuses" :key="value" :value="value">
                        {{ text }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.status">{{ form.errors.status }}</div>
                  </div>

                  <div class="form-group" v-if="form.status === 'paid'">
                    <label for="payment_date">Ngày thanh toán</label>
                    <input
                      type="date"
                      class="form-control"
                      id="payment_date"
                      v-model="form.payment_date"
                      :class="{ 'is-invalid': form.errors.payment_date }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.payment_date">{{ form.errors.payment_date }}</div>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea
                      class="form-control"
                      id="description"
                      v-model="form.description"
                      rows="3"
                      placeholder="Nhập mô tả phiếu chi"
                      :class="{ 'is-invalid': form.errors.description }"
                    ></textarea>
                    <div class="invalid-feedback" v-if="form.errors.description">{{ form.errors.description }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> Lưu
              </button>
              <Link :href="route('payment-vouchers.index')" class="btn btn-secondary ml-2">
                <i class="fas fa-times mr-1"></i> Hủy
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, useForm } from '@inertiajs/vue3'
import { parseCurrency, showSuccess } from '@/utils'
import { computed, onMounted } from 'vue'

const props = defineProps({
  contractors: Array,
  projects: Array,
  bidPackages: Array,
  statuses: Object,
  preselectedContractorId: [String, Number],
  preselectedProjectId: [String, Number],
  preselectedBidPackageId: [String, Number]
})

const form = useForm({
  code: '',
  contractor_id: props.preselectedContractorId || '',
  project_id: props.preselectedProjectId || '',
  bid_package_id: props.preselectedBidPackageId || '',
  amount: '',
  status: 'unpaid',
  payment_date: null,
  description: ''
})

// Lọc gói thầu theo dự án đã chọn
const filteredBidPackages = computed(() => {
  if (!form.project_id) return []
  return props.bidPackages.filter((bp) => bp.project_id == form.project_id)
})

// Xử lý khi thay đổi dự án
const onProjectChange = () => {
  // Reset bid_package_id khi thay đổi dự án
  form.bid_package_id = ''
}

// Xử lý khi thay đổi gói thầu
const onBidPackageChange = () => {
  // Tự động điền mô tả nếu thay đổi gói thầu
  if (form.bid_package_id) {
    const bidPackage = filteredBidPackages.value.find((bp) => bp.id == form.bid_package_id)
    if (bidPackage) {
      form.description = `Thanh toán cho gói thầu ${bidPackage.code || ''} - ${bidPackage.name}`
    }
  }
}

// Xử lý khi thay đổi trạng thái
const onStatusChange = () => {
  // Tự động đặt ngày thanh toán
  if (form.status === 'paid' && !form.payment_date) {
    form.payment_date = new Date().toISOString().substr(0, 10)
  }
}

const submit = () => {
  // Chuyển đổi số tiền từ định dạng tiền tệ sang số
  form.amount = parseCurrency(form.amount)

  form.post(route('payment-vouchers.store'), {
    onSuccess: () => {
      showSuccess('Phiếu chi đã được tạo thành công.')
    }
  })
}
</script>
