<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Deterministic accounts: one super admin, one moderator (both can open the
 * Filament panel) and eight members who own the seeded reviews, submissions,
 * reports and favorites. Passwords are all "password" — demo data only.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->people() as $person) {
            User::updateOrCreate(
                ['email' => $person['email']],
                [
                    'name' => $person['name'],
                    'username' => $person['username'],
                    'password' => Hash::make('password'),
                    'role' => $person['role'],
                    'bio' => $person['bio'] ?? null,
                    'email_verified_at' => now(),
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function people(): array
    {
        return [
            [
                'email' => 'admin@watchkru.test',
                'name' => 'مدیر واچ‌کرو',
                'username' => 'admin',
                'role' => User::ROLE_SUPER_ADMIN,
                'bio' => 'مدیر ارشد آرشیو انجمن‌ها.',
            ],
            [
                'email' => 'moderator@watchkru.test',
                'name' => 'نگار موسوی',
                'username' => 'negar_mod',
                'role' => User::ROLE_MODERATOR,
                'bio' => 'بررسی انجمن‌های ارسالی و گزارش‌های کاربران.',
            ],
            [
                'email' => 'sara@watchkru.test',
                'name' => 'سارا احمدی',
                'username' => 'sara_a',
                'role' => User::ROLE_USER,
                'bio' => 'طرفدار سریال‌های علمی‌تخیلی.',
            ],
            [
                'email' => 'amir@watchkru.test',
                'name' => 'امیر رضایی',
                'username' => 'amir_r',
                'role' => User::ROLE_USER,
                'bio' => 'دنبال‌کنندهٔ اخبار سریال‌ها.',
            ],
            [
                'email' => 'mina@watchkru.test',
                'name' => 'مینا کریمی',
                'username' => 'mina_k',
                'role' => User::ROLE_USER,
                'bio' => 'عاشق سینمای نولان.',
            ],
            [
                'email' => 'reza@watchkru.test',
                'name' => 'رضا شریفی',
                'username' => 'reza_sh',
                'role' => User::ROLE_USER,
                'bio' => 'مدیر چند گروه تلگرامی فیلم و سریال.',
            ],
            [
                'email' => 'niloofar@watchkru.test',
                'name' => 'نیلوفر حسینی',
                'username' => 'niloo_h',
                'role' => User::ROLE_USER,
                'bio' => 'نقدنویس آماتور.',
            ],
            [
                'email' => 'kamran@watchkru.test',
                'name' => 'کامران یزدانی',
                'username' => 'kamran_y',
                'role' => User::ROLE_USER,
                'bio' => 'طرفدار دنیای تل‌ماسه.',
            ],
            [
                'email' => 'parisa@watchkru.test',
                'name' => 'پریسا نادری',
                'username' => 'parisa_n',
                'role' => User::ROLE_USER,
                'bio' => 'دنبال‌کنندهٔ سریال‌های جنایی.',
            ],
            [
                'email' => 'hossein@watchkru.test',
                'name' => 'حسین صادقی',
                'username' => 'hossein_s',
                'role' => User::ROLE_USER,
                'bio' => 'دانشجوی سینما.',
            ],
        ];
    }
}
