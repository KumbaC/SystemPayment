<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        ?int $userId = null
    ): ActivityLog {
        $log = ActivityLog::query()->create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
        ]);

        $this->notifyUsers($log);

        return $log;
    }

    protected function notifyUsers(ActivityLog $log): void
    {
        if (! $log->user_id) {
            return;
        }

        $actor = User::query()->find($log->user_id);
        $actorName = $actor?->name ?? 'Sistema';

        User::query()->where('id', '!=', $log->user_id)->each(function (User $user) use ($log, $actorName) {
            AppNotification::query()->create([
                'user_id' => $user->id,
                'title' => 'Actividad en el sistema',
                'message' => "{$actorName}: {$log->description}",
                'type' => 'activity',
                'link' => route('audit.index'),
            ]);
        });
    }
}
