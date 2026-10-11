<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    public function __construct(protected SettingService $settings) {}

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '';
    }

    public function model(): string
    {
        $model = trim($this->settings->string('model', '', 'ai'));
        if ($model === '') {
            $model = trim((string) config('services.openai.model', 'gpt-4o-mini'));
        }

        return $model !== '' ? $model : 'gpt-4o-mini';
    }

    public function isEnabled(): bool
    {
        return $this->settings->bool('enabled', true, 'ai') && $this->isConfigured();
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function chatJson(array $messages, int $maxTokens = 8000, float $temperature = 0.2): array
    {
        $key = $this->apiKey();
        if ($key === '') {
            throw new \RuntimeException('OpenAI API anahtarı eksik. Ayarlar → Yapay zekâ bölümünden ekleyin.');
        }

        $response = Http::timeout(90)
            ->withToken($key)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model(),
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'response_format' => ['type' => 'json_object'],
                'messages' => $messages,
            ]);

        $json = $response->json() ?? [];

        if (! $response->successful()) {
            $error = (string) ($json['error']['message'] ?? ('HTTP '.$response->status()));
            Log::warning('OpenAI request failed', ['error' => $error, 'status' => $response->status()]);

            throw new \RuntimeException($error);
        }

        $content = trim((string) data_get($json, 'choices.0.message.content', ''));
        if ($content === '') {
            throw new \RuntimeException('Yapay zekâ boş yanıt döndürdü.');
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            $decoded = json_decode($this->extractJson($content), true);
        }

        if (! is_array($decoded)) {
            throw new \RuntimeException('Yapay zekâ yanıtı JSON olarak okunamadı.');
        }

        return $decoded;
    }

    public function ping(): array
    {
        try {
            $this->chatJson([
                ['role' => 'system', 'content' => 'Reply with JSON only.'],
                ['role' => 'user', 'content' => '{"ping":true} — respond with {"ok":true}'],
            ], 32);

            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function apiKey(): string
    {
        $fromSettings = trim($this->settings->string('api_key', '', 'ai'));
        if ($fromSettings !== '') {
            return $fromSettings;
        }

        return trim((string) config('services.openai.key', ''));
    }

    protected function extractJson(string $content): string
    {
        if (preg_match('/\{.*\}/s', $content, $match)) {
            return $match[0];
        }

        return $content;
    }
}
