<?php

namespace Tests\Unit\Models;

use App\Models\Treatment;
use App\Models\TreatmentItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_belongs_to_treatment(): void
    {
        $treatment = Treatment::factory()->create();
        $item = TreatmentItem::factory()->create(['treatment_id' => $treatment->id]);

        $this->assertEquals($treatment->id, $item->treatment->id);
    }

    public function test_total_accessor(): void
    {
        $item = TreatmentItem::factory()->create([
            'quantity' => 3,
            'unit_price' => 25.50,
        ]);

        $this->assertEquals(76.50, $item->total);
    }

    public function test_vat_amount_accessor(): void
    {
        $item = TreatmentItem::factory()->create([
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_rate' => 21.00,
        ]);

        $this->assertEquals(21.00, $item->vat_amount);
    }

    public function test_total_with_vat_accessor(): void
    {
        $item = TreatmentItem::factory()->create([
            'quantity' => 2,
            'unit_price' => 50.00,
            'vat_rate' => 21.00,
        ]);

        // total = 100, vat = 21, total_with_vat = 121
        $this->assertEquals(121.00, $item->total_with_vat);
    }

    public function test_vat_free_item(): void
    {
        $item = TreatmentItem::factory()->vatFree()->create([
            'quantity' => 1,
            'unit_price' => 100.00,
        ]);

        $this->assertEquals(0.00, $item->vat_amount);
        $this->assertEquals(100.00, $item->total_with_vat);
    }
}
