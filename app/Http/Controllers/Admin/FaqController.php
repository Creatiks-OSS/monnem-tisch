<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function __construct(protected ActivityLogService $activity) {}

    public function index(): View
    {
        $faqs = Faq::query()->orderBy('sort_order')->paginate(30);

        return view('admin.faqs.index', compact('faqs'));
    }

    public function create(): View
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $faq = Faq::query()->create($data);
        $this->activity->log('FAQ oluşturuldu', $faq);

        return redirect()->route('admin.faqs.index')->with('success', 'SSS oluşturuldu.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));
        $this->activity->log('FAQ güncellendi', $faq);

        return redirect()->route('admin.faqs.index')->with('success', 'SSS kaydedildi.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        $this->activity->log('FAQ silindi', $faq);

        return redirect()->route('admin.faqs.index')->with('success', 'SSS silindi.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}