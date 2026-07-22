<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Crea un respaldo diario de la base de datos';

    public function handle(BackupService $backupService): int
    {
        try {
            $backup = $backupService->run('Respaldo programado diario');
            $this->info("Respaldo creado: {$backup->filename}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
