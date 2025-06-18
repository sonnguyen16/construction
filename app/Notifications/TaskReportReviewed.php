<?php

namespace App\Notifications;

use App\Models\TaskReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReportReviewed extends Notification implements ShouldQueue
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

        $status = $this->taskReport->status === 'approved' ? 'đã được duyệt' : 'đã bị từ chối';
        $subject = 'Báo cáo tiến độ công việc ' . $status;

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Xin chào ' . $notifiable->name)
            ->line('Báo cáo tiến độ của bạn cho công việc: ' . $this->taskReport->task->name . ' ' . $status)
            ->line('Người duyệt: ' . $this->taskReport->reviewer->name)
            ->when($this->taskReport->review_message, function ($mail) {
                return $mail->line('Phản hồi: ' . $this->taskReport->review_message);
            })
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
        $status = $this->taskReport->status === 'approved' ? 'đã được duyệt' : 'đã bị từ chối';
        
        return [
            'title' => 'Báo cáo tiến độ ' . $status,
            'message' => 'Báo cáo tiến độ công việc ' . $this->taskReport->task->name . ' ' . $status,
            'task_id' => $this->taskReport->task_id,
            'report_id' => $this->taskReport->id,
            'reviewer_id' => $this->taskReport->reviewer_id,
            'project_id' => $this->taskReport->task->project_id,
            'status' => $this->taskReport->status,
            'type' => 'App\\Notifications\\TaskReportReviewed'
        ];
    }
}
