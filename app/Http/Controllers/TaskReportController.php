<?php

namespace App\Http\Controllers;

use App\Helpers\ProjectPermission;
use App\Models\Task;
use App\Models\TaskReport;
use App\Models\TaskReportFile;
use App\Notifications\TaskReportSubmitted;
use App\Notifications\TaskReportReviewed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class TaskReportController extends Controller
{
    /**
     * Lấy danh sách báo cáo của một task
     */
    public function index(Request $request, $projectId, $taskId)
    {
        // Kiểm tra quyền truy cập
        $task = Task::where('project_id', $projectId)
            ->where('id', $taskId)
            ->firstOrFail();

        $reports = TaskReport::with(['user', 'reviewer', 'files'])
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reports);
    }

    /**
     * Lưu báo cáo tiến độ mới
     */
    public function store(Request $request, $projectId, $taskId)
    {
        // Kiểm tra quyền truy cập
        $task = Task::where('project_id', $projectId)
            ->where('id', $taskId)
            ->firstOrFail();

        // Kiểm tra người dùng có phải là người thực hiện task không
        if (!$task->assignees()->where('users.id', Auth::id())->exists()) {
            abort(403, 'Bạn không phải là người thực hiện công việc này');
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'progress' => 'required|integer|min:0|max:100',
            'files.*' => 'nullable|file|max:10240', // Tối đa 10MB mỗi file
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // Tạo báo cáo mới
            $report = TaskReport::create([
                'task_id' => $taskId,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'progress' => $request->progress,
                'status' => 'submitted', // Trạng thái mặc định là đã nộp, chờ duyệt
            ]);

            // Xử lý files đính kèm
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('task_reports/' . $report->id, 'public');

                    TaskReportFile::create([
                        'report_id' => $report->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            // Gửi thông báo cho người quản lý và giám sát
            $managers = $task->managers()->get();
            $supervisors = $task->supervisors()->get();

            $recipients = $managers->merge($supervisors);

            foreach ($recipients as $recipient) {
                $recipient->notify(new TaskReportSubmitted($report));
            }

            // Không cập nhật tiến độ khi gửi báo cáo, chỉ cập nhật khi báo cáo được duyệt

            DB::commit();

            return response()->json([
                'message' => 'Báo cáo tiến độ đã được gửi thành công',
                'report' => $report->load(['user', 'files']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hiển thị chi tiết báo cáo
     */
    public function show($projectId, $taskId, $reportId)
    {
        // Kiểm tra quyền truy cập
        $task = Task::where('project_id', $projectId)
            ->where('id', $taskId)
            ->firstOrFail();

        $report = TaskReport::with(['user', 'reviewer', 'files'])
            ->where('task_id', $taskId)
            ->where('id', $reportId)
            ->firstOrFail();

        return response()->json($report);
    }

    /**
     * Duyệt hoặc từ chối báo cáo
     */
    public function review(Request $request, $projectId, $taskId, $reportId)
    {
        // Kiểm tra quyền truy cập
        $task = Task::where('project_id', $projectId)
            ->where('id', $taskId)
            ->firstOrFail();

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'review_message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $report = TaskReport::where('task_id', $taskId)
            ->where('id', $reportId)
            ->firstOrFail();

        // Kiểm tra nếu báo cáo đã được duyệt trước đó
        if ($report->status !== 'submitted') {
            return response()->json([
                'message' => 'Báo cáo này đã được duyệt hoặc từ chối trước đó',
                'report' => $report->load(['user', 'reviewer', 'files']),
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Cập nhật trạng thái báo cáo
            $report->update([
                'status' => $request->status,
                'reviewer_id' => Auth::id(),
                'review_message' => $request->review_message,
                'reviewed_at' => now(),
            ]);

            // Cập nhật tiến độ của task nếu báo cáo được duyệt
            if ($request->status === 'approved') {
                $task = Task::find($taskId);
                $progress = $report->progress / 100; // Chuyển đổi tỷ lệ từ 1 = 100%

                // Chỉ cập nhật nếu tiến độ mới lớn hơn tiến độ hiện tại
                if ($task && $task->progress < $progress) {
                    $task->update(['progress' => $progress]);
                }
            }

            // Gửi thông báo cho người tạo báo cáo
            $report->user->notify(new TaskReportReviewed($report));

            DB::commit();

            return response()->json([
                'message' => 'Báo cáo tiến độ đã được ' . ($request->status === 'approved' ? 'duyệt' : 'từ chối'),
                'report' => $report->load(['user', 'reviewer', 'files']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xóa báo cáo
     */
    public function destroy($projectId, $taskId, $reportId)
    {
        // Kiểm tra quyền truy cập
        $task = Task::where('project_id', $projectId)
            ->where('id', $taskId)
            ->firstOrFail();

        // Chỉ cho phép người tạo báo cáo hoặc người có quyền quản lý xóa
        $report = TaskReport::where('task_id', $taskId)
            ->where('id', $reportId)
            ->firstOrFail();

        // Không cho phép xóa báo cáo đã được duyệt
        if ($report->status === 'approved') {
            abort(403, 'Không thể xóa báo cáo đã được duyệt');
        }

        DB::beginTransaction();

        try {
            // Xóa các file đính kèm
            foreach ($report->files as $file) {
                Storage::disk('public')->delete($file->file_path);
                $file->delete();
            }

            // Xóa báo cáo
            $report->delete();

            DB::commit();

            return response()->json([
                'message' => 'Báo cáo tiến độ đã được xóa thành công',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }
}
