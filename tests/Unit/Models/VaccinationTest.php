<?php

namespace Tests\Unit\Models;

use App\Models\Patient;
use App\Models\Treatment;
use App\Models\Vaccination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VaccinationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vaccination_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $vaccination = Vaccination::factory()->create(['patient_id' => $patient->id]);

        $this->assertEquals($patient->id, $vaccination->patient->id);
    }

    public function test_vaccination_belongs_to_treatment(): void
    {
        $treatment = Treatment::factory()->create();
        $vaccination = Vaccination::factory()->create([
            'treatment_id' => $treatment->id,
            'patient_id' => $treatment->patient_id,
        ]);

        $this->assertEquals($treatment->id, $vaccination->treatment->id);
    }

    public function test_is_due_accessor(): void
    {
        $due = Vaccination::factory()->due()->create();
        $notDue = Vaccination::factory()->create(['next_due_date' => now()->addYear()]);

        $this->assertTrue($due->is_due);
        $this->assertFalse($notDue->is_due);
    }

    public function test_is_due_soon_accessor(): void
    {
        $soon = Vaccination::factory()->dueSoon()->create();
        $farAway = Vaccination::factory()->create(['next_due_date' => now()->addYear()]);

        $this->assertTrue($soon->is_due_soon);
        $this->assertFalse($farAway->is_due_soon);
    }

    public function test_due_scope(): void
    {
        Vaccination::factory()->due()->count(2)->create();
        Vaccination::factory()->create(['next_due_date' => now()->addYear()]);

        $this->assertCount(2, Vaccination::due()->get());
    }

    public function test_due_soon_scope(): void
    {
        Vaccination::factory()->dueSoon()->count(3)->create();
        Vaccination::factory()->create(['next_due_date' => now()->addYear()]);
        Vaccination::factory()->due()->create();

        $this->assertCount(3, Vaccination::dueSoon()->get());
    }
}
