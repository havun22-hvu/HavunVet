<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\HomeVisit;
use App\Models\Owner;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Treatment;
use App\Models\Vaccination;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_belongs_to_owner(): void
    {
        $owner = Owner::factory()->create();
        $patient = Patient::factory()->create(['owner_id' => $owner->id]);

        $this->assertEquals($owner->id, $patient->owner->id);
    }

    public function test_patient_has_treatments(): void
    {
        $patient = Patient::factory()->create();
        Treatment::factory()->count(2)->create(['patient_id' => $patient->id]);

        $this->assertCount(2, $patient->treatments);
    }

    public function test_patient_has_vaccinations(): void
    {
        $patient = Patient::factory()->create();
        Vaccination::factory()->count(3)->create(['patient_id' => $patient->id]);

        $this->assertCount(3, $patient->vaccinations);
    }

    public function test_patient_has_prescriptions(): void
    {
        $patient = Patient::factory()->create();
        Prescription::factory()->count(2)->create(['patient_id' => $patient->id]);

        $this->assertCount(2, $patient->prescriptions);
    }

    public function test_patient_has_appointments(): void
    {
        $patient = Patient::factory()->create();
        Appointment::factory()->count(2)->create(['patient_id' => $patient->id]);

        $this->assertCount(2, $patient->appointments);
    }

    public function test_patient_has_home_visits(): void
    {
        $patient = Patient::factory()->create();
        HomeVisit::factory()->count(2)->create(['patient_id' => $patient->id]);

        $this->assertCount(2, $patient->homeVisits);
    }

    public function test_age_accessor_shows_years(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 12, 0, 0));

        $patient = Patient::factory()->create([
            'date_of_birth' => Carbon::create(2023, 6, 15),
        ]);

        // diffInYears(now) = 3, diffInMonths(now) % 12 = 0
        $this->assertStringContainsString('jaar', $patient->age);
        $this->assertNotNull($patient->age);

        Carbon::setTestNow();
    }

    public function test_age_accessor_returns_not_null_for_young_animal(): void
    {
        $patient = Patient::factory()->create([
            'date_of_birth' => now()->subDays(60),
        ]);

        // Should return something (either "X maanden" or "0 jaar, X maanden")
        $this->assertNotNull($patient->age);
        $this->assertStringContainsString('maanden', $patient->age);
    }

    public function test_age_accessor_returns_null_without_dob(): void
    {
        $patient = Patient::factory()->create(['date_of_birth' => null]);

        $this->assertNull($patient->age);
    }

    public function test_gender_label_male_neutered(): void
    {
        $patient = Patient::factory()->create(['gender' => 'male', 'neutered' => true]);
        $this->assertEquals('Gecastreerd', $patient->gender_label);
    }

    public function test_gender_label_male_intact(): void
    {
        $patient = Patient::factory()->create(['gender' => 'male', 'neutered' => false]);
        $this->assertEquals('Reu/Kater', $patient->gender_label);
    }

    public function test_gender_label_female_neutered(): void
    {
        $patient = Patient::factory()->create(['gender' => 'female', 'neutered' => true]);
        $this->assertEquals('Gesteriliseerd', $patient->gender_label);
    }

    public function test_gender_label_female_intact(): void
    {
        $patient = Patient::factory()->create(['gender' => 'female', 'neutered' => false]);
        $this->assertEquals('Teef/Poes', $patient->gender_label);
    }

    public function test_alive_scope_excludes_deceased(): void
    {
        Patient::factory()->count(3)->create();
        Patient::factory()->deceased()->count(2)->create();

        $this->assertCount(3, Patient::alive()->get());
    }

    public function test_species_scope(): void
    {
        Patient::factory()->dog()->count(2)->create();
        Patient::factory()->cat()->count(3)->create();

        $this->assertCount(2, Patient::species('hond')->get());
        $this->assertCount(3, Patient::species('kat')->get());
    }

    public function test_search_scope_finds_by_name(): void
    {
        Patient::factory()->create(['name' => 'Buddy']);
        Patient::factory()->create(['name' => 'Rex']);

        $this->assertCount(1, Patient::search('Buddy')->get());
    }

    public function test_search_scope_finds_by_chip_number(): void
    {
        Patient::factory()->create(['chip_number' => '528140000123456']);
        Patient::factory()->create(['chip_number' => '528140000789012']);

        $this->assertCount(1, Patient::search('123456')->get());
    }

    public function test_search_scope_finds_by_owner_name(): void
    {
        $owner = Owner::factory()->create(['name' => 'Jan Pietersen']);
        Patient::factory()->create(['owner_id' => $owner->id, 'name' => 'Buddy']);

        $other = Owner::factory()->create(['name' => 'Kees']);
        Patient::factory()->create(['owner_id' => $other->id, 'name' => 'Rex']);

        $results = Patient::search('Pietersen')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Buddy', $results->first()->name);
    }
}
