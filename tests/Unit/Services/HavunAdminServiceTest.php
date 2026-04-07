<?php

namespace Tests\Unit\Services;

use App\Models\Patient;
use App\Models\Treatment;
use App\Models\TreatmentItem;
use App\Services\HavunAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HavunAdminServiceTest extends TestCase
{
    use RefreshDatabase;

    private HavunAdminService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new HavunAdminService(
            baseUrl: 'https://havunadmin.test/api',
            apiSecret: 'test-secret',
            defaultCategory: 'Dierenarts'
        );
    }

    private function fakeAuth(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
        ]);
    }

    // --- make ---

    public function test_make_creates_instance_from_config(): void
    {
        config([
            'services.havunadmin.url' => 'https://test.nl',
            'services.havunadmin.secret' => 'secret',
            'services.havunadmin.category' => 'Test',
        ]);

        $service = HavunAdminService::make();
        $this->assertInstanceOf(HavunAdminService::class, $service);
    }

    // --- ping ---

    public function test_ping_returns_true_on_success(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
        ]);

        $this->assertTrue($this->service->ping());
    }

    public function test_ping_returns_false_on_failure(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response([], 401),
        ]);

        $this->assertFalse($this->service->ping());
    }

    // --- getToken ---

    public function test_token_is_cached(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'cached-token']),
            'havunadmin.test/api/customers*' => Http::response(['data' => [], 'meta' => ['total' => 0]]),
        ]);

        // First call: 1 token + 1 customer = 2 requests
        // Second call: token cached on service instance + 1 customer = 1 request
        // But Cache::remember also caches, so total = 1 token + 2 customers = 3
        $this->service->searchCustomers();
        $this->service->searchCustomers();

        Http::assertSentCount(3); // 1 token + 2 customer requests
    }

    // --- searchCustomers ---

    public function test_search_customers_returns_data(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/customers*' => Http::response([
                'data' => [['id' => 1, 'name' => 'Jan']],
                'meta' => ['total' => 1],
            ]),
        ]);

        $result = $this->service->searchCustomers('Jan');

        $this->assertCount(1, $result['data']);
        $this->assertEquals('Jan', $result['data'][0]['name']);
    }

    public function test_search_customers_returns_empty_on_failure(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/customers*' => Http::response([], 500),
        ]);

        $result = $this->service->searchCustomers();

        $this->assertEmpty($result['data']);
        $this->assertEquals(0, $result['meta']['total']);
    }

    // --- getCustomer ---

    public function test_get_customer_returns_data(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/customers/1' => Http::response(['id' => 1, 'name' => 'Jan']),
        ]);

        $result = $this->service->getCustomer(1);

        $this->assertNotNull($result);
        $this->assertEquals('Jan', $result['name']);
    }

    public function test_get_customer_returns_null_on_failure(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/customers/999' => Http::response([], 404),
        ]);

        $result = $this->service->getCustomer(999);
        $this->assertNull($result);
    }

    // --- createInvoiceFromTreatment ---

    public function test_create_invoice_from_treatment(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/invoices' => Http::response([
                'id' => 42,
                'invoice_number' => 'INV-001',
            ]),
        ]);

        $patient = Patient::factory()->create();
        $treatment = Treatment::factory()->create([
            'patient_id' => $patient->id,
            'date' => now(),
        ]);
        TreatmentItem::factory()->create([
            'treatment_id' => $treatment->id,
            'description' => 'Consult',
            'quantity' => 1,
            'unit_price' => 35.00,
            'vat_rate' => 21,
        ]);

        $result = $this->service->createInvoiceFromTreatment($treatment->fresh());

        $this->assertNotNull($result);
        $this->assertEquals(42, $result['id']);

        // Check treatment was updated
        $treatment->refresh();
        $this->assertEquals(42, $treatment->havunadmin_invoice_id);
        $this->assertEquals('invoiced', $treatment->status);
    }

    public function test_create_invoice_returns_null_without_items(): void
    {
        $this->fakeAuth();

        $treatment = Treatment::factory()->create();

        $result = $this->service->createInvoiceFromTreatment($treatment->fresh());
        $this->assertNull($result);
    }

    public function test_create_invoice_returns_null_on_api_failure(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/invoices' => Http::response([], 500),
        ]);

        $treatment = Treatment::factory()->create();
        TreatmentItem::factory()->create(['treatment_id' => $treatment->id]);

        $result = $this->service->createInvoiceFromTreatment($treatment->fresh());
        $this->assertNull($result);
    }

    public function test_create_invoice_includes_follow_up_notes(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/invoices' => Http::response(['id' => 1]),
        ]);

        $treatment = Treatment::factory()->create([
            'follow_up_needed' => true,
            'follow_up_date' => '2026-04-14',
        ]);
        TreatmentItem::factory()->create(['treatment_id' => $treatment->id]);

        $this->service->createInvoiceFromTreatment($treatment->fresh());

        Http::assertSent(function ($request) {
            return str_contains($request->body(), 'Follow-up gepland');
        });
    }

    // --- createExpense ---

    public function test_create_expense(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/expenses' => Http::response(['id' => 1, 'amount' => 150.00]),
        ]);

        $result = $this->service->createExpense(
            description: 'Medicijnen inkoop',
            amount: 150.00,
            vatRate: 21,
            supplier: 'Medicinaal BV',
            sourceReference: 'med_001'
        );

        $this->assertNotNull($result);
        $this->assertEquals(150.00, $result['amount']);
    }

    public function test_create_expense_returns_null_on_failure(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/expenses' => Http::response([], 500),
        ]);

        $result = $this->service->createExpense('Test', 100.00);
        $this->assertNull($result);
    }

    // --- getInvoice ---

    public function test_get_invoice(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/invoices/1' => Http::response(['id' => 1, 'status' => 'paid']),
        ]);

        $result = $this->service->getInvoice(1);

        $this->assertNotNull($result);
        $this->assertEquals('paid', $result['status']);
    }

    public function test_get_invoice_returns_null_on_failure(): void
    {
        Http::fake([
            'havunadmin.test/api/auth/token' => Http::response(['token' => 'test-token']),
            'havunadmin.test/api/invoices/999' => Http::response([], 404),
        ]);

        $result = $this->service->getInvoice(999);
        $this->assertNull($result);
    }
}
