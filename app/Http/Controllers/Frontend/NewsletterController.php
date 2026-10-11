<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\NewsletterRequest;
use App\Services\NewsletterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function __construct(
        protected NewsletterService $newsletter
    ) {}

    public function subscribe(NewsletterRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        $subscriber = $this->newsletter->subscribe(
            $data['email'],
            $data['name'] ?? null
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Vielen Dank für Ihre Anmeldung zum Newsletter!',
                'subscriber' => [
                    'email' => $subscriber->email,
                    'name' => $subscriber->name,
                ],
            ]);
        }

        return back()->with('success', 'Vielen Dank für Ihre Anmeldung zum Newsletter!');
    }
}
