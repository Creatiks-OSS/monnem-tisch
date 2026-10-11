<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedDate;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlockedDateController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(Request $request): View|JsonResponse
    {
        $month = $request->query('month', now()->format('Y-m'));
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $blocked = BlockedDate::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($blocked);
        }

        $all = BlockedDate::query()->where('date', '>=', now()->subMonth()->toDateString())->orderBy('date')->paginate(40);

        return view('admin.blocked-dates.index', compact('blocked', 'month', 'start', 'all'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'in:blocked,booked,holiday'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], [
            'date' => 'tarih',
            'type' => 'tür',
            'note' => 'not',
        ]);

        $type = $data['type'] === 'holiday' ? 'special' : $data['type'];

        $item = BlockedDate::query()->updateOrCreate(
            ['date' => $data['date']],
            ['type' => $type, 'note' => $data['note'] ?? null]
        );

        $this->activity->log('Takvim günü kilitlendi', $item);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return back()->with('success', 'Tarih kaydedildi.');
    }

    public function destroy(BlockedDate $blockedDate): RedirectResponse|JsonResponse
    {
        $blockedDate->delete();
        $this->activity->log('Takvim günü açıldı', $blockedDate);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Tarih silindi.');
    }
}