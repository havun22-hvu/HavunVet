<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Dashboard;
use App\Models\Appointment;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\Vaccination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders(): void
    {
        Livewire::test(Dashboard::class)
            ->assertStatus(200)
            ->assertSee('Dashboard');
    }

    public function test_dashboard_shows_today_appointments(): void
    {
        $patient = Patient::factory()->create(['name' => 'Buddy']);
        Appointment::factory()->today()->create([
            'patient_id' => $patient->id,
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('Buddy');
    }

    public function test_dashboard_shows_upcoming_vaccinations(): void
    {
        $patient = Patient::factory()->create(['name' => 'Rex']);
        Vaccination::factory()->dueSoon()->create([
            'patient_id' => $patient->id,
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('Rex');
    }

    public function test_dashboard_shows_follow_ups(): void
    {
        $patient = Patient::factory()->create(['name' => 'Mimi']);
        Treatment::factory()->create([
            'patient_id' => $patient->id,
            'follow_up_needed' => true,
            'follow_up_date' => now()->addDays(2),
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('Mimi');
    }

    public function test_dashboard_shows_low_stock_medications(): void
    {
        Medication::factory()->lowStock()->create(['name' => 'Amoxicillin']);

        Livewire::test(Dashboard::class)
            ->assertSee('Amoxicillin');
    }

    public function test_dashboard_shows_stats(): void
    {
        Patient::factory()->count(5)->create();
        Treatment::factory()->create(['date' => now()]);

        Livewire::test(Dashboard::class)
            ->assertSee('5');
    }
}
