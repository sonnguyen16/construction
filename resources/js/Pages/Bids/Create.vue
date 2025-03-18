<template>
  <AdminLayout>
    <template #header>Thêm giá dự thầu</template>
    <template #breadcrumb>Thêm giá dự thầu</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Thông tin giá dự thầu</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="form-group d-flex gap-2">
                <label>Dự án:</label>
                <p>
                  <strong>{{ project.name }}</strong> ({{ project.code }})
                </p>
              </div>
              <div class="form-group d-flex gap-2">
                <label>Gói thầu:</label>
                <p>
                  <strong>{{ bidPackage.name }}</strong> ({{ bidPackage.code }})
                </p>
              </div>
              <div class="form-group">
                <label for="contractor_id">Nhà thầu <span class="text-danger">*</span></label>
                <select
                  class="form-control"
                  id="contractor_id"
                  v-model="form.contractor_id"
                  :class="{
                    'is-invalid': form.errors.contractor_id
                  }"
                >
                  <option value="">-- Chọn nhà thầu --</option>
                  <option v-for="contractor in availableContractors" :key="contractor.id" :value="contractor.id">
                    {{ contractor.name }}
                  </option>
                </select>
                <div class="invalid-feedback" v-if="form.errors.contractor_id">
                  {{ form.errors.contractor_id }}
                </div>
              </div>
              <div class="form-group">
                <label for="price">Giá dự thầu (VNĐ) <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="price"
                  placeholder="Nhập giá dự thầu"
                  v-model="form.price"
                  :class="{ 'is-invalid': form.errors.price }"
                />
                <div class="invalid-feedback" v-if="form.errors.price">
                  {{ form.errors.price }}
                </div>
              </div>
              <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea
                  class="form-control"
                  id="notes"
                  rows="3"
                  placeholder="Nhập ghi chú"
                  v-model="form.notes"
                  :class="{ 'is-invalid': form.errors.notes }"
                ></textarea>
                <div class="invalid-feedback" v-if="form.errors.notes">
                  {{ form.errors.notes }}
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> Lưu
              </button>
              <Link :href="`/projects/${project.id}`" class="btn btn-default ml-2">
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
import { computed } from 'vue'
import { parseCurrency } from '@/utils'

const props = defineProps({
  bidPackage: Object,
  project: Object,
  contractors: Array,
  existingBidContractorIds: Array,
  errors: Object
})

const form = useForm({
  bid_package_id: props.bidPackage.id,
  contractor_id: '',
  price: '',
  notes: ''
})

// Lọc danh sách nhà thầu chưa đặt giá
const availableContractors = computed(() => {
  return props.contractors.filter((contractor) => !props.existingBidContractorIds.includes(contractor.id))
})

const submit = () => {
  form.post('/bids')
}
</script>
