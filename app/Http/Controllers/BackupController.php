<?php

namespace App\Http\Controllers;

use App\Models\DatabaseBackup;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index()
    {
        return view('pages.backups.index', [
            'title' => 'Respaldos',
            'backups' => DatabaseBackup::query()->latest()->paginate(15),
        ]);
    }

    public function store(BackupService $backupService)
    {
        try {
            $backup = $backupService->run('Respaldo manual');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Respaldo creado: {$backup->filename}");
    }
}
