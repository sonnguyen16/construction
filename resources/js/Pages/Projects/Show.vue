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
            <div class="card-tools d-flex gap-2">
              <div class="btn-group">
                <button
                  @click="toggleViewMode('detailed')"
                  class="btn btn-sm"
                  :class="viewMode === 'detailed' ? 'btn-primary' : 'btn-outline-primary'"
                >
                  <i class="fas fa-th-list"></i> Chi tiết
                </button>
                <button
                  @click="toggleViewMode('summary')"
                  class="btn btn-sm"
                  :class="viewMode === 'summary' ? 'btn-primary' : 'btn-outline-primary'"
                >
                  <i class="fas fa-table"></i> Rút gọn
                </button>
              </div>
              <button @click="openCreateBidPackageModal" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm gói thầu
              </button>
            </div>
          </div>
          <div class="card-body p-0 position-relative" style="overflow: auto; max-height: calc(100vh - 250px)">
            <!-- View rút gọn -->
            <div v-if="viewMode === 'summary'">
              <BidPackageSummaryTable :bid-packages="bidPackages" :is-compact="true" />
            </div>

            <!-- View chi tiết -->
            <div v-else>
              <!-- Header -->
              <div class="grid grid-cols-25 bg-light bid-package-header font-weight-bold py-2">
                <div class="col-span-1 px-2 text-center"><i class="fas fa-sort"></i></div>
                <div class="col-span-1 px-2 text-center"></div>
                <div class="col-span-1 px-2">STT</div>
                <div class="col-span-2 px-2">Mã</div>
                <div class="col-span-3 px-2">Tên gói thầu</div>
                <div class="col-span-3 px-2 text-right">Giá dự toán</div>
                <div class="col-span-3 px-2">Phát sinh</div>
                <div class="col-span-3 px-2 text-right">Giá giao thầu</div>
                <div class="col-span-12 px-2">Danh sách nhà thầu</div>
                <div class="col-span-4 px-2 text-center">Thao tác</div>
              </div>

              <!-- Danh sách gói thầu -->
              <draggable
                v-model="bidPackages"
                v-bind="dragOptions"
                handle=".handle"
                @end="onDragEnd"
                class="bid-packages-list"
              >
                <template #item="{ element: bidPackage, index }">
                  <div>
                    <!-- Dòng gói thầu -->
                    <div
                      :class="[
                        'grid grid-cols-25 gap-1 bid-package-row py-2',
                        { 'text-danger': isPackageLosing(bidPackage) },
                        { expanded: expandedPackages.includes(bidPackage.id) }
                      ]"
                    >
                      <div class="col-span-1 px-2 handle text-center" style="cursor: move">
                        <i class="fas fa-grip-vertical"></i>
                      </div>
                      <div class="col-span-1 px-2 text-center">
                        <button
                          class="btn btn-sm d-flex align-items-center gap-2"
                          @click="togglePackageExpand(bidPackage.id)"
                          :class="expandedPackages.includes(bidPackage.id) ? 'btn-info' : 'btn-secondary'"
                        >
                          <!-- count work items -->
                          <span class="badge badge-secondary">{{ bidPackage.children?.length || 0 }}</span>
                        </button>
                      </div>
                      <div class="col-span-1 px-2">{{ index + 1 }}</div>
                      <div class="col-span-2 px-2">{{ bidPackage.code }}</div>
                      <div class="col-span-3 px-2">{{ bidPackage.name }}</div>
                      <div class="col-span-3 px-2 text-right">
                        {{ formatCurrency(bidPackage.display_estimated_price || 0) }}
                      </div>
                      <div class="col-span-3 px-2">
                        <div class="d-flex justify-between align-items-center">
                          <button
                            v-if="bidPackage.selected_contractor_id"
                            @click="openAdditionalPriceModal(bidPackage)"
                            class="btn btn-sm btn-primary me-2"
                            title="Cập nhật giá phát sinh"
                            :disabled="bidPackage.auto_calculate && !bidPackage.is_work_item"
                          >
                            <i class="fas fa-edit"></i>
                          </button>
                          <button
                            v-else
                            class="btn btn-sm btn-secondary me-2"
                            disabled
                            title="Cần chọn nhà thầu trước khi cập nhật giá phát sinh"
                          >
                            <i class="fas fa-edit"></i>
                          </button>
                          {{ formatCurrency(bidPackage.display_additional_price || 0) }}
                        </div>
                      </div>
                      <div class="col-span-3 px-2 text-right">{{ formatCurrency(bidPackage.display_client_price || 0) }}</div>

                      <!-- Danh sách nhà thầu -->
                      <div class="col-span-12 px-2 bid-contractor-list" :class="{ 'opacity-50': bidPackage.auto_calculate && !bidPackage.is_work_item }">
                        <div v-if="bidPackage.auto_calculate && !bidPackage.is_work_item" class="alert alert-info mb-2">
                          <i class="fas fa-info-circle mr-2"></i> Gói thầu này đang được cấu hình để tính toán tự động từ các gói thầu con. Bạn cần tắt tính năng này để có thể chọn nhà thầu.
                        </div>
                        <div class="bid-contractors-scroll">
                          <div v-if="bidPackage.bids && bidPackage.bids.length > 0" class="bid-contractors">
                            <div v-for="bid in bidPackage.bids" :key="bid.id" class="contractor-item">
                              <div class="contractor-info">
                                <div class="btn-group-vertical">
                                  <input
                                    type="radio"
                                    class="custom-radio"
                                    :name="`bidder_${bidPackage.id}`"
                                    :checked="isSelectedContractor(bidPackage, bid)"
                                    @change="selectContractor(bid)"
                                    :disabled="bidPackage.auto_calculate && !bidPackage.is_work_item"
                                  />
                                  <button
                                    @click="confirmDeleteBid(bid)"
                                    class="btn btn-sm btn-danger"
                                    title="Xóa"
                                    :disabled="bidPackage.auto_calculate && !bidPackage.is_work_item"
                                  >
                                    <i class="fas fa-trash-alt"></i>
                                  </button>
                                  <button
                                    @click="openEditBidModal(bid)"
                                    class="btn btn-sm btn-warning"
                                    title="Sửa giá dự thầu"
                                    :disabled="bidPackage.auto_calculate && !bidPackage.is_work_item"
                                  >
                                    <i class="fas fa-edit"></i>
                                  </button>
                                </div>
                                <div class="contractor-details">
                                  <span class="contractor-price">{{ formatCurrency(bid.price) }}</span>
                                  <span class="contractor-name">{{ bid.contractor.name }}</span>
                                </div>
                              </div>
                            </div>
                            <div class="add-contractor-button">
                              <button
                                @click="openAddBidModal(bidPackage)"
                                class="btn btn-sm btn-success"
                                :disabled="bidPackage.auto_calculate && !bidPackage.is_work_item"
                              >
                                <i class="fas fa-plus me-1"></i> Thêm
                              </button>
                            </div>
                          </div>
                          <button v-else @click="openAddBidModal(bidPackage)" class="btn btn-sm btn-success">
                            <i class="fas fa-plus me-1"></i> Thêm
                          </button>
                        </div>
                      </div>

                      <!-- Thao tác -->
                      <div class="col-span-4 px-2 text-center">
                        <div class="action-buttons">
                          <button
                            class="btn btn-sm btn-info mb-1"
                            @click="openEditBidPackageModal(bidPackage)"
                            title="Sửa"
                          >
                            <i class="fas fa-edit"></i>
                          </button>
                          <button
                            class="btn btn-sm btn-danger mb-1"
                            @click="confirmDeleteBidPackage(bidPackage)"
                            title="Xóa"
                          >
                            <i class="fas fa-trash"></i>
                          </button>
                          <Link
                            :href="route('bid-packages.files', bidPackage.id)"
                            class="btn btn-sm btn-secondary mb-1"
                            title="Files"
                          >
                            <i class="fas fa-file"></i>
                          </Link>
                          <button
                            @click="openCreateWorkItemModal(bidPackage)"
                            class="btn btn-sm btn-success mb-1"
                            title="Thêm hạng mục"
                          >
                            <i class="fas fa-tasks"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Danh sách hạng mục con -->
                    <div
                      v-if="
                        expandedPackages.includes(bidPackage.id) &&
                        bidPackage.children &&
                        bidPackage.children.length > 0
                      "
                      class="work-items-container p-3 bg-light"
                    >
                      <h5 class="mb-3">Danh sách hạng mục của gói thầu {{ bidPackage.name }}</h5>

                      <!-- Hiển thị hạng mục con bằng table thay vì grid -->
                      <div class="table-responsive">
                        <table class="table table-hover">
                          <thead class="bg-light">
                            <tr>
                              <th style="width: 50px" class="text-center">STT</th>
                              <th style="width: 100px">Mã</th>
                              <th style="width: 180px">Tên hạng mục</th>
                              <th style="width: 150px" class="text-right">Giá dự thầu</th>
                              <th style="width: 150px">Phát sinh</th>
                              <th style="width: 150px" class="text-right">Giá giao thầu</th>
                              <th>Danh sách nhà thầu</th>
                              <th style="width: 180px" class="text-center">Thao tác</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr
                              v-for="(workItem, i) in bidPackage.children"
                              :key="workItem.id"
                              :class="{ 'text-danger': isPackageLosing(workItem) }"
                            >
                              <td class="text-center">{{ i + 1 }}</td>
                              <td>{{ workItem.code }}</td>
                              <td>{{ workItem.name }}</td>
                              <td class="text-right">{{ formatCurrency(workItem.estimated_price || 0) }}</td>
                              <td>
                                <div class="d-flex justify-between">
                                  <button
                                    v-if="workItem.selected_contractor_id"
                                    @click="openAdditionalPriceModal(workItem)"
                                    class="btn btn-sm btn-primary me-2"
                                    title="Cập nhật giá phát sinh"
                                  >
                                    <i class="fas fa-edit"></i>
                                  </button>
                                  <button
                                    v-else
                                    class="btn btn-sm btn-secondary me-2"
                                    disabled
                                    title="Cần chọn nhà thầu trước khi cập nhật giá phát sinh"
                                  >
                                    <i class="fas fa-edit"></i>
                                  </button>
                                  {{ formatCurrency(workItem.additional_price || 0) }}
                                </div>
                              </td>
                              <td class="text-right">{{ formatCurrency(workItem.client_price || 0) }}</td>

                              <!-- Danh sách nhà thầu -->
                              <td>
                                <div class="bid-contractors-scroll">
                                  <div v-if="workItem.bids && workItem.bids.length > 0" class="bid-contractors">
                                    <div v-for="bid in workItem.bids" :key="bid.id" class="contractor-item">
                                      <div class="contractor-info">
                                        <div class="btn-group-vertical">
                                          <input
                                            type="radio"
                                            class="custom-radio"
                                            :name="`bidder_${workItem.id}`"
                                            :checked="isSelectedContractor(workItem, bid)"
                                            @change="selectContractor(bid)"
                                          />
                                          <button
                                            @click="confirmDeleteBid(bid)"
                                            class="btn btn-sm btn-danger"
                                            title="Xóa"
                                          >
                                            <i class="fas fa-trash-alt"></i>
                                          </button>
                                          <button
                                            @click="openEditBidModal(bid)"
                                            class="btn btn-sm btn-warning"
                                            title="Sửa giá dự thầu"
                                          >
                                            <i class="fas fa-edit"></i>
                                          </button>
                                        </div>
                                        <div class="contractor-details">
                                          <span class="contractor-price">{{ formatCurrency(bid.price) }}</span>
                                          <span class="contractor-name">{{ bid.contractor.name }}</span>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="add-contractor-button">
                                      <button @click="openAddBidModal(workItem)" class="btn btn-sm btn-success">
                                        <i class="fas fa-plus me-1"></i> Thêm
                                      </button>
                                    </div>
                                  </div>
                                  <button v-else @click="openAddBidModal(workItem)" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus me-1"></i> Thêm
                                  </button>
                                </div>
                              </td>

                              <!-- Thao tác -->
                              <td class="text-center">
                                <div class="action-buttons">
                                  <button
                                    class="btn btn-sm btn-info mb-1"
                                    @click="openEditWorkItemModal(workItem)"
                                    title="Sửa"
                                  >
                                    <i class="fas fa-edit"></i>
                                  </button>
                                  <button
                                    class="btn btn-sm btn-danger mb-1"
                                    @click="confirmDeleteBidPackage(workItem)"
                                    title="Xóa"
                                  >
                                    <i class="fas fa-trash"></i>
                                  </button>
                                  <Link
                                    :href="route('bid-packages.files', workItem.id)"
                                    class="btn btn-sm btn-secondary mb-1"
                                    title="Files"
                                  >
                                    <i class="fas fa-file"></i>
                                  </Link>
                                  <button
                                    v-if="workItem.selected_contractor_id"
                                    @click="goToCreatePaymentVoucher(workItem)"
                                    class="btn btn-sm btn-success mb-1"
                                    title="Tạo phiếu chi"
                                  >
                                    <i class="fas fa-money-bill"></i>
                                  </button>
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <!-- Hiển thị thông báo khi không có hạng mục -->
                    <div v-else-if="expandedPackages.includes(bidPackage.id)" class="work-items-container p-3 bg-light">
                      <p class="text-center mb-2">Chưa có hạng mục nào trong gói thầu này.</p>
                      <div class="text-center">
                        <button @click="openCreateWorkItemModal(bidPackage)" class="btn btn-sm btn-success">
                          <i class="fas fa-plus"></i> Thêm hạng mục mới
                        </button>
                      </div>
                    </div>
                  </div>
                </template>
              </draggable>

              <!-- Message khi không có gói thầu -->
              <div v-if="bidPackages.length === 0" class="text-center p-4">Chưa có gói thầu nào</div>
            </div>

            <!-- Footer với tổng cộng -->
            <div v-if="viewMode === 'detailed'" class="grid grid-cols-25 bg-light font-weight-bold py-2 mt-2 sticky-bottom">
              <div class="col-span-8 text-right px-1">Tổng cộng:</div>
              <div class="col-span-3 text-right px-1">{{ formatCurrency(totalEstimatedPrice) }}</div>
              <div class="col-span-3 text-right px-2">{{ formatCurrency(totalAdditionalPrice) }}</div>
              <div class="col-span-3 text-right px-1">{{ formatCurrency(totalClientPrice) }}</div>
              <div class="col-span-10"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal giá dự thầu (thêm/sửa) -->
    <BidModal
      :project="project"
      :bid-package="bidModalMode === 'add' ? selectedBidPackage : getBidPackageForBid(selectedBid)"
      :bid="selectedBid"
      :contractors="contractors"
      :is-submitting="isSubmitting"
      :is-editing="bidModalMode === 'edit'"
      :modal-id="bidModalMode === 'add' ? 'addBidModal' : 'editBidModal'"
      @submit="handleBidSubmit"
      @update:contractor-id="updateBidContractorId"
      @update:selected-contractor="updateBidSelectedContractor"
      ref="bidModalRef"
    />

    <!-- Modal nhập giá phát sinh -->
    <AdditionalPriceModal
      :bid-package="selectedBidPackage"
      :bid="selectedBid"
      :is-submitting="isSubmitting"
      @submit="handleAdditionalPriceSubmit"
      ref="additionalPriceModalRef"
    />

    <!-- Modal tạo/chỉnh sửa gói thầu -->
    <BidPackageModal
      :project="project"
      :bid-package="selectedBidPackage"
      :is-submitting="isSubmitting"
      :is-editing="isBidPackageEditing"
      modal-id="bidPackageModal"
      @submit="handleBidPackageSubmit"
      ref="bidPackageModalRef"
    />

    <!-- Modal quản lý hạng mục -->
    <BidPackageModal
      :project="project"
      :bid-package="selectedWorkItem"
      :parent-bid-package="selectedBidPackage"
      :is-submitting="isSubmitting"
      :is-editing="isEditingWorkItem"
      :is-work-item="true"
      modal-id="workItemModal"
      @submit="handleWorkItemSubmit"
      ref="workItemModalRef"
    />
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed, onBeforeUnmount, nextTick, watch } from 'vue'
import axios from 'axios'
import draggable from 'vuedraggable'
import { showConfirm, showSuccess, showError, formatCurrency } from '@/utils'
// Import các component modal
import BidModal from './Modals/BidModal.vue'
import BidPackageModal from './Modals/BidPackageModal.vue'
// WorkItemModal đã được gộp vào BidPackageModal
import AdditionalPriceModal from './Modals/AdditionalPriceModal.vue'
import BidPackageSummaryTable from './Components/BidPackageSummaryTable.vue'

const props = defineProps({
  project: Object,
  bidPackageStatuses: Object,
  contractors: Object
})

// Tạo biến để theo dõi các gói thầu có thể kéo thả
const bidPackages = ref(props.project.bid_packages || [])

// Biến để theo dõi chế độ xem (chi tiết hoặc rút gọn)
const viewMode = ref('detailed') // 'detailed' hoặc 'summary'

// Hàm chuyển đổi chế độ xem
const toggleViewMode = (mode) => {
  viewMode.value = mode
}

watch(
  () => props.project,
  (newVal) => {
    bidPackages.value = newVal.bid_packages || []
  }
)

// Theo dõi trạng thái kéo thả
const dragOptions = {
  animation: 200,
  group: 'bid-packages',
  disabled: false,
  ghostClass: 'ghost'
}

// Xử lý khi kết thúc kéo thả
const onDragEnd = () => {
  // Cập nhật thứ tự cho tất cả các gói thầu
  const updatedPackages = bidPackages.value.map((bp, index) => ({
    id: bp.id,
    order: index
  }))

  // Gọi API để cập nhật thứ tự
  axios
    .post(route('bid-packages.update-order'), {
      packages: updatedPackages
    })
    .then((response) => {
      if (response.data.success) {
        // showSuccess('Đã cập nhật thứ tự gói thầu thành công')
      }
    })
    .catch((error) => {
      console.error('Error updating order:', error)
      showError('Có lỗi khi cập nhật thứ tự gói thầu')
    })
}

// Thêm biến để theo dõi các gói thầu đang mở rộng
const expandedPackages = ref([])

// Hàm để mở/đóng một gói thầu
const togglePackageExpand = (packageId) => {
  const index = expandedPackages.value.indexOf(packageId)
  if (index === -1) {
    expandedPackages.value.push(packageId)
  } else {
    expandedPackages.value.splice(index, 1)
  }
}

const selectedBidPackage = ref(null)
const isSubmitting = ref(false)
const selectedContractor = ref(null)
const selectedBid = ref(null)
const editSelectedContractor = ref(null)
const bidModalMode = ref('add')
const bidModalRef = ref(null)
const isEditingWorkItem = ref(false)
const selectedWorkItem = ref(null)

const selectContractor = (bid) => {
  // Lưu trạng thái của radio button hiện tại để khôi phục nếu user hủy
  const currentBidPackage = bidPackages.value.find((bp) => bp.bids.some((b) => b.id === bid.id))
  const currentSelectedBid = currentBidPackage?.bids.find((b) => b.is_selected) || null

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
          console.error('Error selecting contractor:', errors)
          showError('Không thể chọn nhà thầu. Vui lòng thử lại sau.')
          // Khôi phục trạng thái cũ khi có lỗi
          nextTick(() => {
            resetRadioSelection(currentBidPackage, currentSelectedBid)
          })
        }
      })
    } else {
      // Nếu người dùng hủy, khôi phục trạng thái cũ
      nextTick(() => {
        resetRadioSelection(currentBidPackage, currentSelectedBid)
      })
    }
  })
}

// Hàm để reset lại radio button
const resetRadioSelection = (bidPackage, selectedBid) => {
  if (!bidPackage) return

  const radioName = `bidder_${bidPackage.id}`
  const radios = document.getElementsByName(radioName)

  // Bỏ chọn tất cả các radio
  radios.forEach((radio) => {
    radio.checked = false
  })

  // Nếu có nhà thầu được chọn trước đó, đánh dấu lại radio đó
  if (selectedBid) {
    const index = bidPackage.bids.findIndex((b) => b.id === selectedBid.id)
    if (index >= 0 && index < radios.length) {
      radios[index].checked = true
    }
  }
}

// Mở modal thêm giá thầu
const openAddBidModal = (bidPackage) => {
  bidModalMode.value = 'add'
  selectedBidPackage.value = bidPackage
  selectedBid.value = null

  // Đảm bảo các giá trị được làm mới
  if (bidModalRef.value) {
    bidModalRef.value.resetForm()
  }

  // Đảm bảo modal được hiển thị sau khi các giá trị đã được chuẩn bị
  nextTick(() => {
    window.$('#addBidModal').modal('show')
  })
}

// Xử lý khi form giá dự thầu được submit (thêm hoặc sửa)
const handleBidSubmit = (formData) => {
  if (isSubmitting.value) return

  if (bidModalMode.value === 'add') {
    isSubmitting.value = true

    router.post(route('bids.store', selectedBidPackage.value.id), formData, {
      onSuccess: () => {
        window.$('#addBidModal').modal('hide')
        selectedBidPackage.value = null
        selectedContractor.value = null
        showSuccess('Giá dự thầu đã được thêm thành công.')
      },
      onError: (errors) => {
        // Truyền lỗi vào component modal
        if (bidModalRef.value) {
          bidModalRef.value.setErrors(errors)
        }
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } else {
    // Sửa giá dự thầu
    if (!selectedBid.value) return

    isSubmitting.value = true

    router.put(route('bids.update', selectedBid.value.id), formData, {
      onSuccess: () => {
        window.$('#editBidModal').modal('hide')
        selectedBid.value = null
        editSelectedContractor.value = null
        showSuccess('Giá dự thầu đã được cập nhật thành công.')
      },
      onError: (errors) => {
        // Truyền lỗi vào component modal
        if (bidModalRef.value) {
          bidModalRef.value.setErrors(errors)
        }
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  }
}

const confirmDeleteBid = (bid) => {
  showConfirm(
    'Xác nhận xóa giá dự thầu',
    `Bạn có chắc chắn muốn xóa giá dự thầu của nhà thầu "${bid.contractor.name}" với giá ${formatCurrency(
      bid.price
    )} không?`,
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

const openEditBidModal = (bid) => {
  bidModalMode.value = 'edit'
  selectedBid.value = bid

  editSelectedContractor.value = bid.contractor_id
    ? props.contractors.find((c) => c.id == bid.contractor_id)
    : null

  // Đảm bảo modal được hiển thị sau khi các giá trị đã được chuẩn bị
  nextTick(() => {
    window.$('#editBidModal').modal('show')
  })
}

// Kiểm tra xem nhà thầu có phải là nhà thầu được chọn không
const isSelectedContractor = (bidPackage, bid) => {
  return bid && bid.is_selected
}

// Mở modal giá phát sinh
const openAdditionalPriceModal = (bidPackage) => {
  selectedBidPackage.value = bidPackage

  // Truyền trạng thái auto_calculate vào modal
  if (additionalPriceModalRef.value) {
    additionalPriceModalRef.value.isAutoCalculate = bidPackage.auto_calculate && !bidPackage.is_work_item
  }

  window.$('#additionalPriceModal').modal('show')
}

// Xử lý khi form cập nhật giá phát sinh được submit
const handleAdditionalPriceSubmit = (formData) => {
  if (isSubmitting.value) return

  isSubmitting.value = true

  router.patch(
    route('bid-packages.update-additional-price', selectedBidPackage.value.id),
    {
      additional_price: formData.additional_price
    },
    {
      onSuccess: () => {
        window.$('#additionalPriceModal').modal('hide')
        selectedBidPackage.value = null
        showSuccess('Giá phát sinh đã được cập nhật thành công.')
      },
      onError: (errors) => {
        // Truyền lỗi vào component modal
        if (additionalPriceModalRef.value) {
          additionalPriceModalRef.value.setErrors(errors)
        }
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    }
  )
}

// Reference đến component modal giá phát sinh
const additionalPriceModalRef = ref(null)

const isBidPackageEditing = ref(false)

// Reference đến component modal gói thầu
const bidPackageModalRef = ref(null)

// Mở modal tạo gói thầu mới
const openCreateBidPackageModal = () => {
  selectedBidPackage.value = null
  isBidPackageEditing.value = false

  if (bidPackageModalRef.value) {
    bidPackageModalRef.value.resetForm()
  }

  window.$('#bidPackageModal').modal('show')
}

// Mở modal chỉnh sửa gói thầu
const openEditBidPackageModal = (bidPackage) => {
  selectedBidPackage.value = bidPackage
  isBidPackageEditing.value = true
  window.$('#bidPackageModal').modal('show')
}

// Xử lý khi form gói thầu được submit (tạo mới hoặc chỉnh sửa)
const handleBidPackageSubmit = (formData) => {
  if (isSubmitting.value) return

  isSubmitting.value = true

  if (isBidPackageEditing.value && selectedBidPackage.value) {
    // Chỉnh sửa gói thầu
    router.put(route('bid-packages.update', selectedBidPackage.value.id), formData, {
      onSuccess: () => {
        window.$('#bidPackageModal').modal('hide')
        selectedBidPackage.value = null
        showSuccess('Gói thầu đã được cập nhật thành công.')
      },
      onError: (errors) => {
        if (bidPackageModalRef.value) {
          bidPackageModalRef.value.setErrors(errors)
        }
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } else {
    // Tạo gói thầu mới
    let url = route('bid-packages.store', props.project.id)

    router.post(url, formData, {
      onSuccess: () => {
        window.$('#bidPackageModal').modal('hide')
        showSuccess('Gói thầu đã được tạo thành công.')
        router.reload({ preserveState: true })
      },
      onError: (errors) => {
        if (bidPackageModalRef.value) {
          bidPackageModalRef.value.setErrors(errors)
        }
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  }
}

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

// Lấy gói thầu chứa bid
const getBidPackageForBid = (bid) => {
  if (!bid) return null
  return bidPackages.value.find((bp) => bp.bids.some((b) => b.id === bid.id))
}

// Kiểm tra xem gói thầu có bị lỗ không (giá giao thầu > giá dự thầu)
const isPackageLosing = (bidPackage) => {
  const clientPrice = parseInt(bidPackage.client_price) || 0
  const estimatedPrice = parseInt(bidPackage.estimated_price) || 0
  return clientPrice > estimatedPrice
}

// Reference đến component modal hạng mục
const workItemModalRef = ref(null)

// Mở modal thêm hạng mục
const openCreateWorkItemModal = (bidPackage) => {
  // Đảm bảo modal đã được đóng hoàn toàn trước khi mở lại
  if (window.$('#workItemModal').hasClass('show')) {
    window.$('#workItemModal').modal('hide')
    setTimeout(() => {
      openCreateWorkItemModal(bidPackage)
    }, 300)
    return
  }

  selectedBidPackage.value = bidPackage
  selectedWorkItem.value = null
  isEditingWorkItem.value = false

  if (workItemModalRef.value) {
    workItemModalRef.value.resetForm()
  }

  // Đảm bảo modal được hiển thị sau khi các giá trị đã được chuẩn bị
  nextTick(() => {
    window.$('#workItemModal').modal('show')
  })
}

// Mở modal sửa hạng mục
const openEditWorkItemModal = (workItem) => {
  // Đảm bảo modal đã được đóng hoàn toàn trước khi mở lại
  if (window.$('#workItemModal').hasClass('show')) {
    window.$('#workItemModal').modal('hide')
    setTimeout(() => {
      openEditWorkItemModal(workItem)
    }, 300)
    return
  }

  selectedWorkItem.value = workItem
  selectedBidPackage.value = props.project.bid_packages.find((bp) => bp.id === workItem.parent_id)
  isEditingWorkItem.value = true

  if (workItemModalRef.value) {
    workItemModalRef.value.resetForm()
  }

  // Đảm bảo modal được hiển thị sau khi các giá trị đã được chuẩn bị
  nextTick(() => {
    window.$('#workItemModal').modal('show')
  })
}

// Xử lý khi form hạng mục được submit
const handleWorkItemSubmit = (formData) => {
  if (isSubmitting.value) return

  isSubmitting.value = true

  try {
    if (isEditingWorkItem.value) {
      // Cập nhật hạng mục
      router.put(route('bid-packages.update', selectedWorkItem.value.id), formData, {
        onSuccess: () => {
          window.$('#workItemModal').modal('hide')
          selectedWorkItem.value = null
          selectedBidPackage.value = null
          showSuccess('Hạng mục đã được cập nhật thành công.')
        },
        onError: (errors) => {
          // Truyền lỗi vào component modal
          if (workItemModalRef.value) {
            workItemModalRef.value.setErrors(errors)
          }
        },
        onFinish: () => {
          isSubmitting.value = false
        }
      })
    } else {
      // Thêm hạng mục mới
      router.post(route('bid-packages.store', props.project.id), formData, {
        onSuccess: () => {
          window.$('#workItemModal').modal('hide')
          selectedBidPackage.value = null
          showSuccess('Hạng mục đã được tạo thành công.')
        },
        onError: (errors) => {
          // Truyền lỗi vào component modal
          if (workItemModalRef.value) {
            workItemModalRef.value.setErrors(errors)
          }
        },
        onFinish: () => {
          isSubmitting.value = false
        }
      })
    }
  } catch (error) {
    console.error('Lỗi khi xử lý hạng mục:', error)
    showError('Có lỗi xảy ra khi xử lý hạng mục. Vui lòng thử lại sau.')
    isSubmitting.value = false
  }
}

// Chuyển đến trang tạo phiếu chi với thông tin gói thầu
const goToCreatePaymentVoucher = (bidPackage) => {
  router.visit(route('payment-vouchers.create', {
    contractor_id: bidPackage.selected_contractor_id,
    project_id: props.project.id,
    bid_package_id: bidPackage.id,
    redirect_to_expenses: true
  }))
}

// Tính tổng cho các cột
const totalEstimatedPrice = computed(() => {
  return bidPackages.value.reduce((total, bidPackage) => {
    return total + (parseInt(bidPackage.display_estimated_price) || 0)
  }, 0)
})

const totalAdditionalPrice = computed(() => {
  return bidPackages.value.reduce((total, bidPackage) => {
    return total + (parseInt(bidPackage.display_additional_price) || 0)
  }, 0)
})

const totalClientPrice = computed(() => {
  return bidPackages.value.reduce((total, bidPackage) => {
    return total + (parseInt(bidPackage.display_client_price) || 0)
  }, 0)
})

// Xử lý khi component bị hủy
onBeforeUnmount(() => {
  // Hủy sự kiện khi modal đóng
  window.$('#addBidModal').off('hidden.bs.modal')
  window.$('#editBidModal').off('hidden.bs.modal')
  window.$('#workItemModal').off('hidden.bs.modal')
})
</script>

<style scoped>
/* Cải thiện CSS cho header sticky */
.card-body {
  overflow: auto;
  max-height: calc(100vh - 250px);
}

table {
  position: relative;
}

thead.sticky th {
  position: sticky !important;
  top: 0;
  z-index: 1000;
  background-color: #f8f9fa !important;
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.15);
}

.bg-light {
  background-color: #f8f9fa;
}

/* Đảm bảo nội dung bảng không bị header che */
tbody tr:first-child td {
  padding-top: 0.75rem;
}

/* CSS cho phần nút bấm trong bảng */
.btn-group-vertical {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  width: 40px;
  z-index: 1; /* Giảm z-index để không đè lên header sticky */
}

.contractor-info {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  width: 100%;
}

.contractor-details {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.contractor-price {
  font-weight: bold;
  font-size: 1rem;
}

.contractor-name {
  font-size: 0.9rem;
}

/* CSS cho radio button */
.custom-radio {
  width: 20px !important;
  height: 20px !important;
}

/* CSS cho nút thao tác */
.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.action-buttons .btn {
  padding: 0.25rem 0.5rem;
}

/* CSS cho container hạng mục */
.work-items-container {
  width: 100%;
  border-top: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.work-items-row {
  background-color: #f8f9fa;
}

/* CSS cho bảng hạng mục */
.work-items-row table {
  margin-bottom: 0;
}

.work-items-row th {
  background-color: #e9ecef;
  font-weight: bold;
}

.bid-package-header {
  padding: 10px 0;
  margin-bottom: 0;
  border-bottom: 2px solid #dee2e6;
  z-index: 1000;
  position: sticky;
  top: 0;
  background-color: #f8f9fa;
  font-weight: bold;
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.15);
}

.bid-package-row {
  border-bottom: 1px solid #dee2e6;
  align-items: flex-start; /* Đổi từ center để căn trên khi nội dung dài */
  transition: background-color 0.3s;
  padding: 0.75rem 0; /* Tăng padding để có nhiều không gian */
  min-height: 80px; /* Đảm bảo chiều cao tối thiểu */
}

.bid-package-row:hover {
  background-color: #f5f5f5;
}

.work-items-container {
  margin: 0;
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.work-item-header {
  background-color: #e9ecef;
  padding: 8px 0;
  border-radius: 4px;
}

.work-item-row {
  border-bottom: 1px solid #e9ecef;
  align-items: center;
}

.work-item-row:last-child {
  border-bottom: none;
}

.sticky-top {
  position: sticky;
  top: 0;
  z-index: 100;
}

.sticky-bottom {
  position: sticky;
  bottom: 0;
  z-index: 100;
  border-top: 2px solid #dee2e6;
}

/* CSS cho nút sắp xếp */
.handle {
  cursor: move;
  color: #6c757d;
}

.handle:hover {
  color: #007bff;
}

/* Tailwind Grid System với 24 cột */
.grid-cols-25 {
  display: grid;
  grid-template-columns: repeat(33, minmax(0, 1fr));
}

.col-span-1 {
  grid-column: span 1 / span 1;
}

.col-span-2 {
  grid-column: span 2 / span 2;
}

.col-span-3 {
  grid-column: span 3 / span 3;
}

.col-span-4 {
  grid-column: span 4 / span 4;
}

.col-span-8 {
  grid-column: span 8 / span 8;
}

.col-span-10 {
  grid-column: span 10 / span 10;
}

.gap-1 {
  gap: 0.25rem;
}

.px-2 {
  padding-left: 0.5rem;
  padding-right: 0.5rem;
}

.mb-1 {
  margin-bottom: 0.25rem;
}

/* Cải thiện hiển thị các cột */
.bid-package-row {
  border-bottom: 1px solid #dee2e6;
  align-items: center;
  transition: background-color 0.3s;
  padding: 0.5rem 0;
}

.bid-package-row > div {
  overflow: visible; /* Thay vì overflow: hidden */
  white-space: normal; /* Thay vì white-space: nowrap */
  word-break: break-word; /* Cho phép ngắt từ khi cần */
  padding: 8px 0; /* Padding đủ để hiển thị nhiều dòng */
}

/* Cải thiện giao diện Action Buttons */
.action-buttons {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.action-buttons .btn {
  padding: 0.25rem 0.5rem;
  min-width: 32px;
}

/* CSS cho thông tin nhà thầu */
.contractor-info {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  width: 100%;
}

.contractor-details {
  flex: 1;
  min-width: 0;
  width: calc(100% - 50px); /* Trừ đi không gian cho các nút */
}

.contractor-price {
  font-weight: bold;
  display: block;
  width: 100%;
  white-space: normal; /* Cho phép xuống dòng */
  overflow: visible; /* Không ẩn nội dung */
  padding-bottom: 4px;
}

.contractor-name {
  display: block;
  width: 100%;
  white-space: normal; /* Cho phép xuống dòng */
  overflow: visible; /* Không ẩn nội dung */
  word-break: break-word; /* Cho phép ngắt từ khi cần */
}

/* Cải thiện hiển thị hạng mục con */
.work-items-container {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.work-item-header {
  font-weight: bold;
  background-color: #e9ecef;
  padding: 0.5rem;
  border-radius: 4px;
}

.work-item-row {
  padding: 0.5rem;
  border-bottom: 1px solid #eee;
  align-items: center;
}

/* Cải thiện hiển thị các cột */
.bid-package-row {
  border-bottom: 1px solid #dee2e6;
  align-items: center;
  transition: background-color 0.3s;
  padding: 0.5rem 0;
}

.bid-package-row > div {
  overflow: visible; /* Thay vì overflow: hidden */
  white-space: normal; /* Thay vì white-space: nowrap */
  word-break: break-word; /* Cho phép ngắt từ khi cần */
  padding: 8px 0; /* Padding đủ để hiển thị nhiều dòng */
}

/* Thiết lập scroll cho container */
.overflow-auto {
  overflow: auto;
}

/* Cải thiện hiển thị thông tin nhà thầu */
.contractor-info {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  width: 100%;
}

.contractor-details {
  flex: 1;
  min-width: 0;
  width: calc(100% - 50px); /* Trừ đi không gian cho các nút */
}

.contractor-price {
  font-weight: bold;
  display: block;
  width: 100%;
  white-space: normal; /* Cho phép xuống dòng */
  overflow: visible; /* Không ẩn nội dung */
  padding-bottom: 4px;
}

.contractor-name {
  display: block;
  width: 100%;
  white-space: normal; /* Cho phép xuống dòng */
  overflow: visible; /* Không ẩn nội dung */
  word-break: break-word; /* Cho phép ngắt từ khi cần */
}

/* Cải thiện hiển thị cho cột thao tác */
.action-buttons {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 0.25rem;
  width: 100%;
}

.action-buttons .btn {
  padding: 0.25rem 0.5rem;
  min-width: 32px;
  margin-bottom: 0.25rem;
}

/* CSS cho danh sách nhà thầu */
.bid-contractor-list {
  overflow: hidden;
  position: relative;
}

.bid-contractors-scroll {
  width: 100%;
  overflow-x: auto;
  padding-bottom: 10px; /* Tạo không gian cho thanh cuộn */
}

.bid-contractors {
  display: flex;
  flex-direction: row;
  min-width: 100%;
  gap: 10px;
}

.contractor-item {
  min-width: 220px;
  border: 1px solid #e9ecef;
  border-radius: 4px;
  padding: 10px;
  background-color: white;
}

.add-contractor-button {
  display: flex;
  align-items: center;
  padding: 10px;
  min-width: 100px;
}

/* Cập nhật CSS cho contractor-info */
.contractor-info {
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  width: 100%;
}
</style>
