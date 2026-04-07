<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Appointments\AppointmentIndex;
use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppointmentIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders(): void
    {
        Livewire::test(AppointmentIndex::class)
            ->assertStatus(200)
            ->assertSee('Agenda');
    }

    public function test_mount_sets_today_date(): void
    {
        Livewire::test(AppointmentIndex::class)
            ->assertSet('date', now()->toDateString());
    }

    public function test_previous_day(): void
    {
        $today = now()->toDateString();

        Livewire::test(AppointmentIndex::class)
            ->assertSet('date', $today)
            ->call('previousDay')
            ->assertSet('date', now()->subDay()->toDateString());
    }

    public function test_next_day(): void
    {
        $today = now()->toDateString();

        Livewire::test(AppointmentIndex::class)
            ->assertSet('date', $today)
            ->call('nextDay')
            ->assertSet('date', now()->addDay()->toDateString());
    }

    public function test_today_resets_date(): void
    {
        Livewire::test(AppointmentIndex::class)
            ->call('nextDay')
            ->call('nextDay')
            ->call('today')
            ->assertSet('date', now()->toDateString());
    }

    public function test_update_status(): void
    {
        $appointment = Appointment::factory()->create([
            'status' => 'scheduled',
            'scheduled_at' => now(),
        ]);

        Livewire::test(AppointmentIndex::class)
            ->call('updateStatus', $appointment->id, 'completed');

        $appointment->refresh();
        $this->assertEquals('completed', $appointment->status);
    }

    public function test_filter_status(): void
    {
        Appointment::factory()->create([
            'scheduled_at' => now(),
            'status' => 'scheduled',
        ]);
        Appointment::factory()->create([
            'scheduled_at' => now(),
            'status' => 'completed',
        ]);

        Livewire::test(AppointmentIndex::class)
            ->set('filterStatus', 'scheduled')
            ->assertSet('filterStatus', 'scheduled');
    }
}
