<?php

namespace Database\Seeders;

use App\Models\OtpConfiguration;
use Illuminate\Database\Seeder;

class OtpConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OtpConfiguration::updateOrCreate([
            'type'  =>  'whatsapp',
            'value' => '1'
        ]);
    }
}
