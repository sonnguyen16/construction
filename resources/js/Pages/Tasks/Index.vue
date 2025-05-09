<template>
    <AdminLayout>
        <template #header>Quản lý công việc</template>
        <template #breadcrumb>Quản lý công việc</template>
        <GanttChart />
    </AdminLayout>
</template>

<script setup>
import GanttChart from '@/Pages/Tasks/Components/GanttChart.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
</script>
