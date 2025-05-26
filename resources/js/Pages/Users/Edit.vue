<template>
    <AdminLayout>
        <template #header>Chỉnh sửa người dùng</template>
        <template #breadcrumb>Chỉnh sửa người dùng</template>

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
                                />
                                <div
                                    class="invalid-feedback"
                                    v-if="form.errors.name"
                                >
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
                                />
                                <div
                                    class="invalid-feedback"
                                    v-if="form.errors.email"
                                >
                                    {{ form.errors.email }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password"
                                    >Mật khẩu
                                    <small class="text-muted"
                                        >(để trống nếu không thay đổi)</small
                                    ></label
                                >
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    placeholder="Nhập mật khẩu mới"
                                    v-model="form.password"
                                    :class="{
                                        'is-invalid': form.errors.password,
                                    }"
                                />
                                <div
                                    class="invalid-feedback"
                                    v-if="form.errors.password"
                                >
                                    {{ form.errors.password }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation"
                                    >Xác nhận mật khẩu</label
                                >
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_confirmation"
                                    placeholder="Nhập lại mật khẩu mới"
                                    v-model="form.password_confirmation"
                                />
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Vai trò</label>
                                <select
                                    class="form-control"
                                    id="role"
                                    v-model="form.role"
                                    :class="{ 'is-invalid': form.errors.role }"
                                >
                                    <option value="">Chọn vai trò</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                                <div class="invalid-feedback" v-if="form.errors.role">
                                    {{ form.errors.role }}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="avatar">Ảnh đại diện</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input
                                            type="file"
                                            class="custom-file-input"
                                            id="avatar"
                                            @input="handleFileChange"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.avatar,
                                            }"
                                            accept="image/*"
                                        />
                                        <label
                                            class="custom-file-label"
                                            for="avatar"
                                        >
                                            {{ getFileName() }}
                                        </label>
                                    </div>
                                </div>
                                <div
                                    class="invalid-feedback d-block"
                                    v-if="form.errors.avatar"
                                >
                                    {{ form.errors.avatar }}
                                </div>
                                <div class="mt-2">
                                    <img
                                        :src="
                                            avatarPreview ||
                                            user.avatar ||
                                            'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg'
                                        "
                                        class="img-thumbnail"
                                        style="max-height: 200px"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="form.processing"
                            >
                                <i class="fas fa-save mr-1"></i> Cập nhật
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
import AdminLayout from "@/Layouts/AdminLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const props = defineProps({
    user: Object,
    roles: Array,
    userRoles: Array,
    avatar: String
});

const avatarPreview = ref(null);

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: "",
    password_confirmation: "",
    avatar: null,
    role: props.userRoles && props.userRoles.length > 0 ? props.userRoles[0] : '',
    _method: "PUT",
});

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.avatar = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const getFileName = () => {
    if (form.avatar instanceof File) {
        return form.avatar.name;
    } else if (props.user.avatar) {
        return "Ảnh hiện tại";
    } else {
        return "Chọn file";
    }
};

const submit = () => {
    form.post(`/users/${props.user.id}`, {
        forceFormData: true,
    });
};
</script>
