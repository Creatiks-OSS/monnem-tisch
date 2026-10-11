<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\ActivityLogService;
use App\Services\NewsletterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterController extends Controller
{
    public function __construct(
        protected NewsletterService $newsletter,
        protected ActivityLogService $activity
    ) {}

    public function index(Request $request): View
    {
        $subscribers = NewsletterSubscriber::query()
            ->when($request->filled('q'), fn ($q) => $q->where('email', 'like', '%'.$request->q.'%')->orWhere('name', 'like', '%'.$request->q.'%'))
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('admin.newsletter.index', compact('subscribers'));
    }

    public function export(): StreamedResponse
    {
        $this->activity->log('Bülten CSV dışa aktarıldı');

        return $this->newsletter->exportCsv();
    }

    public function destroy(NewsletterSubscriber $newsletter): RedirectResponse
    {
        $newsletter->delete();
        $this->activity->log('Bülten abonesi silindi', $newsletter);

        return back()->with('success', 'Abone silindi.');
    }
}
