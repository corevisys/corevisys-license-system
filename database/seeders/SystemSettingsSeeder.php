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
        SystemSetting::firstOrCreate(['key' => 'gateway_stripe_key'], ['value' => env('STRIPE_PUBLISHABLE_KEY', '')]);
        SystemSetting::firstOrCreate(['key' => 'gateway_stripe_secret'], ['value' => env('STRIPE_SECRET_KEY', '')]);
        SystemSetting::firstOrCreate(['key' => 'gateway_bkash_active'], ['value' => '1']); // Enabling bKash by default for user to see
        SystemSetting::firstOrCreate(['key' => 'gateway_nagad_active'], ['value' => '0']);
        SystemSetting::firstOrCreate(['key' => 'gateway_rocket_active'], ['value' => '0']);

        // bKash Tokenized Checkout (Sandbox credentials)
        SystemSetting::firstOrCreate(['key' => 'gateway_bkash_sandbox'], ['value' => '1']);
        SystemSetting::firstOrCreate(['key' => 'gateway_bkash_username'], ['value' => env('BKASH_USERNAME', '')]);
        SystemSetting::firstOrCreate(['key' => 'gateway_bkash_password'], ['value' => env('BKASH_PASSWORD', '')]);
        SystemSetting::firstOrCreate(['key' => 'gateway_bkash_app_key'], ['value' => env('BKASH_APP_KEY', '')]);
        SystemSetting::firstOrCreate(['key' => 'gateway_bkash_app_secret'], ['value' => env('BKASH_APP_SECRET', '')]);
    }
}
