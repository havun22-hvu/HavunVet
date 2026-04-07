<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Owners\OwnerIndex;
use App\Models\Owner;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OwnerIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders(): void
    {
        Livewire::test(OwnerIndex::class)
            ->assertStatus(200)
            ->assertSee('Eigenaren');
    }

    public function test_lists_owners(): void
    {
        $owner = Owner::factory()->create(['name' => 'Jan de Vries']);

        Livewire::test(OwnerIndex::class)
            ->assertSee('Jan de Vries');
    }

    public function test_search_filters_owners(): void
    {
        Owner::factory()->create(['name' => 'Jan de Vries']);
        Owner::factory()->create(['name' => 'Piet Jansen']);

        Livewire::test(OwnerIndex::class)
            ->set('search', 'Jan de Vries')
            ->assertSee('Jan de Vries')
            ->assertDontSee('Piet Jansen');
    }

    public function test_create_opens_form(): void
    {
        Livewire::test(OwnerIndex::class)
            ->call('create')
            ->assertSet('showForm', true)
            ->assertSet('editing', null)
            ->assertSet('active', true);
    }

    public function test_edit_loads_owner_data(): void
    {
        $owner = Owner::factory()->create([
            'name' => 'Jan',
            'email' => 'jan@test.nl',
            'phone' => '0612345678',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
            'ubn' => '123456',
        ]);

        Livewire::test(OwnerIndex::class)
            ->call('edit', $owner)
            ->assertSet('showForm', true)
            ->assertSet('name', 'Jan')
            ->assertSet('email', 'jan@test.nl')
            ->assertSet('phone', '0612345678')
            ->assertSet('postal_code', '1234AB')
            ->assertSet('city', 'Amsterdam')
            ->assertSet('ubn', '123456');
    }

    public function test_save_creates_new_owner(): void
    {
        Livewire::test(OwnerIndex::class)
            ->call('create')
            ->set('name', 'Nieuwe Eigenaar')
            ->set('email', 'nieuw@test.nl')
            ->set('phone', '0612345678')
            ->call('save');

        $this->assertDatabaseHas('owners', [
            'name' => 'Nieuwe Eigenaar',
            'email' => 'nieuw@test.nl',
        ]);
    }

    public function test_save_updates_existing_owner(): void
    {
        $owner = Owner::factory()->create(['name' => 'Oud']);

        Livewire::test(OwnerIndex::class)
            ->call('edit', $owner)
            ->set('name', 'Nieuw')
            ->call('save');

        $this->assertDatabaseHas('owners', [
            'id' => $owner->id,
            'name' => 'Nieuw',
        ]);
    }

    public function test_save_validates_required_name(): void
    {
        Livewire::test(OwnerIndex::class)
            ->call('create')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_cancel_hides_form(): void
    {
        Livewire::test(OwnerIndex::class)
            ->call('create')
            ->assertSet('showForm', true)
            ->call('cancel')
            ->assertSet('showForm', false);
    }

    public function test_lookup_address_with_invalid_postcode(): void
    {
        Livewire::test(OwnerIndex::class)
            ->call('create')
            ->set('postal_code', 'INVALID')
            ->call('lookupAddress')
            ->assertSet('lookupError', 'Ongeldige postcode (formaat: 1234AB)');
    }

    public function test_lookup_address_without_house_number(): void
    {
        Livewire::test(OwnerIndex::class)
            ->call('create')
            ->set('postal_code', '1234AB')
            ->set('house_number', '')
            ->call('lookupAddress')
            ->assertSet('lookupError', 'Vul een huisnummer in');
    }

    public function test_owners_show_patient_count(): void
    {
        $owner = Owner::factory()->create();
        Patient::factory()->count(3)->create(['owner_id' => $owner->id]);

        Livewire::test(OwnerIndex::class)
            ->assertSee('3');
    }

    public function test_updating_search_resets_page(): void
    {
        Owner::factory()->count(20)->create();

        Livewire::test(OwnerIndex::class)
            ->set('search', 'test')
            ->assertSet('search', 'test');
    }
}
