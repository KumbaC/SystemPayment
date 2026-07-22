<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupService
{
    public function run(?string $notes = null): DatabaseBackup
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $dir = base_path('backup');
        File::ensureDirectoryExists($dir);
        File::ensureDirectoryExists(storage_path('app/backups'));

        $filename = 'backup_'.now()->format('Y-m-d_His').'.sql';
        $publicPath = $dir.DIRECTORY_SEPARATOR.$filename;
        $storagePath = storage_path('app/backups/'.$filename);

        if (($config['driver'] ?? '') === 'mysql') {
            $this->dumpMysql($config, $publicPath);
            File::copy($publicPath, $storagePath);
        } else {
            throw new \RuntimeException('Respaldo automático configurado solo para MySQL.');
        }

        return DatabaseBackup::query()->create([
            'filename' => $filename,
            'path' => $publicPath,
            'size' => filesize($publicPath) ?: 0,
            'status' => 'completed',
            'notes' => $notes ?? 'Respaldo automático diario',
        ]);
    }

    protected function dumpMysql(array $config, string $path): void
    {
        $command = [
            'mysqldump',
            '--host='.($config['host'] ?? '127.0.0.1'),
            '--port='.($config['port'] ?? '3306'),
            '--user='.($config['username'] ?? 'root'),
            '--result-file='.$path,
            $config['database'],
        ];

        if (! empty($config['password'])) {
            array_splice($command, 4, 0, ['--password='.$config['password']]);
        }

        $process = new Process($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('Error al crear respaldo: '.$process->getErrorOutput());
        }
    }
}
