/**
 * Format ngày tháng thành định dạng dd/mm/yyyy hh:mm
 * @param {string} dateString - Chuỗi ngày tháng
 * @returns {string} Ngày tháng đã được format
 */
export function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('vi-VN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

/**
 * Hiển thị thông báo thành công
 * @param {string} message - Nội dung thông báo
 */
export function showSuccess(message) {
  Swal.fire({
    title: 'Thành công!',
    text: message,
    icon: 'success',
    confirmButtonText: 'Đóng',
    confirmButtonColor: '#3085d6'
  })
}

/**
 * Hiển thị thông báo lỗi
 * @param {string} message - Nội dung thông báo
 */
export function showError(message) {
  Swal.fire({
    title: 'Lỗi!',
    text: message,
    icon: 'error',
    confirmButtonText: 'Đóng',
    confirmButtonColor: '#3085d6'
  })
}

/**
 * Hiển thị thông báo cảnh báo
 * @param {string} message - Nội dung thông báo
 */
export function showWarning(message) {
  Swal.fire({
    title: 'Cảnh báo!',
    text: message,
    icon: 'warning',
    confirmButtonText: 'Đóng',
    confirmButtonColor: '#3085d6'
  })
}

/**
 * Hiển thị thông báo xác nhận
 * @param {string} title - Tiêu đề thông báo
 * @param {string} text - Nội dung thông báo
 * @param {string} confirmButtonText - Nội dung nút xác nhận
 * @param {string} cancelButtonText - Nội dung nút hủy
 * @returns {Promise} Promise chứa kết quả xác nhận
 */
export function showConfirm(title, text, confirmButtonText = 'Xác nhận', cancelButtonText = 'Hủy') {
  return Swal.fire({
    title: title,
    text: text,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: confirmButtonText,
    cancelButtonText: cancelButtonText
  })
}

/**
 * Format số thành định dạng tiền tệ khi nhập vào input
 * @param {Event} event - Event từ input
 * @param {boolean} allowDecimal - Cho phép số thập phân hay không
 */
export function formatNumberInput(event, allowDecimal = false) {
  let value = event.target.value

  // Chỉ cho phép số và dấu phẩy
  value = value.replace(/[^\d,]/g, '')

  // Xóa các dấu phẩy cũ
  value = value.replace(/,/g, '')

  if (allowDecimal) {
    // Nếu cho phép số thập phân, giữ lại 2 số sau dấu thập phân
    const parts = value.split('.')
    if (parts[1]) {
      parts[1] = parts[1].slice(0, 2)
      value = parts.join('.')
    }
  }

  // Format số với dấu phẩy
  value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',')

  // Cập nhật giá trị vào input
  event.target.value = value

  // Trigger event để v-model cập nhật
  event.target.dispatchEvent(new Event('input'))
}

/**
 * Format số thành định dạng tiền tệ VN
 * @param {number|string} value - Giá trị cần format
 * @param {boolean} showCurrency - Hiển thị ký hiệu tiền tệ
 * @returns {string} Chuỗi đã được format
 */
export const formatCurrency = (value, showCurrency = false) => {
  if (!value) return '0'

  // Chuyển value thành số
  const number = typeof value === 'string' ? parseInt(value.replace(/,/g, '')) : value

  // Format số với dấu phẩy
  const formatted = Math.round(number)
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, ',')

  // Thêm ký hiệu tiền tệ nếu cần
  return showCurrency ? `${formatted} VNĐ` : formatted
}

/**
 * Chuyển từ chuỗi tiền tệ thành số
 * @param {string|number} value - Giá trị cần parse
 * @returns {number} Số đã được parse
 */
export const parseCurrency = (value) => {
  if (!value) return 0

  // Nếu là số, trả về số đó
  if (typeof value === 'number') return value

  // Xóa tất cả dấu phẩy và ký hiệu tiền tệ
  const numStr = value.toString().replace(/[,VNĐ\s]/g, '')
  return numStr ? parseInt(numStr) : 0
}
