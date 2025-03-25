<template>
  <AdminLayout>
    <template #header>Thêm dự án mới</template>
    <template #breadcrumb>Thêm dự án</template>

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
                <label for="customer_id">Khách hàng <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="customer_id"
                  placeholder="Chọn khách hàng"
                  data-role="inputpicker"
                  :class="{ 'is-invalid': form.errors.customer_id }"
                />
                <div class="invalid-feedback" v-if="form.errors.customer_id">
                  {{ form.errors.customer_id }}
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
                  :class="{ 'is-invalid': form.errors.status }"
                >
                  <option value="active">Đang hoạt động</option>
                  <option value="completed">Hoàn thành</option>
                  <option value="cancelled">Đã hủy</option>
                </select>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary" :disabled="form.processing">
                <i class="fas fa-save mr-1"></i> Lưu
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
import { onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  customers: Array
})

const form = useForm({
  code: '',
  name: '',
  customer_id: '',
  description: '',
  status: 'active'
})

// InputPicker instances
let customerPicker = null

onMounted(() => {
  // Khởi tạo InputPicker cho khách hàng
  customerPicker = window.$('#customer_id').inputpicker({
    data: props.customers.map((customer) => ({
      value: customer.id,
      text: customer.name,
      phone: customer.phone || '',
      email: customer.email || '',
      address: customer.address || ''
    })),
    fields: [
      { name: 'text', text: 'Tên khách hàng' },
      { name: 'phone', text: 'Số điện thoại' },
      { name: 'email', text: 'Email' },
      { name: 'address', text: 'Địa chỉ' }
    ],
    fieldText: 'text',
    fieldValue: 'value',
    filterOpen: false,
    headShow: true,
    autoOpen: true,
    width: '100%'
  })
})

// Hủy InputPicker khi component unmount
onBeforeUnmount(() => {
  try {
    if (customerPicker) window.$('#customer_id').inputpicker('destroy')
  } catch (e) {
    console.error('Lỗi khi hủy InputPicker:', e)
  }
})

const submit = () => {
  form.customer_id = window.$('#customer_id').val()
  form.post(route('projects.store'), {
    onSuccess: () => {
      showSuccess('Dự án đã được tạo thành công.')
    }
  })
}
</script>
