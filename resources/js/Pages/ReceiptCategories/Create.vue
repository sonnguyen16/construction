<template>
  <AdminLayout>
    <template #header>Thêm loại thu mới</template>
    <template #breadcrumb>Thêm loại thu mới</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Thông tin loại thu</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="form-group">
                <label for="name">Tên loại thu <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Nhập tên loại thu"
                  v-model="form.name"
                  :class="{ 'is-invalid': form.errors.name }"
                />
                <div class="invalid-feedback" v-if="form.errors.name">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="form-group">
                <label for="note">Ghi chú</label>
                <textarea
                  class="form-control"
                  id="note"
                  rows="3"
                  placeholder="Nhập ghi chú"
                  v-model="form.note"
                  :class="{ 'is-invalid': form.errors.note }"
                ></textarea>
                <div class="invalid-feedback" v-if="form.errors.note">
                  {{ form.errors.note }}
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> Lưu
              </button>
              <Link :href="route('receipt-categories.index')" class="btn btn-default ml-2">
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
  note: ''
})

const submit = () => {
  form.post(route('receipt-categories.store'), {
    onSuccess: () => {
      showSuccess('Loại thu đã được tạo thành công.')
    }
  })
}
</script>
