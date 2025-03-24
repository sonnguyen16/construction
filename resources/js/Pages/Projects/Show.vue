<template>
  <AdminLayout>
    <template #header>{{ project.name }}</template>
    <template #breadcrumb>Chi tiết dự án</template>
    <!-- Danh sách gói thầu -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách gói thầu</h3>
            <div class="card-tools">
              <button @click="openCreateBidPackageModal" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm gói thầu
              </button>
            </div>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>STT</th>
                  <th>Mã</th>
                  <th>Tên gói thầu</th>
                  <th>Giá dự toán</th>
                  <th>Phát sinh</th>
                  <th>Giá giao thầu</th>
                  <th style="width: 15%">Nhà thầu 1</th>
                  <th style="width: 15%">Nhà thầu 2</th>
                  <th style="width: 15%">Nhà thầu 3</th>
                  <th class="text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(bidPackage, index) in project.bid_packages" :key="bidPackage.id">
                  <td>{{ index + 1 }}</td>
                  <td>{{ bidPackage.code }}</td>
                  <td>{{ bidPackage.name }}</td>
                  <td>{{ formatCurrency(bidPackage.estimated_price || 0) }}</td>
                  <td>
                    <div class="d-flex justify-between">
                      {{ formatCurrency(bidPackage.additional_price || 0) }}
                      <button
                        @click="openAdditionalPriceModal(bidPackage)"
                        class="btn btn-sm btn-primary ml-2"
                        title="Cập nhật giá phát sinh"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                    </div>
                  </td>
                  <td>{{ formatCurrency(bidPackage.client_price || 0) }}</td>
                  <!-- Nhà thầu 1 -->
                  <td>
                    <div v-if="getBidderAtIndex(bidPackage, 0)">
                      <div class="d-flex align-items-start">
                        <input
                          type="radio"
                          style="margin-top: 6px"
                          :name="`bidder_${bidPackage.id}`"
                          :checked="isSelectedContractor(bidPackage, getBidderAtIndex(bidPackage, 0))"
                          @change="selectContractor(getBidderAtIndex(bidPackage, 0))"
                        />
                        <span class="ml-2"
                          >{{ getBidderAtIndex(bidPackage, 0).contractor.name }} -
                          {{ formatCurrency(getBidderAtIndex(bidPackage, 0).price) }}</span
                        >
                      </div>
                    </div>
                    <button v-else @click="openAddBidModal(bidPackage)" class="btn btn-sm btn-success">
                      <i class="fas fa-plus me-1 mb-1"></i> Thêm
                    </button>
                  </td>
                  <!-- Nhà thầu 2 -->
                  <td>
                    <div v-if="getBidderAtIndex(bidPackage, 1)">
                      <div class="d-flex align-items-start">
                        <input
                          style="margin-top: 6px"
                          type="radio"
                          :name="`bidder_${bidPackage.id}`"
                          :checked="isSelectedContractor(bidPackage, getBidderAtIndex(bidPackage, 1))"
                          @change="selectContractor(getBidderAtIndex(bidPackage, 1))"
                        />
                        <span class="ml-2"
                          >{{ getBidderAtIndex(bidPackage, 1).contractor.name }} -
                          {{ formatCurrency(getBidderAtIndex(bidPackage, 1).price) }}</span
                        >
                      </div>
                    </div>
                    <button v-else @click="openAddBidModal(bidPackage)" class="btn btn-sm btn-success">
                      <i class="fas fa-plus me-1 mb-1"></i> Thêm
                    </button>
                  </td>
                  <!-- Nhà thầu 3 -->
                  <td>
                    <div v-if="getBidderAtIndex(bidPackage, 2)">
                      <div class="d-flex align-items-start">
                        <input
                          style="margin-top: 6px"
                          type="radio"
                          :name="`bidder_${bidPackage.id}`"
                          :checked="isSelectedContractor(bidPackage, getBidderAtIndex(bidPackage, 2))"
                          @change="selectContractor(getBidderAtIndex(bidPackage, 2))"
                        />
                        <span class="ml-2"
                          >{{ getBidderAtIndex(bidPackage, 2).contractor.name }} -
                          {{ formatCurrency(getBidderAtIndex(bidPackage, 2).price) }}</span
                        >
                      </div>
                    </div>
                    <button v-else @click="openAddBidModal(bidPackage)" class="btn btn-sm btn-success">
                      <i class="fas fa-plus me-1 mb-1"></i> Thêm
                    </button>
                  </td>
                  <td class="text-center">
                    <div class="btn-group">
                      <button class="btn btn-xs btn-info" @click="openEditBidPackageModal(bidPackage)">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-xs btn-danger" @click="confirmDeleteBidPackage(bidPackage)">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="project.bid_packages.length === 0">
                  <td colspan="10" class="text-center">Chưa có gói thầu nào</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal thêm giá dự thầu -->
    <div
      class="modal fade"
      id="addBidModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="addBidModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addBidModalLabel">Thêm giá dự thầu</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitAddBid">
              <div class="form-group d-flex gap-2">
                <label>Dự án:</label>
                <p>{{ project.name }} ({{ project.code }})</p>
              </div>
              <div class="form-group d-flex gap-2">
                <label>Gói thầu:</label>
                <p>
                  {{ selectedBidPackage?.name }}
                  ({{ selectedBidPackage?.code }})
                </p>
              </div>

              <!-- Select cho nhà thầu -->
              <div class="form-group">
                <label for="contractor_id">Nhà thầu:</label>
                <select
                  class="form-control"
                  id="contractor_id"
                  v-model="bidForm.contractor_id"
                  :class="{ 'is-invalid': bidFormErrors.contractor_id }"
                >
                  <option value="">Chọn nhà thầu</option>
                  <option v-for="contractor in availableContractors" :key="contractor.id" :value="contractor.id">
                    {{ contractor.name }} {{ contractor.phone ? '- ' + contractor.phone : '' }}
                  </option>
                </select>
                <div class="invalid-feedback" v-if="bidFormErrors.contractor_id">
                  {{ bidFormErrors.contractor_id }}
                </div>
              </div>

              <!-- Hiển thị thông tin nhà thầu đã chọn -->
              <div class="form-group bg-light p-3 rounded" v-if="selectedContractor">
                <h6>Thông tin nhà thầu đã chọn</h6>
                <div><strong>Tên:</strong> {{ selectedContractor.name }}</div>
                <div v-if="selectedContractor.phone"><strong>SĐT:</strong> {{ selectedContractor.phone }}</div>
                <div v-if="selectedContractor.email"><strong>Email:</strong> {{ selectedContractor.email }}</div>
                <div v-if="selectedContractor.address"><strong>Địa chỉ:</strong> {{ selectedContractor.address }}</div>
                <div v-if="selectedContractor.notes"><strong>Ghi chú:</strong> {{ selectedContractor.notes }}</div>
              </div>

              <div class="form-group">
                <label for="price">Giá dự thầu (VNĐ) <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="price"
                  placeholder="Nhập giá dự thầu"
                  v-model="bidForm.price"
                  :class="{ 'is-invalid': bidFormErrors.price }"
                />
                <div class="invalid-feedback" v-if="bidFormErrors.price">
                  {{ bidFormErrors.price }}
                </div>
              </div>
              <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea
                  class="form-control"
                  id="notes"
                  rows="3"
                  placeholder="Nhập ghi chú"
                  v-model="bidForm.notes"
                  :class="{ 'is-invalid': bidFormErrors.notes }"
                ></textarea>
                <div class="invalid-feedback" v-if="bidFormErrors.notes">
                  {{ bidFormErrors.notes }}
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="submitAddBid" :disabled="isSubmitting">
              <i class="fas fa-save mr-1"></i> Lưu
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal nhập giá phát sinh -->
    <div
      class="modal fade"
      id="additionalPriceModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="additionalPriceModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="additionalPriceModalLabel">Cập nhật giá phát sinh</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitAdditionalPrice">
              <div class="form-group">
                <label>Gói thầu:</label>
                <p>{{ selectedBidPackage?.name }} ({{ selectedBidPackage?.code }})</p>
              </div>
              <div class="form-group">
                <label for="additional_price">Giá phát sinh (VNĐ) <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="additional_price"
                  placeholder="Nhập giá phát sinh"
                  v-model="additionalPriceForm.additional_price"
                  :class="{ 'is-invalid': additionalPriceFormErrors.additional_price }"
                />
                <div class="invalid-feedback" v-if="additionalPriceFormErrors.additional_price">
                  {{ additionalPriceFormErrors.additional_price }}
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="submitAdditionalPrice" :disabled="isSubmitting">
              <i class="fas fa-save mr-1"></i> Lưu
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Thêm Modal tạo gói thầu mới -->
    <div
      class="modal fade"
      id="createBidPackageModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="createBidPackageModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="createBidPackageModalLabel">Thêm gói thầu mới</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitCreateBidPackage">
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
                  v-model="bidPackageForm.code"
                  :class="{ 'is-invalid': bidPackageFormErrors.code }"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.code">
                  {{ bidPackageFormErrors.code }}
                </div>
              </div>
              <div class="form-group">
                <label for="name">Tên gói thầu <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Nhập tên gói thầu"
                  v-model="bidPackageForm.name"
                  :class="{ 'is-invalid': bidPackageFormErrors.name }"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.name">
                  {{ bidPackageFormErrors.name }}
                </div>
              </div>
              <div class="form-group">
                <label for="estimated_price">Giá dự toán (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="estimated_price"
                  placeholder="Nhập giá dự toán"
                  v-model="bidPackageForm.estimated_price"
                  :class="{ 'is-invalid': bidPackageFormErrors.estimated_price }"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.estimated_price">
                  {{ bidPackageFormErrors.estimated_price }}
                </div>
              </div>
              <div class="form-group">
                <label for="client_price">Giá giao thầu (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="client_price"
                  placeholder="Nhập giá giao thầu"
                  v-model="bidPackageForm.client_price"
                  :class="{ 'is-invalid': bidPackageFormErrors.client_price }"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.client_price">
                  {{ bidPackageFormErrors.client_price }}
                </div>
                <small class="form-text text-muted">Có thể để trống và cập nhật sau.</small>
              </div>
              <div class="form-group">
                <label for="description">Ghi chú</label>
                <textarea
                  class="form-control"
                  id="description"
                  rows="3"
                  placeholder="Nhập ghi chú"
                  v-model="bidPackageForm.description"
                  :class="{ 'is-invalid': bidPackageFormErrors.description }"
                ></textarea>
                <div class="invalid-feedback" v-if="bidPackageFormErrors.description">
                  {{ bidPackageFormErrors.description }}
                </div>
              </div>
              <div class="form-group">
                <label for="status">Trạng thái <span class="text-danger">*</span></label>
                <select
                  class="form-control"
                  id="status"
                  v-model="bidPackageForm.status"
                  :class="{ 'is-invalid': bidPackageFormErrors.status }"
                >
                  <option value="open">Đang mở thầu</option>
                  <option value="awarded">Đã chọn nhà thầu</option>
                  <option value="completed">Hoàn thành</option>
                  <option value="cancelled">Đã hủy</option>
                </select>
                <div class="invalid-feedback" v-if="bidPackageFormErrors.status">
                  {{ bidPackageFormErrors.status }}
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="submitCreateBidPackage" :disabled="isSubmitting">
              <i class="fas fa-save mr-1"></i> Lưu
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal chỉnh sửa gói thầu -->
    <div
      class="modal fade"
      id="editBidPackageModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="editBidPackageModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editBidPackageModalLabel">Chỉnh sửa gói thầu</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitEditBidPackage" v-if="selectedBidPackage">
              <div class="form-group d-flex gap-2">
                <label>Dự án:</label>
                <p>
                  <strong>{{ project.name }}</strong> ({{ project.code }})
                </p>
              </div>
              <div class="form-group">
                <label for="edit_code">Mã gói thầu <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="edit_code"
                  placeholder="Nhập mã gói thầu"
                  v-model="bidPackageForm.code"
                  :class="{ 'is-invalid': bidPackageFormErrors.code }"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.code">
                  {{ bidPackageFormErrors.code }}
                </div>
              </div>
              <div class="form-group">
                <label for="edit_name">Tên gói thầu <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="edit_name"
                  placeholder="Nhập tên gói thầu"
                  v-model="bidPackageForm.name"
                  :class="{ 'is-invalid': bidPackageFormErrors.name }"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.name">
                  {{ bidPackageFormErrors.name }}
                </div>
              </div>
              <div class="form-group">
                <label for="edit_estimated_price">Giá dự toán (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="edit_estimated_price"
                  placeholder="Nhập giá dự toán"
                  v-model="bidPackageForm.estimated_price"
                  :class="{ 'is-invalid': bidPackageFormErrors.estimated_price }"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.estimated_price">
                  {{ bidPackageFormErrors.estimated_price }}
                </div>
              </div>
              <div class="form-group">
                <label for="edit_client_price">Giá giao thầu (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="edit_client_price"
                  placeholder="Nhập giá giao thầu"
                  v-model="bidPackageForm.client_price"
                  :class="{ 'is-invalid': bidPackageFormErrors.client_price }"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.client_price">
                  {{ bidPackageFormErrors.client_price }}
                </div>
              </div>
              <div class="form-group">
                <label for="edit_description">Ghi chú</label>
                <textarea
                  class="form-control"
                  id="edit_description"
                  rows="3"
                  placeholder="Nhập ghi chú"
                  v-model="bidPackageForm.description"
                  :class="{ 'is-invalid': bidPackageFormErrors.description }"
                ></textarea>
                <div class="invalid-feedback" v-if="bidPackageFormErrors.description">
                  {{ bidPackageFormErrors.description }}
                </div>
              </div>
              <div class="form-group">
                <label for="edit_status">Trạng thái <span class="text-danger">*</span></label>
                <select
                  class="form-control"
                  id="edit_status"
                  v-model="bidPackageForm.status"
                  :class="{ 'is-invalid': bidPackageFormErrors.status }"
                >
                  <option value="open">Đang mở thầu</option>
                  <option value="awarded">Đã chọn nhà thầu</option>
                  <option value="completed">Hoàn thành</option>
                  <option value="cancelled">Đã hủy</option>
                </select>
                <div class="invalid-feedback" v-if="bidPackageFormErrors.status">
                  {{ bidPackageFormErrors.status }}
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="submitEditBidPackage" :disabled="isSubmitting">
              <i class="fas fa-save mr-1"></i> Lưu
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'
import { showConfirm, showSuccess, showError, showWarning, formatCurrency, parseCurrency } from '@/utils'

const props = defineProps({
  project: Object,
  bidPackageStatuses: Object
})

const selectedBidPackage = ref(null)
const clientPrices = ref({})
const bidForm = ref({
  contractor_id: '',
  price: '',
  notes: ''
})
const bidFormErrors = ref({})
const isSubmitting = ref(false)
const contractorSearch = ref('')
const contractors = ref([])
const filteredContractors = ref([])
const selectedContractor = ref(null)
const availableContractors = ref([])
let inputpickerInstance = null
const additionalPriceForm = ref({
  additional_price: ''
})
const additionalPriceFormErrors = ref({})
const bidPackageForm = ref({
  code: '',
  name: '',
  description: '',
  estimated_price: '',
  client_price: '',
  status: 'open'
})
const bidPackageFormErrors = ref({})

// Khởi tạo giá trị cho clientPrices
onMounted(() => {
  props.project.bid_packages.forEach((bidPackage) => {
    clientPrices.value[bidPackage.id] = bidPackage.client_price || ''
  })
})

const confirmDeleteBidPackage = (bidPackage) => {
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa gói thầu "${bidPackage.name}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        deleteBidPackage(bidPackage)
      }
    }
  )
}

const deleteBidPackage = (bidPackage) => {
  router.delete(route('bid-packages.destroy', bidPackage.id), {
    onSuccess: () => {
      showSuccess('Gói thầu đã được xóa thành công.')
    },
    onError: (errors) => {
      showError('Không thể xóa gói thầu. Vui lòng thử lại sau.')
    }
  })
}

const selectContractor = (bid) => {
  showConfirm(
    'Xác nhận chọn nhà thầu',
    `Bạn có chắc chắn muốn chọn nhà thầu "${bid.contractor.name}" với giá ${formatCurrency(bid.price)} không?`,
    'Chọn',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      router.post(route('bids.select-contractor', bid.id), {
        onSuccess: () => {
          showSuccess('Đã chọn nhà thầu thành công.')
        },
        onError: (errors) => {
          showError('Không thể chọn nhà thầu. Vui lòng thử lại sau.')
        }
      })
    }
  })
}

// Lấy danh sách nhà thầu khi component được tạo
onMounted(async () => {
  try {
    const response = await axios.get('/api/contractors')
    contractors.value = response.data
    filteredContractors.value = [...contractors.value]
  } catch (error) {
    console.error('Không thể lấy danh sách nhà thầu:', error)
  }
})

// Mở modal thêm giá thầu
const openAddBidModal = async (bidPackage) => {
  selectedBidPackage.value = bidPackage
  bidForm.value = {
    contractor_id: '',
    price: '',
    notes: ''
  }

  bidFormErrors.value = {}
  selectedContractor.value = null
  contractorSearch.value = ''

  // Lọc các nhà thầu đã đặt giá
  const existingBidContractorIds = bidPackage.bids.map((bid) => bid.contractor_id)
  availableContractors.value = contractors.value.filter(
    (contractor) => !existingBidContractorIds.includes(contractor.id)
  )

  window.$('#addBidModal').modal('show')

  // Đợi modal hiển thị xong rồi khởi tạo InputPicker
  await nextTick()

  try {
    // Khởi tạo InputPicker mới
    window.$('#contractor_picker').inputpicker({
      data: availableContractors.value.map((contractor) => ({
        value: contractor.id,
        text: contractor.name,
        phone: contractor.phone || '',
        email: contractor.email || '',
        address: contractor.address || '',
        notes: contractor.notes || ''
      })),
      fields: [
        { name: 'text', text: 'Tên nhà thầu' },
        { name: 'notes', text: 'Ghi chú' }
      ],
      fieldText: 'text',
      fieldValue: 'value',
      filterOpen: true,
      autoOpen: true,
      headShow: true,
      width: '100%',
      selectMode: 'single',
      responsive: true
    })

    // Lưu instance để có thể hủy sau này
    inputpickerInstance = window.$('#contractor_picker')

    // Xử lý sự kiện change
    window.$('#contractor_picker').on('change', function (e) {
      const contractorId = window.$(this).val()
      bidForm.value.contractor_id = contractorId

      if (contractorId) {
        const contractor = availableContractors.value.find((c) => c.id == contractorId)
        if (contractor) {
          selectedContractor.value = contractor
        }
      } else {
        selectedContractor.value = null
      }
    })
  } catch (error) {
    console.error('Lỗi khi khởi tạo InputPicker:', error)
    alert('Có lỗi khi khởi tạo InputPicker. Vui lòng thử lại.')
  }
}

// Gửi form thêm giá thầu
const submitAddBid = async () => {
  if (isSubmitting.value) return

  bidFormErrors.value = {}
  isSubmitting.value = true

  try {
    await router.post(
      route('bids.store', selectedBidPackage.value.id),
      {
        ...bidForm.value
      },
      {
        onSuccess: () => {
          window.$('#addBidModal').modal('hide')
          selectedBidPackage.value = null
          bidForm.value = {
            contractor_id: '',
            price: '',
            notes: ''
          }
          selectedContractor.value = null
          showSuccess('Giá dự thầu đã được thêm thành công.')
        },
        onError: (errors) => {
          bidFormErrors.value = errors
        },
        onFinish: () => {
          isSubmitting.value = false
        }
      }
    )
  } catch (error) {
    console.error('Lỗi khi thêm giá thầu:', error)
    showError('Có lỗi xảy ra khi thêm giá dự thầu. Vui lòng thử lại sau.')
    isSubmitting.value = false
  }
}

// Xử lý khi modal đóng
window.$('#addBidModal').on('hidden.bs.modal', function () {
  // Hủy InputPicker khi modal đóng
  try {
    if (inputpickerInstance) {
      window.$('#contractor_picker').inputpicker('destroy')
      inputpickerInstance = null
    }
  } catch (e) {
    console.log('Không thể hủy InputPicker khi đóng modal:', e)
  }
})

// Xử lý khi component bị hủy
onBeforeUnmount(() => {
  // Hủy sự kiện khi modal đóng
  window.$('#addBidModal').off('hidden.bs.modal')

  // Hủy InputPicker nếu còn tồn tại
  try {
    if (inputpickerInstance) {
      window.$('#contractor_picker').inputpicker('destroy')
      inputpickerInstance = null
    }
  } catch (e) {
    console.log('Không thể hủy InputPicker khi hủy component:', e)
  }
})

// Lấy nhà thầu tại vị trí index cho bảng
const getBidderAtIndex = (bidPackage, index) => {
  if (!bidPackage.bids || bidPackage.bids.length <= index) {
    return null
  }
  return bidPackage.bids[index]
}

// Kiểm tra xem nhà thầu có phải là nhà thầu được chọn không
const isSelectedContractor = (bidPackage, bid) => {
  return bid && bid.is_selected
}

// Mở modal giá phát sinh
const openAdditionalPriceModal = (bidPackage) => {
  selectedBidPackage.value = bidPackage
  additionalPriceForm.value = {
    additional_price: formatCurrency(bidPackage.additional_price || 0)
  }
  additionalPriceFormErrors.value = {}
  window.$('#additionalPriceModal').modal('show')
}

// Gửi form cập nhật giá phát sinh
const submitAdditionalPrice = async () => {
  if (isSubmitting.value) return

  additionalPriceFormErrors.value = {}
  isSubmitting.value = true

  // Chuyển đổi giá phát sinh từ định dạng hiển thị sang số
  const additional_price = parseCurrency(additionalPriceForm.value.additional_price)

  try {
    await router.patch(
      route('bid-packages.update-additional-price', selectedBidPackage.value.id),
      {
        additional_price: additional_price
      },
      {
        onSuccess: () => {
          window.$('#additionalPriceModal').modal('hide')
          selectedBidPackage.value = null
          additionalPriceForm.value = {
            additional_price: ''
          }
          showSuccess('Giá phát sinh đã được cập nhật thành công.')
        },
        onError: (errors) => {
          additionalPriceFormErrors.value = errors
        },
        onFinish: () => {
          isSubmitting.value = false
        }
      }
    )
  } catch (error) {
    console.error('Lỗi khi cập nhật giá phát sinh:', error)
    showError('Có lỗi xảy ra khi cập nhật giá phát sinh. Vui lòng thử lại sau.')
    isSubmitting.value = false
  }
}

// Mở modal tạo gói thầu mới
const openCreateBidPackageModal = () => {
  bidPackageForm.value = {
    code: '',
    name: '',
    description: '',
    estimated_price: '',
    client_price: '',
    status: 'open'
  }
  bidPackageFormErrors.value = {}
  window.$('#createBidPackageModal').modal('show')
}

// Gửi form tạo gói thầu mới
const submitCreateBidPackage = async () => {
  if (isSubmitting.value) return

  bidPackageFormErrors.value = {}
  isSubmitting.value = true

  // Parse các giá trị tiền tệ thành số (đã được nhân với 100 trong parseCurrency)
  const formData = {
    ...bidPackageForm.value,
    estimated_price: parseCurrency(bidPackageForm.value.estimated_price),
    client_price: parseCurrency(bidPackageForm.value.client_price)
  }

  try {
    await router.post(route('bid-packages.store', props.project.id), formData, {
      onSuccess: () => {
        window.$('#createBidPackageModal').modal('hide')
        showSuccess('Gói thầu đã được tạo thành công.')
      },
      onError: (errors) => {
        bidPackageFormErrors.value = errors
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } catch (error) {
    console.error('Lỗi khi tạo gói thầu:', error)
    showError('Có lỗi xảy ra khi tạo gói thầu. Vui lòng thử lại sau.')
    isSubmitting.value = false
  }
}

// Mở modal chỉnh sửa gói thầu
const openEditBidPackageModal = (bidPackage) => {
  selectedBidPackage.value = bidPackage
  bidPackageForm.value = {
    code: bidPackage.code || '',
    name: bidPackage.name || '',
    description: bidPackage.description || '',
    estimated_price: formatCurrency(bidPackage.estimated_price || 0),
    client_price: formatCurrency(bidPackage.client_price || 0),
    status: bidPackage.status || 'open'
  }
  bidPackageFormErrors.value = {}
  window.$('#editBidPackageModal').modal('show')
}

// Gửi form chỉnh sửa gói thầu
const submitEditBidPackage = async () => {
  if (isSubmitting.value || !selectedBidPackage.value) return

  bidPackageFormErrors.value = {}
  isSubmitting.value = true

  // Parse các giá trị tiền tệ thành số
  const formData = {
    project_id: props.project.id,
    ...bidPackageForm.value,
    estimated_price: parseCurrency(bidPackageForm.value.estimated_price),
    client_price: parseCurrency(bidPackageForm.value.client_price)
  }

  try {
    await router.put(route('bid-packages.update', selectedBidPackage.value.id), formData, {
      onSuccess: () => {
        window.$('#editBidPackageModal').modal('hide')
        selectedBidPackage.value = null
        showSuccess('Gói thầu đã được cập nhật thành công.')
      },
      onError: (errors) => {
        bidPackageFormErrors.value = errors
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } catch (error) {
    console.error('Lỗi khi cập nhật gói thầu:', error)
    showError('Có lỗi xảy ra khi cập nhật gói thầu. Vui lòng thử lại sau.')
    isSubmitting.value = false
  }
}
</script>

<style>
.table-responsive {
  overflow-x: auto;
}

.inputpicker-div {
  width: 100% !important;
  max-height: 300px;
  overflow-y: auto;
  z-index: 9999;
}

.inputpicker-arrow {
  top: 50% !important;
  transform: translateY(-50%) !important;
}

.inputpicker-input {
  width: 100% !important;
}

.inputpicker-list-item:hover {
  background-color: #f8f9fa;
}

.inputpicker-wrapped-list {
  border: 1px solid #ccc !important;
}

.inputpicker-active {
  background-color: #007bff !important;
  color: white !important;
}

/* Thêm style cho info-box */
.info-box {
  border-radius: 0.25rem;
  box-shadow: 0 0 1px rgba(0, 0, 0, 0.125), 0 1px 3px rgba(0, 0, 0, 0.2);
  display: block;
  margin-bottom: 20px;
  min-height: 80px;
  padding: 0;
  position: relative;
  width: 100%;
}

.info-box .info-box-content {
  display: flex;
  flex-direction: column;
  padding: 10px;
}

.info-box .info-box-text {
  display: block;
  font-size: 14px;
  margin-bottom: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.info-box .info-box-number {
  display: block;
  font-size: 18px;
  font-weight: bold;
}

/* Đảm bảo các nút trong btn-group có kích thước đồng nhất */
.btn-group .btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
