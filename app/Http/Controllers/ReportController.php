<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\Project;
use App\Models\ReceiptVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Báo cáo chi tiết phiếu chi theo dự án
     */
    public function paymentsByProject(Request $request)
    {
        $query = PaymentVoucher::query()
            ->with(['contractor', 'bidPackage.project', 'creator']);

        // Lọc theo dự án
        if ($request->has('project_id') && $request->project_id) {
            $query->whereHas('bidPackage.project', function($q) use ($request) {
                $q->where('id', $request->project_id);
            });
        }

        // Lọc theo khoảng thời gian
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sắp xếp theo dự án, gói thầu và ngày tạo
        $query->orderBy('created_at', 'desc');

        $payments = $query->get();

        // Lấy danh sách dự án cho bộ lọc
        $projects = Project::orderBy('name')->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'code' => $project->code,
                    'name' => $project->name
                ];
            });

        return Inertia::render('Reports/PaymentsByProject', [
            'payments' => $payments,
            'projects' => $projects,
            'filters' => $request->only(['project_id', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Báo cáo phiếu thu theo dự án
     */
    public function receiptsByProject(Request $request)
    {
        // Xử lý các tham số filter
        $projectId = $request->input('project_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Query lấy phiếu thu
        $query = ReceiptVoucher::with(['customer', 'project', 'creator']);

        // Áp dụng các filter
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Lấy danh sách phiếu thu
        $receipts = $query->orderBy('created_at', 'desc')->get();

        // Lấy danh sách dự án để hiển thị trong dropdown filter
        $projects = Project::orderBy('name')->get(['id', 'name', 'code']);

        return Inertia::render('Reports/ReceiptsByProject', [
            'receipts' => $receipts,
            'projects' => $projects,
            'filters' => [
                'project_id' => $projectId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ]
        ]);
    }
}
