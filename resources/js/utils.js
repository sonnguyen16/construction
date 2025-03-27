/**
 * Format ngày tháng thành định dạng dd/mm/yyyy hh:mm
 * @param {string} dateString - Chuỗi ngày tháng
 * @returns {string} Ngày tháng đã được format
 */
export function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
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
    });
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
    });
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
    });
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
    });
}

// Hàm định dạng số thành tiền tệ
export const formatCurrency = (value) => {
    return Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
};

// Hàm chuyển từ chuỗi tiền tệ thành số nguyên
export const parseCurrency = (value) => {
    if (!value) return 0;
    // Nếu là số, trả về số đó
    if (typeof value === 'number') return value;

    // Xóa tất cả dấu chấm phân cách
    const numStr = value.toString().replace(/\./g, '');
    return numStr ? parseInt(numStr) : 0;
};
