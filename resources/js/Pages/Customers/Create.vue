<template>
  <AdminLayout>
    <template #header>Thêm khách hàng mới</template>
    <template #breadcrumb>Thêm khách hàng mới</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Thông tin khách hàng</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="form-group">
                <label for="name">Tên khách hàng <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Nhập tên khách hàng"
                  v-model="form.name"
                  :class="{ 'is-invalid': form.errors.name }"
                />
                <div class="invalid-feedback" v-if="form.errors.name">
                  {{ form.errors.name }}
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
                <label for="address">Địa chỉ</label>
                <textarea
                  class="form-control"
                  id="address"
                  rows="3"
                  placeholder="Nhập địa chỉ"
                  v-model="form.address"
                  :class="{ 'is-invalid': form.errors.address }"
                ></textarea>
                <div class="invalid-feedback" v-if="form.errors.address">
                  {{ form.errors.address }}
                </div>
              </div>

              <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea
                  class="form-control"
                  id="description"
                  rows="3"
                  placeholder="Nhập mô tả"
                  v-model="form.description"
                  :class="{ 'is-invalid': form.errors.description }"
                ></textarea>
                <div class="invalid-feedback" v-if="form.errors.description">
                  {{ form.errors.description }}
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> Lưu
              </button>
              <Link :href="route('customers.index')" class="btn btn-default ml-2">
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
  email: '',
  phone: '',
  address: '',
  description: ''
})

const submit = () => {
  form.post(route('customers.store'), {
    onSuccess: () => {
      showSuccess('Khách hàng đã được tạo thành công.')
    }
  })
}
</script>
