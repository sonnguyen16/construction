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
            <li class="nav-item">
              <Link href="/" class="nav-link" :class="{ active: $page.url === '/' }">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Bảng điều khiển</p>
              </Link>
            </li>
            <li class="nav-item">
              <Link
                href="/projects"
                class="nav-link"
                :class="{
                  active: $page.url.startsWith('/projects')
                }"
              >
                <i class="nav-icon fas fa-project-diagram"></i>
                <p>Quản lý dự án</p>
              </Link>
            </li>
            <li class="nav-item">
              <Link
                href="/contractors"
                class="nav-link"
                :class="{
                  active: $page.url.startsWith('/contractors')
                }"
              >
                <i class="nav-icon fas fa-hard-hat"></i>
                <p>Quản lý nhà thầu</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link
                :href="route('customers.index')"
                class="nav-link"
                :class="{ active: $page.component.startsWith('Customers/') }"
              >
                <i class="fas fa-users nav-icon"></i>
                <p>Quản lý khách hàng</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link
                :href="route('payment-vouchers.index')"
                class="nav-link"
                :class="{ active: $page.component.startsWith('PaymentVouchers/') }"
              >
                <i class="fas fa-money-check-alt nav-icon"></i>
                <p>Quản lý phiếu chi</p>
              </Link>
            </li>
            <li class="nav-item">
              <Link
                :href="route('receipt-vouchers.index')"
                class="nav-link"
                :class="{ active: $page.component.startsWith('ReceiptVouchers/') }"
              >
                <i class="fas fa-money-bill-wave nav-icon"></i>
                <p>Quản lý phiếu thu</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link
                href="/users"
                class="nav-link"
                :class="{
                  active: $page.url.startsWith('/users')
                }"
              >
                <i class="nav-icon fas fa-users"></i>
                <p>Quản lý người dùng</p>
              </Link>
            </li>

            <li class="nav-item">
              <Link
                :href="route('reports.payments-by-project')"
                class="nav-link"
                :class="{ active: route().current('reports.payments-by-project') }"
              >
                <i class="nav-icon fas fa-money-bill-wave"></i>
                <p>Báo cáo chi</p>
              </Link>
            </li>
            <li class="nav-item">
              <Link
                :href="route('reports.receipts-by-project')"
                class="nav-link"
                :class="{ active: route().current('reports.receipts-by-project') }"
              >
                <i class="nav-icon fas fa-money-bill-wave"></i>
                <p>Báo cáo thu</p>
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
import { onMounted, watch } from 'vue'
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
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* Đảm bảo chiều cao cố định và cho phép cuộn */
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

/* Các style khác giữ nguyên */
body,
.wrapper {
  font-family: 'Montserrat', sans-serif !important;
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
