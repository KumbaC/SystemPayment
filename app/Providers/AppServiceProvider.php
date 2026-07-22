<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\AppNotification;
use Illuminate\Support\Facades\View;
use App\Models\License;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('components.header.notification-dropdown', function ($view) {
            if (! auth()->check()) {
                $view->with(['notifications' => collect(), 'unreadCount' => 0]);

                return;
            }

            $view->with([
                'notifications' => AppNotification::query()
                    ->where('user_id', auth()->id())
                    ->latest()
                    ->limit(8)
                    ->get(),
                'unreadCount' => AppNotification::query()
                    ->where('user_id', auth()->id())
                    ->whereNull('read_at')
                    ->count(),
            ]);
        });

        // Share license state with all views so the modal can be shown when needed
        View::composer('*', function ($view) {
            $isUnlocked = false;

            // session-based short term unlock
            if (Session::has('license_valid_until')) {
                try {
                    $until = \Carbon\Carbon::parse(Session::get('license_valid_until'));
                    if ($until->isFuture()) {
                        $isUnlocked = true;
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }

            // check database for any active non-expired license
            if (! $isUnlocked) {
                $now = now();
                $license = License::where('active', true)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '>', $now)
                    ->orderBy('expires_at', 'desc')
                    ->first();

                if ($license) {
                    $isUnlocked = true;
                    // ensure expires_at is a Carbon instance (in case it's a string)
                    try {
                        $expires = \Carbon\Carbon::parse($license->expires_at);
                        Session::put('license_valid_until', $expires->toDateTimeString());
                    } catch (\Throwable $e) {
                        // fallback: store raw value
                        Session::put('license_valid_until', $license->expires_at);
                    }
                }
            }

            $view->with('license_unlocked', $isUnlocked);
        });
    }
}
