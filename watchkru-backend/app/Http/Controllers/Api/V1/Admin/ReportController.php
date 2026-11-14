<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CommunityReport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $query = CommunityReport::with(['community:id,name,slug', 'user:id,username,name']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return $this->paginated(
            $query->latest()->paginate(min((int) $request->get('per_page', 20), 50)),
            fn ($items) => collect($items)->map(fn (CommunityReport $r) => [
                'id' => $r->id,
                'report_type' => $r->report_type,
                'description' => $r->description,
                'status' => $r->status,
                'community' => $r->community ? ['name' => $r->community->name, 'slug' => $r->community->slug] : null,
                'created_at' => $r->created_at->toDateString(),
            ])
        );
    }

    public function update(Request $request, CommunityReport $report)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'resolved', 'dismissed'])],
        ]);

        $report->update([
            'status' => $data['status'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        AuditLog::record('report.update', $report, null, $data);

        return $this->ok($report, 'وضعیت گزارش به‌روزرسانی شد.');
    }
}
