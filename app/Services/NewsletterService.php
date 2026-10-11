<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterService
{
    public function subscribe(string $email, ?string $name = null): NewsletterSubscriber
    {
        $email = strtolower(trim($email));

        $subscriber = NewsletterSubscriber::withTrashed()
            ->where('email', $email)
            ->first();

        if ($subscriber) {
            if (method_exists($subscriber, 'trashed') && $subscriber->trashed()) {
                $subscriber->restore();
            }

            $subscriber->fill([
                'name' => $name ?? $subscriber->name,
                'is_active' => true,
                'subscribed_at' => $subscriber->subscribed_at ?? now(),
                'unsubscribed_at' => null,
            ])->save();

            return $subscriber->fresh();
        }

        return NewsletterSubscriber::query()->create([
            'email' => $email,
            'name' => $name,
            'is_active' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ]);
    }

    public function unsubscribe(string $email): void
    {
        $email = strtolower(trim($email));

        $subscriber = NewsletterSubscriber::query()->where('email', $email)->first();

        if (! $subscriber) {
            return;
        }

        $subscriber->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
    }

    public function exportCsv(): StreamedResponse
    {
        $filename = 'newsletter-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['email', 'name', 'is_active', 'subscribed_at', 'unsubscribed_at'], ';');

            NewsletterSubscriber::query()
                ->orderBy('email')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->email,
                            $row->name,
                            $row->is_active ? '1' : '0',
                            optional($row->subscribed_at)?->toDateTimeString(),
                            optional($row->unsubscribed_at)?->toDateTimeString(),
                        ], ';');
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
