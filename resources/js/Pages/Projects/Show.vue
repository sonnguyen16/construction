<template>
  <AdminLayout>
    <template #header>Chi tiết dự án</template>
    <template #breadcrumb>Chi tiết dự án</template>

    <!-- Thông tin dự án -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Thông tin dự án</h3>
            <div class="card-tools">
              <Link :href="`/projects/${project.id}/edit`" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Sửa dự án
              </Link>
              <button @click="createReceiptVoucher()" class="btn btn-sm btn-success ml-2">
                <i class="fas fa-money-bill"></i> Tạo phiếu thu
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group d-flex gap-2">
                  <label>Mã dự án:</label>
                  <p>{{ project.code }}</p>
                </div>
                <div class="form-group d-flex gap-2">
                  <label>Tên dự án:</label>
                  <p>{{ project.name }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group d-flex gap-2">
                  <label>Trạng thái:</label>
                  <p>
                    <span :class="getStatusClass(project.status)">
                      {{ getStatusLabel(project.status) }}
                    </span>
                  </p>
                </div>
                <div class="form-group d-flex gap-2">
                  <label>Ngày tạo:</label>
                  <p>{{ formatDate(project.created_at) }}</p>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group d-flex gap-2">
                  <label>Ghi chú:</label>
                  <p>
                    {{ project.description || 'Không có ghi chú' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tổng quan tài chính -->
    <div class="row">
      <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
          <div class="inner">
            <h3>
              {{ formatCurrency(total_client_price || 0) }}
            </h3>
            <p>Tổng giá giao thầu</p>
          </div>
          <div class="icon">
            <i class="fas fa-dollar-sign"></i>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3>
              {{ formatCurrency(total_estimated_price || 0) }}
            </h3>
            <p>Tổng giá dự toán</p>
          </div>
          <div class="icon">
            <i class="fas fa-calculator"></i>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
          <div class="inner">
            <h3>{{ formatCurrency(total_profit || 0) }}</h3>
            <p>Tổng lợi nhuận</p>
          </div>
          <div class="icon">
            <i class="fas fa-chart-line"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Danh sách gói thầu -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Danh sách gói thầu</h3>
            <div class="card-tools">
              <Link :href="route('bid-packages.create', project.id)" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm gói thầu
              </Link>
            </div>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th style="width: 40px"></th>
                  <th>Mã</th>
                  <th>Tên gói thầu</th>
                  <th>Trạng thái</th>
                  <th>Nhà thầu được chọn</th>
                  <th>Giá dự toán</th>
                  <th>Giá giao thầu</th>
                  <th>Lợi nhuận</th>
                  <th style="width: 350px">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="bidPackage in project.bid_packages" :key="bidPackage.id">
                  <!-- Dòng thông tin gói thầu -->
                  <tr>
                    <td>
                      <button @click="toggleBidPackageDetails(bidPackage.id)" class="btn btn-xs btn-default">
                        <i
                          :class="
                            expandedBidPackages.includes(bidPackage.id) ? 'fas fa-chevron-up' : 'fas fa-chevron-down'
                          "
                        ></i>
                      </button>
                    </td>
                    <td>{{ bidPackage.code }}</td>
                    <td>{{ bidPackage.name }}</td>
                    <td>
                      <span :class="getBidPackageStatusClass(bidPackage.status)">
                        {{ getBidPackageStatusLabel(bidPackage.status) }}
                      </span>
                    </td>
                    <td>
                      {{ bidPackage.selected_contractor ? bidPackage.selected_contractor.name : '-' }}
                    </td>
                    <td>
                      {{ bidPackage.estimated_price ? formatCurrency(bidPackage.estimated_price) : '-' }}
                    </td>
                    <td>
                      {{ bidPackage.client_price ? formatCurrency(bidPackage.client_price) : '-' }}
                    </td>
                    <td>
                      <span :class="getProfitClass(bidPackage.profit)">
                        {{ bidPackage.profit ? formatCurrency(bidPackage.profit) : '-' }}
                      </span>
                    </td>
                    <td>
                      <div class="btn-group">
                        <button
                          @click="router.visit(`/bid-packages/${bidPackage.id}/edit`)"
                          class="btn btn-xs btn-primary"
                        >
                          <i class="fas fa-edit me-1 mb-1"></i> Sửa
                        </button>
                        <button @click="openAddBidModal(bidPackage)" class="btn btn-xs btn-success">
                          <i class="fas fa-plus me-1 mb-1"></i> Thêm giá thầu
                        </button>
                        <button @click="confirmDeleteBidPackage(bidPackage)" class="btn btn-xs btn-danger">
                          <i class="fas fa-trash me-1 mb-1"></i> Xóa
                        </button>
                      </div>
                    </td>
                  </tr>

                  <!-- Chi tiết gói thầu (hiển thị khi mở rộng) -->
                  <tr v-if="expandedBidPackages.includes(bidPackage.id)">
                    <td colspan="9" class="p-0">
                      <div class="p-3 bg-light">
                        <div class="row">
                          <div class="col-md-12">
                            <h5 class="mb-2 font-bold text-lg">Chi tiết gói thầu</h5>
                            <p v-if="bidPackage.description">
                              {{ bidPackage.description }}
                            </p>

                            <!-- Thêm thông tin chi tiết về giá ở đây -->
                            <div class="row mt-2">
                              <div class="col-md-4">
                                <div class="info-box bg-light">
                                  <div class="info-box-content">
                                    <span class="info-box-text">Giá dự toán</span>
                                    <span class="info-box-number">{{
                                      bidPackage.estimated_price ? formatCurrency(bidPackage.estimated_price) : '-'
                                    }}</span>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="info-box bg-light">
                                  <div class="info-box-content">
                                    <span class="info-box-text">Giá giao thầu</span>
                                    <span class="info-box-number">{{
                                      bidPackage.client_price ? formatCurrency(bidPackage.client_price) : '-'
                                    }}</span>
                                  </div>
                                </div>
                              </div>
                              <div class="col-md-4">
                                <div class="info-box bg-light">
                                  <div class="info-box-content">
                                    <span class="info-box-text">Lợi nhuận</span>
                                    <span class="info-box-number" :class="getProfitClass(bidPackage.profit)">
                                      {{ bidPackage.profit ? formatCurrency(bidPackage.profit) : '-' }}
                                    </span>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <!-- Phiếu chi đã thanh toán -->
                            <div
                              class="mt-3"
                              v-if="bidPackage.payment_vouchers && bidPackage.payment_vouchers.length > 0"
                            >
                              <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-2 font-bold text-lg">Phiếu chi đã thanh toán</h6>
                                <button @click="createPaymentVoucher(bidPackage)" class="btn btn-xs btn-primary">
                                  <i class="fas fa-plus"></i> Tạo phiếu chi
                                </button>
                              </div>
                              <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                  <thead>
                                    <tr>
                                      <th>Mã phiếu chi</th>
                                      <th>Nhà thầu</th>
                                      <th>Số tiền</th>
                                      <th>Ngày tạo</th>
                                      <th style="width: 150px">Thao tác</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr v-for="voucher in bidPackage.payment_vouchers" :key="voucher.id">
                                      <td>{{ voucher.code }}</td>
                                      <td>{{ voucher.contractor.name }}</td>
                                      <td>{{ formatCurrency(voucher.amount) }}</td>
                                      <td>{{ formatDate(voucher.created_at) }}</td>
                                      <td>
                                        <div class="btn-group">
                                          <Link
                                            :href="route('payment-vouchers.show', voucher.id)"
                                            class="btn btn-xs btn-info"
                                          >
                                            <i class="fas fa-eye"></i> Xem
                                          </Link>
                                          <Link
                                            :href="route('payment-vouchers.edit', voucher.id)"
                                            class="btn btn-xs btn-primary"
                                          >
                                            <i class="fas fa-edit"></i> Sửa
                                          </Link>
                                          <button @click="confirmDeleteVoucher(voucher)" class="btn btn-xs btn-danger">
                                            <i class="fas fa-trash"></i> Xóa
                                          </button>
                                        </div>
                                      </td>
                                    </tr>
                                  </tbody>
                                  <tfoot>
                                    <tr class="bg-light">
                                      <td colspan="2" class="text-right"><strong>Tổng cộng:</strong></td>
                                      <td>
                                        <strong>{{ formatCurrency(getTotalPaymentAmount(bidPackage)) }}</strong>
                                      </td>
                                      <td colspan="2"></td>
                                    </tr>
                                  </tfoot>
                                </table>
                              </div>
                            </div>

                            <!-- Thêm phần hiển thị phiếu thu -->
                            <div
                              class="mt-3"
                              v-if="bidPackage.receipt_vouchers && bidPackage.receipt_vouchers.length > 0"
                            >
                              <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-2 font-bold text-lg">Phiếu thu liên quan</h6>
                                <button @click="createReceiptVoucher(bidPackage)" class="btn btn-xs btn-success">
                                  <i class="fas fa-plus"></i> Tạo phiếu thu
                                </button>
                              </div>
                              <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                  <thead>
                                    <tr>
                                      <th>Mã phiếu thu</th>
                                      <th>Khách hàng</th>
                                      <th>Số tiền</th>
                                      <th>Trạng thái</th>
                                      <th>Ngày tạo</th>
                                      <th style="width: 150px">Thao tác</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr v-for="voucher in bidPackage.receipt_vouchers" :key="voucher.id">
                                      <td>{{ voucher.code }}</td>
                                      <td>{{ voucher.customer.name }}</td>
                                      <td>{{ formatCurrency(voucher.amount) }}</td>
                                      <td>
                                        <span
                                          :class="{
                                            'badge badge-warning': voucher.status === 'pending',
                                            'badge badge-success': voucher.status === 'completed'
                                          }"
                                        >
                                          {{ voucher.status === 'pending' ? 'Chưa thanh toán' : 'Đã thanh toán' }}
                                        </span>
                                      </td>
                                      <td>{{ formatDate(voucher.created_at) }}</td>
                                      <td>
                                        <div class="btn-group">
                                          <Link
                                            :href="route('receipt-vouchers.show', voucher.id)"
                                            class="btn btn-xs btn-info"
                                          >
                                            <i class="fas fa-eye"></i> Xem
                                          </Link>
                                          <Link
                                            :href="route('receipt-vouchers.edit', voucher.id)"
                                            class="btn btn-xs btn-primary"
                                          >
                                            <i class="fas fa-edit"></i> Sửa
                                          </Link>
                                          <button
                                            @click="confirmDeleteReceiptVoucher(voucher)"
                                            class="btn btn-xs btn-danger"
                                          >
                                            <i class="fas fa-trash"></i> Xóa
                                          </button>
                                        </div>
                                      </td>
                                    </tr>
                                  </tbody>
                                  <tfoot>
                                    <tr class="bg-light">
                                      <td colspan="2" class="text-right"><strong>Tổng cộng:</strong></td>
                                      <td>
                                        <strong>{{
                                          formatCurrency(getTotalReceiptAmountForBidPackage(bidPackage))
                                        }}</strong>
                                      </td>
                                      <td colspan="3"></td>
                                    </tr>
                                  </tfoot>
                                </table>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Danh sách giá dự thầu -->
                        <div class="mt-3">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-2 font-bold text-lg">Danh sách giá dự thầu</h6>
                            <button @click="openAddBidModal(bidPackage)" class="btn btn-xs btn-success">
                              <i class="fas fa-plus"></i> Thêm giá thầu
                            </button>
                          </div>
                          <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                              <thead>
                                <tr>
                                  <th>Nhà thầu</th>
                                  <th>Giá dự thầu</th>
                                  <th>Ghi chú</th>
                                  <th style="width: 200px">Thao tác</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="bid in bidPackage.bids"
                                  :key="bid.id"
                                  :class="{
                                    'table-success': bid.is_selected
                                  }"
                                >
                                  <td>
                                    {{ bid.contractor.name }}
                                  </td>
                                  <td>
                                    {{ formatCurrency(bid.price) }}
                                  </td>
                                  <td>
                                    {{ bid.notes || '-' }}
                                  </td>
                                  <td>
                                    <div class="btn-group">
                                      <button
                                        v-if="!bid.is_selected && bidPackage.status === 'open'"
                                        @click="selectContractor(bid)"
                                        class="btn btn-xs btn-success"
                                      >
                                        <i class="fas fa-check"></i> Chọn
                                      </button>
                                      <Link :href="route('bids.edit', bid.id)" class="btn btn-xs btn-primary">
                                        <i class="fas fa-edit"></i> Sửa
                                      </Link>
                                      <button @click="confirmDeleteBid(bid)" class="btn btn-xs btn-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                      </button>
                                    </div>
                                  </td>
                                </tr>
                                <tr v-if="bidPackage.bids.length === 0">
                                  <td colspan="4" class="text-center">Chưa có giá dự thầu nào</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>

                        <!-- Form cập nhật giá giao thầu -->
                        <div class="mt-3" v-if="bidPackage.status === 'awarded' || bidPackage.status === 'completed'">
                          <h6 class="mb-2 font-bold text-lg">Cập nhật giá giao thầu</h6>
                          <form @submit.prevent="updateClientPrice(bidPackage)">
                            <div class="input-group">
                              <input
                                type="text"
                                class="form-control"
                                placeholder="Nhập giá giao thầu"
                                v-model="clientPrices[bidPackage.id]"
                                :class="{ 'is-invalid': bidFormErrors.price }"
                              />
                              <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                  <i class="fas fa-save"></i> Cập nhật
                                </button>
                              </div>
                            </div>
                          </form>
                        </div>
                      </div>
                    </td>
                  </tr>
                </template>
                <tr v-if="project.bid_packages.length === 0">
                  <td colspan="9" class="text-center">Chưa có gói thầu nào</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal xác nhận xóa gói thầu -->
    <div
      class="modal fade"
      id="deleteBidPackageModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="deleteBidPackageModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteBidPackageModalLabel">Xác nhận xóa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Bạn có chắc chắn muốn xóa gói thầu
            <strong>{{ selectedBidPackage?.name }}</strong
            >?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" @click="deleteBidPackage">Xóa</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal xác nhận xóa giá dự thầu -->
    <div
      class="modal fade"
      id="deleteBidModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="deleteBidModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteBidModalLabel">Xác nhận xóa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Bạn có chắc chắn muốn xóa giá dự thầu của nhà thầu
            <strong>{{ selectedBid?.contractor?.name }}</strong
            >?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" @click="deleteBid">Xóa</button>
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

              <!-- InputPicker cho nhà thầu -->
              <div class="form-group">
                <label for="contractor_picker">Tìm kiếm nhà thầu:</label>
                <input
                  type="text"
                  class="form-control"
                  id="contractor_picker"
                  :class="{ 'is-invalid': bidFormErrors.contractor_id }"
                />
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

    <!-- Thêm Modal xác nhận xóa phiếu thu -->
    <div
      class="modal fade"
      id="deleteReceiptVoucherModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="deleteReceiptVoucherModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteReceiptVoucherModalLabel">Xác nhận xóa</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Bạn có chắc chắn muốn xóa phiếu thu
            <strong>{{ selectedReceiptVoucher?.code }}</strong
            >?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-danger" @click="deleteReceiptVoucher">Xóa</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, watch, onBeforeUnmount, nextTick } from 'vue'
import axios from 'axios'
import { formatCurrency, parseCurrency, showConfirm, showSuccess, showError, formatDate, showWarning } from '@/utils'

const props = defineProps({
  project: Object,
  bidPackageStatuses: Object
})

const expandedBidPackages = ref([])
const selectedBidPackage = ref(null)
const selectedBid = ref(null)
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
const selectedReceiptVoucher = ref(null)

// Khởi tạo giá trị cho clientPrices
onMounted(() => {
  props.project.bid_packages.forEach((bidPackage) => {
    clientPrices.value[bidPackage.id] = bidPackage.client_price || ''
  })
})

const getStatusLabel = (status) => {
  const statusMap = {
    active: 'Đang hoạt động',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy'
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    active: 'badge badge-success',
    completed: 'badge badge-info',
    cancelled: 'badge badge-danger'
  }
  return classMap[status] || 'badge badge-secondary'
}

const getBidPackageStatusLabel = (status) => {
  return props.bidPackageStatuses[status] || status
}

const getBidPackageStatusClass = (status) => {
  const classMap = {
    open: 'badge badge-primary',
    awarded: 'badge badge-warning',
    completed: 'badge badge-success',
    cancelled: 'badge badge-danger'
  }
  return classMap[status] || 'badge badge-secondary'
}

const getProfitClass = (profit) => {
  if (profit === null || profit === undefined) return ''
  return profit > 0 ? 'text-success' : profit < 0 ? 'text-danger' : ''
}

const toggleBidPackageDetails = (bidPackageId) => {
  const index = expandedBidPackages.value.indexOf(bidPackageId)
  if (index === -1) {
    expandedBidPackages.value.push(bidPackageId)
  } else {
    expandedBidPackages.value.splice(index, 1)
  }
}

const total_client_price = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => total + parseInt(bidPackage.client_price ?? 0), 0)
})

const total_estimated_price = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => total + parseInt(bidPackage.estimated_price ?? 0), 0)
})

const total_profit = computed(() => {
  return props.project.bid_packages.reduce((total, bidPackage) => total + parseInt(bidPackage.profit ?? 0), 0)
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

const confirmDeleteBid = (bid) => {
  showConfirm(
    'Xác nhận xóa',
    `Bạn có chắc chắn muốn xóa giá dự thầu của nhà thầu "${bid.contractor.name}" không?`,
    'Xóa',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      deleteBid(bid)
    }
  })
}

const deleteBid = (bid) => {
  router.delete(route('bids.destroy', bid.id), {
    onSuccess: () => {
      showSuccess('Giá dự thầu đã được xóa thành công.')
    },
    onError: (errors) => {
      showError('Không thể xóa giá dự thầu. Vui lòng thử lại sau.')
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

const updateClientPrice = (bidPackage) => {
  // Chuyển đổi giá trị từ định dạng tiền tệ sang số
  const clientPrice = parseCurrency(clientPrices.value[bidPackage.id])

  router.patch(
    route('bid-packages.update-client-price', bidPackage.id),
    {
      client_price: clientPrice
    },
    {
      onSuccess: () => {
        showSuccess('Giá giao thầu đã được cập nhật thành công.')
      },
      onError: (errors) => {
        showError('Không thể cập nhật giá giao thầu. Vui lòng thử lại sau.')
      }
    }
  )
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

// Hàm tạo phiếu chi nhanh
const createPaymentVoucher = (bidPackage) => {
  // Nếu gói thầu chưa có nhà thầu được chọn
  if (!bidPackage.selected_contractor_id) {
    showWarning('Gói thầu này chưa có nhà thầu được chọn. Vui lòng chọn nhà thầu trước khi tạo phiếu chi.')
    return
  }

  // Chuyển hướng đến trang tạo phiếu chi với thông tin gói thầu và nhà thầu được chọn
  router.visit('/payment-vouchers/create', {
    data: {
      project_id: props.project.id,
      bid_package_id: bidPackage.id,
      contractor_id: bidPackage.selected_contractor_id
    }
  })
}

// Hàm tính tổng tiền đã chi cho một gói thầu
const getTotalPaymentAmount = (bidPackage) => {
  if (!bidPackage.payment_vouchers || bidPackage.payment_vouchers.length === 0) return 0
  return bidPackage.payment_vouchers.reduce((total, voucher) => total + parseInt(voucher.amount || 0), 0)
}

// Thêm hàm tạo phiếu thu
const createReceiptVoucher = (bidPackage = null) => {
  // Chuyển hướng đến trang tạo phiếu thu với thông tin dự án và gói thầu nếu có
  const data = {
    project_id: props.project.id,
    customer_id: props.project.customer_id
  }

  // Nếu gọi hàm với một gói thầu cụ thể
  if (bidPackage) {
    data.bid_package_id = bidPackage.id
  }

  router.visit('/receipt-vouchers/create', { data })
}

// Thêm hàm xác nhận xóa phiếu thu
const confirmDeleteReceiptVoucher = (voucher) => {
  selectedReceiptVoucher.value = voucher
  showConfirm('Xác nhận xóa', `Bạn có chắc chắn muốn xóa phiếu thu "${voucher.code}" không?`, 'Xóa', 'Hủy').then(
    (result) => {
      if (result.isConfirmed) {
        deleteReceiptVoucher(voucher)
      }
    }
  )
}

// Thêm hàm xóa phiếu thu
const deleteReceiptVoucher = (voucher) => {
  router.delete(route('receipt-vouchers.destroy', voucher.id), {
    onSuccess: () => {
      showSuccess('Phiếu thu đã được xóa thành công.')
    },
    onError: (errors) => {
      showError('Không thể xóa phiếu thu. Vui lòng thử lại sau.')
    }
  })
}

// Thêm hàm tính tổng tiền phiếu thu cho một gói thầu
const getTotalReceiptAmountForBidPackage = (bidPackage) => {
  if (!bidPackage.receipt_vouchers || bidPackage.receipt_vouchers.length === 0) return 0
  return bidPackage.receipt_vouchers.reduce((total, voucher) => total + parseInt(voucher.amount || 0), 0)
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
