<?php

namespace Tests\Unit;

use App\Services\ProductCalorieCatalog;
use PHPUnit\Framework\TestCase;

class ProductCalorieCatalogTest extends TestCase
{
    public function test_known_dishes_have_portion_calories(): void
    {
        $catalog = new ProductCalorieCatalog;

        $this->assertSame(150, $catalog->forName('Datteln im Speckmantel'));
        $this->assertSame(365, $catalog->forName('Fiesta Mexicana Platte'));
        $this->assertSame(190, $catalog->forName('Frühlingsrollen Thai Style'));
        $this->assertSame(105, $catalog->forName('Gartensalat'));
        $this->assertSame(215, $catalog->forName('Mini Cheese Burger'));
        $this->assertSame(215, $catalog->forName('Mini Cheeseburger'));
        $this->assertSame(100, $catalog->forName('Obstplatte', 'fingerfood'));
        $this->assertSame(100, $catalog->forName('Obstplatte', 'desserts'));
        $this->assertSame(510, $catalog->forName('Lasagne Bolognese'));
        $this->assertSame(380, $catalog->forName('Mettbrötchen'));
        $this->assertSame(40, $catalog->forName('Sauerkraut'));
    }

    public function test_unknown_dishes_fall_back_to_category_default(): void
    {
        $catalog = new ProductCalorieCatalog;

        $this->assertSame(180, $catalog->forName('Unbekanntes Gericht', 'vorspeisen'));
        $this->assertSame(400, $catalog->forName('Unbekanntes Gericht', 'hauptgerichte'));
        $this->assertSame(200, $catalog->forName('Unbekanntes Gericht'));
    }
}
