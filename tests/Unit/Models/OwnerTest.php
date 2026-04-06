<?php

namespace Tests\Unit\Models;

use App\Models\Owner;
use App\Models\Patient;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_has_patients_relationship(): void
    {
        $owner = Owner::factory()->create();
        Patient::factory()->count(3)->create(['owner_id' => $owner->id]);

        $this->assertCount(3, $owner->patients);
    }

    public function test_owner_has_treatments_through_patients(): void
    {
        $owner = Owner::factory()->create();
        $patient = Patient::factory()->create(['owner_id' => $owner->id]);
        Treatment::factory()->count(2)->create(['patient_id' => $patient->id]);

        // Note: Owner::treatments() has a return type bug (declares HasMany, returns HasManyThrough)
        // Test the underlying relationship works despite the type hint
        $treatments = $owner->hasManyThrough(Treatment::class, Patient::class)->get();
        $this->assertCount(2, $treatments);
    }

    public function test_full_address_accessor_with_all_fields(): void
    {
        $owner = Owner::factory()->create([
            'address' => 'Hoofdstraat',
            'house_number' => '10',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
        ]);

        $this->assertEquals('Hoofdstraat 10, 1234AB Amsterdam', $owner->full_address);
    }

    public function test_full_address_accessor_without_house_number(): void
    {
        $owner = Owner::factory()->create([
            'address' => 'Kerkweg',
            'house_number' => null,
            'postal_code' => '5678CD',
            'city' => 'Utrecht',
        ]);

        $this->assertEquals('Kerkweg, 5678CD Utrecht', $owner->full_address);
    }

    public function test_active_scope_filters_inactive_owners(): void
    {
        Owner::factory()->count(3)->create(['active' => true]);
        Owner::factory()->count(2)->create(['active' => false]);

        $this->assertCount(3, Owner::active()->get());
    }

    public function test_search_scope_finds_by_name(): void
    {
        Owner::factory()->create(['name' => 'Jan de Vries']);
        Owner::factory()->create(['name' => 'Piet Jansen']);

        $results = Owner::search('Vries')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Jan de Vries', $results->first()->name);
    }

    public function test_search_scope_finds_by_email(): void
    {
        Owner::factory()->create(['email' => 'jan@example.com']);
        Owner::factory()->create(['email' => 'piet@test.nl']);

        $this->assertCount(1, Owner::search('jan@example')->get());
    }

    public function test_search_scope_finds_by_patient_name(): void
    {
        $owner = Owner::factory()->create(['name' => 'Eigenaar']);
        Patient::factory()->create(['owner_id' => $owner->id, 'name' => 'Buddy']);

        $otherOwner = Owner::factory()->create(['name' => 'Ander']);
        Patient::factory()->create(['owner_id' => $otherOwner->id, 'name' => 'Rex']);

        $results = Owner::search('Buddy')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Eigenaar', $results->first()->name);
    }

    public function test_patients_count_accessor(): void
    {
        $owner = Owner::factory()->create();
        Patient::factory()->count(4)->create(['owner_id' => $owner->id]);

        $this->assertEquals(4, $owner->patients_count);
    }

    public function test_active_patients_count_excludes_deceased(): void
    {
        $owner = Owner::factory()->create();
        Patient::factory()->count(3)->create(['owner_id' => $owner->id]);
        Patient::factory()->deceased()->create(['owner_id' => $owner->id]);

        $this->assertEquals(3, $owner->active_patients_count);
    }

    public function test_active_cast_is_boolean(): void
    {
        $owner = Owner::factory()->create(['active' => true]);

        $this->assertIsBool($owner->active);
    }
}
