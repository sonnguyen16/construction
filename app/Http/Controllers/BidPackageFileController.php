<?php

namespace App\Http\Controllers;

use App\Models\BidPackage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ProjectPermission;

class BidPackageFileController extends Controller
{
    /**
     * Display file manager for bid package
     *
     * @param BidPackage $bidPackage
     * @return \Inertia\Response
     */
    public function index(BidPackage $bidPackage)
    {
        if (!ProjectPermission::hasPermissionInProject('bid-packages.files', $bidPackage->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem gói thầu.');
        }
        // Đảm bảo gói thầu tồn tại
        if (!$bidPackage) {
            abort(404, 'Gói thầu không tồn tại');
        }

        // Load dự án để hiển thị thông tin
        $bidPackage->load('project');

        // Tạo đường dẫn thư mục của gói thầu
        $bidPackageFolder = "bid_packages/{$bidPackage->code}_" . $bidPackage->name;
        $fullPath = "files/1/$bidPackageFolder";

        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->makeDirectory($fullPath);
        }

        // Tạo URL với working_dir để mở đúng thư mục gói thầu
        $lfm_url = route('unisharp.lfm.show');

        return Inertia::render('BidPackages/Files', [
            'bidPackage' => $bidPackage,
            'project' => $bidPackage->project,
            'fileManagerUrl' => $lfm_url,
            'bidPackageFolder' => $bidPackageFolder,
        ]);
    }
}
