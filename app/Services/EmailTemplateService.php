<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    /**
     * @return array{subject: string, html: string, text: string}
     */
    public function render(string $key, array $variables = []): array
    {
        $template = EmailTemplate::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            $subject = $variables['subject'] ?? 'Nachricht von Monnemtisch Catering';
            $body = $variables['body'] ?? ($variables['message'] ?? '');

            return [
                'subject' => $this->replacePlaceholders((string) $subject, $variables),
                'html' => nl2br(e($this->replacePlaceholders((string) $body, $variables))),
                'text' => $this->replacePlaceholders((string) $body, $variables),
            ];
        }

        return [
            'subject' => $this->replacePlaceholders((string) $template->subject, $variables),
            'html' => $this->replacePlaceholders((string) $template->body_html, $variables),
            'text' => $this->replacePlaceholders((string) ($template->body_text ?: strip_tags($template->body_html)), $variables),
        ];
    }

    public function sendUsingTemplate(string $key, string $to, array $vars = []): void
    {
        $rendered = $this->render($key, $vars);

        try {
            Mail::html($rendered['html'], function ($message) use ($to, $rendered) {
                $message->to($to)->subject($rendered['subject']);
            });
        } catch (\Throwable $e) {
            Log::warning('Email template send failed', [
                'key' => $key,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            Mail::raw($rendered['text'], function ($message) use ($to, $rendered) {
                $message->to($to)->subject($rendered['subject']);
            });
        }
    }

    public function replacePlaceholders(string $content, array $variables): string
    {
        $replacements = [];
        foreach ($variables as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $replacements['{'.$key.'}'] = (string) ($value ?? '');
            }
        }

        return strtr($content, $replacements);
    }
}
