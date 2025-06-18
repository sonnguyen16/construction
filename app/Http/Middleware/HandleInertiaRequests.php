<?php

namespace App\Http\Middleware;

use App\Models\ProjectRole;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar' => $request->user()->avatar,
                    'project_roles' => $request->user()->projectRoles()->with(['project', 'role'])->whereHas('project', function ($query) {
                        $query->whereNull('deleted_at');
                    })->get()->map(function ($projectRole) use ($request) {
                        // Lấy danh sách quyền của vai trò trong dự án
                        $permissions = $projectRole->role->permissions->pluck('name')->toArray();

                        // Kiểm tra xem đây có phải là dự án và vai trò hiện tại không
                        $isCurrentProject = $request->session()->get('current_project_id') == $projectRole->project_id;
                        $isCurrentRole = $request->session()->get('current_role_id') == $projectRole->role_id;
                        $isActive = $isCurrentProject && $isCurrentRole;

                        return [
                            'project_id' => $projectRole->project_id,
                            'project_name' => $projectRole->project->name,
                            'role_id' => $projectRole->role_id,
                            'role_name' => $projectRole->role->name,
                            'permissions' => $permissions,
                            'is_active' => $isActive,
                        ];
                    }),
                    'current_project_role' => $request->user()->projectRoles()
                        ->with(['project', 'role'])
                        ->where('project_id', $request->session()->get('current_project_id') ?? $request->user()->projectRoles()->first()->project_id ?? null)
                        ->where('role_id', $request->session()->get('current_role_id') ?? $request->user()->projectRoles()->first()->role_id ?? null)
                        ->first(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
