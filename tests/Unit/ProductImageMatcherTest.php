<?php

namespace Tests\Unit;

use App\Services\ProductImageMatcher;
use PHPUnit\Framework\TestCase;

class ProductImageMatcherTest extends TestCase
{
    public function test_canonical_key_normalizes_names_and_filenames(): void
    {
        $matcher = new ProductImageMatcher;

        $this->assertSame(
            $matcher->canonicalKey('Käseplatte'),
            $matcher->canonicalKey('Käseplatte.png')
        );
        $this->assertSame(
            $matcher->canonicalKey('Große Käseplatte'),
            $matcher->canonicalKey('Große_Käseplatte_202605040437.jpeg')
        );
        $this->assertSame(
            $matcher->canonicalKey('Mini Cheese Burger'),
            $matcher->canonicalKey('Mini Cheeseburger')
        );
        $this->assertSame(
            $matcher->canonicalKey('Lachs-Ei Wrap'),
            $matcher->canonicalKey('Lach-Ei Wrap')
        );
        $this->assertSame(
            $matcher->canonicalKey('Erdbeer-Tiramisu'),
            $matcher->canonicalKey('Erdbeer- Tiramisu')
        );
        $this->assertSame(
            $matcher->canonicalKey("Baba's Knusperplatte"),
            $matcher->canonicalKey('Babas Knusperplatte')
        );
    }
}
