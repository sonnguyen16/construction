<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Chuyển flash messages từ session sang props của Inertia
        Inertia::share([
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'error' => session('error'),
                    'warning' => session('warning'),
                ];
            },
        ]);

        // Xóa flash messages khỏi session sau khi đã chuyển sang props
        // Điều này sẽ ngăn Laravel hiển thị flash messages mặc định
        $this->clearFlashMessages();
    }

    /**
     * Xóa flash messages khỏi session
     */
    private function clearFlashMessages(): void
    {
        if (session()->has('success')) {
            session()->flash('success', session('success'));
        }

        if (session()->has('error')) {
            session()->flash('error', session('error'));
        }

        if (session()->has('warning')) {
            session()->flash('warning', session('warning'));
        }
    }
}
