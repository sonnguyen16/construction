<template>
  <AdminLayout>
    <template #header>Chỉnh sửa giá dự thầu</template>
    <template #breadcrumb>Chỉnh sửa giá dự thầu</template>

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
              <div class="form-group d-flex gap-2">
                <label>Nhà thầu:</label>
                <p>
                  <strong>{{ contractor.name }}</strong>
                </p>
              </div>
              <div class="form-group">
                <label for="price">Giá dự thầu (VNĐ) <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="price"
                  placeholder="Nhập giá dự thầu"
                  v-model="form.price"
                  :class="{ 'is-invalid': errors.price }"
                />
                <div class="invalid-feedback" v-if="errors.price">
                  {{ errors.price }}
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
                  :class="{ 'is-invalid': errors.notes }"
                ></textarea>
                <div class="invalid-feedback" v-if="errors.notes">
                  {{ errors.notes }}
                </div>
              </div>
              <div class="form-group" v-if="bid.is_selected">
                <div class="alert alert-success">
                  <i class="fas fa-check-circle mr-2"></i> Nhà thầu này đã được chọn cho gói thầu này.
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> Cập nhật
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
import { parseCurrency } from '@/utils'

const props = defineProps({
  bid: Object,
  bidPackage: Object,
  project: Object,
  contractor: Object,
  errors: Object
})

const form = useForm({
  bid_package_id: props.bid.bid_package_id,
  contractor_id: props.bid.contractor_id,
  price: props.bid.price,
  notes: props.bid.notes
})

const submit = () => {
  form.put(`/bids/${props.bid.id}`)
}
</script>
