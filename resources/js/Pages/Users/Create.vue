<template>
    <AdminLayout>
        <template #header>Thêm người dùng mới</template>
        <template #breadcrumb>Thêm người dùng</template>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin người dùng</h3>
                    </div>
                    <form @submit.prevent="submit">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Tên</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="name"
                                    placeholder="Nhập tên người dùng"
                                    v-model="form.name"
                                    :class="{ 'is-invalid': form.errors.name }"
                                >
                                <div class="invalid-feedback" v-if="form.errors.name">
                                    {{ form.errors.name }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    placeholder="Nhập email"
                                    v-model="form.email"
                                    :class="{ 'is-invalid': form.errors.email }"
                                >
                                <div class="invalid-feedback" v-if="form.errors.email">
                                    {{ form.errors.email }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password">Mật khẩu</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    placeholder="Nhập mật khẩu"
                                    v-model="form.password"
                                    :class="{ 'is-invalid': form.errors.password }"
                                >
                                <div class="invalid-feedback" v-if="form.errors.password">
                                    {{ form.errors.password }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation">Xác nhận mật khẩu</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_confirmation"
                                    placeholder="Nhập lại mật khẩu"
                                    v-model="form.password_confirmation"
                                >
                            </div>
                            <div class="form-group">
                                <label for="avatar">Ảnh đại diện</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input
                                            type="file"
                                            class="custom-file-input"
                                            id="avatar"
                                            @input="form.avatar = $event.target.files[0]"
                                            :class="{ 'is-invalid': form.errors.avatar }"
                                        >
                                        <label class="custom-file-label" for="avatar">
                                            {{ form.avatar ? form.avatar.name : 'Chọn file' }}
                                        </label>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block" v-if="form.errors.avatar">
                                    {{ form.errors.avatar }}
                                </div>
                                <div class="mt-2" v-if="avatarPreview">
                                    <img :src="avatarPreview" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                                <i class="fas fa-save mr-1"></i> Lưu
                            </button>
                            <Link href="/users" class="btn btn-default ml-2">
                                <i class="fas fa-times mr-1"></i> Hủy
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const avatarPreview = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    avatar: null
});

watch(() => form.avatar, (newAvatar) => {
    if (newAvatar && newAvatar instanceof File) {
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(newAvatar);
    }
});

const submit = () => {
    form.post('/users', {
        forceFormData: true
    });
};
</script>
