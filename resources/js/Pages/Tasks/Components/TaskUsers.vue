<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="m-0">Danh sách nhân sự</h5>
      <button class="btn btn-sm btn-primary" @click="openAddUserModal"><i class="fas fa-plus"></i> Thêm nhân sự</button>
    </div>

    <!-- Bảng danh sách nhân sự -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th style="width: 50px">STT</th>
            <th style="width: 80px">Ảnh</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Vai trò</th>
            <th>Thời gian (ngày)</th>
            <th style="width: 100px">Thao tác</th>
          </tr>
        </thead>
        <tbody v-if="users.length > 0">
          <tr v-for="(user, index) in users" :key="user.id">
            <td class="text-center">{{ index + 1 }}</td>
            <td class="text-center">
              <img
                :src="user.avatar ? user.avatar : '/img/default-avatar.png'"
                alt="Avatar"
                class="img-circle"
                style="width: 40px; height: 40px"
              />
            </td>
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span
                class="badge"
                :class="{
                  'badge-info': user.role === 0,
                  'badge-primary': user.role === 1,
                  'badge-warning': user.role === 2
                }"
              >
                {{ user.role_name }}
              </span>
            </td>
            <td>{{ user.duration || 'N/A' }}</td>
            <td class="text-center">
              <button class="btn btn-sm btn-danger" @click="confirmRemoveUser(user)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <tr>
            <td colspan="7" class="text-center">Chưa có nhân sự nào được thêm vào công việc này</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal thêm nhân sự -->
    <div
      class="modal fade"
      id="addUserModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="addUserModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel">Thêm nhân sự</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="user_id">Chọn nhân sự</label>
              <input type="text" class="form-control" id="user_id_picker" placeholder="Nhập tên nhân sự" />
              <div v-if="errors.user_id" class="text-danger mt-1">{{ errors.user_id }}</div>
            </div>
            <div class="form-group">
              <label for="role">Vai trò</label>
              <select v-model="form.role" class="form-control" id="role">
                <option value="0">Người thực hiện</option>
                <option value="1">Người phụ trách</option>
                <option value="2">Người giám sát</option>
              </select>
              <div v-if="errors.role" class="text-danger mt-1">{{ errors.role }}</div>
            </div>
            <div class="form-group">
              <label for="duration">Thời gian làm việc (ngày)</label>
              <input
                type="number"
                v-model="form.duration"
                class="form-control"
                id="duration"
                min="1"
                placeholder="Nhập số ngày làm việc"
              />
              <div v-if="errors.duration" class="text-danger mt-1">{{ errors.duration }}</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            <button type="button" class="btn btn-primary" @click="addUser" :disabled="loading">
              <i v-if="loading" class="fas fa-spinner fa-spin"></i> Thêm
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { showSuccess, showError } from '@/utils'

const props = defineProps({
  taskId: {
    type: Number,
    required: true
  }
})

const users = ref([])
const allUsers = ref([])
const availableUsers = ref([])
const loading = ref(false)
const errors = ref({})
const inputpickerInstance = ref(null)
const isInputPickerInitialized = ref(false)
const selectedUser = ref(null)

const form = ref({
  user_id: '',
  role: '0',
  duration: '1'
})

// Lấy danh sách nhân sự của công việc
const loadUsers = async () => {
  try {
    const response = await axios.get(`/tasks/${props.taskId}/users`)
    users.value = response.data
  } catch (error) {
    console.error('Lỗi khi tải danh sách nhân sự:', error)
    showError('Không thể tải danh sách nhân sự')
  }
}

// Lấy danh sách tất cả người dùng
const loadAllUsers = async () => {
  try {
    const response = await axios.get('/api/users')
    allUsers.value = response.data
  } catch (error) {
    console.error('Lỗi khi tải danh sách người dùng:', error)
    showError('Không thể tải danh sách người dùng')
  }
}

// Mở modal thêm nhân sự
const openAddUserModal = () => {
  // Reset form và errors
  form.value = {
    user_id: '',
    role: '0',
    duration: '1'
  }
  errors.value = {}

  // Tính toán danh sách người dùng có thể thêm
  availableUsers.value = allUsers.value.filter((user) => !users.value.some((u) => u.id === user.id))

  // Mở modal
  window.$('#addUserModal').modal('show')

  // Khởi tạo InputPicker sau khi modal hiển thị
  nextTick(() => {
    initInputPicker()
  })
}

// Thêm nhân sự vào công việc
const addUser = async () => {
  if (!form.value.user_id) {
    errors.value = { user_id: 'Vui lòng chọn nhân sự' }
    return
  }

  loading.value = true
  errors.value = {}

  try {
    const response = await axios.post(`/tasks/${props.taskId}/users`, {
      user_id: form.value.user_id,
      duration: parseInt(form.value.duration),
      role: parseInt(form.value.role)
    })

    users.value.push(response.data)
    showSuccess('Đã thêm nhân sự vào công việc')
    $('#addUserModal').modal('hide')
  } catch (error) {
    console.error('Lỗi khi thêm nhân sự:', error)
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors
    } else if (error.response && error.response.data && error.response.data.message) {
      showError(error.response.data.message)
    } else {
      showError('Không thể thêm nhân sự')
    }
  } finally {
    loading.value = false
  }
}

// Xác nhận xóa nhân sự
const confirmRemoveUser = (user) => {
  Swal.fire({
    title: 'Xác nhận xóa',
    text: `Bạn có chắc chắn muốn xóa ${user.name} khỏi công việc này?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Xác nhận',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      removeUser(user)
    }
  })
}

// Xóa nhân sự khỏi công việc
const removeUser = async (user) => {
  try {
    await axios.delete(`/tasks/${props.taskId}/users/${user.id}?role=${user.role}`)
    users.value = users.value.filter((u) => !(u.id === user.id && u.role === user.role))
    showSuccess('Đã xóa nhân sự khỏi công việc')
  } catch (error) {
    console.error('Lỗi khi xóa nhân sự:', error)
    showError('Không thể xóa nhân sự')
  }
}

// Khởi tạo InputPicker
const initInputPicker = async () => {
  // Nếu đã khởi tạo, không khởi tạo lại
  if (isInputPickerInitialized.value) {
    updateInputPickerValue()
    return
  }

  try {
    await nextTick()
    const userInputId = 'user_id_picker'

    // Đảm bảo element tồn tại
    const inputElement = document.getElementById(userInputId)
    if (!inputElement) {
      console.error(`Không tìm thấy element với id ${userInputId}`)
      return
    }

    // Đảm bảo jQuery đã được khởi tạo
    if (!window.$ || !window.$.fn || !window.$.fn.inputpicker) {
      console.error('jQuery hoặc plugin inputpicker chưa được khởi tạo')
      return
    }

    // Khởi tạo InputPicker
    const $input = window.$(`#${userInputId}`)

    // Đảm bảo element đã được jQuery chọn đúng
    if ($input.length === 0) {
      console.error(`jQuery không thể tìm thấy element với id ${userInputId}`)
      return
    }

    // Xóa tất cả sự kiện trước
    $input.off('change')

    // Khởi tạo InputPicker mới
    $input.inputpicker({
      data: availableUsers.value.map((user) => ({
        value: user.id,
        text: user.name,
        email: user.email || '',
        avatar: user.avatar || ''
      })),
      fields: [
        { name: 'text', text: 'Tên nhân sự' },
        { name: 'email', text: 'Email' }
      ],
      fieldText: 'text',
      fieldValue: 'value',
      filterOpen: true,
      autoOpen: true,
      headShow: true,
      width: '100%'
    })

    // Lưu instance để tham chiếu sau này
    inputpickerInstance.value = $input
    isInputPickerInitialized.value = true

    // Đặt giá trị ban đầu nếu có
    updateInputPickerValue()

    // Xử lý sự kiện change
    $input.on('change', function () {
      const userId = window.$(this).val()
      form.value.user_id = userId

      if (userId) {
        const user = availableUsers.value.find((u) => u.id == userId)
        if (user) {
          selectedUser.value = user
        }
      } else {
        selectedUser.value = null
      }
    })
  } catch (error) {
    console.error('Lỗi khi khởi tạo InputPicker:', error)
  }
}

// Cập nhật giá trị InputPicker
const updateInputPickerValue = () => {
  if (inputpickerInstance.value && form.value.user_id) {
    inputpickerInstance.value.val(form.value.user_id)
    const user = availableUsers.value.find((u) => u.id == form.value.user_id)
    if (user) {
      selectedUser.value = user
    }
  }
}

onMounted(() => {
  loadUsers()
  loadAllUsers()
})
</script>
