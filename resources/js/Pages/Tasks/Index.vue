<template>
  <AdminLayout>
    <template #header>Quản lý công việc</template>
    <template #breadcrumb>Quản lý công việc</template>
    <GanttChart :projects="projects" :defaultProject="defaultProject" />
  </AdminLayout>
</template>

<script setup>
import GanttChart from '@/Pages/Tasks/Components/GanttChart.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  projects: Array,
  defaultProject: Object
})
</script>

<style scoped>
.btn {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary {
  background-color: #64748b;
  color: white;
}

.btn-secondary:hover {
  background-color: #475569;
}
</style>
