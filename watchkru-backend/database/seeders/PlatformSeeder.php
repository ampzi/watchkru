<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            'telegram_group' => 'گروه تلگرام',
            'telegram_channel' => 'کانال تلگرام',
            'discord_server' => 'سرور دیسکورد',
            'instagram_page' => 'پیج اینستاگرام',
            'reddit_community' => 'انجمن ردیت',
            'whatsapp_group' => 'گروه واتس‌اپ',
            'website_forum' => 'وب‌سایت / فروم',
            'other' => 'سایر',
        ];

        foreach ($platforms as $slug => $name) {
            Platform::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
