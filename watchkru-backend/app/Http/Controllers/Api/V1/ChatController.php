<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatGroupRequest;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Resources\ChatGroupResource;
use App\Http\Resources\ChatMessageResource;
use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\ChatAlert;
use App\Support\AdminNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ChatController extends Controller
{
    use ApiResponses;

    /** Groups the user belongs to (plus the ones they created, pending or not). */
    public function index(Request $request)
    {
        $user = $request->user();

        $groups = ChatGroup::query()
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('members', fn ($m) => $m->whereKey($user->id));
            })
            ->withCount('members')
            ->with(['latestMessage.user'])
            ->orderByDesc('updated_at')
            ->get();

        return $this->ok(ChatGroupResource::collection($groups));
    }

    public function store(StoreChatGroupRequest $request)
    {
        $user = $request->user();

        $group = DB::transaction(function () use ($request, $user) {
            $group = ChatGroup::create([
                'name' => $request->name,
                'slug' => ChatGroup::uniqueSlug($request->name),
                'description' => $request->description,
                'created_by' => $user->id,
                'status' => ChatGroup::STATUS_PENDING,
            ]);

            $group->members()->attach($user->id, [
                'role' => ChatGroup::ROLE_OWNER,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            AdminNotifier::send(
                'گروه گفت‌وگوی تازه',
                "«{$group->name}» توسط {$user->display_name} ساخته شد و منتظر تأیید است.",
                url('/admin/chat-groups'),
                'warning',
                'heroicon-o-user-group',
            );

            return $group;
        });

        return $this->ok(
            new ChatGroupResource($group->loadCount('members')),
            'گروه ساخته شد و پس از تأیید مدیران در فهرست گفت‌وگوها نمایش داده می‌شود.',
            201
        );
    }

    public function messages(Request $request, ChatGroup $group)
    {
        $this->authorizeMember($request, $group);

        $after = (int) $request->get('after_id', 0);
        $limit = min((int) $request->get('limit', 60), 100);

        $messages = $group->messages()
            ->with('user')
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return $this->ok(ChatMessageResource::collection($messages));
    }

    public function send(StoreChatMessageRequest $request, ChatGroup $group)
    {
        $user = $request->user();
        $this->authorizeMember($request, $group);

        if (! $group->isApproved()) {
            return $this->fail('این گروه هنوز تأیید نشده است.', 403);
        }

        $message = DB::transaction(function () use ($request, $group, $user) {
            $message = ChatMessage::create([
                'chat_group_id' => $group->id,
                'user_id' => $user->id,
                'body' => trim($request->body),
            ]);

            $group->members()->updateExistingPivot($user->id, [
                'last_read_message_id' => $message->id,
            ]);

            $group->touch();

            $recipients = $group->members()
                ->where('users.id', '!=', $user->id)
                ->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new ChatAlert(
                    "پیام تازه در «{$group->name}»",
                    "{$user->display_name}: ".mb_substr($message->body, 0, 80),
                    '/chat?group='.$group->id,
                ));
            }

            return $message;
        });

        return $this->ok(new ChatMessageResource($message->load('user')), 'پیام ارسال شد.', 201);
    }

    public function markRead(Request $request, ChatGroup $group)
    {
        $this->authorizeMember($request, $group);

        $lastId = $group->messages()->max('id');

        $group->members()->updateExistingPivot($request->user()->id, [
            'last_read_message_id' => $lastId,
        ]);

        return $this->ok(['last_read_message_id' => $lastId], 'گروه خوانده شد.');
    }

    /** Owners ask for deletion; staff approve it in the panel. */
    public function requestDeletion(Request $request, ChatGroup $group)
    {
        $user = $request->user();

        if ($group->created_by !== $user->id) {
            return $this->fail('فقط سازندهٔ گروه می‌تواند درخواست حذف بدهد.', 403);
        }

        if ($group->status === ChatGroup::STATUS_DELETION_REQUESTED) {
            return $this->ok(new ChatGroupResource($group), 'درخواست حذف قبلاً ثبت شده است.');
        }

        $group->update(['status' => ChatGroup::STATUS_DELETION_REQUESTED]);

        AdminNotifier::send(
            'درخواست حذف گروه',
            "{$user->display_name} درخواست حذف «{$group->name}» را ثبت کرد.",
            url('/admin/chat-groups'),
            'danger',
            'heroicon-o-trash',
        );

        return $this->ok(new ChatGroupResource($group), 'درخواست حذف ثبت شد و پس از تأیید مدیران اعمال می‌شود.');
    }

    protected function authorizeMember(Request $request, ChatGroup $group): void
    {
        $isMember = $group->members()->whereKey($request->user()->id)->exists()
            || $group->created_by === $request->user()->id;

        abort_unless($isMember, 403, 'شما عضو این گروه نیستید.');
    }

    /** Members of a group, exposed for the group info sheet. */
    public function members(Request $request, ChatGroup $group)
    {
        $this->authorizeMember($request, $group);

        $members = $group->members()
            ->select('users.id', 'users.name', 'users.username', 'users.avatar')
            ->orderBy('chat_group_user.role')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->display_name,
                'username' => $u->username,
                'avatar' => $u->avatar,
                'role' => $u->pivot->role,
            ]);

        return $this->ok($members);
    }
}
