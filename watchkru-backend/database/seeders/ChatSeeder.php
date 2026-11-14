<?php

namespace Database\Seeders;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Two chat groups for the demo: an approved, active one (so the chat page has
 * a real conversation) and a pending one plus a deletion request, so the
 * approval workflow in the admin panel is visible immediately.
 */
class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@watchkru.test')->first();
        $sara = User::query()->where('email', 'sara@watchkru.test')->first();
        $amir = User::query()->where('email', 'amir@watchkru.test')->first();
        $mina = User::query()->where('email', 'mina@watchkru.test')->first();
        $reza = User::query()->where('email', 'reza@watchkru.test')->first();

        if (! $sara || ! $amir) {
            return;
        }

        $silo = ChatGroup::updateOrCreate(
            ['slug' => 'silo-fa-watch'],
            [
                'name' => 'تماشای گروهی سایلو',
                'description' => 'هر هفته یک قسمت سایلو را با هم می‌بینیم و بعد از آن درباره‌اش حرف می‌زنیم. اسپویل فقط بعد از پخش.',
                'created_by' => $sara->id,
                'status' => ChatGroup::STATUS_APPROVED,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(9),
            ]
        );

        $members = array_filter([$sara, $amir, $mina, $reza]);
        foreach ($members as $i => $member) {
            $silo->members()->syncWithoutDetaching([
                $member->id => [
                    'role' => $i === 0 ? ChatGroup::ROLE_OWNER : ChatGroup::ROLE_MEMBER,
                    'created_at' => now()->subDays(8),
                    'updated_at' => now()->subDays(8),
                ],
            ]);
        }

        if ($silo->messages()->count() === 0) {
            $script = [
                [$sara, 'سلام! قسمت چهارم فصل سوم را کی می‌بینید؟'],
                [$amir, 'من امشب می‌بینم، ساعت ده؟'],
                [$mina, 'من هم هستم. لطفاً تا فردا اسپویل نکنید 😅'],
                [$sara, 'قرارمان ده شب. بعد از قسمت، همین‌جا بحث می‌کنیم.'],
                [$reza, 'عالی. من قسمت قبل را دوباره دیدم، چند نکته دربارهٔ «بیرون» دارم.'],
            ];

            foreach ($script as $index => [$user, $body]) {
                ChatMessage::create([
                    'chat_group_id' => $silo->id,
                    'user_id' => $user->id,
                    'body' => $body,
                    'created_at' => now()->subHours(6 - $index),
                    'updated_at' => now()->subHours(6 - $index),
                ]);
            }
        }

        $pending = ChatGroup::updateOrCreate(
            ['slug' => 'true-detective-fa'],
            [
                'name' => 'کارآگاه حقیقی — تحلیل فصل‌به‌فصل',
                'description' => 'گروهی برای تحلیل پرونده‌های True Detective و مقایسهٔ فصل‌ها.',
                'created_by' => $amir->id,
                'status' => ChatGroup::STATUS_PENDING,
            ]
        );
        $pending->members()->syncWithoutDetaching([
            $amir->id => ['role' => ChatGroup::ROLE_OWNER],
            $mina->id => ['role' => ChatGroup::ROLE_MEMBER],
        ]);

        $toDelete = ChatGroup::updateOrCreate(
            ['slug' => 'the-bear-fa-cooking'],
            [
                'name' => 'آشپزخانهٔ خرس',
                'description' => 'گفت‌وگو دربارهٔ The Bear و آشپزی سریال.',
                'created_by' => $reza->id,
                'status' => ChatGroup::STATUS_DELETION_REQUESTED,
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(3),
            ]
        );
        $toDelete->members()->syncWithoutDetaching([
            $reza->id => ['role' => ChatGroup::ROLE_OWNER],
        ]);
    }
}
