<?php

namespace Tests\Unit\Models;

use App\Models\Medication;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_medication_has_prescriptions(): void
    {
        $medication = Medication::factory()->create();
        Prescription::factory()->count(2)->create(['medication_id' => $medication->id]);

        $this->assertCount(2, $medication->prescriptions);
    }

    public function test_is_low_stock_accessor(): void
    {
        $low = Medication::factory()->lowStock()->create();
        $ok = Medication::factory()->create(['stock_quantity' => 50, 'min_stock_level' => 10]);

        $this->assertTrue($low->is_low_stock);
        $this->assertFalse($ok->is_low_stock);
    }

    public function test_is_expired_accessor(): void
    {
        $expired = Medication::factory()->expired()->create();
        $valid = Medication::factory()->create(['expiry_date' => now()->addYear()]);

        $this->assertTrue($expired->is_expired);
        $this->assertFalse($valid->is_expired);
    }

    public function test_is_expiring_soon_accessor(): void
    {
        $soon = Medication::factory()->expiringSoon()->create();
        $farAway = Medication::factory()->create(['expiry_date' => now()->addYear()]);

        $this->assertTrue($soon->is_expiring_soon);
        $this->assertFalse($farAway->is_expiring_soon);
    }

    public function test_full_name_accessor(): void
    {
        $med = Medication::factory()->create([
            'name' => 'Amoxicillin',
            'strength' => '250mg',
            'dosage_form' => 'tablet',
        ]);

        $this->assertEquals('Amoxicillin 250mg (tablet)', $med->full_name);
    }

    public function test_full_name_without_optional_fields(): void
    {
        $med = Medication::factory()->create([
            'name' => 'Paracetamol',
            'strength' => null,
            'dosage_form' => null,
        ]);

        $this->assertEquals('Paracetamol', $med->full_name);
    }

    public function test_search_scope_finds_by_name(): void
    {
        Medication::factory()->create(['name' => 'Amoxicillin']);
        Medication::factory()->create(['name' => 'Ibuprofen']);

        $this->assertCount(1, Medication::search('Amox')->get());
    }

    public function test_search_scope_finds_by_active_ingredient(): void
    {
        Medication::factory()->create(['active_ingredient' => 'amoxicilline']);
        Medication::factory()->create(['active_ingredient' => 'ibuprofen']);

        $this->assertCount(1, Medication::search('amoxicilline')->get());
    }

    public function test_expired_scope(): void
    {
        Medication::factory()->expired()->count(2)->create();
        Medication::factory()->create(['expiry_date' => now()->addYear()]);

        $this->assertCount(2, Medication::expired()->get());
    }

    public function test_expiring_soon_scope(): void
    {
        Medication::factory()->expiringSoon()->count(2)->create();
        Medication::factory()->create(['expiry_date' => now()->addYear()]);
        Medication::factory()->expired()->create();

        $this->assertCount(2, Medication::expiringSoon()->get());
    }
}
