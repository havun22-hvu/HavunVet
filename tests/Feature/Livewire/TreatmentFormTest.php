<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Treatments\TreatmentForm;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\TreatmentItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * TreatmentForm Livewire component tests.
 *
 * Note: TreatmentForm::render() queries work_locations table which was dropped
 * in migration 2025_01_02_000030. Tests that trigger render() cannot run.
 * We test the component methods that do NOT call render().
 */
class TreatmentFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_mount_sets_defaults_for_new_treatment(): void
    {
        $patient = Patient::factory()->create();

        $component = new TreatmentForm();
        $component->mount($patient);

        $this->assertEquals($patient->id, $component->patient->id);
        $this->assertEquals(now()->toDateString(), $component->date);
        $this->assertNull($component->treatment);
        $this->assertEmpty($component->items);
    }

    public function test_mount_loads_existing_treatment(): void
    {
        $patient = Patient::factory()->create();
        $treatment = Treatment::factory()->create([
            'patient_id' => $patient->id,
            'complaint' => 'Kreupel',
            'diagnosis' => 'Verstuikt',
            'anamnesis' => 'Sinds gisteren',
            'examination' => 'Rechterbeen pijnlijk',
            'treatment_description' => 'Rust',
            'veterinarian' => 'Dr. Test',
        ]);
        TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'description' => 'Consult',
            'quantity' => 1,
            'unit_price' => 35.00,
        ]);

        $component = new TreatmentForm();
        $component->mount($patient, $treatment);

        $this->assertEquals($treatment->id, $component->treatment->id);
        $this->assertEquals('Kreupel', $component->complaint);
        $this->assertEquals('Verstuikt', $component->diagnosis);
        $this->assertCount(1, $component->items);
        $this->assertEquals('Consult', $component->items[0]['description']);
    }

    public function test_add_item_adds_empty_item(): void
    {
        $patient = Patient::factory()->create();

        $component = new TreatmentForm();
        $component->mount($patient);
        $component->addItem();

        $this->assertCount(1, $component->items);
        $this->assertEquals('', $component->items[0]['description']);
        $this->assertEquals(1, $component->items[0]['quantity']);
        $this->assertEquals(21, $component->items[0]['vat_rate']);
    }

    public function test_remove_item_removes_by_index(): void
    {
        $patient = Patient::factory()->create();

        $component = new TreatmentForm();
        $component->mount($patient);
        $component->addItem();
        $component->addItem();

        $component->items[0]['description'] = 'First';
        $component->items[1]['description'] = 'Second';

        $component->removeItem(0);

        $this->assertCount(1, $component->items);
        $this->assertEquals('Second', $component->items[0]['description']);
    }

    public function test_add_multiple_items(): void
    {
        $patient = Patient::factory()->create();

        $component = new TreatmentForm();
        $component->mount($patient);
        $component->addItem();
        $component->addItem();
        $component->addItem();

        $this->assertCount(3, $component->items);
    }
}
