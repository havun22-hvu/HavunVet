<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\HomeVisit;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_visit_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $visit = HomeVisit::factory()->create(['patient_id' => $patient->id]);

        $this->assertEquals($patient->id, $visit->patient->id);
    }

    public function test_home_visit_belongs_to_treatment(): void
    {
        $treatment = Treatment::factory()->create();
        $visit = HomeVisit::factory()->create([
            'treatment_id' => $treatment->id,
            'patient_id' => $treatment->patient_id,
        ]);

        $this->assertEquals($treatment->id, $visit->treatment->id);
    }

    public function test_home_visit_belongs_to_appointment(): void
    {
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);
        $visit = HomeVisit::factory()->create([
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
        ]);

        $this->assertEquals($appointment->id, $visit->appointment->id);
    }

    public function test_full_address_accessor(): void
    {
        $visit = HomeVisit::factory()->create([
            'address' => 'Kerkstraat 5',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
        ]);

        $this->assertEquals('Kerkstraat 5, 1234AB Amsterdam', $visit->full_address);
    }

    public function test_status_label_accessor(): void
    {
        $scheduled = HomeVisit::factory()->create(['status' => 'scheduled']);
        $completed = HomeVisit::factory()->completed()->create();
        $inTransit = HomeVisit::factory()->create(['status' => 'in_transit']);

        $this->assertEquals('Gepland', $scheduled->status_label);
        $this->assertEquals('Afgerond', $completed->status_label);
        $this->assertEquals('Onderweg', $inTransit->status_label);
    }

    public function test_calculate_travel_cost_with_distance(): void
    {
        $visit = HomeVisit::factory()->create(['travel_distance_km' => 50]);

        // 50km * 0.40 = 20.00 (> minimum of 15.00)
        $this->assertEquals(20.00, $visit->calculateTravelCost());
    }

    public function test_calculate_travel_cost_minimum_applies(): void
    {
        $visit = HomeVisit::factory()->create(['travel_distance_km' => 5]);

        // 5km * 0.40 = 2.00 (< minimum of 15.00), so minimum applies
        $this->assertEquals(15.00, $visit->calculateTravelCost());
    }

    public function test_calculate_travel_cost_without_distance(): void
    {
        $visit = HomeVisit::factory()->create(['travel_distance_km' => null]);

        $this->assertEquals(15.00, $visit->calculateTravelCost());
    }

    public function test_calculate_travel_cost_custom_rates(): void
    {
        $visit = HomeVisit::factory()->create(['travel_distance_km' => 100]);

        // 100km * 0.50 = 50.00 with custom minimum of 20.00
        $this->assertEquals(50.00, $visit->calculateTravelCost(20.00, 0.50));
    }

    public function test_upcoming_scope(): void
    {
        HomeVisit::factory()->create([
            'scheduled_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);
        HomeVisit::factory()->create([
            'scheduled_at' => now()->addDay(),
            'status' => 'completed',
        ]);
        HomeVisit::factory()->create([
            'scheduled_at' => now()->addDay(),
            'status' => 'cancelled',
        ]);

        $this->assertCount(1, HomeVisit::upcoming()->get());
    }

    public function test_status_scope(): void
    {
        HomeVisit::factory()->count(2)->create(['status' => 'scheduled']);
        HomeVisit::factory()->completed()->count(3)->create();

        $this->assertCount(2, HomeVisit::status('scheduled')->get());
        $this->assertCount(3, HomeVisit::status('completed')->get());
    }
}
