<template>
  <AdminLayout>
    <template #header>Thêm nhà thầu mới</template>
    <template #breadcrumb>Thêm nhà thầu</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Thông tin nhà thầu</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="form-group">
                <label for="name">Tên <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Nhập tên nhà thầu"
                  v-model="form.name"
                  :class="{ 'is-invalid': form.errors.name }"
                />
                <div class="invalid-feedback" v-if="form.errors.name">
                  {{ form.errors.name }}
                </div>
              </div>
              <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input
                  type="text"
                  class="form-control"
                  id="phone"
                  placeholder="Nhập số điện thoại"
                  v-model="form.phone"
                  :class="{ 'is-invalid': form.errors.phone }"
                />
                <div class="invalid-feedback" v-if="form.errors.phone">
                  {{ form.errors.phone }}
                </div>
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="email"
                  placeholder="Nhập email"
                  v-model="form.email"
                  :class="{ 'is-invalid': form.errors.email }"
                />
                <div class="invalid-feedback" v-if="form.errors.email">
                  {{ form.errors.email }}
                </div>
              </div>
              <div class="form-group">
                <label for="address">Địa chỉ</label>
                <input
                  type="text"
                  class="form-control"
                  id="address"
                  placeholder="Nhập địa chỉ"
                  v-model="form.address"
                  :class="{
                    'is-invalid': form.errors.address
                  }"
                />
                <div class="invalid-feedback" v-if="form.errors.address">
                  {{ form.errors.address }}
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
              <Link :href="route('contractors.index')" class="btn btn-default ml-2">
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
import { showSuccess } from '@/utils'
const form = useForm({
  name: '',
  phone: '',
  email: '',
  address: '',
  notes: ''
})

const submit = () => {
  form.post(route('contractors.store'), {
    onSuccess: () => {
      showSuccess('Nhà thầu đã được tạo thành công.')
    }
  })
}
</script>
