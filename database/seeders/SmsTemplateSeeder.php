<?php

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SmsTemplate::create([
            'identifier'    => 'account_deletion_request',
            'sms_body'      => 'Your account deletion request has been received. If this was a mistake, please contact support.',
            'status'        => 1,
        ]);
    }
}
