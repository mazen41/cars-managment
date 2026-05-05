<?php

namespace App\Observers;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyObserver
{
    public function saved(Currency $currency)
    {
        // Clear the default symbol cache
        Cache::forget('system_default_currency_symbol');

        // Clear the specific code cache (e.g., currency_symbol_USD)
        Cache::forget("currency_symbol_{$currency->code}");
    }

    public function deleted(Currency $currency)
    {
        Cache::forget('system_default_currency_symbol');
        Cache::forget("currency_symbol_{$currency->code}");
    }
}
