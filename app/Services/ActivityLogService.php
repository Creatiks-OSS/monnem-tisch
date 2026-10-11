<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public function log(string $description, mixed $subject = null, array $properties = []): void
    {
        $payload = [
            'user_id' => Auth::id(),
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
        ];

        if ($subject instanceof Model) {
            $payload['subject_type'] = $subject->getMorphClass();
            $payload['subject_id'] = $subject->getKey();
        }

        ActivityLog::query()->create($payload);
    }
}
