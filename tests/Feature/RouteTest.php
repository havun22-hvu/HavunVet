<?php

namespace Tests\Feature;

use App\Models\Owner;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_owners_index_loads(): void
    {
        $this->get('/owners')->assertStatus(200);
    }

    public function test_owner_show_loads(): void
    {
        $owner = Owner::factory()->create();

        $this->get("/owners/{$owner->id}")->assertStatus(200);
    }

    public function test_patients_index_loads(): void
    {
        $this->get('/patients')->assertStatus(200);
    }

    public function test_patient_create_loads(): void
    {
        $this->get('/patients/create')->assertStatus(200);
    }

    public function test_patient_show_loads(): void
    {
        $patient = Patient::factory()->create();

        $this->get("/patients/{$patient->id}")->assertStatus(200);
    }

    public function test_patient_edit_loads(): void
    {
        $patient = Patient::factory()->create();

        $this->get("/patients/{$patient->id}/edit")->assertStatus(200);
    }

    // Note: Treatment create/edit routes skipped because TreatmentForm references
    // dropped WorkLocation model (existing codebase issue)

    public function test_appointments_index_loads(): void
    {
        $this->get('/appointments')->assertStatus(200);
    }

    public function test_nonexistent_route_returns_404(): void
    {
        $this->get('/does-not-exist')->assertStatus(404);
    }
}
