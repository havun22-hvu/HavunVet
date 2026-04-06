<?php

namespace Tests\Unit\Models;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointment_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);

        $this->assertEquals($patient->id, $appointment->patient->id);
    }

    public function test_end_time_accessor(): void
    {
        $appointment = Appointment::factory()->create([
            'scheduled_at' => '2026-04-06 10:00:00',
            'duration_minutes' => 30,
        ]);

        $this->assertEquals('2026-04-06 10:30:00', $appointment->end_time->format('Y-m-d H:i:s'));
    }

    public function test_type_label_accessor(): void
    {
        $consult = Appointment::factory()->create(['type' => 'consult']);
        $surgery = Appointment::factory()->create(['type' => 'surgery']);
        $emergency = Appointment::factory()->create(['type' => 'emergency']);

        $this->assertEquals('Consult', $consult->type_label);
        $this->assertEquals('Operatie', $surgery->type_label);
        $this->assertEquals('Spoed', $emergency->type_label);
    }

    public function test_status_label_accessor(): void
    {
        $scheduled = Appointment::factory()->create(['status' => 'scheduled']);
        $completed = Appointment::factory()->completed()->create();
        $cancelled = Appointment::factory()->cancelled()->create();

        $this->assertEquals('Gepland', $scheduled->status_label);
        $this->assertEquals('Afgerond', $completed->status_label);
        $this->assertEquals('Geannuleerd', $cancelled->status_label);
    }

    public function test_status_color_accessor(): void
    {
        $scheduled = Appointment::factory()->create(['status' => 'scheduled']);
        $completed = Appointment::factory()->completed()->create();

        $this->assertEquals('gray', $scheduled->status_color);
        $this->assertEquals('green', $completed->status_color);
    }

    public function test_upcoming_scope_excludes_completed_and_cancelled(): void
    {
        Appointment::factory()->create([
            'scheduled_at' => now()->addDay(),
            'status' => 'scheduled',
        ]);
        Appointment::factory()->create([
            'scheduled_at' => now()->addDay(),
            'status' => 'completed',
        ]);
        Appointment::factory()->create([
            'scheduled_at' => now()->addDay(),
            'status' => 'cancelled',
        ]);

        $this->assertCount(1, Appointment::upcoming()->get());
    }

    public function test_status_scope(): void
    {
        Appointment::factory()->count(2)->create(['status' => 'scheduled']);
        Appointment::factory()->completed()->count(3)->create();

        $this->assertCount(2, Appointment::status('scheduled')->get());
        $this->assertCount(3, Appointment::status('completed')->get());
    }
}
