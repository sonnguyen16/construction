<template>
  <AdminLayout>
    <template #header>Thêm gói thầu mới</template>
    <template #breadcrumb>Thêm gói thầu</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Thông tin gói thầu</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="form-group d-flex gap-2">
                <label>Dự án:</label>
                <p>
                  <strong>{{ project.name }}</strong> ({{ project.code }})
                </p>
              </div>
              <div class="form-group">
                <label for="code">Mã gói thầu <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="code"
                  placeholder="Nhập mã gói thầu"
                  v-model="form.code"
                  :class="{ 'is-invalid': form.errors.code }"
                />
                <div class="invalid-feedback" v-if="form.errors.code">
                  {{ form.errors.code }}
                </div>
              </div>
              <div class="form-group">
                <label for="name">Tên gói thầu <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Nhập tên gói thầu"
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
                <label for="estimated_price">Giá dự toán (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="estimated_price"
                  placeholder="Nhập giá dự toán"
                  v-model="form.estimated_price"
                  :class="{ 'is-invalid': errors.estimated_price }"
                />
                <div class="invalid-feedback" v-if="errors.estimated_price">
                  {{ errors.estimated_price }}
                </div>
              </div>
              <div class="form-group">
                <label for="client_price">Giá giao thầu (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="client_price"
                  placeholder="Nhập giá giao thầu"
                  v-model="form.client_price"
                  :class="{ 'is-invalid': errors.client_price }"
                />
                <div class="invalid-feedback" v-if="errors.client_price">
                  {{ errors.client_price }}
                </div>
                <small class="form-text text-muted">Có thể để trống và cập nhật sau.</small>
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
                  <option value="open">Đang mở thầu</option>
                  <option value="awarded">Đã chọn nhà thầu</option>
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
import { parseCurrency } from '@/utils'

const props = defineProps({
  project: Object,
  statuses: Object,
  errors: Object
})

const form = useForm({
  project_id: props.project ? props.project.id : '',
  code: '',
  name: '',
  description: '',
  estimated_price: '',
  client_price: '',
  profit: '',
  status: 'open'
})

const submit = () => {
  form.profit = form.client_price - form.estimated_price
  form.post(`/projects/${props.project.id}/bid-packages`)
}
</script>
