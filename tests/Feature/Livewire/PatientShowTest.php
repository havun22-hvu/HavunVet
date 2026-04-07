<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Patients\PatientShow;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders(): void
    {
        $patient = Patient::factory()->create(['name' => 'Buddy']);

        Livewire::test(PatientShow::class, ['patient' => $patient])
            ->assertStatus(200)
            ->assertSee('Buddy');
    }

    public function test_mark_deceased(): void
    {
        $patient = Patient::factory()->create();

        Livewire::test(PatientShow::class, ['patient' => $patient])
            ->call('markDeceased');

        $patient->refresh();
        $this->assertNotNull($patient->deceased_at);
    }
}
