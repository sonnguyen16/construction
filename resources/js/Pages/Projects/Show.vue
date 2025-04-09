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
              <button @click="openCreateBidPackageModal" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Thêm gói thầu
              </button>
            </div>
          </div>
          <div class="card-body p-0 position-relative" style="overflow: auto; max-height: calc(100vh - 250px)">
            <!-- Header -->
            <div class="grid grid-cols-24 bg-light bid-package-header font-weight-bold py-2">
              <div class="col-span-1 px-2 text-center"><i class="fas fa-sort"></i></div>
              <div class="col-span-1 px-2 text-center"></div>
              <div class="col-span-1 px-2">STT</div>
              <div class="col-span-2 px-2">Mã</div>
              <div class="col-span-3 px-2">Tên gói thầu</div>
              <div class="col-span-3 px-2 text-right">Giá dự thầu</div>
              <div class="col-span-3 px-2">Phát sinh</div>
              <div class="col-span-3 px-2 text-right">Giá giao thầu</div>
              <div class="col-span-12 px-2">Danh sách nhà thầu</div>
              <div class="col-span-3 px-2 text-center">Thao tác</div>
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
                      'grid grid-cols-24 gap-1 bid-package-row py-2',
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
                      {{ formatCurrency(bidPackage.estimated_price || 0) }}
                    </div>
                    <div class="col-span-3 px-2">
                      <div class="d-flex justify-between">
                        <button
                          v-if="bidPackage.selected_contractor_id"
                          @click="openAdditionalPriceModal(bidPackage)"
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
                        {{ formatCurrency(bidPackage.additional_price || 0) }}
                      </div>
                    </div>
                    <div class="col-span-3 px-2 text-right">{{ formatCurrency(bidPackage.client_price || 0) }}</div>

                    <!-- Danh sách nhà thầu -->
                    <div class="col-span-12 px-2 bid-contractor-list">
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
                                />
                                <button @click="confirmDeleteBid(bid)" class="btn btn-sm btn-danger" title="Xóa">
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
                            <button @click="openAddBidModal(bidPackage)" class="btn btn-sm btn-success">
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
                    <div class="col-span-3 px-2 text-center">
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
                      expandedPackages.includes(bidPackage.id) && bidPackage.children && bidPackage.children.length > 0
                    "
                    class="work-items-container p-3 bg-light"
                  >
                    <h5 class="mb-3">Danh sách hạng mục của gói thầu {{ bidPackage.name }}</h5>

                    <!-- Hiển thị hạng mục con bằng table thay vì grid -->
                    <div class="table-responsive">
                      <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                          <tr>
                            <th style="width: 50px" class="text-center">STT</th>
                            <th style="width: 100px">Mã</th>
                            <th style="width: 180px">Tên hạng mục</th>
                            <th style="width: 150px" class="text-right">Giá dự thầu</th>
                            <th style="width: 150px">Phát sinh</th>
                            <th style="width: 150px" class="text-right">Giá giao thầu</th>
                            <th>Danh sách nhà thầu</th>
                            <th style="width: 150px" class="text-center">Thao tác</th>
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
                                  @click="openEditBidPackageModal(workItem)"
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

            <!-- Footer với tổng cộng -->
            <div class="grid grid-cols-24 bg-light font-weight-bold py-2 mt-2 sticky-bottom">
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
                <input
                  type="text"
                  class="form-control"
                  id="contractor_id"
                  placeholder="Nhập tên nhà thầu"
                  v-model="bidForm.contractor_id"
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
                  @input="formatNumberInput($event)"
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
                  @input="formatNumberInput($event)"
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
                <label for="estimated_price">Giá dự thầu (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="estimated_price"
                  placeholder="Nhập giá dự thầu"
                  v-model="bidPackageForm.estimated_price"
                  :class="{ 'is-invalid': bidPackageFormErrors.estimated_price }"
                  @input="formatNumberInput($event)"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.estimated_price">
                  {{ bidPackageFormErrors.estimated_price }}
                </div>
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
                <label for="edit_estimated_price">Giá dự thầu (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="edit_estimated_price"
                  placeholder="Nhập giá dự thầu"
                  v-model="bidPackageForm.estimated_price"
                  :class="{ 'is-invalid': bidPackageFormErrors.estimated_price }"
                  @input="formatNumberInput($event)"
                />
                <div class="invalid-feedback" v-if="bidPackageFormErrors.estimated_price">
                  {{ bidPackageFormErrors.estimated_price }}
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
                <input
                  type="text"
                  class="form-control"
                  id="edit_status"
                  placeholder="Chọn trạng thái"
                  data-role="inputpicker"
                  :class="{ 'is-invalid': bidPackageFormErrors.status }"
                />
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

    <!-- Modal sửa giá dự thầu -->
    <div
      class="modal fade"
      id="editBidModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="editBidModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editBidModalLabel">Sửa giá dự thầu</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitEditBid" v-if="selectedBid">
              <div class="form-group">
                <label>Dự án:</label>
                <p>
                  <strong>{{ project.name }}</strong> ({{ project.code }})
                </p>
              </div>
              <div class="form-group">
                <label>Gói thầu:</label>
                <p>
                  <strong>{{ getBidPackageForBid(selectedBid)?.name }}</strong> ({{
                    getBidPackageForBid(selectedBid)?.code
                  }})
                </p>
              </div>
              <div class="form-group">
                <label>Nhà thầu:</label>
                <input
                  type="text"
                  class="form-control"
                  id="edit_contractor_id"
                  placeholder="Nhập tên nhà thầu"
                  v-model="editBidForm.contractor_id"
                  :class="{ 'is-invalid': editBidFormErrors.contractor_id }"
                />
                <div class="invalid-feedback" v-if="editBidFormErrors.contractor_id">
                  {{ editBidFormErrors.contractor_id }}
                </div>
              </div>

              <!-- Hiển thị thông tin nhà thầu đã chọn -->
              <div class="form-group bg-light p-3 rounded" v-if="editSelectedContractor">
                <h6>Thông tin nhà thầu đã chọn</h6>
                <div><strong>Tên:</strong> {{ editSelectedContractor.name }}</div>
                <div v-if="editSelectedContractor.phone"><strong>SĐT:</strong> {{ editSelectedContractor.phone }}</div>
                <div v-if="editSelectedContractor.email">
                  <strong>Email:</strong> {{ editSelectedContractor.email }}
                </div>
                <div v-if="editSelectedContractor.address">
                  <strong>Địa chỉ:</strong> {{ editSelectedContractor.address }}
                </div>
                <div v-if="editSelectedContractor.notes">
                  <strong>Ghi chú:</strong> {{ editSelectedContractor.notes }}
                </div>
              </div>

              <div class="form-group">
                <label for="edit_price">Giá dự thầu (VNĐ) <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="edit_price"
                  placeholder="Nhập giá dự thầu"
                  v-model="editBidForm.price"
                  :class="{ 'is-invalid': editBidFormErrors.price }"
                  @input="formatNumberInput($event)"
                />
                <div class="invalid-feedback" v-if="editBidFormErrors.price">
                  {{ editBidFormErrors.price }}
                </div>
              </div>
              <div class="form-group">
                <label for="edit_notes">Ghi chú</label>
                <textarea
                  class="form-control"
                  id="edit_notes"
                  rows="3"
                  placeholder="Nhập ghi chú"
                  v-model="editBidForm.notes"
                  :class="{ 'is-invalid': editBidFormErrors.notes }"
                ></textarea>
                <div class="invalid-feedback" v-if="editBidFormErrors.notes">
                  {{ editBidFormErrors.notes }}
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
            <button type="button" class="btn btn-primary" @click="submitEditBid" :disabled="isSubmitting">
              <i class="fas fa-save mr-1"></i> Lưu
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Thêm modal quản lý hạng mục -->
    <div
      class="modal fade"
      id="workItemModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="workItemModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="workItemModalLabel">
              {{ isEditingWorkItem ? 'Sửa hạng mục' : 'Thêm hạng mục mới' }}
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitWorkItem">
              <div class="form-group d-flex gap-2">
                <label>Gói thầu:</label>
                <p>
                  <strong>{{ selectedBidPackage?.name }}</strong> ({{ selectedBidPackage?.code }})
                </p>
              </div>

              <div class="form-group">
                <label for="name">Tên hạng mục <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  id="name"
                  placeholder="Nhập tên hạng mục"
                  v-model="workItemForm.name"
                  :class="{ 'is-invalid': workItemFormErrors.name }"
                />
                <div class="invalid-feedback" v-if="workItemFormErrors.name">
                  {{ workItemFormErrors.name }}
                </div>
              </div>

              <div class="form-group">
                <label for="work_item_contractor_id">Nhà thầu</label>
                <input
                  type="text"
                  class="form-control"
                  id="work_item_contractor_id"
                  placeholder="Chọn nhà thầu"
                  v-model="workItemForm.contractor_id"
                  :class="{ 'is-invalid': workItemFormErrors.contractor_id }"
                />
                <div class="invalid-feedback" v-if="workItemFormErrors.contractor_id">
                  {{ workItemFormErrors.contractor_id }}
                </div>
              </div>

              <div class="form-group">
                <label for="price">Giá hạng mục (VNĐ)</label>
                <input
                  type="text"
                  class="form-control"
                  id="price"
                  placeholder="Nhập giá hạng mục"
                  v-model="workItemForm.price"
                  :class="{ 'is-invalid': workItemFormErrors.price }"
                  @input="formatNumberInput($event)"
                />
                <div class="invalid-feedback" v-if="workItemFormErrors.price">
                  {{ workItemFormErrors.price }}
                </div>
              </div>

              <div class="form-group">
                <label for="status">Trạng thái <span class="text-danger">*</span></label>
                <select
                  class="form-control"
                  id="status"
                  v-model="workItemForm.status"
                  :class="{ 'is-invalid': workItemFormErrors.status }"
                >
                  <option value="pending">Chưa bắt đầu</option>
                  <option value="in_progress">Đang thực hiện</option>
                  <option value="completed">Hoàn thành</option>
                </select>
                <div class="invalid-feedback" v-if="workItemFormErrors.status">
                  {{ workItemFormErrors.status }}
                </div>
              </div>

              <div class="form-group">
                <label for="notes">Ghi chú</label>
                <textarea
                  class="form-control"
                  id="notes"
                  rows="3"
                  placeholder="Nhập ghi chú"
                  v-model="workItemForm.notes"
                  :class="{ 'is-invalid': workItemFormErrors.notes }"
                ></textarea>
                <div class="invalid-feedback" v-if="workItemFormErrors.notes">
                  {{ workItemFormErrors.notes }}
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-primary" @click="submitWorkItem" :disabled="isSubmitting">
              <i class="fas fa-save mr-1"></i> {{ isEditingWorkItem ? 'Cập nhật' : 'Thêm mới' }}
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
import { ref, computed, onMounted, onBeforeUnmount, nextTick, watch } from 'vue'
import axios from 'axios'
import draggable from 'vuedraggable'
import { showConfirm, showSuccess, showError, formatCurrency, parseCurrency, formatNumberInput } from '@/utils'

const props = defineProps({
  project: Object,
  bidPackageStatuses: Object,
  contractors: Object
})

// Tạo biến để theo dõi các gói thầu có thể kéo thả
const bidPackages = ref(props.project.bid_packages || [])

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
  status: 'open'
})
const bidPackageFormErrors = ref({})
const selectedBid = ref(null)
const editBidForm = ref({
  contractor_id: '',
  price: '',
  notes: ''
})
const editBidFormErrors = ref({})
const editSelectedContractor = ref(null)
let editInputpickerInstance = null

// Thêm các biến cho workItem
const workItemForm = ref({
  name: '',
  contractor_id: '',
  price: '',
  notes: '',
  status: 'pending'
})
const workItemFormErrors = ref({})
const isEditingWorkItem = ref(false)
const selectedWorkItem = ref(null)

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

// Lấy danh sách nhà thầu khi component được tạo
onMounted(async () => {
  try {
    filteredContractors.value = [...props.contractors]
  } catch (error) {
    console.error('Không thể khởi tạo dữ liệu:', error)
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

  // Sử dụng tất cả nhà thầu, không cần lọc
  availableContractors.value = [...props.contractors]

  window.$('#addBidModal').modal('show')

  // Đợi modal hiển thị xong rồi khởi tạo InputPicker
  await nextTick()

  try {
    // Khởi tạo InputPicker mới
    window.$('#contractor_id').inputpicker({
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
      filterOpen: false,
      autoOpen: true,
      headShow: true,
      width: '100%',
      selectMode: 'single',
      responsive: true
    })

    // Lưu instance để có thể hủy sau này
    inputpickerInstance = window.$('#contractor_id')

    // Xử lý sự kiện change
    window.$('#contractor_id').on('change', function (e) {
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
        ...bidForm.value,
        price: parseCurrency(bidForm.value.price)
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
      window.$('#contractor_id').inputpicker('destroy')
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
  window.$('#editBidModal').off('hidden.bs.modal')
  window.$('#workItemModal').off('hidden.bs.modal')

  // Hủy InputPicker nếu còn tồn tại
  try {
    if (inputpickerInstance) {
      window.$('#contractor_id').inputpicker('destroy')
      window.$('#contractor_id').off('change')
      inputpickerInstance = null
    }

    if (editInputpickerInstance) {
      window.$('#edit_contractor_id').inputpicker('destroy')
      window.$('#edit_contractor_id').off('change')
      editInputpickerInstance = null
    }

    window.$('#work_item_contractor_id').inputpicker('destroy')
    window.$('#work_item_contractor_id').off('change')
  } catch (e) {
    console.log('Không thể hủy InputPicker khi hủy component:', e)
  }
})

// Lấy danh sách nhà thầu khi component được tạo
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

  // Parse các giá trị tiền tệ thành số
  const formData = {
    ...bidPackageForm.value,
    estimated_price: parseCurrency(bidPackageForm.value.estimated_price),
    client_price: parseCurrency(bidPackageForm.value.client_price)
  }

  try {
    let url =
      formData.is_work_item && selectedBidPackage.value
        ? route('bid-packages.work-items.store', selectedBidPackage.value.id)
        : route('bid-packages.store', props.project.id)

    await router.post(url, formData, {
      onSuccess: () => {
        window.$('#createBidPackageModal').modal('hide')
        showSuccess(formData.is_work_item ? 'Hạng mục đã được tạo thành công.' : 'Gói thầu đã được tạo thành công.')
        router.reload({ preserveState: true })
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

// Tính tổng cho các cột
const totalEstimatedPrice = computed(() => {
  return bidPackages.value.reduce((total, bidPackage) => {
    return total + (parseInt(bidPackage.estimated_price) || 0)
  }, 0)
})

const totalAdditionalPrice = computed(() => {
  return bidPackages.value.reduce((total, bidPackage) => {
    return total + (parseInt(bidPackage.additional_price) || 0)
  }, 0)
})

const totalClientPrice = computed(() => {
  return bidPackages.value.reduce((total, bidPackage) => {
    return total + (parseInt(bidPackage.client_price) || 0)
  }, 0)
})

// Lấy gói thầu chứa bid
const getBidPackageForBid = (bid) => {
  if (!bid) return null
  return bidPackages.value.find((bp) => bp.bids.some((b) => b.id === bid.id))
}

// Mở modal sửa giá dự thầu
const openEditBidModal = async (bid) => {
  selectedBid.value = bid
  editBidForm.value = {
    contractor_id: bid.contractor_id,
    price: formatCurrency(bid.price || 0),
    notes: bid.notes || ''
  }
  editBidFormErrors.value = {}
  editSelectedContractor.value = bid.contractor

  try {
    const response = await axios.get('/api/contractors')
    contractors.value = response.data
  } catch (error) {
    console.error('Không thể lấy danh sách nhà thầu:', error)
  }

  window.$('#editBidModal').modal('show')

  // Đợi modal hiển thị xong rồi khởi tạo InputPicker
  await nextTick()

  try {
    // Khởi tạo InputPicker mới
    window.$('#edit_contractor_id').inputpicker({
      data: contractors.value.map((contractor) => ({
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
      filterOpen: false,
      autoOpen: true,
      headShow: true,
      width: '100%',
      selectMode: 'single',
      responsive: true,
      selectedValue: bid.contractor_id
    })

    // Lưu instance để có thể hủy sau này
    editInputpickerInstance = window.$('#edit_contractor_id')

    // Xử lý sự kiện change
    window.$('#edit_contractor_id').on('change', function (e) {
      const contractorId = window.$(this).val()
      editBidForm.value.contractor_id = contractorId

      if (contractorId) {
        const contractor = contractors.value.find((c) => c.id == contractorId)
        if (contractor) {
          editSelectedContractor.value = contractor
        }
      } else {
        editSelectedContractor.value = null
      }
    })
  } catch (error) {
    console.error('Lỗi khi khởi tạo InputPicker cho sửa nhà thầu:', error)
  }
}

// Gửi form sửa giá dự thầu
const submitEditBid = async () => {
  if (isSubmitting.value || !selectedBid.value) return

  editBidFormErrors.value = {}
  isSubmitting.value = true

  // Parse giá trị tiền tệ thành số
  const formData = {
    ...editBidForm.value,
    price: parseCurrency(editBidForm.value.price)
  }

  try {
    await router.put(route('bids.update', selectedBid.value.id), formData, {
      onSuccess: () => {
        window.$('#editBidModal').modal('hide')
        selectedBid.value = null
        editSelectedContractor.value = null
        showSuccess('Giá dự thầu đã được cập nhật thành công.')
      },
      onError: (errors) => {
        editBidFormErrors.value = errors
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } catch (error) {
    console.error('Lỗi khi cập nhật giá dự thầu:', error)
    showError('Có lỗi xảy ra khi cập nhật giá dự thầu. Vui lòng thử lại sau.')
    isSubmitting.value = false
  }
}

// Kiểm tra xem gói thầu có bị lỗ không (giá giao thầu > giá dự thầu)
const isPackageLosing = (bidPackage) => {
  const clientPrice = parseInt(bidPackage.client_price) || 0
  const estimatedPrice = parseInt(bidPackage.estimated_price) || 0
  return clientPrice > estimatedPrice
}

// Mở modal thêm hạng mục
const openCreateWorkItemModal = (bidPackage) => {
  selectedBidPackage.value = bidPackage
  bidPackageForm.value = {
    code: '',
    name: '',
    description: '',
    estimated_price: '',
    status: 'open',
    is_work_item: true
  }
  bidPackageFormErrors.value = {}
  window.$('#createBidPackageModal').modal('show')
}

// Mở modal sửa hạng mục
const openEditWorkItemModal = async (workItem) => {
  selectedWorkItem.value = workItem
  selectedBidPackage.value = props.project.bid_packages.find((bp) => bp.id === workItem.bid_package_id)
  workItemForm.value = {
    name: workItem.name,
    contractor_id: workItem.contractor_id || '',
    price: workItem.price ? formatCurrency(workItem.price) : '',
    notes: workItem.notes || '',
    status: workItem.status
  }
  workItemFormErrors.value = {}
  isEditingWorkItem.value = true
  window.$('#workItemModal').modal('show')

  // Đợi modal hiển thị xong rồi khởi tạo InputPicker
  await nextTick()

  try {
    // Lấy danh sách nhà thầu nếu chưa có
    if (contractors.value.length === 0) {
      const response = await axios.get('/api/contractors')
      contractors.value = response.data
    }

    // Khởi tạo InputPicker mới
    window.$('#work_item_contractor_id').inputpicker({
      data: contractors.value.map((contractor) => ({
        value: contractor.id,
        text: contractor.name,
        phone: contractor.phone || '',
        email: contractor.email || '',
        address: contractor.address || '',
        notes: contractor.notes || ''
      })),
      fields: [
        { name: 'text', text: 'Tên nhà thầu' },
        { name: 'phone', text: 'SĐT' }
      ],
      fieldText: 'text',
      fieldValue: 'value',
      filterOpen: true,
      autoOpen: true,
      headShow: true,
      width: '100%',
      selectMode: 'single',
      responsive: true,
      selectedValue: workItemForm.value.contractor_id
    })

    // Xử lý sự kiện change
    window.$('#work_item_contractor_id').on('change', function (e) {
      const contractorId = window.$(this).val()
      workItemForm.value.contractor_id = contractorId
    })
  } catch (error) {
    console.error('Lỗi khi khởi tạo InputPicker:', error)
  }
}

// Gửi form thêm/sửa hạng mục
const submitWorkItem = async () => {
  if (isSubmitting.value) return

  workItemFormErrors.value = {}
  isSubmitting.value = true

  try {
    if (isEditingWorkItem.value) {
      // Cập nhật hạng mục
      await router.put(
        route('work-items.update', selectedWorkItem.value.id),
        {
          ...workItemForm.value,
          price: parseCurrency(workItemForm.value.price)
        },
        {
          onSuccess: () => {
            window.$('#workItemModal').modal('hide')
            selectedWorkItem.value = null
            selectedBidPackage.value = null
            showSuccess('Hạng mục đã được cập nhật thành công.')
          },
          onError: (errors) => {
            workItemFormErrors.value = errors
          },
          onFinish: () => {
            isSubmitting.value = false
          }
        }
      )
    } else {
      // Thêm hạng mục mới
      await router.post(
        route('work-items.store', selectedBidPackage.value.id),
        {
          ...workItemForm.value,
          price: parseCurrency(workItemForm.value.price)
        },
        {
          onSuccess: () => {
            window.$('#workItemModal').modal('hide')
            selectedBidPackage.value = null
            showSuccess('Hạng mục đã được tạo thành công.')
          },
          onError: (errors) => {
            workItemFormErrors.value = errors
          },
          onFinish: () => {
            isSubmitting.value = false
          }
        }
      )
    }
  } catch (error) {
    console.error('Lỗi khi xử lý hạng mục:', error)
    showError('Có lỗi xảy ra khi xử lý hạng mục. Vui lòng thử lại sau.')
    isSubmitting.value = false
  }
}

// Xác nhận xóa hạng mục
const confirmDeleteWorkItem = (workItem) => {
  showConfirm(
    'Xác nhận xóa hạng mục',
    `Bạn có chắc chắn muốn xóa hạng mục "${workItem.name}" không?`,
    'Xóa',
    'Hủy'
  ).then((result) => {
    if (result.isConfirmed) {
      deleteWorkItem(workItem)
    }
  })
}

// Xóa hạng mục
const deleteWorkItem = (workItem) => {
  router.delete(route('work-items.destroy', workItem.id), {
    onSuccess: () => {
      showSuccess('Hạng mục đã được xóa thành công.')
    },
    onError: (errors) => {
      showError('Không thể xóa hạng mục. Vui lòng thử lại sau.')
    }
  })
}

// Lấy trạng thái hạng mục
const getWorkItemStatusLabel = (status) => {
  const statusMap = {
    pending: 'Chưa bắt đầu',
    in_progress: 'Đang thực hiện',
    completed: 'Hoàn thành'
  }
  return statusMap[status] || status
}

// Lấy class cho trạng thái hạng mục
const getWorkItemStatusClass = (status) => {
  const classMap = {
    pending: 'badge badge-warning',
    in_progress: 'badge badge-info',
    completed: 'badge badge-success'
  }
  return classMap[status] || 'badge badge-secondary'
}

// Xử lý khi modal đóng
window.$('#workItemModal').on('hidden.bs.modal', function () {
  // Hủy InputPicker khi modal đóng
  try {
    window.$('#work_item_contractor_id').inputpicker('destroy')
    window.$('#work_item_contractor_id').off('change')
  } catch (e) {
    console.log('Không thể hủy InputPicker khi đóng modal hạng mục:', e)
  }
})
</script>

<style scoped>
/* Thêm CSS cho phần tên dự án và nút thêm gói thầu cố định */
.fixed-header {
  position: sticky;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  width: 100%;
  background-color: #f4f6f9;
  padding: 10px 5px;
}

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
  gap: 1rem;
  align-items: flex-start;
  z-index: 1;
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
.grid-cols-24 {
  display: grid;
  grid-template-columns: repeat(32, minmax(0, 1fr));
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
