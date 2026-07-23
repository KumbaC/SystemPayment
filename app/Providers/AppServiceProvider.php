<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\AppNotification;
use App\Models\PayableInvoice;
use App\Models\ReceivableInvoice;
use Illuminate\Support\Facades\Auth;
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
            if (! Auth::check()) {
                $view->with(['notifications' => collect(), 'unreadCount' => 0, 'notificationAlerts' => []]);

                return;
            }

            $user = Auth::user();
            $today = now()->toDateString();

            $overdueReceivables = ReceivableInvoice::query()
                ->whereIn('status', ['pending', 'partial'])
                ->whereNotNull('due_date')
                ->where('due_date', '<', $today)
                ->whereRaw('amount_ves > paid_ves')
                ->get();

            $overduePayables = PayableInvoice::query()
                ->whereIn('status', ['pending', 'partial'])
                ->whereNotNull('due_date')
                ->where('due_date', '<', $today)
                ->whereRaw('amount_ves > paid_ves')
                ->get();

            $receivablesCount = $overdueReceivables->count();
            $payablesCount = $overduePayables->count();

            $syncOverdueNotification = function (string $type, string $title, string $message, string $link, bool $active) use ($user) {
                $existing = AppNotification::query()
                    ->where('user_id', $user->id)
                    ->where('type', $type)
                    ->latest('id')
                    ->first();

                if (! $active) {
                    if ($existing && ! $existing->read_at) {
                        $existing->update(['read_at' => now()]);
                    }

                    return;
                }

                if ($existing) {
                    $existing->update([
                        'title' => $title,
                        'message' => $message,
                        'link' => $link,
                        'read_at' => null,
                    ]);

                    return;
                }

                AppNotification::query()->create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'link' => $link,
                ]);
            };

            $syncOverdueNotification(
                'overdue_receivables',
                'Cuentas por cobrar vencidas',
                "Hay {$receivablesCount} cuenta(s) por cobrar vencida(s).",
                route('accounts-receivable.index'),
                $receivablesCount > 0
            );

            $syncOverdueNotification(
                'overdue_payables',
                'Facturas por pagar vencidas',
                "Hay {$payablesCount} factura(s) por pagar vencida(s).",
                route('accounts-payable.index'),
                $payablesCount > 0
            );

            $alerts = [];
            if ($receivablesCount > 0) {
                $alerts[] = [
                    'key' => 'overdue_receivables_'.$receivablesCount,
                    'title' => 'Cuentas por cobrar vencidas',
                    'text' => "Tienes {$receivablesCount} cuenta(s) por cobrar vencida(s).",
                    'icon' => 'warning',
                ];
            }

            if ($payablesCount > 0) {
                $alerts[] = [
                    'key' => 'overdue_payables_'.$payablesCount,
                    'title' => 'Facturas por pagar vencidas',
                    'text' => "Tienes {$payablesCount} factura(s) por pagar vencida(s).",
                    'icon' => 'warning',
                ];
            }

            $view->with([
                'notifications' => AppNotification::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(8)
                    ->get(),
                'unreadCount' => AppNotification::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count(),
                'notificationAlerts' => $alerts,
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
