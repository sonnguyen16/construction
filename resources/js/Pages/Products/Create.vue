<template>
  <AdminLayout>
    <template #header>Thêm sản phẩm mới</template>
    <template #breadcrumb>Thêm sản phẩm mới</template>

    <div class="row">
      <div class="col-md-12">
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title">Thông tin sản phẩm</h3>
          </div>
          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <!-- Mã sản phẩm -->
                  <div class="form-group">
                    <label for="code">Mã sản phẩm <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="code"
                      placeholder="Nhập mã sản phẩm"
                      v-model="form.code"
                      :class="{ 'is-invalid': form.errors.code }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.code">
                      {{ form.errors.code }}
                    </div>
                  </div>

                  <!-- Tên sản phẩm -->
                  <div class="form-group">
                    <label for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      id="name"
                      placeholder="Nhập tên sản phẩm"
                      v-model="form.name"
                      :class="{ 'is-invalid': form.errors.name }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.name">
                      {{ form.errors.name }}
                    </div>
                  </div>

                  <!-- Giá nhập -->
                  <div class="form-group">
                    <label for="import_price">Giá nhập <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input
                        type="text"
                        class="form-control"
                        id="import_price"
                        placeholder="Nhập giá nhập"
                        v-model="form.import_price"
                        :class="{ 'is-invalid': form.errors.import_price }"
                        @input="formatNumberInput($event)"
                      />
                      <div class="input-group-append">
                        <span class="input-group-text">VNĐ</span>
                      </div>
                      <div class="invalid-feedback" v-if="form.errors.import_price">
                        {{ form.errors.import_price }}
                      </div>
                    </div>
                  </div>

                  <!-- Giá xuất -->
                  <div class="form-group">
                    <label for="export_price">Giá xuất <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input
                        type="text"
                        class="form-control"
                        id="export_price"
                        placeholder="Nhập giá xuất"
                        v-model="form.export_price"
                        :class="{ 'is-invalid': form.errors.export_price }"
                        @input="formatNumberInput($event)"
                      />
                      <div class="input-group-append">
                        <span class="input-group-text">VNĐ</span>
                      </div>
                      <div class="invalid-feedback" v-if="form.errors.export_price">
                        {{ form.errors.export_price }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <!-- Tồn đầu -->
                  <div class="form-group">
                    <label for="initial_stock">Tồn đầu <span class="text-danger">*</span></label>
                    <input
                      type="number"
                      class="form-control"
                      id="initial_stock"
                      placeholder="Nhập số lượng tồn đầu"
                      v-model="form.initial_stock"
                      min="0"
                      :class="{ 'is-invalid': form.errors.initial_stock }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.initial_stock">
                      {{ form.errors.initial_stock }}
                    </div>
                  </div>

                  <!-- Ngưỡng cảnh báo -->
                  <div class="form-group">
                    <label for="warning_threshold">Ngưỡng cảnh báo <span class="text-danger">*</span></label>
                    <input
                      type="number"
                      class="form-control"
                      id="warning_threshold"
                      placeholder="Nhập ngưỡng cảnh báo"
                      v-model="form.warning_threshold"
                      min="0"
                      :class="{ 'is-invalid': form.errors.warning_threshold }"
                    />
                    <div class="invalid-feedback" v-if="form.errors.warning_threshold">
                      {{ form.errors.warning_threshold }}
                    </div>
                  </div>

                  <!-- Danh mục -->
                  <div class="form-group">
                    <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                    <select
                      class="form-control"
                      id="category_id"
                      v-model="form.category_id"
                      :class="{ 'is-invalid': form.errors.category_id }"
                    >
                      <option value="">-- Chọn danh mục --</option>
                      <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.category_id">
                      {{ form.errors.category_id }}
                    </div>
                  </div>

                  <!-- Đơn vị -->
                  <div class="form-group">
                    <label for="unit_id">Đơn vị <span class="text-danger">*</span></label>
                    <select
                      class="form-control"
                      id="unit_id"
                      v-model="form.unit_id"
                      :class="{ 'is-invalid': form.errors.unit_id }"
                    >
                      <option value="">-- Chọn đơn vị --</option>
                      <option v-for="unit in units" :key="unit.id" :value="unit.id">
                        {{ unit.name }}
                      </option>
                    </select>
                    <div class="invalid-feedback" v-if="form.errors.unit_id">
                      {{ form.errors.unit_id }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Ghi chú -->
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
              <Link :href="route('products.index')" class="btn btn-default ml-2">
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
import { formatNumberInput, parseCurrency } from '@/utils'

const props = defineProps({
  categories: Array,
  units: Array
})

const form = useForm({
  code: '',
  name: '',
  import_price: '',
  export_price: '',
  initial_stock: 0,
  warning_threshold: 0,
  category_id: '',
  unit_id: '',
  notes: ''
})

const submit = () => {
  // Chuyển đổi giá trị từ định dạng tiền tệ sang số
  form.import_price = parseCurrency(form.import_price)
  form.export_price = parseCurrency(form.export_price)

  form.post(route('products.store'), {
    onSuccess: () => {
      showSuccess('Sản phẩm đã được tạo thành công.')
    }
  })
}
</script>
