<template>
  <AdminLayout>
    <template #header>Thùng rác công việc</template>
    <template #breadcrumb>
      <div class="flex items-center">
        <span> Quản lý công việc </span>
        <span class="mx-2">/</span>
        <span>Thùng rác</span>
      </div>
    </template>

    <TrashList :projects="projects" :default-project="defaultProject" />
  </AdminLayout>
</template>

<script setup>
import TrashList from '@/Pages/Tasks/Components/TrashList.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  projects: Array,
  defaultProject: Object
})
</script>
