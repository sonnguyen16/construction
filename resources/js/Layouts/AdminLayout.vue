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

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li
              v-for="(item, index) in menuItems"
              :key="index"
              class="nav-item"
              :class="{ 'menu-open': item.children && isMenuActive(item) }"
            >
              <!-- Menu có submenu -->
              <template v-if="item.children">
                <a href="#" class="nav-link" :class="{ active: isMenuActive(item) }">
                  <i :class="['nav-icon', item.icon]"></i>
                  <p>
                    {{ item.label }}
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li v-for="(child, childIndex) in item.children" :key="childIndex" class="nav-item">
                    <Link
                      :href="child.href"
                      class="nav-link"
                      :class="{ active: child.isActive($page) }"
                      style="padding-left: 25px"
                    >
                      <i :class="['nav-icon', child.icon]" style="font-size: 0.85em"></i>
                      <p style="margin-left: 5px">{{ child.label }}</p>
                    </Link>
                  </li>
                </ul>
              </template>

              <!-- Menu không có submenu -->
              <Link v-else :href="item.href" class="nav-link" :class="{ active: item.isActive($page) }">
                <i :class="['nav-icon', item.icon]"></i>
                <p>{{ item.label }}</p>
              </Link>
            </li>
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
  <!-- ./wrapper -->
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { onMounted, watch, computed, ref } from 'vue'

// Định nghĩa các mục menu
const menuItems = [
  {
    href: '/',
    icon: 'fas fa-tachometer-alt',
    label: 'Bảng điều khiển',
    isActive: (page) => page.url === '/'
  },
  {
    label: 'Quản lý dự án',
    icon: 'fas fa-project-diagram',
    isActive: (page) => page.url.startsWith('/projects') || page.component.startsWith('ProjectCategories/'),
    children: [
      {
        href: '/projects',
        icon: 'fas fa-list',
        label: 'Danh sách dự án',
        isActive: (page) => page.url.startsWith('/projects') && !page.component.startsWith('ProjectCategories/')
      },
      {
        href: route('project-categories.index'),
        icon: 'fas fa-tags',
        label: 'Danh mục dự án',
        isActive: (page) => page.component.startsWith('ProjectCategories/')
      }
    ]
  },

  {
    label: 'Quản lý thu chi',
    icon: 'fas fa-money-bill',
    isActive: (page) =>
      page.component.startsWith('PaymentVouchers/') ||
      page.component.startsWith('ReceiptVouchers/') ||
      page.component.startsWith('PaymentCategories/') ||
      page.component.startsWith('ReceiptCategories/') ||
      page.component.startsWith('Reports/Financial') ||
      page.component.startsWith('Reports/ContractorDebt') ||
      page.component.startsWith('Reports/CustomerDebt'),
    children: [
      {
        href: route('payment-vouchers.index'),
        icon: 'fas fa-money-check-alt',
        label: 'Phiếu chi',
        isActive: (page) => page.component.startsWith('PaymentVouchers/')
      },
      {
        href: route('receipt-vouchers.index'),
        icon: 'fas fa-money-bill-wave',
        label: 'Phiếu thu',
        isActive: (page) => page.component.startsWith('ReceiptVouchers/')
      },
      {
        href: route('payment-categories.index'),
        icon: 'fas fa-tags',
        label: 'Loại chi',
        isActive: (page) => page.component.startsWith('PaymentCategories/')
      },
      {
        href: route('receipt-categories.index'),
        icon: 'fas fa-tags',
        label: 'Loại thu',
        isActive: (page) => page.component.startsWith('ReceiptCategories/')
      },
      {
        href: route('reports.financial'),
        icon: 'fas fa-chart-pie',
        label: 'Báo cáo thu chi',
        isActive: (page) => page.component === 'Reports/Financial'
      },
      {
        href: route('reports.contractor-debt'),
        icon: 'fas fa-file-invoice-dollar',
        label: 'Công nợ nhà cung cấp',
        isActive: (page) => page.component === 'Reports/ContractorDebt'
      },
      {
        href: route('reports.customer-debt'),
        icon: 'fas fa-hand-holding-usd',
        label: 'Công nợ khách hàng',
        isActive: (page) => page.component === 'Reports/CustomerDebt'
      }
    ]
  },
  {
    label: 'Quản lý kho',
    icon: 'fas fa-warehouse',
    isActive: (page) =>
      page.component.startsWith('Products/') ||
      page.url.includes('/categories') ||
      page.component.startsWith('Units/') ||
      page.component.startsWith('ImportVouchers/') ||
      page.component.startsWith('ExportVouchers/'),
    children: [
      {
        href: route('products.index'),
        icon: 'fas fa-boxes',
        label: 'Sản phẩm',
        isActive: (page) => page.component.startsWith('Products/')
      },
      {
        href: route('categories.index'),
        icon: 'fas fa-tags',
        label: 'Loại sản phẩm',
        isActive: (page) => page.url.includes('/categories')
      },
      {
        href: route('units.index'),
        icon: 'fas fa-ruler',
        label: 'Đơn vị tính',
        isActive: (page) => page.component.startsWith('Units/')
      },
      {
        href: route('import-vouchers.index'),
        icon: 'fas fa-file-import',
        label: 'Phiếu nhập kho',
        isActive: (page) => page.component.startsWith('ImportVouchers/')
      },
      {
        href: route('export-vouchers.index'),
        icon: 'fas fa-file-export',
        label: 'Phiếu xuất kho',
        isActive: (page) => page.component.startsWith('ExportVouchers/')
      }
    ]
  },
  {
    label: 'Quản lý công việc',
    icon: 'fas fa-tasks',
    isActive: (page) => page.component.startsWith('Tasks/'),
    href: route('tasks.index')
  },
  {
    href: '/contractors',
    icon: 'fas fa-hard-hat',
    label: 'Quản lý nhà thầu',
    isActive: (page) => page.url.startsWith('/contractors')
  },
  {
    href: route('customers.index'),
    icon: 'fas fa-users',
    label: 'Quản lý khách hàng',
    isActive: (page) => page.component.startsWith('Customers/')
  },
  {
    href: '/users',
    icon: 'fas fa-users',
    label: 'Quản lý người dùng',
    isActive: (page) => page.url.startsWith('/users')
  }
]

// Kiểm tra xem menu có đang được kích hoạt không
const isMenuActive = (item) => {
  return item.isActive($page)
}
import { showSuccess, showError, showWarning } from '@/utils'

const $page = usePage()

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

// Hiển thị flash messages khi component được tạo
onMounted(() => {
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
  }, 200) // Tăng thời gian chờ lên 200ms
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
</script>

<style>
html,
body {
  height: 100%;
  margin: 0;
  padding: 0;
  overflow: hidden;
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
</style>
