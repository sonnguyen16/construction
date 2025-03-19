<template>
  <div class="login-page">
    <div class="login-box">
      <div class="login-logo">
        <a href="/"><b>Hoàng Tâm</b></a>
      </div>
      <!-- /.login-logo -->
      <div class="card">
        <div class="card-body login-card-body">
          <p class="login-box-msg">Đăng nhập để bắt đầu</p>

          <form @submit.prevent="submit">
            <div class="input-group mb-3">
              <input type="email" class="form-control" placeholder="Email" v-model="form.email" />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-envelope"></span>
                </div>
              </div>
            </div>
            <div class="input-group mb-3">
              <input type="password" class="form-control" placeholder="Mật khẩu" v-model="form.password" />
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
            </div>
            <div class="row">
              <!-- /.col -->
              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block" :disabled="processing">Đăng nhập</button>
              </div>
              <!-- /.col -->
            </div>
          </form>
        </div>
        <!-- /.login-card-body -->
      </div>
    </div>
    <!-- /.login-box -->
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const processing = ref(false)
const form = useForm({
  email: '',
  password: '',
  remember: false
})

const submit = () => {
  processing.value = true
  form.post('/login', {
    onFinish: () => {
      processing.value = false
    }
  })
}
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

.login-page {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f4f6f9;
}
</style>
