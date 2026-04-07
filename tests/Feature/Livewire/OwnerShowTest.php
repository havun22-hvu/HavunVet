<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Owners\OwnerShow;
use App\Models\Owner;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_with_owner(): void
    {
        $owner = Owner::factory()->create(['name' => 'Jan de Vries']);

        Livewire::test(OwnerShow::class, ['owner' => $owner])
            ->assertStatus(200)
            ->assertSee('Jan de Vries');
    }

    public function test_shows_owner_patients(): void
    {
        $owner = Owner::factory()->create();
        Patient::factory()->create(['owner_id' => $owner->id, 'name' => 'Buddy']);

        Livewire::test(OwnerShow::class, ['owner' => $owner])
            ->assertSee('Buddy');
    }

    public function test_create_patient_opens_form(): void
    {
        $owner = Owner::factory()->create();

        Livewire::test(OwnerShow::class, ['owner' => $owner])
            ->call('createPatient')
            ->assertSet('showPatientForm', true)
            ->assertSet('species', 'dog')
            ->assertSet('gender', 'unknown');
    }

    public function test_save_patient_creates_patient(): void
    {
        $owner = Owner::factory()->create();

        Livewire::test(OwnerShow::class, ['owner' => $owner])
            ->call('createPatient')
            ->set('patient_name', 'Buddy')
            ->set('species', 'dog')
            ->set('breed', 'Labrador')
            ->set('gender', 'male')
            ->call('savePatient');

        $this->assertDatabaseHas('patients', [
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'dog',
            'breed' => 'Labrador',
        ]);
    }

    public function test_save_patient_validates_required_fields(): void
    {
        $owner = Owner::factory()->create();

        Livewire::test(OwnerShow::class, ['owner' => $owner])
            ->call('createPatient')
            ->set('patient_name', '')
            ->call('savePatient')
            ->assertHasErrors(['patient_name']);
    }

    public function test_cancel_patient_hides_form(): void
    {
        $owner = Owner::factory()->create();

        Livewire::test(OwnerShow::class, ['owner' => $owner])
            ->call('createPatient')
            ->assertSet('showPatientForm', true)
            ->call('cancelPatient')
            ->assertSet('showPatientForm', false);
    }

    public function test_save_patient_with_all_fields(): void
    {
        $owner = Owner::factory()->create();

        Livewire::test(OwnerShow::class, ['owner' => $owner])
            ->call('createPatient')
            ->set('patient_name', 'Rex')
            ->set('species', 'dog')
            ->set('breed', 'Herder')
            ->set('date_of_birth', '2020-01-15')
            ->set('gender', 'male')
            ->set('neutered', true)
            ->set('chip_number', '528140000123456')
            ->set('weight', 32.5)
            ->set('color', 'zwart')
            ->set('coat_type', 'kort')
            ->set('patient_notes', 'Braaf dier')
            ->call('savePatient');

        $this->assertDatabaseHas('patients', [
            'owner_id' => $owner->id,
            'name' => 'Rex',
            'chip_number' => '528140000123456',
        ]);
    }
}
