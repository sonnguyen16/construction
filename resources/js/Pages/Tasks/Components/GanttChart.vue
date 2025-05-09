<template>
  <div>
    <div class="toolbar">
      <label class="mr-1 text-sm font-normal">Chế độ xem:</label>
      <select v-model="currentView" @change="changeView">
        <option value="day">Day</option>
        <option value="week">Week</option>
        <option value="month">Month</option>
        <option value="year">Year</option>
      </select>
    </div>
    <div ref="ganttContainer" style="height: 70vh"></div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import 'dhtmlx-gantt/codebase/dhtmlxgantt.css'
import gantt from 'dhtmlx-gantt'

const ganttContainer = ref(null)
const currentView = ref('day')

// Đổi chế độ xem
function changeView() {
  switch (currentView.value) {
    case 'day':
      gantt.config.scale_unit = 'day'
      gantt.config.date_scale = '%d %M'
      gantt.config.subscales = []
      break
    case 'week':
      gantt.config.scale_unit = 'week'
      gantt.config.date_scale = 'Tuần #%W'
      gantt.config.subscales = [{ unit: 'day', step: 1, date: '%D' }]
      break
    case 'month':
      gantt.config.scale_unit = 'month'
      gantt.config.date_scale = '%F, %Y'
      gantt.config.subscales = [{ unit: 'week', step: 1, date: 'Tuần %W' }]
      break
    case 'year':
      gantt.config.scale_unit = 'year'
      gantt.config.date_scale = '%Y'
      gantt.config.subscales = [{ unit: 'month', step: 1, date: '%M' }]
      break
  }

  gantt.render() // 🔥 cập nhật lại Gantt sau khi thay đổi scale
}

onMounted(() => {
  gantt.config.date_format = '%d-%m-%Y'

  // Cột task + nút thêm task con
  gantt.config.columns = [
    { name: 'text', label: 'Tên công việc', tree: true, width: 200, editor: { type: 'text', map_to: 'text' } },
    {
      name: 'start_date',
      label: 'Bắt đầu',
      align: 'center',
      width: 100,
      editor: { type: 'date', map_to: 'start_date' }
    },
    {
      name: 'duration',
      label: 'Thời gian',
      align: 'center',
      width: 90,
      editor: { type: 'number', map_to: 'duration' }
    },
    {
      name: 'progress',
      label: '% Hoàn thành',
      align: 'center',
      width: 120,
      template: (task) => `${Math.round(task.progress * 100)}%`,
      editor: { type: 'number', map_to: 'progress' }
    },
    {
      name: 'add',
      label: '',
      width: 50,
      template: (task) => {
        if (!task.parent || task.parent === 0) {
          return `<button class='add-subtask-btn' data-taskid='${task.id}'>➕</button>`
        }
        return ''
      }
    }
  ]

  gantt.config.calendar_property = 'start_date'

  // Khởi tạo thang thời gian mặc định// Thiết lập mặc định là 'day'
  gantt.config.scale_unit = 'day'
  gantt.config.date_scale = '%d %M'
  gantt.config.subscales = []

  gantt.attachEvent('onAfterTaskAdd', function (id, task) {
    //add to database
    console.log(id, task)
  })

  gantt.attachEvent('onAfterTaskUpdate', function (id, task) {
    console.log(id, task)
  })

  gantt.attachEvent('onAfterTaskDelete', function (id, task) {
    //delete to database
    console.log(id, task)
  })

  gantt.attachEvent('onAfterLinkAdd', function (id, link) {
    console.log('Đã tạo liên kết:', link)
  })

  gantt.attachEvent('onAfterLinkDelete', function (id, link) {
    console.log('Đã xóa liên kết:', link)
    // type: 0 (Finish to Start), 1 (Start to Start), 2 (Finish to Finish), 3 (Start to Finish)
  })

  // Dữ liệu mẫu
  gantt.init(ganttContainer.value)
  gantt.parse({
    data: [
      { id: 1, text: 'Project A', start_date: '09-05-2025', duration: 5, open: true, progress: 0.2 },
      { id: 2, text: 'Task con A1', start_date: '10-05-2025', duration: 3, parent: 1, progress: 0.5 }
    ]
  })
})
</script>

<style scoped>
.toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}
</style>
