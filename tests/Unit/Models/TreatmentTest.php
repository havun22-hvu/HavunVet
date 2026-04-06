<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Treatment;
use App\Models\TreatmentItem;
use App\Models\Vaccination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_treatment_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $treatment = Treatment::factory()->create(['patient_id' => $patient->id]);

        $this->assertEquals($patient->id, $treatment->patient->id);
    }

    public function test_treatment_has_items(): void
    {
        $treatment = Treatment::factory()->create();
        TreatmentItem::factory()->count(3)->create(['treatment_id' => $treatment->id]);

        $this->assertCount(3, $treatment->items);
    }

    public function test_treatment_has_prescriptions(): void
    {
        $treatment = Treatment::factory()->create();
        Prescription::factory()->count(2)->create(['treatment_id' => $treatment->id]);

        $this->assertCount(2, $treatment->prescriptions);
    }

    public function test_treatment_has_vaccinations(): void
    {
        $treatment = Treatment::factory()->create();
        Vaccination::factory()->count(2)->create([
            'treatment_id' => $treatment->id,
            'patient_id' => $treatment->patient_id,
        ]);

        $this->assertCount(2, $treatment->vaccinations);
    }

    public function test_total_accessor_sums_items(): void
    {
        $treatment = Treatment::factory()->create();
        TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'quantity' => 2,
            'unit_price' => 25.00,
        ]);
        TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'quantity' => 1,
            'unit_price' => 50.00,
        ]);

        $this->assertEquals(100.00, $treatment->total);
    }

    public function test_total_with_vat_accessor(): void
    {
        $treatment = Treatment::factory()->create();
        TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_rate' => 21.00,
        ]);

        $this->assertEquals(121.00, $treatment->total_with_vat);
    }

    public function test_status_label_accessor(): void
    {
        $draft = Treatment::factory()->create(['status' => 'draft']);
        $completed = Treatment::factory()->create(['status' => 'completed']);
        $invoiced = Treatment::factory()->create(['status' => 'invoiced']);

        $this->assertEquals('Concept', $draft->status_label);
        $this->assertEquals('Afgerond', $completed->status_label);
        $this->assertEquals('Gefactureerd', $invoiced->status_label);
    }

    public function test_status_scope(): void
    {
        Treatment::factory()->count(2)->create(['status' => 'draft']);
        Treatment::factory()->count(3)->create(['status' => 'completed']);

        $this->assertCount(2, Treatment::status('draft')->get());
        $this->assertCount(3, Treatment::status('completed')->get());
    }

    public function test_needs_follow_up_scope(): void
    {
        // Should be included: follow up needed within 7 days
        Treatment::factory()->create([
            'follow_up_needed' => true,
            'follow_up_date' => now()->addDays(3),
        ]);

        // Should NOT be included: follow up too far out
        Treatment::factory()->create([
            'follow_up_needed' => true,
            'follow_up_date' => now()->addDays(14),
        ]);

        // Should NOT be included: no follow up
        Treatment::factory()->create([
            'follow_up_needed' => false,
            'follow_up_date' => null,
        ]);

        $this->assertCount(1, Treatment::needsFollowUp()->get());
    }
}
