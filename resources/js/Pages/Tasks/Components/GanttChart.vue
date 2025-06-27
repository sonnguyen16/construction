<template>
  <div>
    <div class="toolbar flex align-items-center justify-between mb-3">
      <div class="flex align-items-center gap-4">
        <div class="flex align-items-center gap-2">
          <label class="text-md font-normal">Dự án:</label>
          <select v-model="selectedProject" @change="loadTasks" class="select" disabled>
            <option v-for="project in props.projects" :key="project.id" :value="project.id">{{ project.name }}</option>
          </select>
          <small v-if="currentProject.value" class="text-muted"
            >Dự án được điều chỉnh từ dropdown chọn dự án chính</small
          >
        </div>
        <div class="flex align-items-center gap-2">
          <label class="text-md font-normal">Chế độ xem:</label>
          <select v-model="currentView" @change="changeView" class="select">
            <option value="day">Ngày</option>
            <option value="week">Tuần</option>
            <option value="month">Tháng</option>
            <option value="year">Năm</option>
          </select>
        </div>
      </div>
      <div>
        <Link :href="route('tasks.trash')" class="btn btn-sm btn-secondary">
          <i class="fas fa-trash mr-1"></i> Công việc đã xóa
        </Link>
      </div>
    </div>
    <div ref="ganttContainer" style="height: calc(100vh - 250px)"></div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import 'dhtmlx-gantt/codebase/dhtmlxgantt.css'
import gantt from 'dhtmlx-gantt'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'
import { usePermission } from '@/Composables/usePermission'
import { showWarning } from '@/utils'
import { useCurrentProject } from '@/Composables/useCurrentProject'

const ganttContainer = ref(null)
const currentView = ref(localStorage.getItem('gantt_view_mode') || 'day')
const selectedProject = ref(localStorage.getItem('gantt_project_id') || null)
const props = defineProps({
  projects: Array,
  defaultProject: Object
})

const { currentProject } = useCurrentProject()

// Đổi chế độ xem
function changeView() {
  switch (currentView.value) {
    case 'day':
      gantt.config.scale_unit = 'day'
      gantt.config.date_scale = '%d %M'
      gantt.config.subscales = []
      localStorage.setItem('gantt_view_mode', 'day')
      break
    case 'week':
      gantt.config.scale_unit = 'week'
      gantt.config.date_scale = 'Week #%W'
      gantt.config.subscales = [{ unit: 'day', step: 1, date: '%D' }]
      localStorage.setItem('gantt_view_mode', 'week')
      break
    case 'month':
      gantt.config.scale_unit = 'month'
      gantt.config.date_scale = '%F, %Y '
      gantt.config.subscales = [{ unit: 'week', step: 1, date: 'Week %W' }]
      localStorage.setItem('gantt_view_mode', 'month')
      break
    case 'year':
      gantt.config.scale_unit = 'year'
      gantt.config.date_scale = '%Y'
      gantt.config.subscales = [{ unit: 'month', step: 1, date: '%M' }]
      localStorage.setItem('gantt_view_mode', 'year')
      break
  }

  gantt.render() // 🔥 cập nhật lại Gantt sau khi thay đổi scale
}

// Tải danh sách công việc theo dự án
async function loadTasks() {
  if (!selectedProject.value) return

  try {
    const response = await axios.get(`/projects/${selectedProject.value}/tasks`)
    gantt.clearAll()
    gantt.parse({
      data: response.data.data,
      links: response.data.links
    })
  } catch (error) {
    console.error('Lỗi khi tải dữ liệu công việc:', error)
  }
}

// Xử lý kéo thả task
async function handleTaskDrag() {
  try {
    // Lấy tất cả các task từ gantt
    const allTasks = []
    gantt.eachTask(function (task) {
      allTasks.push({
        id: task.id,
        parent_id: task.parent > 0 ? task.parent : null,
        order: gantt.getGlobalTaskIndex(task.id)
      })
    })

    // Gửi tất cả thông tin task lên server
    await axios.post('/tasks/update-all-positions', {
      tasks: allTasks
    })
  } catch (error) {
    console.error('Lỗi khi cập nhật vị trí công việc:', error)
    loadTasks() // Tải lại task nếu có lỗi
  }
}

// Khởi tạo Gantt
function initGantt() {
  gantt.config.show_task_wbs = true
  gantt.config.date_format = '%d/%m/%Y'
  gantt.config.date_grid = '%d/%m/%Y'
  gantt.config.autoscroll = true

  // Cấu hình hiển thị công việc cha dưới dạng đường line
  gantt.config.open_tree_initially = true
  gantt.config.show_progress = true

  // Bật chức năng kéo thả
  gantt.config.order_branch = true // Cho phép sắp xếp lại thứ tự
  gantt.config.order_branch_free = true // Cho phép kéo task đến bất kỳ vị trí nào
  gantt.config.drag_move = true // Cho phép di chuyển task

  // Tắt các warning và error mặc định của dhtmlx
  gantt.config.show_errors = false
  gantt.config.show_warnings = false

  //cho phép scroll gantt
  gantt.config.autoscroll = true

  // Định nghĩa loại task dựa trên cấp bậc
  gantt.templates.task_class = function (start, end, task) {
    if (!task.parent || task.parent == 0) {
      return 'level-1-task'
    }
    return ''
  }

  // Tùy chỉnh lightbox
  gantt.config.lightbox.sections = [
    { name: 'description', height: 70, map_to: 'text', type: 'textarea', focus: true },
    { name: 'time', type: 'duration', map_to: 'auto' },
    { name: 'predecessors', type: 'predecessors', map_to: 'auto' }
  ]

  // Định nghĩa các nhãn cho lightbox
  gantt.locale.labels.section_description = 'Tên công việc'
  gantt.locale.labels.section_time = 'Thời gian'
  gantt.locale.labels.section_predecessors = 'Công việc tiền nhiệm'

  // Tạo control tùy chỉnh cho predecessors
  gantt.form_blocks.predecessors = {
    render: function (sns) {
      return "<div class='predecessors-wrapper'><div class='predecessors-header'><div class='predecessor-task'>Công việc</div><div class='predecessor-type'>Loại liên kết</div><div class='predecessor-action'></div></div><div class='predecessors-list'></div></div>"
    },
    set_value: function (node, value, task) {
      var listNode = node.querySelector('.predecessors-list')
      listNode.innerHTML = ''

      // Lấy tất cả các liên kết đến task hiện tại
      var links = gantt.getLinks()
      var predecessors = links.filter(function (link) {
        return link.target == task.id
      })

      if (predecessors.length === 0) {
        listNode.innerHTML = '<div class="no-predecessors">Không có công việc tiền nhiệm</div>'
      } else {
        predecessors.forEach(function (link) {
          var sourceTask = gantt.getTask(link.source)
          var row = document.createElement('div')
          row.className = 'predecessor-row'
          row.dataset.linkId = link.id

          var linkTypes = [
            { id: 0, text: 'Kết thúc - Bắt đầu (FS)' },
            { id: 1, text: 'Bắt đầu - Bắt đầu (SS)' },
            { id: 2, text: 'Kết thúc - Kết thúc (FF)' },
            { id: 3, text: 'Bắt đầu - Kết thúc (SF)' }
          ]

          var typeSelect = '<select class="link-type-select" data-link-id="' + link.id + '">'
          linkTypes.forEach(function (type) {
            var selected = type.id == link.type ? 'selected' : ''
            typeSelect += '<option value="' + type.id + '" ' + selected + '>' + type.text + '</option>'
          })
          typeSelect += '</select>'

          row.innerHTML =
            '<div class="predecessor-task">' +
            sourceTask.text +
            '</div>' +
            '<div class="predecessor-type">' +
            typeSelect +
            '</div>' +
            '<div class="predecessor-action"><button class="btn-delete-predecessor" data-link-id="' +
            link.id +
            '"><i class="fas fa-trash"></i></button></div>'

          listNode.appendChild(row)
        })

        // Thêm sự kiện cho các nút xóa và thay đổi loại liên kết
        var deleteButtons = node.querySelectorAll('.btn-delete-predecessor')
        deleteButtons.forEach(function (btn) {
          btn.onclick = function () {
            var linkId = this.dataset.linkId
            gantt.confirm({
              text: 'Bạn có chắc chắn muốn xóa liên kết này?',
              callback: function (result) {
                if (result) {
                  gantt.deleteLink(linkId)
                }
              }
            })
          }
        })

        var typeSelects = node.querySelectorAll('.link-type-select')
        typeSelects.forEach(function (select) {
          select.onchange = function () {
            var linkId = this.dataset.linkId
            var link = gantt.getLink(linkId)
            var newType = parseInt(this.value)

            if (link && link.type != newType) {
              link.type = newType
              gantt.updateLink(linkId)
            }
          }
        })
      }

      // Không cần thêm sự kiện cho nút thêm tiền nhiệm vì đã bỏ nút này

      return true
    },
    get_value: function (node, task) {
      return task // Không cần trả về gì đặc biệt vì chúng ta quản lý liên kết riêng biệt
    },
    focus: function (node) {}
  }

  // Cột task + nút thêm task con và nút truy cập chi tiết
  gantt.config.columns = [
    {
      name: 'wbs',
      label: 'WBS',
      width: 60,
      template: gantt.getWBSCode
    },
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
      label: 'Số ngày',
      align: 'center',
      width: 90,
      editor: { type: 'number', map_to: 'duration', max: 1000 }
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
      name: 'assignees',
      label: 'Người thực hiện',
      align: 'left',
      width: 150,
      template: (task) => {
        if (!task.users || !Array.isArray(task.users) || task.users.length === 0) {
          return '<span class="text-muted">Chưa phân công</span>'
        }

        // Lọc ra những người thực hiện (role = 0)
        const assignees = task.users.filter((user) => user.pivot && user.pivot.role === 0)

        if (assignees.length === 0) {
          return '<span class="text-muted">Chưa phân công</span>'
        }

        // Hiển thị tối đa 3 người, nếu nhiều hơn thì hiển thị số còn lại
        const maxDisplay = 2
        const displayUsers = assignees.slice(0, maxDisplay)
        const remainingCount = assignees.length - maxDisplay

        let html = '<div class="assignee-list">'

        // Hiển thị avatar và tên của người thực hiện
        displayUsers.forEach((user) => {
          const avatarUrl = user.avatar
            ? user.avatar.startsWith('http')
              ? user.avatar
              : `/storage/avatars/${user.avatar}`
            : '/images/default-avatar.jpg'

          html += `
            <div class="assignee-item" title="${user.name}">
              <img src="${avatarUrl}" class="assignee-avatar" alt="${user.name}" />
              <span class="assignee-name">${user.name}</span>
            </div>
          `
        })

        // Hiển thị số người còn lại
        if (remainingCount > 0) {
          html += `<div class="assignee-more">+${remainingCount}</div>`
        }

        html += '</div>'
        return html
      }
    },
    {
      name: 'add',
      label: '',
      width: 100,
      align: 'center',
      template: (task) => {
        let html = ''
        // Nút thêm task con - chỉ hiển thị khi có quyền tạo công việc trong dự án
        html += `<button class='add-subtask-btn gantt-action-btn' title='Thêm công việc con' data-taskid='${task.id}'>➕</button>`
        return html
      }
    },
    {
      name: 'users&products',
      label: 'Quản lý',
      width: 100,
      align: 'center',
      template: (task) => {
        let html = ''
        // Nút quản lý vật tư và nhân sự - chỉ hiển thị khi có quyền xem chi tiết trong dự án
        html += `<a href='/tasks/${task.id}' class='gantt-action-btn' title='Quản lý vật tư và nhân sự'><i class='fas fa-cog'></i></a>`
        return html
      }
    }
  ]

  // Khởi tạo thang thời gian mặc định
  gantt.config.scale_unit = 'day'
  gantt.config.date_scale = '%d %M'
  gantt.config.subscales = []

  // Xử lý sự kiện sau khi hoàn tất việc sắp xếp lại hàng
  gantt.attachEvent('onRowDragEnd', function (id, target) {
    handleTaskDrag()
  })

  gantt.attachEvent('onAfterTaskAdd', async function (id, task) {
    try {
      // Đảm bảo ngày có định dạng d-m-Y
      const response = await axios.post('/tasks', {
        name: task.text,
        start_date: new Date(task.start_date).toDateString(),
        duration: task.duration,
        progress: task.progress || 0,
        project_id: selectedProject.value,
        parent_id: task.parent > 0 ? task.parent : null
      })

      // Cập nhật ID từ server
      gantt.changeTaskId(id, response.data.id)
    } catch (error) {
      showWarning('Lỗi khi thêm công việc: ' + error.response.data.error)
      loadTasks()
    }
  })

  gantt.attachEvent('onAfterTaskUpdate', async function (id, task) {
    try {
      // Đảm bảo ngày có định dạng d-m-Y
      await axios.put(`/tasks/${id}`, {
        name: task.text,
        start_date: new Date(task.start_date).toDateString(),
        duration: task.duration,
        progress: task.progress,
        parent_id: task.parent > 0 ? task.parent : null
      })
    } catch (error) {
      showWarning('Lỗi khi cập nhật công việc: ' + error.response.data.error)
      loadTasks()
    }
  })

  gantt.attachEvent('onAfterTaskDelete', async function (id) {
    try {
      await axios.delete(`/tasks/${id}`)
      loadTasks()
    } catch (error) {
      showWarning('Lỗi khi xóa công việc: ' + error.response.data.error)
      loadTasks()
    }
  })

  // Xử lý sự kiện thêm liên kết
  gantt.attachEvent('onAfterLinkAdd', async function (id, link) {
    try {
      const response = await axios.post('/task-links', {
        source_id: link.source,
        target_id: link.target,
        type: link.type
      })

      // Cập nhật ID từ server
      gantt.changeLinkId(id, response.data.id)
      loadTasks()
    } catch (error) {
      showWarning('Lỗi khi tạo liên kết: ' + error.response.data.error)
      loadTasks()
    }
  })

  // Xử lý sự kiện cập nhật liên kết (khi thay đổi loại liên kết)
  gantt.attachEvent('onAfterLinkUpdate', async function (id, link) {
    try {
      await axios.put(`/task-links/${id}`, {
        source_id: link.source,
        target_id: link.target,
        type: link.type
      })
    } catch (error) {
      showWarning('Lỗi khi cập nhật liên kết: ' + error.response.data.error)
      loadTasks()
    }
  })

  // Xử lý sự kiện xóa liên kết
  gantt.attachEvent('onAfterLinkDelete', async function (id, link) {
    try {
      await axios.delete(`/task-links/${id}`)
      loadTasks()
    } catch (error) {
      showWarning('Lỗi khi xóa liên kết: ' + error.response.data.error)
    }
  })

  gantt.init(ganttContainer.value)
}

// Tải danh sách dự án
const loadProjects = async () => {
  try {
    // Ưu tiên sử dụng dự án hiện tại từ composable nếu có
    if (currentProject.value) {
      selectedProject.value = currentProject.value.id
      loadTasks()
      return
    }

    // Nếu không có dự án hiện tại, thử lấy từ localStorage
    const project_id_from_storage = localStorage.getItem('gantt_project_id')

    if (project_id_from_storage) {
      // Kiểm tra xem dự án có tồn tại không
      const project_exists = props.projects.some((project) => project.id == project_id_from_storage)
      if (project_exists) {
        selectedProject.value = project_id_from_storage
        loadTasks()
        return
      }
    }

    // Nếu không có dự án nào được lưu hoặc dự án không tồn tại, sử dụng dự án mặc định hoặc dự án đầu tiên
    if (props.projects.length > 0) {
      const defaultId = props.defaultProject?.id || props.projects[0].id
      selectedProject.value = defaultId
      localStorage.setItem('gantt_project_id', defaultId)
      loadTasks()
    }
  } catch (error) {
    console.error('Lỗi khi tải danh sách dự án:', error)
  }
}

// Theo dõi thay đổi của dự án hiện tại
watch(
  () => currentProject.value,
  (newProject) => {
    if (newProject) {
      selectedProject.value = newProject.id
      // Lưu vào localStorage
      localStorage.setItem('gantt_project_id', newProject.id)
      // Tải lại các công việc khi dự án thay đổi
      loadTasks()
    }
  }
)

// Theo dõi thay đổi của selectedProject
watch(
  () => selectedProject.value,
  (newValue) => {
    if (newValue) {
      localStorage.setItem('gantt_project_id', newValue)
    }
  }
)

onMounted(() => {
  initGantt()
  gantt.init(ganttContainer.value)

  // Nếu có dự án mặc định, chọn dự án đó
  if (props.defaultProject && !currentProject.value) {
    selectedProject.value = props.defaultProject.id
  }

  loadProjects()
  changeView()
})
</script>

<style scoped>
.toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.select {
  padding: 4px 10px;
  width: 200px;
  border: 1px solid #ccc;
}

/* CSS cho các nút thao tác trong Gantt */
:deep(.gantt-action-btn) {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  margin: 0 2px;
  border-radius: 4px;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  color: #495057;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
}

:deep(.gantt-action-btn:hover) {
  background-color: #e9ecef;
  border-color: #ced4da;
}

:deep(.add-subtask-btn) {
  color: #28a745;
}

:deep(.fa-cog) {
  color: #007bff;
  font-size: 12px;
}

/* Đảm bảo chỉ các task cấp 1 được hiển thị dạng line */
:deep(.level-1-task) {
  background-color: transparent !important;
  border-top: 3px solid #2196f3 !important;
  border-left: none !important;
  border-right: none !important;
  border-bottom: none !important;
  box-shadow: none !important;
  height: 4px !important;
  margin-top: 12px !important;
}

/* CSS cho cột người thực hiện */
:deep(.assignee-list) {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

:deep(.assignee-item) {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 2px 0;
}

:deep(.assignee-avatar) {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid #e0e0e0;
}

:deep(.assignee-name) {
  font-size: 12px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100px;
}

:deep(.assignee-more) {
  font-size: 12px;
  color: #666;
  background-color: #f0f0f0;
  border-radius: 10px;
  padding: 1px 8px;
  display: inline-block;
  margin-top: 2px;
}

:deep(.level-1-task):before {
  content: '' !important;
  position: absolute !important;
  left: 0 !important;
  top: -9px !important;
  height: 16px !important;
  width: 3px !important;
  background-color: #2196f3 !important;
}

:deep(.level-1-task):after {
  content: '' !important;
  position: absolute !important;
  right: 0 !important;
  top: -9px !important;
  height: 16px !important;
  width: 3px !important;
  background-color: #2196f3 !important;
}

:deep(.level-1-task) .gantt_task_progress {
  display: none !important;
}

:deep(.level-1-task) .gantt_task_content {
  display: none !important;
}
</style>
