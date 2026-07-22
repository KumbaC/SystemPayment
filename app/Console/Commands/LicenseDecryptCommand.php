<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class LicenseDecryptCommand extends Command
{
    protected $signature = 'license:decrypt {encrypted}';
    protected $description = 'Decrypt an encrypted license string (requires APP_KEY)';

    public function handle()
    {
        $enc = $this->argument('encrypted');
        try {
            $plain = Crypt::decryptString($enc);
            $this->line($plain);
            return 0;
        } catch (\Throwable $e) {
            $this->error('Failed to decrypt: '.$e->getMessage());
            return 1;
        }
    }
}
