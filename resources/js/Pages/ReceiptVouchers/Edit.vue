<template>
  <AdminLayout>
    <template #header>Chỉnh sửa phiếu thu</template>
    <template #breadcrumb>Chỉnh sửa phiếu thu</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin phiếu thu</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Mã phiếu thu:</label>
                    <input type="text" class="form-control" :value="receiptVoucher.code" disabled />
                  </div>

                  <!-- Select cho khách hàng -->
                  <div class="form-group">
                    <label for="customer_id">Khách hàng <span class="text-danger">*</span></label>
                    <select
                      class="form-control"
                      id="customer_id"
                      v-model="form.customer_id"
                      :class="{ 'is-invalid': form.errors.customer_id }"
                    >
                      <option value="">Chọn khách hàng</option>
                      <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                        {{ customer.name }} {{ customer.phone ? '- ' + customer.phone : '' }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.customer_id">{{ form.errors.customer_id }}</div>
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
                  <div class="form-group">
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
                      placeholder="Nhập mô tả"
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
              <Link :href="route('receipt-vouchers.index')" class="btn btn-secondary ml-2">
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
import { parseCurrency, showSuccess, formatCurrency, formatDate } from '@/utils'
import { computed, watch, onMounted } from 'vue'

const props = defineProps({
  receiptVoucher: Object,
  customers: Array,
  projects: Array,
  bidPackages: Array,
  statuses: Object
})

const form = useForm({
  customer_id: props.receiptVoucher.customer_id || '',
  project_id: props.receiptVoucher.project_id || '',
  bid_package_id: props.receiptVoucher.bid_package_id || '',
  amount: props.receiptVoucher.amount || '',
  status: props.receiptVoucher.status || 'unpaid',
  payment_date: props.receiptVoucher.payment_date || '',
  description: props.receiptVoucher.description || ''
})

// Lọc gói thầu theo dự án đã chọn
const filteredBidPackages = computed(() => {
  if (!form.project_id) return props.bidPackages
  return props.bidPackages.filter(
    (bp) =>
      bp.project_id == form.project_id || bp.project_name === props.projects.find((p) => p.id == form.project_id)?.name
  )
})

// Xử lý khi thay đổi dự án
const onProjectChange = () => {
  // Reset bid_package_id khi thay đổi dự án
  form.bid_package_id = ''
}

// Xử lý khi thay đổi gói thầu
const onBidPackageChange = () => {
  // Tự động điền mô tả nếu thay đổi gói thầu
  if (form.bid_package_id && form.bid_package_id !== props.receiptVoucher.bid_package_id) {
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
  // Chuyển đổi giá trị từ định dạng tiền tệ sang số
  form.amount = parseCurrency(form.amount)

  form.put(route('receipt-vouchers.update', props.receiptVoucher.id), {
    onSuccess: () => {
      showSuccess('Phiếu thu đã được cập nhật thành công.')
    }
  })
}
</script>
