<?php

namespace App\Jobs;

use App\Services\IngredientAnalysisService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AnalyzeOrderIngredientsJob
{
    use Dispatchable;

    public function __construct(public int $orderId) {}

    public function handle(IngredientAnalysisService $analysis): void
    {
        try {
            $analysis->analyze($this->orderId);
        } catch (\Throwable $e) {
            Log::warning('Order ingredient analysis job failed', [
                'order_id' => $this->orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
