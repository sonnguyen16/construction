<template>
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
          </a>
          <div class="navbar-search-block">
            <form class="form-inline">
              <div class="input-group input-group-sm">
                <input
                  class="form-control form-control-navbar"
                  type="search"
                  placeholder="Tìm kiếm"
                  aria-label="Search"
                />
                <div class="input-group-append">
                  <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                  </button>
                  <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </li>

        <!-- Thông báo -->
        <li class="nav-item dropdown">
          <a
            class="nav-link position-relative"
            data-toggle="dropdown"
            href="#"
            @click="loadNotifications"
            style="padding-right: 20px"
          >
            <i class="far fa-bell"></i>
            <span
              v-if="unreadNotificationsCount > 0"
              class="badge badge-danger navbar-badge"
              style="right: 5px; top: 5px; font-size: 0.6rem"
              >{{ unreadNotificationsCount }}</span
            >
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="min-width: 300px; max-width: 350px">
            <span class="dropdown-header">{{ unreadNotificationsCount }} thông báo chưa đọc</span>
            <div class="dropdown-divider"></div>

            <div v-if="loadingNotifications" class="text-center p-3">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="sr-only">Loading...</span>
              </div>
            </div>

            <div v-else-if="notifications.length === 0" class="dropdown-item text-center">
              <p class="text-muted mb-0">Không có thông báo nào</p>
            </div>

            <template v-else>
              <a
                v-for="notification in notifications"
                :key="notification.id"
                :href="getNotificationUrl(notification)"
                class="dropdown-item py-2"
                :class="{ 'bg-light': !notification.read_at }"
                @click.prevent="readNotification(notification)"
              >
                <div class="d-flex">
                  <div class="mr-3 pt-1">
                    <i :class="getNotificationIcon(notification)" class="text-primary fa-lg"></i>
                  </div>
                  <div style="width: calc(100% - 30px)">
                    <p class="mb-1 font-weight-bold text-truncate">{{ notification.data.title }}</p>
                    <p
                      class="text-muted mb-1 small"
                      style="
                        line-height: 1.3;
                        max-height: 2.6em;
                        overflow: hidden;
                        display: -webkit-box;
                        -webkit-line-clamp: 2;
                        line-clamp: 2;
                        -webkit-box-orient: vertical;
                      "
                    >
                      {{ notification.data.message }}
                    </p>
                    <small class="text-muted d-block text-right">{{ notification.created_at }}</small>
                  </div>
                </div>
              </a>
              <div class="dropdown-divider"></div>
            </template>

            <a
              href="#"
              class="dropdown-item dropdown-footer"
              @click.prevent="markAllAsRead"
              v-if="unreadNotificationsCount > 0"
            >
              Đánh dấu tất cả là đã đọc
            </a>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="fas fa-user"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            <a href="#" class="dropdown-item"> <i class="fas fa-user-cog mr-2"></i> Hồ sơ </a>
            <div class="dropdown-divider"></div>
            <form @submit.prevent="logout" class="dropdown-item">
              <button type="submit" class="btn p-0"><i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất</button>
            </form>
          </div>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <Link href="/" class="brand-link">
        <img
          src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png"
          alt="AdminLTE Logo"
          class="brand-image img-circle elevation-3"
          style="opacity: 0.8"
        />
        <span class="brand-text font-weight-light">Hoàng Tâm</span>
      </Link>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex" v-if="$page.props.auth.user">
          <div class="image">
            <img
              :src="$page.props.auth.user.avatar || 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg'"
              class="img-circle elevation-2"
              alt="User Avatar"
              style="width: 2.1rem; height: 2.1rem; object-fit: cover; max-width: none"
            />
          </div>
          <div class="info">
            <a href="#" class="d-block">{{ $page.props.auth.user.name }}</a>
          </div>
        </div>

        <!-- Dự án và vai trò hiện tại -->
        <div class="project-role-panel mt-1 pb-2" v-if="hasProjects">
          <div class="d-flex align-items-center px-2">
            <div class="dropdown w-100">
              <button
                class="btn btn-secondary btn-sm dropdown-toggle w-100 text-left"
                type="button"
                id="projectRoleDropdown"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
              >
                {{
                  currentProjectRole
                    ? currentProjectRole.project_name.length > 12
                      ? currentProjectRole.project_name.slice(0, 10) + '...'
                      : currentProjectRole.project_name
                    : 'Chọn dự án'
                }}
                <span class="badge badge-info ml-2">{{
                  currentProjectRole
                    ? currentProjectRole.role_name.length > 12
                      ? currentProjectRole.role_name.slice(0, 12) + '...'
                      : currentProjectRole.role_name
                    : ''
                }}</span>
              </button>
              <div class="dropdown-menu" aria-labelledby="projectRoleDropdown">
                <template v-for="projectRole in projectRoles" :key="`${projectRole.project_id}-${projectRole.role_id}`">
                  <a class="dropdown-item project-role-item" href="#" @click.prevent="changeProjectRole(projectRole)">
                    <div class="d-flex justify-content-between align-items-center w-100">
                      <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <div class="project-name text-truncate">{{ projectRole.project_name }}</div>
                        <span class="badge badge-info ml-2 flex-shrink-0">{{ projectRole.role_name }}</span>
                      </div>
                      <i v-if="projectRole.is_active" class="fas fa-check text-success ml-2 flex-shrink-0"></i>
                    </div>
                  </a>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <template v-for="item in menuItems">
              <li
                v-if="!item.visible || item.visible()"
                class="nav-item"
                :class="{ 'menu-open': item && item.children && isMenuActive(item) }"
              >
                <!-- Menu có submenu -->
                <template v-if="item.children">
                  <a href="#" class="nav-link" :class="{ active: isMenuActive(item) }">
                    <i :class="[item.icon, 'nav-icon']"></i>
                    <p>
                      {{ item.label }}
                      <i class="fas fa-angle-left right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <template v-for="child in item.children">
                      <li v-if="!child.visible || child.visible()" class="nav-item">
                        <Link :href="child.href" class="nav-link" :class="{ active: child.isActive($page) }">
                          <i :class="[child.icon, 'nav-icon']"></i>
                          <p>{{ child.label }}</p>
                        </Link>
                      </li>
                    </template>
                  </ul>
                </template>

                <!-- Menu không có submenu -->
                <template v-else>
                  <Link :href="item.href" class="nav-link" :class="{ active: item.isActive($page) }">
                    <i :class="[item.icon, 'nav-icon']"></i>
                    <p>{{ item.label }}</p>
                  </Link>
                </template>
              </li>
            </template>
          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">
                <slot name="header">Bảng điều khiển</slot>
              </h1>
            </div>
            <!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <Link href="/">Trang chủ</Link>
                </li>
                <li class="breadcrumb-item active">
                  <slot name="breadcrumb">Bảng điều khiển</slot>
                </li>
              </ol>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <div class="content">
        <div class="container-fluid">
          <slot />
        </div>
      </div>
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
      <!-- To the right -->
      <div class="float-right d-none d-sm-inline">Phiên bản 1.0</div>
      <!-- Default to the left -->
      <strong>Copyright &copy; 2025 <a href="/">Hoàng Tâm</a>.</strong>
    </footer>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { onMounted, watch, computed, ref } from 'vue'
import { useCurrentProject } from '@/Composables/useCurrentProject'
import { usePermission } from '@/Composables/usePermission'
import { showSuccess, showError, showWarning } from '@/utils'
import axios from 'axios'

const $page = usePage()
const { can, hasViewPermissionInAnyProject } = usePermission()

// Sử dụng composable useCurrentProject
const { projectRoles, currentProjectRole, currentProject, currentRole, hasProjects, changeProjectRole, loading } =
  useCurrentProject()

// Kiểm tra xem menu có đang được kích hoạt không
const isMenuActive = (item) => {
  if (item.children) {
    return item.children.some((child) => child.isActive($page))
  }
  return item.isActive($page)
}

const logout = () => {
  router.post('/logout')
}

// Xử lý flash messages
const showFlashMessages = () => {
  const { flash } = usePage().props
  if (flash.success) {
    showSuccess(flash.success)
  }
  if (flash.error) {
    showError(flash.error)
  }
  if (flash.warning) {
    showWarning(flash.warning)
  }
}

// Biến cho thông báo
const notifications = ref([])
const unreadNotificationsCount = ref(0)
const loadingNotifications = ref(false)

// Phương thức tải thông báo
const loadNotifications = async () => {
  loadingNotifications.value = true
  try {
    const response = await axios.get('/notifications')
    notifications.value = response.data.notifications
    unreadNotificationsCount.value = response.data.unread_count
  } catch (error) {
    console.error('Lỗi khi tải thông báo:', error)
  } finally {
    loadingNotifications.value = false
  }
}

// Phương thức đánh dấu thông báo đã đọc
const readNotification = async (notification) => {
  try {
    await axios.post(`/notifications/${notification.id}/read`)
    notification.read_at = new Date()
    unreadNotificationsCount.value = Math.max(0, unreadNotificationsCount.value - 1)

    // Chuyển hướng đến URL của thông báo nếu có
    const url = getNotificationUrl(notification)
    if (url) {
      router.visit(url)
    }
  } catch (error) {
    console.error('Lỗi khi đánh dấu đã đọc:', error)
  }
}

// Phương thức đánh dấu tất cả thông báo đã đọc
const markAllAsRead = async () => {
  try {
    await axios.post('/notifications/read-all')
    notifications.value.forEach((notification) => {
      notification.read_at = new Date()
    })
    unreadNotificationsCount.value = 0
  } catch (error) {
    console.error('Lỗi khi đánh dấu tất cả đã đọc:', error)
  }
}

// Phương thức lấy URL từ thông báo
const getNotificationUrl = (notification) => {
  // Xử lý URL dựa trên loại thông báo
  if (notification.data && notification.data.url) {
    return notification.data.url
  }

  if (notification.data) {
    // Nếu là thông báo về báo cáo mới
    if (notification.type === 'App\\Notifications\\TaskReportSubmitted' || notification.type === 'task_report') {
      // Chuyển đến trang chi tiết task với tab reports
      return `/tasks/${notification.data.task_id}?tab=reports`
    }

    // Nếu là thông báo về duyệt hoặc từ chối báo cáo
    if (notification.type === 'App\\Notifications\\TaskReportReviewed' || notification.type === 'task_report_review') {
      // Chuyển đến trang danh sách các báo cáo đã duyệt với task_id
      return `/task-reports/reviewed?task_id=${notification.data.task_id}`
    }
  }

  return '#'
}

// Phương thức lấy icon cho thông báo
const getNotificationIcon = (notification) => {
  // Xử lý icon dựa trên loại thông báo
  if (notification.data && notification.data.type) {
    switch (notification.data.type) {
      case 'task_report':
        return 'fas fa-tasks'
      case 'task_report_review':
        return 'fas fa-clipboard-check'
      default:
        return 'fas fa-bell'
    }
  }

  return 'fas fa-bell'
}

// Tải thông báo ban đầu và thiết lập interval để cập nhật
onMounted(() => {
  loadNotifications()
  // Cập nhật thông báo mỗi phút
  const notificationInterval = setInterval(loadNotifications, 60000)

  // Hiển thị flash messages khi component được tạo
  showFlashMessages()

  // Script hỗ trợ menu collapse
  // Sử dụng setTimeout để đảm bảo jQuery đã được load
  setTimeout(() => {
    try {
      // Trước tiên gỡ bỏ tất cả các sự kiện click đã được gắn trước đó
      $(document).off('click', '.nav-sidebar .nav-item > a.nav-link')

      // Sau đó mới gắn sự kiện mới
      $(document).on('click', '.nav-sidebar .nav-item > a.nav-link', function (e) {
        if ($(this).next('.nav-treeview').length > 0) {
          e.preventDefault()
          e.stopPropagation() // Ngăn chặn sự kiện lan truyền

          // Toggle class menu-open cho parent
          var $parentLi = $(this).parent('.nav-item')
          $parentLi.toggleClass('menu-open')

          // Toggle hiển thị submenu
          var $submenu = $(this).next('.nav-treeview')
          if ($parentLi.hasClass('menu-open')) {
            $submenu.slideDown(300)
          } else {
            $submenu.slideUp(300)
          }
        }
      })

      // Đảm bảo các menu đã mở sẵn hiển thị đúng
      $('.nav-sidebar .nav-item.menu-open > .nav-treeview').show()
    } catch (error) {
      console.error('Lỗi khi khởi tạo menu collapse:', error)
    }
  }, 200) // Tăng thời gian c
})

// Theo dõi thay đổi của flash messages
watch(
  () => usePage().props.flash,
  (newFlash) => {
    if (newFlash.success || newFlash.error || newFlash.warning) {
      showFlashMessages()
    }
  },
  { deep: true }
)

const menuItems = [
  {
    href: '/',
    icon: 'fas fa-tachometer-alt',
    label: 'Bảng điều khiển',
    isActive(page) {
      return page.component === 'Home'
    },
    visible() {
      return hasViewPermissionInAnyProject('dashboard')
    }
  },
  {
    label: 'Quản lý dự án',
    icon: 'fas fa-project-diagram',
    isActive(page) {
      return page.component.startsWith('Projects') || page.component.startsWith('ProjectCategories')
    },
    visible() {
      return hasViewPermissionInAnyProject('projects') || hasViewPermissionInAnyProject('project-categories')
    },
    children: [
      {
        href: '/projects',
        icon: 'fas fa-list',
        label: 'Danh sách dự án',
        isActive(page) {
          return page.component.startsWith('Projects') && !page.component.includes('Categories')
        },
        visible() {
          return hasViewPermissionInAnyProject('projects')
        }
      },
      {
        href: route('project-categories.index'),
        icon: 'fas fa-tags',
        label: 'Danh mục dự án',
        isActive(page) {
          return page.component.startsWith('ProjectCategories')
        },
        visible() {
          return hasViewPermissionInAnyProject('project-categories')
        }
      }
    ]
  },

  {
    label: 'Quản lý tài chính',
    icon: 'fas fa-money-bill-wave',
    isActive(page) {
      return (
        page.component.startsWith('PaymentVouchers') ||
        page.component.startsWith('ReceiptVouchers') ||
        page.component.startsWith('PaymentCategories') ||
        page.component.startsWith('ReceiptCategories') ||
        page.component.startsWith('Reports') ||
        page.component.startsWith('Loans')
      )
    },
    visible() {
      return (
        hasViewPermissionInAnyProject('payment-vouchers') ||
        hasViewPermissionInAnyProject('receipt-vouchers') ||
        hasViewPermissionInAnyProject('payment-categories') ||
        hasViewPermissionInAnyProject('receipt-categories') ||
        hasViewPermissionInAnyProject('reports') ||
        hasViewPermissionInAnyProject('loans')
      )
    },
    children: [
      {
        href: route('payment-vouchers.index'),
        icon: 'fas fa-money-check-alt',
        label: 'Phiếu chi',
        isActive(page) {
          return page.component.startsWith('PaymentVouchers')
        },
        visible() {
          return hasViewPermissionInAnyProject('payment-vouchers')
        }
      },
      {
        href: route('receipt-vouchers.index'),
        icon: 'fas fa-money-bill-wave',
        label: 'Phiếu thu',
        isActive(page) {
          return page.component.startsWith('ReceiptVouchers')
        },
        visible() {
          return hasViewPermissionInAnyProject('receipt-vouchers')
        }
      },
      {
        href: route('payment-categories.index'),
        icon: 'fas fa-tags',
        label: 'Loại chi',
        isActive(page) {
          return page.component.startsWith('PaymentCategories')
        },
        visible() {
          return hasViewPermissionInAnyProject('payment-categories')
        }
      },
      {
        href: route('receipt-categories.index'),
        icon: 'fas fa-tags',
        label: 'Loại thu',
        isActive(page) {
          return page.component.startsWith('ReceiptCategories')
        },
        visible() {
          return hasViewPermissionInAnyProject('receipt-categories')
        }
      },
      {
        href: route('reports.financial'),
        icon: 'fas fa-chart-pie',
        label: 'Báo cáo thu chi',
        isActive(page) {
          return page.component === 'Reports/Financial'
        },
        visible() {
          return hasViewPermissionInAnyProject('reports')
        }
      },
      {
        href: route('reports.contractor-debt'),
        icon: 'fas fa-file-invoice-dollar',
        label: 'Công nợ nhà cung cấp',
        isActive(page) {
          return page.component === 'Reports/ContractorDebt'
        },
        visible() {
          return hasViewPermissionInAnyProject('reports')
        }
      },
      {
        href: route('reports.customer-debt'),
        icon: 'fas fa-hand-holding-usd',
        label: 'Công nợ khách hàng',
        isActive(page) {
          return page.component === 'Reports/CustomerDebt'
        },
        visible() {
          return hasViewPermissionInAnyProject('reports')
        }
      },
      {
        href: route('loans.index'),
        icon: 'fas fa-money-bill-wave',
        label: 'Khoản vay',
        isActive(page) {
          return page.component.startsWith('Loans')
        },
        visible() {
          return hasViewPermissionInAnyProject('loans')
        }
      }
    ]
  },
  {
    label: 'Quản lý kho',
    icon: 'fas fa-warehouse',
    isActive(page) {
      return (
        page.component.startsWith('Products') ||
        page.component.startsWith('Categories') ||
        page.component.startsWith('Units') ||
        page.component.startsWith('ImportVouchers') ||
        page.component.startsWith('ExportVouchers')
      )
    },
    visible() {
      return (
        hasViewPermissionInAnyProject('products') ||
        hasViewPermissionInAnyProject('categories') ||
        hasViewPermissionInAnyProject('units') ||
        hasViewPermissionInAnyProject('import-vouchers') ||
        hasViewPermissionInAnyProject('export-vouchers')
      )
    },
    children: [
      {
        href: route('products.index'),
        icon: 'fas fa-boxes',
        label: 'Sản phẩm',
        isActive(page) {
          return page.component.startsWith('Products')
        },
        visible() {
          return hasViewPermissionInAnyProject('products')
        }
      },
      {
        href: route('categories.index'),
        icon: 'fas fa-tags',
        label: 'Loại sản phẩm',
        isActive(page) {
          return page.component.startsWith('Categories')
        },
        visible() {
          return hasViewPermissionInAnyProject('categories')
        }
      },
      {
        href: route('units.index'),
        icon: 'fas fa-ruler',
        label: 'Đơn vị tính',
        isActive(page) {
          return page.component.startsWith('Units')
        },
        visible() {
          return hasViewPermissionInAnyProject('units')
        }
      },
      {
        href: route('import-vouchers.index'),
        icon: 'fas fa-file-import',
        label: 'Phiếu nhập kho',
        isActive(page) {
          return page.component.startsWith('ImportVouchers')
        },
        visible() {
          return hasViewPermissionInAnyProject('import-vouchers')
        }
      },
      {
        href: route('export-vouchers.index'),
        icon: 'fas fa-file-export',
        label: 'Phiếu xuất kho',
        isActive(page) {
          return page.component.startsWith('ExportVouchers')
        },
        visible() {
          return hasViewPermissionInAnyProject('export-vouchers')
        }
      }
    ]
  },
  {
    label: 'Quản lý công việc',
    icon: 'fas fa-tasks',
    isActive(page) {
      return page.component.startsWith('Tasks')
    },
    href: route('tasks.index'),
    visible() {
      return hasViewPermissionInAnyProject('tasks')
    }
  },
  {
    href: '/contractors',
    icon: 'fas fa-hard-hat',
    label: 'Quản lý nhà thầu',
    isActive(page) {
      return page.component.startsWith('Contractors')
    },
    visible() {
      return hasViewPermissionInAnyProject('contractors')
    }
  },
  {
    href: route('customers.index'),
    icon: 'fas fa-users',
    label: 'Quản lý khách hàng',
    isActive(page) {
      return page.component.startsWith('Customers')
    },
    visible() {
      return hasViewPermissionInAnyProject('customers')
    }
  },
  {
    label: 'Quản lý hệ thống',
    icon: 'fas fa-cogs',
    isActive(page) {
      return page.component.startsWith('Users') || page.component.startsWith('Roles')
    },
    visible() {
      return hasViewPermissionInAnyProject('users') || hasViewPermissionInAnyProject('roles')
    },
    children: [
      {
        href: '/users',
        icon: 'fas fa-users',
        label: 'Quản lý người dùng',
        isActive(page) {
          return page.component.startsWith('Users')
        },
        visible() {
          return hasViewPermissionInAnyProject('users')
        }
      },
      {
        href: route('roles.index'),
        icon: 'fas fa-user-tag',
        label: 'Vai trò & Phân quyền',
        isActive(page) {
          return page.component.startsWith('Roles')
        },
        visible() {
          return hasViewPermissionInAnyProject('roles')
        }
      }
    ]
  }
]
</script>
<style>
html,
body {
  height: 100%;
  margin: 0;
  padding: 0;
  overflow: hidden;
}

.nav-treeview .nav-link {
  padding-left: 2rem;
}
.wrapper {
  height: 100vh;
  overflow: hidden;
}

.content-wrapper {
  height: calc(100vh - 57px - 57px); /* Trừ đi chiều cao của navbar và footer */
  overflow-y: auto;
}

/* Tùy chỉnh thanh cuộn */
.content-wrapper::-webkit-scrollbar {
  width: 8px;
}

.content-wrapper::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.content-wrapper::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 10px;
}

.content-wrapper::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

/* Đảm bảo bảng có thanh cuộn ngang khi cần thiết */
.table-responsive {
  overflow-x: auto;
}

/* CSS cho dropdown project role */
.project-role-item {
  padding: 10px 15px;
  white-space: normal;
  max-width: 230px;
}

.project-role-item .project-name {
  font-weight: 600;
  margin-right: 5px;
  max-width: 60%;
}

.project-role-item .badge {
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
}

.main-sidebar .brand-text,
.main-header .nav-link,
.content-header h1,
.sidebar-dark-primary .nav-sidebar .nav-link p {
  font-weight: 600;
}

.main-sidebar .brand-text {
  text-transform: uppercase;
  letter-spacing: 1px;
}

.content-header h1 {
  font-size: 1.8rem;
  color: #343a40;
}

.nav-sidebar .nav-link p {
  font-size: 0.95rem;
}

.sidebar-dark-primary {
  background-color: #2c3e50;
}

.sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
  background-color: #e67e22;
}

.btn-primary {
  background-color: #e67e22;
  border-color: #d35400;
}

.btn-primary:hover {
  background-color: #d35400;
  border-color: #c0392b;
}

.dropdown-toggle::after {
  float: right;
  margin-top: 8px;
}
</style>
