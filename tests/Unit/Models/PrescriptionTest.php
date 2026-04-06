<?php

namespace Tests\Unit\Models;

use App\Models\Medication;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescription_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $prescription = Prescription::factory()->create(['patient_id' => $patient->id]);

        $this->assertEquals($patient->id, $prescription->patient->id);
    }

    public function test_prescription_belongs_to_treatment(): void
    {
        $treatment = Treatment::factory()->create();
        $prescription = Prescription::factory()->create([
            'treatment_id' => $treatment->id,
            'patient_id' => $treatment->patient_id,
        ]);

        $this->assertEquals($treatment->id, $prescription->treatment->id);
    }

    public function test_prescription_belongs_to_medication(): void
    {
        $medication = Medication::factory()->create();
        $prescription = Prescription::factory()->create(['medication_id' => $medication->id]);

        $this->assertEquals($medication->id, $prescription->medication->id);
    }

    public function test_is_dispensed_accessor(): void
    {
        $dispensed = Prescription::factory()->dispensed()->create();
        $notDispensed = Prescription::factory()->create(['dispensed_at' => null]);

        $this->assertTrue($dispensed->is_dispensed);
        $this->assertFalse($notDispensed->is_dispensed);
    }

    public function test_dosage_instructions_accessor(): void
    {
        $prescription = Prescription::factory()->create([
            'medication_name' => 'Amoxicillin',
            'dosage' => '250mg',
            'frequency' => '2x daags',
            'duration_days' => 7,
        ]);

        $instructions = $prescription->dosage_instructions;

        $this->assertStringContainsString('Amoxicillin', $instructions);
        $this->assertStringContainsString('250mg', $instructions);
        $this->assertStringContainsString('2x daags', $instructions);
        $this->assertStringContainsString('7 dagen', $instructions);
    }

    public function test_dosage_instructions_without_duration(): void
    {
        $prescription = Prescription::factory()->create([
            'medication_name' => 'Ibuprofen',
            'dosage' => '400mg',
            'frequency' => '3x daags',
            'duration_days' => null,
        ]);

        $instructions = $prescription->dosage_instructions;

        $this->assertStringNotContainsString('dagen', $instructions);
    }
}
