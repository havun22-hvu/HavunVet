<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Patients\PatientForm;
use App\Models\Owner;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PatientFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_for_new_patient(): void
    {
        Livewire::test(PatientForm::class)
            ->assertStatus(200);
    }

    public function test_component_renders_for_existing_patient(): void
    {
        $patient = Patient::factory()->create(['name' => 'Buddy']);

        Livewire::test(PatientForm::class, ['patient' => $patient])
            ->assertSet('name', 'Buddy')
            ->assertSet('owner_id', $patient->owner_id);
    }

    public function test_mount_loads_existing_patient_data(): void
    {
        $patient = Patient::factory()->create([
            'name' => 'Rex',
            'species' => 'hond',
            'breed' => 'Labrador',
            'gender' => 'male',
            'neutered' => true,
            'chip_number' => '528140000123456',
            'weight' => 30.5,
            'color' => 'geel',
            'allergies' => ['pollen'],
            'notes' => 'Braaf',
        ]);

        Livewire::test(PatientForm::class, ['patient' => $patient])
            ->assertSet('name', 'Rex')
            ->assertSet('species', 'hond')
            ->assertSet('breed', 'Labrador')
            ->assertSet('gender', 'male')
            ->assertSet('neutered', true)
            ->assertSet('chip_number', '528140000123456')
            ->assertSet('color', 'geel')
            ->assertSet('notes', 'Braaf');
    }

    public function test_owner_search_returns_results(): void
    {
        Owner::factory()->create(['name' => 'Jan de Vries', 'active' => true]);
        Owner::factory()->create(['name' => 'Piet Jansen', 'active' => true]);

        Livewire::test(PatientForm::class)
            ->set('ownerSearch', 'Jan de Vries')
            ->assertNotSet('ownerResults', []);
    }

    public function test_owner_search_too_short_clears_results(): void
    {
        Livewire::test(PatientForm::class)
            ->set('ownerSearch', 'J')
            ->assertSet('ownerResults', []);
    }

    public function test_select_owner(): void
    {
        $owner = Owner::factory()->create(['name' => 'Jan']);

        Livewire::test(PatientForm::class)
            ->call('selectOwner', $owner->id)
            ->assertSet('owner_id', $owner->id)
            ->assertSet('ownerSearch', '')
            ->assertSet('ownerResults', [])
            ->assertSet('showNewOwnerForm', false);
    }

    public function test_clear_owner(): void
    {
        $owner = Owner::factory()->create();

        Livewire::test(PatientForm::class)
            ->call('selectOwner', $owner->id)
            ->call('clearOwner')
            ->assertSet('owner_id', null)
            ->assertSet('selectedOwner', null);
    }

    public function test_toggle_new_owner_form(): void
    {
        Livewire::test(PatientForm::class)
            ->call('toggleNewOwnerForm')
            ->assertSet('showNewOwnerForm', true)
            ->assertSet('owner_id', null);
    }

    public function test_save_creates_patient_with_existing_owner(): void
    {
        $owner = Owner::factory()->create();

        Livewire::test(PatientForm::class)
            ->call('selectOwner', $owner->id)
            ->set('name', 'Buddy')
            ->set('species', 'hond')
            ->set('gender', 'male')
            ->call('save');

        $this->assertDatabaseHas('patients', [
            'owner_id' => $owner->id,
            'name' => 'Buddy',
            'species' => 'hond',
        ]);
    }

    public function test_save_creates_patient_with_new_owner(): void
    {
        Livewire::test(PatientForm::class)
            ->call('toggleNewOwnerForm')
            ->set('new_owner_name', 'Nieuwe Eigenaar')
            ->set('new_owner_phone', '0612345678')
            ->set('new_owner_email', 'nieuw@test.nl')
            ->set('name', 'Buddy')
            ->set('species', 'hond')
            ->set('gender', 'male')
            ->call('save');

        $this->assertDatabaseHas('owners', ['name' => 'Nieuwe Eigenaar']);
        $this->assertDatabaseHas('patients', ['name' => 'Buddy']);
    }

    public function test_save_updates_existing_patient(): void
    {
        $patient = Patient::factory()->create(['name' => 'Oud']);

        Livewire::test(PatientForm::class, ['patient' => $patient])
            ->set('name', 'Nieuw')
            ->call('save');

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Nieuw',
        ]);
    }

    public function test_save_validates_required_fields(): void
    {
        Livewire::test(PatientForm::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_save_validates_owner_required(): void
    {
        Livewire::test(PatientForm::class)
            ->set('name', 'Buddy')
            ->set('species', 'hond')
            ->set('gender', 'male')
            ->call('save')
            ->assertHasErrors(['owner_id']);
    }
}
