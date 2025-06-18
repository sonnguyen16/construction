<?php

namespace App\Notifications;

use App\Models\TaskReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReportSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $taskReport;

    /**
     * Tạo một instance thông báo mới.
     */
    public function __construct(TaskReport $taskReport)
    {
        $this->taskReport = $taskReport;
    }

    /**
     * Lấy các kênh thông báo.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Lấy nội dung thông báo dạng mail.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('tasks.show', [
            'project' => $this->taskReport->task->project_id,
            'task' => $this->taskReport->task_id,
            'tab' => 'reports'
        ]);

        return (new MailMessage)
            ->subject('Báo cáo tiến độ công việc mới')
            ->greeting('Xin chào ' . $notifiable->name)
            ->line('Một báo cáo tiến độ mới đã được gửi cho công việc: ' . $this->taskReport->task->name)
            ->line('Người gửi: ' . $this->taskReport->user->name)
            ->line('Tiến độ: ' . $this->taskReport->progress . '%')
            ->line('Nội dung: ' . $this->taskReport->message)
            ->action('Xem chi tiết', $url)
            ->line('Cảm ơn bạn đã sử dụng ứng dụng của chúng tôi!');
    }

    /**
     * Lấy nội dung thông báo dạng mảng.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Báo cáo tiến độ công việc mới',
            'message' => 'Công việc ' . $this->taskReport->task->name . ' có báo cáo tiến độ mới',
            'task_id' => $this->taskReport->task_id,
            'report_id' => $this->taskReport->id,
            'user_id' => $this->taskReport->user_id,
            'project_id' => $this->taskReport->task->project_id,
            'progress' => $this->taskReport->progress,
            'type' => 'App\\Notifications\\TaskReportSubmitted'
        ];
    }
}
