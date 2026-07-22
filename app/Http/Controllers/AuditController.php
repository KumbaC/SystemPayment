<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20);

        return view('pages.audit.index', [
            'title' => 'Auditoría',
            'logs' => $logs,
        ]);
    }
}
