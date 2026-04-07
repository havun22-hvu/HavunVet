<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Patients\PatientIndex;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders(): void
    {
        Livewire::test(PatientIndex::class)
            ->assertStatus(200)
            ->assertSee('Patiënten');
    }

    public function test_lists_patients(): void
    {
        Patient::factory()->create(['name' => 'Buddy']);

        Livewire::test(PatientIndex::class)
            ->assertSee('Buddy');
    }

    public function test_search_filters_patients(): void
    {
        Patient::factory()->create(['name' => 'Buddy']);
        Patient::factory()->create(['name' => 'Rex']);

        Livewire::test(PatientIndex::class)
            ->set('search', 'Buddy')
            ->assertSee('Buddy')
            ->assertDontSee('Rex');
    }

    public function test_species_filter(): void
    {
        Patient::factory()->create(['name' => 'Buddy', 'species' => 'hond']);
        Patient::factory()->create(['name' => 'Mimi', 'species' => 'kat']);

        Livewire::test(PatientIndex::class)
            ->set('species', 'hond')
            ->assertSee('Buddy')
            ->assertDontSee('Mimi');
    }

    public function test_show_deceased_toggle(): void
    {
        Patient::factory()->create(['name' => 'Levend']);
        Patient::factory()->deceased()->create(['name' => 'Overleden']);

        Livewire::test(PatientIndex::class)
            ->assertSee('Levend')
            ->assertDontSee('Overleden')
            ->set('showDeceased', true)
            ->assertSee('Levend')
            ->assertSee('Overleden');
    }

    public function test_updating_search_resets_page(): void
    {
        Patient::factory()->count(25)->create();

        Livewire::test(PatientIndex::class)
            ->set('search', 'test')
            ->assertSet('search', 'test');
    }

    public function test_updating_species_resets_page(): void
    {
        Patient::factory()->count(25)->create();

        Livewire::test(PatientIndex::class)
            ->set('species', 'hond')
            ->assertSet('species', 'hond');
    }
}
