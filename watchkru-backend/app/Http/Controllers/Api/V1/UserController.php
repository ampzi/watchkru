<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Community;
use App\Models\CommunityReport;
use App\Models\CommunitySubmission;
use App\Models\Favorite;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponses;

    public function profile(Request $request)
    {
        return $this->ok(new UserResource($request->user()));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = Storage::disk('public')->url(
                $request->file('avatar')->store('avatars', 'public')
            );
        }

        $user->update($data);

        return $this->ok(new UserResource($user), 'پروفایل به‌روزرسانی شد.');
    }

    public function submissions(Request $request)
    {
        $submissions = $request->user()->submissions()
            ->with('community:id,name,slug')
            ->latest()
            ->get()
            ->map(fn (CommunitySubmission $s) => [
                'id' => $s->id,
                'name' => $s->community?->name ?? data_get($s->submission_data, 'name'),
                'status' => $s->status,
                'admin_note' => $s->admin_note,
                'created_at' => $s->created_at->toDateString(),
            ]);

        return $this->ok($submissions);
    }

    public function reports(Request $request)
    {
        $reports = $request->user()->reports()
            ->with('community:id,name,slug')
            ->latest()
            ->get()
            ->map(fn (CommunityReport $r) => [
                'id' => $r->id,
                'name' => $r->community?->name,
                'description' => $r->report_type,
                'status' => $r->status,
                'created_at' => $r->created_at->toDateString(),
            ]);

        return $this->ok($reports);
    }

    public function favorites(Request $request)
    {
        $favorites = $request->user()->favorites()->with('favoritable')->latest()->get()
            ->map(fn (Favorite $f) => [
                'id' => $f->id,
                'type' => $f->favoritable_type === Media::class ? 'media' : 'community',
                'name' => $f->favoritable?->title ?? $f->favoritable?->name,
                'slug' => $f->favoritable?->slug,
                'created_at' => $f->created_at->toDateString(),
            ]);

        return $this->ok($favorites);
    }

    public function addFavorite(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(['media', 'community'])],
            'id' => ['required', 'integer'],
        ]);

        $class = $request->type === 'media' ? Media::class : Community::class;
        $class::findOrFail($request->id);

        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'favoritable_type' => $class,
            'favoritable_id' => $request->id,
        ]);

        return $this->ok($favorite, 'به علاقه‌مندی‌ها اضافه شد.', 201);
    }

    public function removeFavorite(Request $request)
    {
        $request->validate([
            'type' => ['required', Rule::in(['media', 'community'])],
            'id' => ['required', 'integer'],
        ]);

        $class = $request->type === 'media' ? Media::class : Community::class;

        Favorite::where('user_id', $request->user()->id)
            ->where('favoritable_type', $class)
            ->where('favoritable_id', $request->id)
            ->delete();

        return $this->ok([], 'از علاقه‌مندی‌ها حذف شد.');
    }
}
