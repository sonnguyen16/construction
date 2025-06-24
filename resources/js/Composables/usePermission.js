import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermission() {
    const page = usePage();

    /**
     * Kiểm tra xem người dùng đã đăng nhập chưa
     */
    const isLoggedIn = computed(() => !!page.props.auth && !!page.props.auth.user);

    /**
     * Lấy danh sách vai trò của người dùng trong các dự án
     */
    const userProjectRoles = computed(() => {
        if (!isLoggedIn.value) return [];
        return page.props.auth.user?.project_roles || [];
    });

    /**
     * Kiểm tra người dùng có vai trò trong dự án không
     * @param {string|array} roles - Tên vai trò hoặc mảng tên vai trò cần kiểm tra
     * @param {number} projectId - ID của dự án cần kiểm tra
     * @returns {boolean} - Trả về true nếu có vai trò trong dự án, ngược lại false
     */
    function hasRoleInProject(roles, projectId) {
        // Lấy vai trò của người dùng trong dự án
        const projectRole = userProjectRoles.value.find(pr => pr.project_id === projectId);

        if (!projectRole) {
            return false;
        }

        // Nếu không cần kiểm tra tên vai trò cụ thể
        if (!roles) {
            return true;
        }

        // Kiểm tra vai trò
        if (Array.isArray(roles)) {
            return roles.includes(projectRole.role_name);
        }

        return projectRole.role_name === roles;
    }

    /**
     * Kiểm tra người dùng có quyền trong dự án không
     * @param {string|array} permissions - Tên quyền hoặc mảng tên quyền cần kiểm tra
     * @param {number} projectId - ID của dự án cần kiểm tra
     * @returns {boolean} - Trả về true nếu có quyền trong dự án, ngược lại false
     */
    function canInProject(permissions, projectId) {
        // Lấy vai trò của người dùng trong dự án
        const projectRole = userProjectRoles.value.find(pr => pr.project_id === projectId);

        // Nếu không có vai trò trong dự án
        if (!projectRole) {
            return false;
        }

        // Lấy danh sách quyền của vai trò trong dự án
        const rolePermissions = projectRole.permissions || [];

        // Kiểm tra quyền
        if (Array.isArray(permissions)) {
            return permissions.some(p => rolePermissions.includes(p));
        }

        return rolePermissions.includes(permissions);
    }

    /**
     * Kiểm tra xem người dùng có quyền global trong bất kỳ dự án nào không
     * @param {string|array} permissions - Tên quyền hoặc mảng tên quyền cần kiểm tra
     * @returns {boolean} - Trả về true nếu có quyền global trong bất kỳ dự án nào, ngược lại false
     */
    function hasGlobalPermission(permissions) {
        // Kiểm tra tất cả các dự án của người dùng
        for (const projectRole of userProjectRoles.value) {
            // Lấy danh sách quyền global của vai trò trong dự án
            const globalPermissions = projectRole.permissions || [];

            // Kiểm tra quyền
            if (Array.isArray(permissions)) {
                if (permissions.some(p => globalPermissions.includes(p))) {
                    return true;
                }
            } else if (globalPermissions.includes(permissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kiểm tra xem người dùng có quyền view trong ít nhất một dự án nào không
     * Dùng cho AdminLayout để ẩn/hiện các menu
     * @param {string} permissionBase - Tên quyền cơ sở, sẽ thêm '.view' để kiểm tra
     * @returns {boolean} - Trả về true nếu có quyền view trong ít nhất một dự án, ngược lại false
     */
    function hasViewPermissionInAnyProject(permissionBase) {
        const viewPermission = `${permissionBase}.view`;

        // Kiểm tra tất cả các dự án của người dùng
        for (const projectRole of userProjectRoles.value) {
            // Lấy danh sách quyền của vai trò trong dự án
            const permissions = projectRole.permissions || [];

            // Kiểm tra quyền view
            if (permissions.includes(viewPermission)) {
                return true;
            }
        }

        return false;
    }

    function hasPermissionInAnyProject(permissionBase) {
        const viewPermission = `${permissionBase}`;
        for (const projectRole of userProjectRoles.value) {
            const permissions = projectRole.permissions || [];
            if (permissions.includes(viewPermission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Kiểm tra xem người dùng có phải là Super Admin trong dự án không
     * @param {number} projectId - ID của dự án cần kiểm tra
     * @returns {boolean} - Trả về true nếu là Super Admin trong dự án, ngược lại false
     */
    function isSuperAdminInProject(projectId) {
        return hasRoleInProject('Super Admin', projectId);
    }

    /**
     * Lấy danh sách các dự án mà người dùng có vai trò
     * @returns {array} - Mảng chứa các dự án
     */
    const userProjects = computed(() => {
        return userProjectRoles.value.map(pr => ({
            id: pr.project_id,
            name: pr.project_name,
            role: pr.role_name,
            permissions: pr.permissions || []
        }));
    });

    return {
        page,
        userProjectRoles,
        userProjects,
        hasRoleInProject,
        canInProject,
        hasGlobalPermission,
        hasViewPermissionInAnyProject,
        hasPermissionInAnyProject,
        isSuperAdminInProject
    };
}
