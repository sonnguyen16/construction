import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermission() {
  const page = usePage();
  
  /**
   * Kiểm tra xem người dùng đã đăng nhập chưa
   */
  const isLoggedIn = computed(() => !!page.props.auth && !!page.props.auth.user);

  /**
   * Lấy danh sách quyền của người dùng hiện tại
   */
  const userPermissions = computed(() => {
    if (!isLoggedIn.value) return [];
    return page.props.auth.user?.can || [];
  });
  
  /**
   * Lấy danh sách vai trò của người dùng hiện tại
   */
  const userRoles = computed(() => {
    if (!isLoggedIn.value) return [];
    return page.props.auth.user?.roles || [];
  });
  
  /**
   * Kiểm tra người dùng có quyền không
   * @param {string|array} permissions - Tên quyền hoặc mảng tên quyền cần kiểm tra
   * @returns {boolean} - Trả về true nếu có quyền, ngược lại false
   */
  function can(permissions) {
    if (Array.isArray(permissions)) {
      // Kiểm tra nếu có ít nhất một quyền trong danh sách
      return permissions.some(permission => userPermissions.value.includes(permission));
    }
    
    // Kiểm tra một quyền duy nhất
    return userPermissions.value.includes(permissions);
  }
  
  /**
   * Kiểm tra người dùng có tất cả các quyền không
   * @param {array} permissions - Mảng tên quyền cần kiểm tra
   * @returns {boolean} - Trả về true nếu có tất cả quyền, ngược lại false
   */
  function canAll(permissions) {
    // Kiểm tra nếu có tất cả các quyền trong danh sách
    return permissions.every(permission => userPermissions.value.includes(permission));
  }
  
  /**
   * Kiểm tra người dùng có thuộc vai trò không
   * @param {string|array} roles - Tên vai trò hoặc mảng tên vai trò cần kiểm tra
   * @returns {boolean} - Trả về true nếu có vai trò, ngược lại false
   */
  function hasRole(roles) {
    if (Array.isArray(roles)) {
      // Kiểm tra nếu có ít nhất một vai trò trong danh sách
      return roles.some(role => userRoles.value.includes(role));
    }
    
    // Kiểm tra một vai trò duy nhất
    return userRoles.value.includes(roles);
  }
  
  return {
    userPermissions,
    userRoles,
    can,
    canAll,
    hasRole
  };
}
