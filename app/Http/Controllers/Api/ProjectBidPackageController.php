<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectBidPackageController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $bidPackages = $project->bidPackages()
            ->select('id', 'code', 'name')
            ->where('status', 'active')
            ->get();

        return response()->json($bidPackages);
    }
}
