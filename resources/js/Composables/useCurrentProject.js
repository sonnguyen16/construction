import { ref, computed, onMounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

// Biến lưu trữ trạng thái toàn cục
const selectedProjectRole = ref(null)
const loading = ref(false)

// Hàm để lưu và đọc dự án và vai trò hiện tại từ localStorage
const STORAGE_KEY = 'current_project_role'

const saveToLocalStorage = (projectRole) => {
    if (projectRole) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            project_id: projectRole.project_id,
            role_id: projectRole.role_id
        }))
    }
}

const getFromLocalStorage = () => {
    const stored = localStorage.getItem(STORAGE_KEY)
    return stored ? JSON.parse(stored) : null
}

export function useCurrentProject() {
    const page = usePage()
    
    // Lấy danh sách vai trò dự án của người dùng từ props
    const projectRoles = computed(() => {
        if (!page.props.auth.user) return []
        return page.props.auth.user.project_roles || []
    })
    
    // Lấy danh sách vai trò dự án của người dùng từ props Inertia
    const userProjectRoles = computed(() => page.props.user_project_roles || [])
    
    // Tìm vai trò dự án dựa trên project_id và role_id
    const findProjectRole = (projectId, roleId) => {
        return projectRoles.value.find(pr => 
            pr.project_id === projectId && pr.role_id === roleId
        )
    }
    
    // Khởi tạo selectedProjectRole từ localStorage hoặc từ props
    const initSelectedProjectRole = () => {
        // Đọc từ localStorage trước
        const storedData = getFromLocalStorage()
        
        if (storedData && projectRoles.value.length > 0) {
            // Tìm vai trò dự án tương ứng trong danh sách
            const foundProjectRole = findProjectRole(storedData.project_id, storedData.role_id)
            
            if (foundProjectRole) {
                selectedProjectRole.value = foundProjectRole
                return
            }
        }
        
        // Nếu không có trong localStorage hoặc không tìm thấy, kiểm tra trong props
        const activeProjectRole = projectRoles.value.find(pr => pr.is_active)
        
        if (activeProjectRole) {
            selectedProjectRole.value = activeProjectRole
            // Lưu vào localStorage
            saveToLocalStorage(activeProjectRole)
        } else if (projectRoles.value.length > 0) {
            // Nếu không có dự án nào được đánh dấu là active, chọn dự án đầu tiên
            selectedProjectRole.value = projectRoles.value[0]
            // Lưu vào localStorage
            saveToLocalStorage(projectRoles.value[0])
        }
    }
    
    // Lấy vai trò dự án hiện tại được chọn
    const currentProjectRole = computed(() => {
        // Nếu đã có selectedProjectRole thì trả về luôn
        if (selectedProjectRole.value) {
            return selectedProjectRole.value
        }
        
        // Nếu chưa có selectedProjectRole nhưng có danh sách project roles
        if (projectRoles.value.length > 0) {
            // Tự động chọn project role đầu tiên
            selectedProjectRole.value = projectRoles.value[0]
            // Lưu vào local storage
            saveToLocalStorage(projectRoles.value[0])
            return projectRoles.value[0]
        }
        
        return null
    })
    
    // Lấy dự án hiện tại
    const currentProject = computed(() => {
        if (!currentProjectRole.value) return null
        return {
            id: currentProjectRole.value.project_id,
            name: currentProjectRole.value.project_name
        }
    })
    
    // Lấy vai trò hiện tại
    const currentRole = computed(() => {
        if (!currentProjectRole.value) return null
        return {
            id: currentProjectRole.value.role_id,
            name: currentProjectRole.value.role_name
        }
    })
    
    // Kiểm tra xem người dùng có dự án nào không
    const hasProjects = computed(() => {
        return projectRoles.value.length > 0
    })
    
    // Cập nhật quyền trong session mà không cần tải lại trang
    const updatePermissions = async (projectId, roleId) => {
        try {
            // Gọi API để cập nhật quyền trong session
            await axios.post(route('user.change-project-role'), {
                project_id: projectId,
                role_id: roleId
            })
            
            // Cập nhật trạng thái is_active cho các projectRole
            projectRoles.value.forEach(pr => {
                pr.is_active = (pr.project_id === projectId && pr.role_id === roleId)
            })
            
            // Cập nhật quyền trong Inertia props
            if (page.props.auth && page.props.auth.user) {
                // Cập nhật quyền trong Inertia props (nếu cần)
                // Có thể cần thêm logic để cập nhật quyền ở đây
            }
            
            return true
        } catch (error) {
            console.error('Lỗi khi cập nhật quyền:', error)
            return false
        }
    }
    
    // Thay đổi dự án và vai trò hiện tại
    const changeProjectRole = async (projectRole) => {
        loading.value = true
        
        try {
            // Lưu vào localStorage
            saveToLocalStorage(projectRole)
            
            // Cập nhật quyền trong session
            const success = await updatePermissions(projectRole.project_id, projectRole.role_id)
            
            if (success) {
                // Cập nhật selectedProjectRole
                selectedProjectRole.value = projectRole
                
                // Cập nhật giao diện mà không cần tải lại trang
                updateCurrentPageForProjectChange(projectRole.project_id)
            } else {
                // Nếu cập nhật không thành công, tải lại trang
                window.location.reload()
            }
        } catch (error) {
            console.error('Lỗi khi thay đổi dự án:', error)
            // Nếu có lỗi, tải lại trang
            window.location.reload()
        } finally {
            loading.value = false
        }
    }
    
    // Cập nhật trang hiện tại khi thay đổi dự án
    const updateCurrentPageForProjectChange = (projectId) => {
        // Kiểm tra xem đang ở trang nào
        const currentUrl = window.location.pathname
        
        // Nếu đang ở trang chủ
        if (currentUrl === '/' || currentUrl === '/home') {
            // Tải lại trang với dự án mới
            router.get(currentUrl, { project_id: projectId }, { preserveState: true, replace: true })
        }
        // Nếu đang ở trang phiếu chi
        else if (currentUrl.includes('/payment-vouchers')) {
            // Nếu đang ở trang danh sách phiếu chi
            if (currentUrl === '/payment-vouchers') {
                // Cập nhật bộ lọc dự án và tải lại trang
                router.get('/payment-vouchers', { project_id: projectId }, { preserveState: true, replace: true })
            } 
            // Nếu đang ở trang tạo phiếu chi mới
            else if (currentUrl === '/payment-vouchers/create') {
                // Tải lại trang với dự án mới
                router.get('/payment-vouchers/create', { project_id: projectId })
            }
        }
    }
    
    // Khởi tạo khi component được tạo
    onMounted(() => {
        initSelectedProjectRole()
    })
    
    return {
        projectRoles,
        currentProjectRole,
        currentProject,
        currentRole,
        hasProjects,
        changeProjectRole,
        loading
    }
}
