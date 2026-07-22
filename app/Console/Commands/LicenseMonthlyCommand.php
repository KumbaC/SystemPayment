<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class LicenseMonthlyCommand extends Command
{
    protected $signature = 'license:monthly {--month=} {--year=} {--encrypt-only}';
    protected $description = 'Generate the monthly license key (plain and encrypted). Optionally specify month and year.';

    public function handle()
    {
        $month = $this->option('month') ?? date('m');
        $year = $this->option('year') ?? date('y');

        $plain = sprintf('MI-CLAVE-%02d-%02s', intval($month), $year);
        $enc = Crypt::encryptString($plain);

        if ($this->option('encrypt-only')) {
            $this->line($enc);
            return 0;
        }

        $this->line('Plain: '.$plain);
        $this->line('Encrypted: '.$enc);
        return 0;
    }
}
