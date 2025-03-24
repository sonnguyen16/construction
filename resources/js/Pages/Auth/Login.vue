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
import { ref } from 'vue'
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
