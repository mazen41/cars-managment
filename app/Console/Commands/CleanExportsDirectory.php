<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanExportsDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-exports-directory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = storage_path('exports');

        if (File::exists($directory)) {
            File::cleanDirectory($directory);
            $this->info('Storage directory cleaned successfully.');
        } else {
            $this->error('Directory does not exist.');
        }
    }
}
