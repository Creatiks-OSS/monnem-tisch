<?php

namespace Database\Seeders;

use App\Services\ProductImageMatcher;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $result = app(ProductImageMatcher::class)->sync();

        $this->command?->info(
            'Yemek görselleri: '.$result['matched'].' ürün eşleşti, '.$result['copied'].' dosya kopyalandı.'
        );
    }
}
