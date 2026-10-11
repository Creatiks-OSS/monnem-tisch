<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function __construct(protected SettingService $settings) {}

    public function isConfigured(): bool
    {
        return $this->settings->bool('telegram_enabled', false, 'notifications')
            && $this->botToken() !== ''
            && $this->chatId() !== '';
    }

    public function send(string $text): array
    {
        $token = $this->botToken();
        $chatId = $this->chatId();

        if ($token === '' || $chatId === '') {
            return ['ok' => false, 'error' => 'Bot token veya Chat ID eksik.'];
        }

        try {
            $response = Http::timeout(12)
                ->asForm()
                ->post('https://api.telegram.org/bot'.$token.'/sendMessage', [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            $json = $response->json() ?? [];

            if ($response->successful() && ($json['ok'] ?? false) === true) {
                return ['ok' => true];
            }

            $error = (string) ($json['description'] ?? ('HTTP '.$response->status()));

            Log::warning('Telegram send failed', ['error' => $error]);

            return ['ok' => false, 'error' => $error];
        } catch (\Throwable $e) {
            Log::warning('Telegram send exception', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function botToken(): string
    {
        return trim($this->settings->string('telegram_bot_token', '', 'notifications'));
    }

    protected function chatId(): string
    {
        return trim($this->settings->string('telegram_chat_id', '', 'notifications'));
    }
}
