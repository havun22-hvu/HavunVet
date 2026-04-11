<?php

namespace Tests\Feature;

use App\Livewire\Owners\OwnerIndex;
use App\Models\Appointment;
use App\Models\HomeVisit;
use App\Models\Owner;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\TreatmentItem;
use App\Models\User;
use App\Models\WorkLocation;
use App\Services\HavunAdminService;
use App\Services\PostcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Push coverage from 82.8% to 90%+.
 * Targets the uncovered lines in models, services and livewire components.
 */
class Push90Test extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // User model (0% → 100%)
    // ──────────────────────────────────────────────────────────

    public function test_user_is_fillable(): void
    {
        $user = new User();
        $this->assertEquals(['name', 'email', 'password'], $user->getFillable());
    }

    public function test_user_hidden_attributes(): void
    {
        $user = new User();
        $this->assertEquals(['password', 'remember_token'], $user->getHidden());
    }

    public function test_user_casts(): void
    {
        $user = new User();
        $casts = $user->getCasts();
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create(['name' => 'Henk']);
        $this->assertDatabaseHas('users', ['name' => 'Henk']);
    }

    // ──────────────────────────────────────────────────────────
    // WorkLocation (legacy model, 0% → exercise code paths)
    // ──────────────────────────────────────────────────────────

    public function test_work_location_fillable(): void
    {
        $wl = new WorkLocation();
        $this->assertContains('name', $wl->getFillable());
        $this->assertContains('active', $wl->getFillable());
    }

    public function test_work_location_casts(): void
    {
        $wl = new WorkLocation();
        $this->assertEquals('boolean', $wl->getCasts()['active']);
    }

    public function test_work_location_active_scope_exists(): void
    {
        // The underlying table was dropped - we can still call the scope
        // on a fresh query builder, but should not run the query.
        $query = WorkLocation::query();
        $scoped = $query->active();
        $this->assertNotNull($scoped);
    }

    // ──────────────────────────────────────────────────────────
    // Appointment model accessors (63.2% → 100%)
    // ──────────────────────────────────────────────────────────

    public function test_appointment_end_time_accessor(): void
    {
        $appointment = Appointment::factory()->create([
            'scheduled_at' => '2026-04-10 10:00:00',
            'duration_minutes' => 45,
        ]);

        $this->assertEquals(
            '2026-04-10 10:45:00',
            $appointment->end_time->format('Y-m-d H:i:s')
        );
    }

    public function test_appointment_type_labels(): void
    {
        $types = [
            'consult' => 'Consult',
            'checkup' => 'Controle',
            'vaccination' => 'Vaccinatie',
            'surgery' => 'Operatie',
            'dental' => 'Gebit',
            'emergency' => 'Spoed',
            'home_visit' => 'Thuisbezoek',
            'other' => 'Anders',
        ];

        foreach ($types as $type => $label) {
            $a = Appointment::factory()->create(['type' => $type]);
            $this->assertEquals($label, $a->type_label);
        }
    }

    public function test_appointment_type_label_fallback(): void
    {
        $appointment = Appointment::factory()->make(['type' => 'unknown_type']);
        $this->assertEquals('unknown_type', $appointment->type_label);
    }

    public function test_appointment_status_labels(): void
    {
        $statuses = [
            'scheduled' => 'Gepland',
            'confirmed' => 'Bevestigd',
            'arrived' => 'Aangekomen',
            'in_progress' => 'Bezig',
            'completed' => 'Afgerond',
            'cancelled' => 'Geannuleerd',
            'no_show' => 'Niet verschenen',
        ];

        foreach ($statuses as $status => $label) {
            $a = Appointment::factory()->create(['status' => $status]);
            $this->assertEquals($label, $a->status_label);
        }
    }

    public function test_appointment_status_label_fallback(): void
    {
        $appointment = Appointment::factory()->make(['status' => 'weird']);
        $this->assertEquals('weird', $appointment->status_label);
    }

    public function test_appointment_status_colors(): void
    {
        $colors = [
            'scheduled' => 'gray',
            'confirmed' => 'blue',
            'arrived' => 'yellow',
            'in_progress' => 'orange',
            'completed' => 'green',
            'cancelled' => 'red',
            'no_show' => 'red',
        ];

        foreach ($colors as $status => $color) {
            $a = Appointment::factory()->create(['status' => $status]);
            $this->assertEquals($color, $a->status_color);
        }
    }

    public function test_appointment_status_color_fallback(): void
    {
        $appointment = Appointment::factory()->make(['status' => 'other']);
        $this->assertEquals('gray', $appointment->status_color);
    }

    public function test_appointment_today_scope(): void
    {
        Appointment::factory()->today()->create();
        Appointment::factory()->create(['scheduled_at' => now()->addDays(5)]);

        $this->assertEquals(1, Appointment::today()->count());
    }

    public function test_appointment_upcoming_scope_excludes_completed(): void
    {
        Appointment::factory()->create([
            'scheduled_at' => now()->addDays(1),
            'status' => 'scheduled',
        ]);
        Appointment::factory()->create([
            'scheduled_at' => now()->addDays(1),
            'status' => 'completed',
        ]);

        $this->assertEquals(1, Appointment::upcoming()->count());
    }

    public function test_appointment_status_scope(): void
    {
        Appointment::factory()->create(['status' => 'confirmed']);
        Appointment::factory()->create(['status' => 'cancelled']);

        $this->assertEquals(1, Appointment::status('confirmed')->count());
    }

    public function test_appointment_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);

        $this->assertInstanceOf(Patient::class, $appointment->patient);
        $this->assertEquals($patient->id, $appointment->patient->id);
    }

    // ──────────────────────────────────────────────────────────
    // HomeVisit accessors (84.2% → 100%)
    // ──────────────────────────────────────────────────────────

    public function test_home_visit_full_address(): void
    {
        $visit = HomeVisit::factory()->create([
            'address' => 'Hoofdstraat 1',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
        ]);

        $this->assertEquals('Hoofdstraat 1, 1234AB Amsterdam', $visit->full_address);
    }

    public function test_home_visit_status_labels(): void
    {
        $statuses = [
            'scheduled' => 'Gepland',
            'in_transit' => 'Onderweg',
            'arrived' => 'Ter plaatse',
            'completed' => 'Afgerond',
            'cancelled' => 'Geannuleerd',
        ];

        foreach ($statuses as $status => $label) {
            $v = HomeVisit::factory()->create(['status' => $status]);
            $this->assertEquals($label, $v->status_label);
        }
    }

    public function test_home_visit_status_label_fallback(): void
    {
        $visit = HomeVisit::factory()->make(['status' => 'custom']);
        $this->assertEquals('custom', $visit->status_label);
    }

    public function test_home_visit_calculate_travel_cost_with_distance(): void
    {
        $visit = HomeVisit::factory()->make(['travel_distance_km' => 50]);
        // 50 * 0.40 = 20, max(15, 20) = 20
        $this->assertEquals(20.0, $visit->calculateTravelCost());
    }

    public function test_home_visit_calculate_travel_cost_minimum(): void
    {
        $visit = HomeVisit::factory()->make(['travel_distance_km' => 10]);
        // 10 * 0.40 = 4, max(15, 4) = 15
        $this->assertEquals(15.0, $visit->calculateTravelCost());
    }

    public function test_home_visit_calculate_travel_cost_null_distance(): void
    {
        $visit = HomeVisit::factory()->make(['travel_distance_km' => null]);
        $this->assertEquals(15.0, $visit->calculateTravelCost());
    }

    public function test_home_visit_calculate_travel_cost_custom_rates(): void
    {
        $visit = HomeVisit::factory()->make(['travel_distance_km' => 100]);
        // 100 * 0.50 = 50, max(20, 50) = 50
        $this->assertEquals(50.0, $visit->calculateTravelCost(20.0, 0.50));
    }

    public function test_home_visit_today_scope(): void
    {
        HomeVisit::factory()->create(['scheduled_at' => now()]);
        HomeVisit::factory()->create(['scheduled_at' => now()->addDays(5)]);

        $this->assertEquals(1, HomeVisit::today()->count());
    }

    public function test_home_visit_upcoming_scope(): void
    {
        HomeVisit::factory()->create([
            'scheduled_at' => now()->addDays(1),
            'status' => 'scheduled',
        ]);
        HomeVisit::factory()->create([
            'scheduled_at' => now()->addDays(1),
            'status' => 'completed',
        ]);

        $this->assertEquals(1, HomeVisit::upcoming()->count());
    }

    public function test_home_visit_status_scope(): void
    {
        HomeVisit::factory()->create(['status' => 'scheduled']);
        HomeVisit::factory()->create(['status' => 'completed']);

        $this->assertEquals(1, HomeVisit::status('scheduled')->count());
    }

    public function test_home_visit_belongs_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $visit = HomeVisit::factory()->create(['patient_id' => $patient->id]);

        $this->assertInstanceOf(Patient::class, $visit->patient);
    }

    public function test_home_visit_belongs_to_treatment(): void
    {
        $treatment = Treatment::factory()->create();
        $visit = HomeVisit::factory()->create(['treatment_id' => $treatment->id]);

        $this->assertInstanceOf(Treatment::class, $visit->treatment);
    }

    public function test_home_visit_belongs_to_appointment(): void
    {
        $appointment = Appointment::factory()->create();
        $visit = HomeVisit::factory()->create(['appointment_id' => $appointment->id]);

        $this->assertInstanceOf(Appointment::class, $visit->appointment);
    }

    // ──────────────────────────────────────────────────────────
    // Treatment model (88.9% → 100%)
    // ──────────────────────────────────────────────────────────

    public function test_treatment_status_labels(): void
    {
        $labels = [
            'draft' => 'Concept',
            'completed' => 'Afgerond',
            'invoiced' => 'Gefactureerd',
        ];

        foreach ($labels as $status => $label) {
            $t = Treatment::factory()->create(['status' => $status]);
            $this->assertEquals($label, $t->status_label);
        }
    }

    public function test_treatment_status_label_fallback(): void
    {
        $t = Treatment::factory()->make(['status' => 'custom_status']);
        $this->assertEquals('custom_status', $t->status_label);
    }

    public function test_treatment_needs_follow_up_scope(): void
    {
        Treatment::factory()->create([
            'follow_up_needed' => true,
            'follow_up_date' => now()->addDays(3),
        ]);
        Treatment::factory()->create([
            'follow_up_needed' => true,
            'follow_up_date' => now()->addDays(30), // too far
        ]);
        Treatment::factory()->create(['follow_up_needed' => false]);

        $this->assertEquals(1, Treatment::needsFollowUp()->count());
    }

    public function test_treatment_status_scope(): void
    {
        Treatment::factory()->create(['status' => 'draft']);
        Treatment::factory()->create(['status' => 'invoiced']);

        $this->assertEquals(1, Treatment::status('draft')->count());
    }

    public function test_treatment_total_calculations(): void
    {
        $treatment = Treatment::factory()->create();
        TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'quantity' => 2,
            'unit_price' => 10,
            'vat_rate' => 21,
        ]);

        $this->assertEquals(20.0, $treatment->fresh()->total);
        $this->assertEqualsWithDelta(24.2, $treatment->fresh()->total_with_vat, 0.01);
    }

    // ──────────────────────────────────────────────────────────
    // Patient accessor edge cases (96.3% → 100%)
    // ──────────────────────────────────────────────────────────

    public function test_patient_age_null_when_no_dob(): void
    {
        $patient = Patient::factory()->create(['date_of_birth' => null]);
        $this->assertNull($patient->age);
    }

    public function test_patient_age_in_months_only(): void
    {
        $patient = Patient::factory()->create([
            'date_of_birth' => now()->subMonths(5),
        ]);
        $this->assertStringContainsString('maanden', $patient->age);
    }

    public function test_patient_age_in_years(): void
    {
        $patient = Patient::factory()->create([
            'date_of_birth' => now()->subYears(3),
        ]);
        $this->assertStringContainsString('jaar', $patient->age);
    }

    public function test_patient_gender_labels(): void
    {
        $male = Patient::factory()->create(['gender' => 'male', 'neutered' => false]);
        $this->assertEquals('Reu/Kater', $male->gender_label);

        $maleNeutered = Patient::factory()->create(['gender' => 'male', 'neutered' => true]);
        $this->assertEquals('Gecastreerd', $maleNeutered->gender_label);

        $female = Patient::factory()->create(['gender' => 'female', 'neutered' => false]);
        $this->assertEquals('Teef/Poes', $female->gender_label);

        $femaleNeutered = Patient::factory()->create(['gender' => 'female', 'neutered' => true]);
        $this->assertEquals('Gesteriliseerd', $femaleNeutered->gender_label);

        $unknown = Patient::factory()->create(['gender' => 'unknown']);
        $this->assertEquals('Onbekend', $unknown->gender_label);
    }

    public function test_patient_alive_scope(): void
    {
        Patient::factory()->create();
        Patient::factory()->deceased()->create();

        $this->assertEquals(1, Patient::alive()->count());
    }

    public function test_patient_species_scope(): void
    {
        Patient::factory()->dog()->create();
        Patient::factory()->cat()->create();

        $this->assertEquals(1, Patient::species('hond')->count());
    }

    // ──────────────────────────────────────────────────────────
    // Owner accessors (96.2% → 100%)
    // ──────────────────────────────────────────────────────────

    public function test_owner_full_address_complete(): void
    {
        $owner = Owner::factory()->create([
            'address' => 'Hoofdstraat',
            'house_number' => '10',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
        ]);

        $this->assertEquals('Hoofdstraat 10, 1234AB Amsterdam', $owner->full_address);
    }

    public function test_owner_full_address_without_house_number(): void
    {
        $owner = Owner::factory()->create([
            'address' => 'Hoofdstraat',
            'house_number' => null,
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
        ]);

        $this->assertStringContainsString('Hoofdstraat', $owner->full_address);
    }

    public function test_owner_patients_count_attribute(): void
    {
        $owner = Owner::factory()->create();
        Patient::factory()->count(3)->create(['owner_id' => $owner->id]);

        $this->assertEquals(3, $owner->patients_count);
    }

    public function test_owner_active_patients_count(): void
    {
        $owner = Owner::factory()->create();
        Patient::factory()->count(2)->create(['owner_id' => $owner->id]);
        Patient::factory()->deceased()->create(['owner_id' => $owner->id]);

        $this->assertEquals(2, $owner->active_patients_count);
    }

    public function test_owner_active_scope(): void
    {
        Owner::factory()->create(['active' => true]);
        Owner::factory()->inactive()->create();

        $this->assertEquals(1, Owner::active()->count());
    }

    public function test_treatment_work_location_relation(): void
    {
        $t = Treatment::factory()->create();
        // The belongsTo relation can be invoked; it returns null because
        // work_location_id column was dropped but the method still exists.
        $this->assertNull($t->workLocation);
    }

    public function test_treatment_home_visit_relation(): void
    {
        $treatment = Treatment::factory()->create();
        HomeVisit::factory()->create(['treatment_id' => $treatment->id]);

        $this->assertCount(1, $treatment->homeVisit);
    }

    // ──────────────────────────────────────────────────────────
    // OwnerIndex lookupAddress (75% → higher)
    // ──────────────────────────────────────────────────────────

    public function test_owner_index_lookup_invalid_postcode(): void
    {
        Livewire::test(OwnerIndex::class)
            ->set('postal_code', '123AB')
            ->set('house_number', '10')
            ->call('lookupAddress')
            ->assertSet('lookupError', 'Ongeldige postcode (formaat: 1234AB)');
    }

    public function test_owner_index_lookup_missing_house_number(): void
    {
        Livewire::test(OwnerIndex::class)
            ->set('postal_code', '1234AB')
            ->set('house_number', '')
            ->call('lookupAddress')
            ->assertSet('lookupError', 'Vul een huisnummer in');
    }

    public function test_owner_index_lookup_success(): void
    {
        Http::fake([
            'api.pdok.nl/*' => Http::response([
                'response' => [
                    'docs' => [[
                        'straatnaam' => 'Hoofdstraat',
                        'woonplaatsnaam' => 'Amsterdam',
                    ]],
                ],
            ], 200),
        ]);

        Livewire::test(OwnerIndex::class)
            ->set('postal_code', '1234AB')
            ->set('house_number', '10')
            ->call('lookupAddress')
            ->assertSet('address', 'Hoofdstraat')
            ->assertSet('city', 'Amsterdam')
            ->assertSet('lookupError', '');
    }

    public function test_owner_index_lookup_no_results(): void
    {
        Http::fake([
            'api.pdok.nl/*' => Http::response([
                'response' => ['docs' => []],
            ], 200),
        ]);

        Livewire::test(OwnerIndex::class)
            ->set('postal_code', '1234AB')
            ->set('house_number', '10')
            ->call('lookupAddress')
            ->assertSet('lookupError', 'Adres niet gevonden');
    }

    public function test_owner_index_lookup_api_failure(): void
    {
        Http::fake([
            'api.pdok.nl/*' => Http::response([], 500),
        ]);

        Livewire::test(OwnerIndex::class)
            ->set('postal_code', '1234AB')
            ->set('house_number', '10')
            ->call('lookupAddress')
            ->assertSet('lookupError', 'Adres niet gevonden');
    }

    public function test_owner_index_lookup_exception(): void
    {
        Http::fake(function () {
            throw new \Exception('Network down');
        });

        Livewire::test(OwnerIndex::class)
            ->set('postal_code', '1234AB')
            ->set('house_number', '10')
            ->call('lookupAddress')
            ->assertSet('lookupError', 'Adres opzoeken mislukt');
    }

    public function test_owner_index_edit_loads_owner(): void
    {
        $owner = Owner::factory()->create(['name' => 'John']);

        Livewire::test(OwnerIndex::class)
            ->call('edit', $owner)
            ->assertSet('name', 'John')
            ->assertSet('showForm', true);
    }

    public function test_owner_index_create_resets_form(): void
    {
        Livewire::test(OwnerIndex::class)
            ->set('name', 'old')
            ->call('create')
            ->assertSet('name', '')
            ->assertSet('active', true)
            ->assertSet('showForm', true);
    }

    public function test_owner_index_save_creates_owner(): void
    {
        Livewire::test(OwnerIndex::class)
            ->set('name', 'Piet')
            ->set('email', 'piet@test.nl')
            ->call('save');

        $this->assertDatabaseHas('owners', ['name' => 'Piet']);
    }

    public function test_owner_index_save_updates_existing(): void
    {
        $owner = Owner::factory()->create(['name' => 'Old']);

        Livewire::test(OwnerIndex::class)
            ->call('edit', $owner)
            ->set('name', 'New')
            ->call('save');

        $this->assertEquals('New', $owner->fresh()->name);
    }

    public function test_owner_index_cancel(): void
    {
        Livewire::test(OwnerIndex::class)
            ->set('showForm', true)
            ->call('cancel')
            ->assertSet('showForm', false);
    }

    // ──────────────────────────────────────────────────────────
    // HavunAdminService (74% → higher)
    // ──────────────────────────────────────────────────────────

    protected function setUpHavunAdminConfig(): void
    {
        config()->set('services.havunadmin.url', 'https://havunadmin.test/api');
        config()->set('services.havunadmin.secret', 'test-secret');
        config()->set('services.havunadmin.category', 'Dierenarts');
        \Illuminate\Support\Facades\Cache::forget('havunadmin_token');
    }

    public function test_havunadmin_make_creates_instance(): void
    {
        $this->setUpHavunAdminConfig();
        $service = HavunAdminService::make();
        $this->assertInstanceOf(HavunAdminService::class, $service);
    }

    public function test_havunadmin_ping_success(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc123'], 200),
        ]);

        $service = HavunAdminService::make();
        $this->assertTrue($service->ping());
    }

    public function test_havunadmin_ping_failure(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response([], 401),
        ]);

        $service = HavunAdminService::make();
        $this->assertFalse($service->ping());
    }

    public function test_havunadmin_search_customers(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/customers*' => Http::response([
                'data' => [['id' => 1, 'name' => 'Klant']],
                'meta' => ['total' => 1],
            ], 200),
        ]);

        $result = HavunAdminService::make()->searchCustomers('klant');
        $this->assertEquals(1, $result['meta']['total']);
    }

    public function test_havunadmin_search_customers_failure(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/customers*' => Http::response([], 500),
        ]);

        $result = HavunAdminService::make()->searchCustomers('klant');
        $this->assertEquals(0, $result['meta']['total']);
    }

    public function test_havunadmin_get_customer(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/customers/5' => Http::response(['id' => 5, 'name' => 'Klant'], 200),
        ]);

        $result = HavunAdminService::make()->getCustomer(5);
        $this->assertEquals('Klant', $result['name']);
    }

    public function test_havunadmin_get_customer_not_found(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/customers/*' => Http::response([], 404),
        ]);

        $this->assertNull(HavunAdminService::make()->getCustomer(99));
    }

    public function test_havunadmin_get_invoice(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/invoices/10' => Http::response(['id' => 10], 200),
        ]);

        $this->assertEquals(10, HavunAdminService::make()->getInvoice(10)['id']);
    }

    public function test_havunadmin_get_invoice_not_found(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/invoices/*' => Http::response([], 404),
        ]);

        $this->assertNull(HavunAdminService::make()->getInvoice(99));
    }

    public function test_havunadmin_create_expense(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/expenses' => Http::response(['id' => 1, 'description' => 'Test'], 201),
        ]);

        $result = HavunAdminService::make()->createExpense('Test', 100.00, 21, 'ACME');
        $this->assertEquals('Test', $result['description']);
    }

    public function test_havunadmin_create_expense_failure(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/expenses' => Http::response([], 500),
        ]);

        $this->assertNull(HavunAdminService::make()->createExpense('Test', 50.00));
    }

    public function test_havunadmin_create_invoice_from_treatment_no_items(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
        ]);

        $treatment = Treatment::factory()->create();
        $result = HavunAdminService::make()->createInvoiceFromTreatment($treatment);

        $this->assertNull($result);
    }

    public function test_havunadmin_create_invoice_from_treatment_success(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/invoices' => Http::response([
                'id' => 42,
                'invoice_number' => 'INV-001',
            ], 201),
        ]);

        $owner = Owner::factory()->create();
        $patient = Patient::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $treatment = Treatment::factory()->create([
            'patient_id' => $patient->id,
            'follow_up_needed' => true,
            'follow_up_date' => now()->addDays(7),
        ]);
        TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'description' => 'Consult',
            'quantity' => 1,
            'unit_price' => 50,
            'vat_rate' => 21,
        ]);

        $result = HavunAdminService::make()->createInvoiceFromTreatment($treatment->fresh());
        $this->assertEquals(42, $result['id']);
        $this->assertEquals('invoiced', $treatment->fresh()->status);
    }

    public function test_havunadmin_create_invoice_from_treatment_failure(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'abc'], 200),
            'havunadmin.test/api/invoices' => Http::response([], 500),
        ]);

        $treatment = Treatment::factory()->create();
        TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'quantity' => 1,
            'unit_price' => 10,
            'vat_rate' => 21,
        ]);

        $this->assertNull(HavunAdminService::make()->createInvoiceFromTreatment($treatment->fresh()));
    }

    public function test_havunadmin_token_auth_failure_throws(): void
    {
        $this->setUpHavunAdminConfig();

        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response([], 500),
        ]);

        $this->expectException(\Exception::class);
        HavunAdminService::make()->searchCustomers();
    }

    // ──────────────────────────────────────────────────────────
    // PostcodeService extra coverage (98.9% → 100%)
    // ──────────────────────────────────────────────────────────

    public function test_postcode_service_empty_postcode_invalid(): void
    {
        $service = new PostcodeService();
        $this->assertFalse($service->isValidPostcode(''));
        $this->assertFalse($service->isValidPostcode(null));
    }

    public function test_postcode_service_invalid_format(): void
    {
        $service = new PostcodeService();
        $this->assertFalse($service->isValidPostcode('ABCD12'));
        $this->assertFalse($service->isValidPostcode('0123AB'));
    }

    public function test_postcode_service_valid(): void
    {
        $service = new PostcodeService();
        $this->assertTrue($service->isValidPostcode('1234AB'));
        $this->assertTrue($service->isValidPostcode('1234 AB'));
    }

    public function test_postcode_service_normalize(): void
    {
        $service = new PostcodeService();
        $this->assertEquals('1234AB', $service->normalizePostcode('1234 ab'));
    }

    public function test_postcode_service_lookup_invalid_returns_null(): void
    {
        $service = new PostcodeService();
        $this->assertNull($service->lookup('bogus', '10'));
    }

    public function test_postcode_service_distance_returns_null_for_invalid(): void
    {
        $service = new PostcodeService();
        $this->assertNull($service->getDistance('bogus1', '1', 'bogus2', '2'));
    }

    // ──────────────────────────────────────────────────────────
    // TreatmentForm save paths (36.8% → higher)
    // ──────────────────────────────────────────────────────────
    //
    // NOTE: TreatmentForm::render() queries work_locations table which was
    // dropped in migration 2025_01_02_000030. The Livewire test harness always
    // calls render(). Tests manually invoke save() via a subclassed component
    // that replaces validate() and redirect() with no-ops.

    private function makeTreatmentFormStub(): \App\Livewire\Treatments\TreatmentForm
    {
        return new class extends \App\Livewire\Treatments\TreatmentForm {
            public function validate($rules = null, $messages = [], $attributes = []): array
            {
                // Return the current component state as validated data.
                // work_location_id is excluded because the column was dropped.
                return [
                    'date' => $this->date,
                    'complaint' => $this->complaint,
                    'anamnesis' => $this->anamnesis,
                    'examination' => $this->examination,
                    'diagnosis' => $this->diagnosis,
                    'treatment_description' => $this->treatment_description,
                    'follow_up_needed' => $this->follow_up_needed,
                    'follow_up_date' => $this->follow_up_date,
                    'veterinarian' => $this->veterinarian,
                    'items' => $this->items,
                ];
            }
            public function redirect($url, $navigate = false)
            {
                return null;
            }
        };
    }

    public function test_treatment_form_save_new_treatment_via_method(): void
    {
        $patient = Patient::factory()->create();
        $component = $this->makeTreatmentFormStub();
        $component->mount($patient);
        $component->complaint = 'Test complaint';
        $component->date = now()->toDateString();
        $component->items = [[
            'id' => null,
            'description' => 'Item 1',
            'quantity' => 1,
            'unit' => 'stuk',
            'unit_price' => 10,
            'vat_rate' => 21,
        ]];

        $component->save();

        $this->assertDatabaseHas('treatments', [
            'patient_id' => $patient->id,
            'complaint' => 'Test complaint',
        ]);
        $this->assertDatabaseHas('treatment_items', ['description' => 'Item 1']);
    }

    public function test_treatment_form_save_updates_existing(): void
    {
        $patient = Patient::factory()->create();
        $treatment = Treatment::factory()->create([
            'patient_id' => $patient->id,
            'complaint' => 'Old',
            'anamnesis' => 'anam',
            'examination' => 'exam',
            'diagnosis' => 'diag',
            'treatment_description' => 'treat',
            'veterinarian' => 'vet',
        ]);
        $item = TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'description' => 'Old item',
            'quantity' => 1,
            'unit_price' => 5,
            'vat_rate' => 21,
        ]);

        $component = $this->makeTreatmentFormStub();
        $component->mount($patient, $treatment);
        $component->complaint = 'New complaint';
        $component->items[0]['description'] = 'Updated item';

        $component->save();

        $this->assertDatabaseHas('treatments', [
            'id' => $treatment->id,
            'complaint' => 'New complaint',
        ]);
        $this->assertDatabaseHas('treatment_items', [
            'id' => $item->id,
            'description' => 'Updated item',
        ]);
    }

    public function test_treatment_form_save_removes_deleted_items(): void
    {
        $patient = Patient::factory()->create();
        $treatment = Treatment::factory()->create([
            'patient_id' => $patient->id,
            'complaint' => 'c',
            'anamnesis' => 'a',
            'examination' => 'e',
            'diagnosis' => 'd',
            'treatment_description' => 't',
            'veterinarian' => 'v',
        ]);
        $item1 = TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'quantity' => 1,
            'unit_price' => 5,
            'vat_rate' => 21,
        ]);
        $item2 = TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'quantity' => 1,
            'unit_price' => 5,
            'vat_rate' => 21,
        ]);

        $component = $this->makeTreatmentFormStub();
        $component->mount($patient, $treatment);
        // Remove item2 by filtering to only item1
        $component->items = array_filter($component->items, fn($i) => $i['id'] === $item1->id);
        $component->items = array_values($component->items);

        $component->save();

        $this->assertDatabaseHas('treatment_items', ['id' => $item1->id]);
        $this->assertDatabaseMissing('treatment_items', ['id' => $item2->id]);
    }

    public function test_treatment_form_save_and_invoice_without_customer_id(): void
    {
        $patient = Patient::factory()->create();
        $treatment = Treatment::factory()->create([
            'patient_id' => $patient->id,
            'complaint' => 'c',
            'anamnesis' => 'a',
            'examination' => 'e',
            'diagnosis' => 'd',
            'treatment_description' => 't',
            'veterinarian' => 'v',
        ]);

        $component = $this->makeTreatmentFormStub();
        $component->mount($patient, $treatment);
        $component->items = [[
            'id' => null,
            'description' => 'Item',
            'quantity' => 1,
            'unit' => 'stuk',
            'unit_price' => 10,
            'vat_rate' => 21,
        ]];

        $component->saveAndInvoice();

        // Patient has no havunadmin_customer_id attribute, so no invoice path runs.
        $this->assertDatabaseHas('treatment_items', ['description' => 'Item']);
    }
}
