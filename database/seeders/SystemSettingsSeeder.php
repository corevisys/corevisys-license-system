<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::firstOrCreate(['key' => 'api_enabled'], ['value' => 'true']);
        SystemSetting::firstOrCreate(['key' => 'min_supported_version'], ['value' => '1.0.0']);
        SystemSetting::firstOrCreate(['key' => 'default_theme'], ['value' => 'dark-modern']);
        
        // Payment Gateways
        SystemSetting::firstOrCreate(['key' => 'gateway_stripe_active'], ['value' => '1']);
        SystemSetting::firstOrCreate(['key' => 'gateway_bkash_active'], ['value' => '1']); // Enabling bKash by default for user to see
        SystemSetting::firstOrCreate(['key' => 'gateway_nagad_active'], ['value' => '0']);
        SystemSetting::firstOrCreate(['key' => 'gateway_rocket_active'], ['value' => '0']);
    }
}
