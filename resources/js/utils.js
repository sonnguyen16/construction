
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
 * Format số thành định dạng tiền tệ
 * @param {number} amount - Số tiền cần format
 * @param {string} currency - Đơn vị tiền tệ (mặc định: đ)
 * @returns {string} Chuỗi đã được format
 */
export function formatCurrency(amount, currency = 'đ') {
    if (amount === null || amount === undefined) return '-';
    return new Intl.NumberFormat('vi-VN').format(amount) + ' ' + currency;
}

/**
 * Chuyển đổi chuỗi tiền tệ thành số
 * @param {string} formattedAmount - Chuỗi tiền tệ cần chuyển đổi
 * @returns {number} Số đã được chuyển đổi
 */
export function parseCurrency(formattedAmount) {
    if (!formattedAmount) return 0;

    // Loại bỏ tất cả các ký tự không phải số, trừ dấu phẩy thập phân
    // Đầu tiên, loại bỏ tất cả dấu chấm ngăn cách hàng nghìn
    const withoutDots = String(formattedAmount).replace(/\./g, '');

    // Loại bỏ các ký tự không phải số và dấu phẩy
    const numericString = withoutDots.replace(/[^\d,]/g, '');

    // Chuyển đổi dấu phẩy thành dấu chấm (để JavaScript hiểu đúng)
    const withDecimalPoint = numericString.replace(/,/g, '.');

    // Chuyển đổi thành số
    return parseFloat(withDecimalPoint) || 0;
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

/**
 * Tạo directive để format input tiền tệ
 */
export const currencyDirective = {
    mounted(el, binding) {
        const inputElement = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!inputElement) return;

        const formatValue = (value) => {
            if (!value) return '';

            // Loại bỏ tất cả các ký tự không phải số
            const numericValue = value.replace(/[^\d]/g, '');

            // Format số với dấu chấm ngăn cách hàng nghìn
            return new Intl.NumberFormat('vi-VN').format(numericValue);
        };

        inputElement.addEventListener('input', (e) => {
            const cursorPosition = e.target.selectionStart;
            const oldLength = e.target.value.length;
            const oldValue = e.target.value;

            // Lưu vị trí con trỏ tương đối
            const oldValueBeforeCursor = oldValue.substring(0, cursorPosition);
            const oldNumericBeforeCursor = oldValueBeforeCursor.replace(/[^\d]/g, '').length;

            // Format giá trị
            const numericValue = e.target.value.replace(/[^\d]/g, '');
            const formattedValue = formatValue(numericValue);
            e.target.value = formattedValue;

            // Đặt lại vị trí con trỏ
            if (formattedValue) {
                let newCursorPosition = 0;
                let countNumeric = 0;

                for (let i = 0; i < formattedValue.length; i++) {
                    if (/\d/.test(formattedValue[i])) {
                        countNumeric++;
                    }
                    if (countNumeric > oldNumericBeforeCursor) break;
                    newCursorPosition = i + 1;
                }

                e.target.setSelectionRange(newCursorPosition, newCursorPosition);
            }

            // Emit event để Vue cập nhật v-model
            e.target.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // Format giá trị ban đầu
        if (inputElement.value) {
            inputElement.value = formatValue(inputElement.value);
        }
    },
    updated(el, binding) {
        const inputElement = el.tagName === 'INPUT' ? el : el.querySelector('input');
        if (!inputElement) return;

        // Format giá trị khi binding thay đổi
        if (binding.value !== binding.oldValue) {
            const formatValue = (value) => {
                if (!value) return '';

                // Đảm bảo value là chuỗi
                const stringValue = String(value);

                // Nếu value đã được format (có dấu chấm), không format lại
                if (stringValue.includes('.')) return stringValue;

                // Loại bỏ tất cả các ký tự không phải số
                const numericValue = stringValue.replace(/[^\d]/g, '');

                // Format số với dấu chấm ngăn cách hàng nghìn
                return new Intl.NumberFormat('vi-VN').format(numericValue);
            };

            const currentValue = inputElement.value;
            const formattedBindingValue = formatValue(binding.value);

            // Chỉ cập nhật nếu giá trị thực sự khác
            if (currentValue !== formattedBindingValue) {
                inputElement.value = formattedBindingValue;
            }
        }
    }
};