<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class LicenseEncryptCommand extends Command
{
    protected $signature = 'license:encrypt {key}';
    protected $description = 'Encrypt a plain license key using app encryption';

    public function handle()
    {
        $key = $this->argument('key');
        $enc = Crypt::encryptString($key);
        $this->line($enc);
        return 0;
    }
}
