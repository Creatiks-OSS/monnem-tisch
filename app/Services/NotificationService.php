<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function notifyAdmins(string $type, string $title, string $body, array $data = []): void
    {
        $admins = collect();

        try {
            $admins = User::query()
                ->where('is_active', true)
                ->role(['Super Admin', 'Admin', 'Manager', 'Sales', 'Kitchen', 'Editor'])
                ->get();
        } catch (\Throwable) {
            $admins = collect();
        }

        if ($admins->isEmpty()) {
            $admins = User::query()->where('is_active', true)->get();
        }

        foreach ($admins as $admin) {
            try {
                AdminNotification::query()->create([
                    'user_id' => $admin->id,
                    'type' => $type,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data ?: null,
                    'read_at' => null,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Admin notification failed', [
                    'user_id' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
