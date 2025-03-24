import { formatCurrency, parseCurrency } from '@/utils'

export default {
    mounted(el, binding) {
        // Đặt id duy nhất cho mỗi input để tránh xung đột
        el._currencyId = `currency_${Date.now()}_${Math.floor(Math.random() * 1000)}`

        const handleInput = function (e) {
            // Lưu vị trí con trỏ
            const cursorPos = e.target.selectionStart

            // Lấy giá trị hiện tại và xóa các ký tự không phải số
            let value = e.target.value.replace(/\D/g, '')

            // Nếu không có giá trị, đặt thành chuỗi rỗng
            if (!value) {
                e.target.value = ''
                return
            }

            // Chuyển thành số nguyên
            value = parseInt(value)

            // Định dạng giá trị
            const formattedValue = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')

            // Cập nhật giá trị
            e.target.value = formattedValue

            // Điều chỉnh vị trí con trỏ
            const formattedLength = formattedValue.length
            const originalLength = e.target._previousValue ? e.target._previousValue.length : 0
            const diff = formattedLength - originalLength

            // Lưu giá trị hiện tại để so sánh lần sau
            e.target._previousValue = formattedValue

            // Thiết lập lại vị trí con trỏ
            setTimeout(() => {
                const newPos = Math.max(0, Math.min(cursorPos + diff, formattedLength))
                e.target.setSelectionRange(newPos, newPos)
            }, 0)

            // Phát sự kiện input để cập nhật v-model
            e.target.dispatchEvent(new Event('input', { bubbles: true }))
        }

        // Gắn hàm xử lý vào sự kiện input
        el.addEventListener('input', handleInput)

        // Lưu hàm xử lý để có thể gỡ bỏ sau này
        el._handleInput = handleInput

        // Định dạng giá trị ban đầu nếu có
        if (el.value) {
            const event = { target: el }
            handleInput(event)
        }
    },

    updated(el, binding) {
        // Nếu giá trị thay đổi từ bên ngoài (không phải từ sự kiện input)
        if (binding.value !== binding.oldValue) {
            // Chỉ cập nhật nếu khác với giá trị hiện tại của input
            const currentFormattedValue = el.value

            // Nếu là số, định dạng nó
            if (binding.value !== undefined && binding.value !== null) {
                let numValue = binding.value
                if (typeof numValue === 'string') {
                    numValue = numValue.replace(/\D/g, '')
                    if (numValue) numValue = parseInt(numValue)
                    else numValue = 0
                }

                // Định dạng giá trị
                const formattedValue = numValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')

                // Cập nhật nếu khác với giá trị hiện tại
                if (formattedValue !== currentFormattedValue) {
                    el.value = formattedValue
                    el._previousValue = formattedValue
                }
            } else {
                // Nếu giá trị là null hoặc undefined, đặt về rỗng
                el.value = ''
                el._previousValue = ''
            }
        }
    },

    unmounted(el) {
        // Gỡ bỏ event listener khi component bị hủy
        if (el._handleInput) {
            el.removeEventListener('input', el._handleInput)
        }
    }
}
