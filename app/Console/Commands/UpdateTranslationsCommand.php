<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Translation;

class UpdateTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:update {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update translations from JSON file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        // Check if the file exists
        if (!File::exists($filePath)) {
            $this->error("File not found: $filePath");
            return Command::FAILURE;
        }

        // Read and decode the JSON file
        $translationsData = json_decode(File::get($filePath), true);

        if (is_null($translationsData)) {
            $this->error("Invalid JSON format in file: $filePath");
            return Command::FAILURE;
        }

        // Loop through each translation and update the database
        foreach ($translationsData as $translation) {
            if (isset($translation['lang'], $translation['lang_key'], $translation['lang_value'])) {
                Translation::updateOrCreate(
                    ['lang' => $translation['lang'], 'lang_key' => $translation['lang_key']],
                    [
                        'lang'  =>$translation['lang'],
                        'lang_key' => $translation['lang_key'],
                        'lang_value' => $translation['lang_value']
                    ]
                );
            }
        }

        $this->info("Translations updated successfully.");
        return Command::SUCCESS;
    }
}
