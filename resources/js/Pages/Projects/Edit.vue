<template>
  <AdminLayout>
    <template #header>Chỉnh sửa dự án</template>
    <template #breadcrumb>Chỉnh sửa dự án</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Thông tin dự án</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="form-group">
                <label for="code">Mã dự án <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="code"
                  placeholder="Nhập mã dự án"
                  v-model="form.code"
                  :class="{ 'is-invalid': form.errors.code }"
                />
                <div class="invalid-feedback" v-if="form.errors.code">
                  {{ form.errors.code }}
                </div>
              </div>
              <div class="form-group">
                <label for="name">Tên dự án <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Nhập tên dự án"
                  v-model="form.name"
                  :class="{ 'is-invalid': form.errors.name }"
                />
                <div class="invalid-feedback" v-if="form.errors.name">
                  {{ form.errors.name }}
                </div>
              </div>
              <div class="form-group">
                <label for="description">Ghi chú</label>
                <textarea
                  class="form-control"
                  id="description"
                  rows="3"
                  placeholder="Nhập ghi chú"
                  v-model="form.description"
                  :class="{
                    'is-invalid': form.errors.description
                  }"
                ></textarea>
                <div class="invalid-feedback" v-if="form.errors.description">
                  {{ form.errors.description }}
                </div>
              </div>
              <div class="form-group">
                <label for="status">Trạng thái <span class="text-danger">*</span></label>
                <select
                  class="form-control"
                  id="status"
                  v-model="form.status"
                  :class="{
                    'is-invalid': form.errors.status
                  }"
                >
                  <option value="active">Đang hoạt động</option>
                  <option value="completed">Hoàn thành</option>
                  <option value="cancelled">Đã hủy</option>
                </select>
                <div class="invalid-feedback" v-if="form.errors.status">
                  {{ form.errors.status }}
                </div>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> Cập nhật
              </button>
              <Link :href="route('projects.index')" class="btn btn-default ml-2">
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
const props = defineProps({
  project: Object
})

const form = useForm({
  code: props.project.code,
  name: props.project.name,
  description: props.project.description || '',
  status: props.project.status,
  _method: 'PUT'
})

const submit = () => {
  form.post(route('projects.update', props.project.id), {
    onSuccess: () => {
      showSuccess('Dự án đã được cập nhật thành công.')
    }
  })
}
</script>
