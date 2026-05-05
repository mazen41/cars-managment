<?php

namespace App\Console\Commands;

use App\Http\Middleware\Language;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Translation;
use App\Models\Language as AppLanguage;
class ExtractTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:extract {language}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract missing translations for a specific language';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
{
    // Get the language argument
    $language = $this->argument('language');
    if (AppLanguage::where('code', $language)->doesntExist()) {
        $this->error("Language '{$language}' does not exist.");
        return Command::FAILURE;
    }

    // Fetch English translations
    $englishTranslations = Translation::where('lang', 'en')->pluck('lang_value', 'lang_key')->toArray();

    // Fetch translations for the specified language
    $languageTranslations = Translation::where('lang', $language)->pluck('lang_value', 'lang_key')->toArray();

    $missingTranslations = [];

    // Identify missing or null translations
    foreach ($englishTranslations as $key => $value) {
        if (!array_key_exists($key, $languageTranslations) || is_null($languageTranslations[$key])) {
            $missingTranslations[] = [
                'lang'  => $language,
                'lang_key' => $key,
                'lang_value' => $value,
            ];
        }
    }

    // Output the filtered translations to a JSON file
    $outputFilePath = base_path("{$language}_translations.json");
    File::put($outputFilePath, json_encode($missingTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $this->info("Translations for language '{$language}' have been extracted to: {$outputFilePath}");
    return Command::SUCCESS;
}

}
