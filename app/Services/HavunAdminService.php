<?php

namespace App\Services;

use App\Models\Treatment;
use App\Models\WorkSession;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HavunAdminService
{
    private ?string $token = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiSecret,
        private readonly string $defaultCategory = 'Dierenarts'
    ) {}

    /**
     * Create instance from config
     */
    public static function make(): self
    {
        return new self(
            baseUrl: config('services.havunadmin.url'),
            apiSecret: config('services.havunadmin.secret'),
            defaultCategory: config('services.havunadmin.category', 'Dierenarts')
        );
    }

    /**
     * Get authenticated HTTP client
     */
    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->getToken())
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Get or refresh API token
     */
    private function getToken(): string
    {
        if ($this->token) {
            return $this->token;
        }

        $this->token = Cache::remember('havunadmin_token', 3500, function () {
            $response = Http::baseUrl($this->baseUrl)
                ->post('/auth/token', [
                    'service' => 'havunvet',
                    'secret' => $this->apiSecret,
                ]);

            if ($response->failed()) {
                Log::error('HavunAdmin: Failed to get token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Failed to authenticate with HavunAdmin');
            }

            return $response->json('token');
        });

        return $this->token;
    }

    /**
     * Search customers
     */
    public function searchCustomers(string $search = '', int $page = 1, int $perPage = 50): array
    {
        $response = $this->client()->get('/customers', [
            'search' => $search,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        if ($response->failed()) {
            Log::error('HavunAdmin: Failed to fetch customers', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return ['data' => [], 'meta' => ['total' => 0]];
        }

        return $response->json();
    }

    /**
     * Get single customer
     */
    public function getCustomer(int $customerId): ?array
    {
        $response = $this->client()->get("/customers/{$customerId}");

        if ($response->failed()) {
            Log::error('HavunAdmin: Failed to fetch customer', [
                'customer_id' => $customerId,
                'status' => $response->status(),
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Create invoice from treatment
     */
    public function createInvoiceFromTreatment(Treatment $treatment): ?array
    {
        $items = $treatment->items->map(fn ($item) => [
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'vat_rate' => $item->vat_rate,
        ])->toArray();

        if (empty($items)) {
            Log::warning('HavunAdmin: No items to invoice', [
                'treatment_id' => $treatment->id,
            ]);
            return null;
        }

        $response = $this->client()->post('/invoices', [
            'customer_id' => $treatment->patient->havunadmin_customer_id,
            'source' => 'havunvet',
            'source_reference' => "treatment_{$treatment->id}",
            'category' => $this->defaultCategory,
            'date' => $treatment->date->toDateString(),
            'due_days' => 14,
            'items' => $items,
            'notes' => $treatment->follow_up_needed
                ? "Follow-up gepland: {$treatment->follow_up_date?->format('d-m-Y')}"
                : null,
        ]);

        if ($response->failed()) {
            Log::error('HavunAdmin: Failed to create invoice', [
                'treatment_id' => $treatment->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $invoice = $response->json();

        // Update treatment with invoice ID
        $treatment->update([
            'havunadmin_invoice_id' => $invoice['id'],
            'status' => 'invoiced',
        ]);

        return $invoice;
    }

    /**
     * Create invoice for work sessions (kliniek-inhuur)
     */
    public function createInvoiceFromWorkSessions(array $sessionIds, int $clinicCustomerId): ?array
    {
        $sessions = WorkSession::with('workLocation')
            ->whereIn('id', $sessionIds)
            ->get();

        if ($sessions->isEmpty()) {
            return null;
        }

        $location = $sessions->first()->workLocation;
        $totalHours = $sessions->sum('worked_hours');
        $hourlyRate = $location->hourly_rate ?? 0;

        $items = [[
            'description' => "Inhuur dierenarts - {$location->name} ({$sessions->count()} dagen, {$totalHours} uur)",
            'quantity' => $totalHours,
            'unit_price' => $hourlyRate,
            'vat_rate' => 21,
        ]];

        // Group sessions by month for description
        $period = $sessions->min('date')->format('d-m-Y') . ' t/m ' . $sessions->max('date')->format('d-m-Y');

        $response = $this->client()->post('/invoices', [
            'customer_id' => $clinicCustomerId,
            'source' => 'havunvet',
            'source_reference' => 'work_sessions_' . implode('_', $sessionIds),
            'category' => $this->defaultCategory,
            'date' => now()->toDateString(),
            'due_days' => 30,
            'items' => $items,
            'notes' => "Periode: {$period}",
        ]);

        if ($response->failed()) {
            Log::error('HavunAdmin: Failed to create work session invoice', [
                'session_ids' => $sessionIds,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $invoice = $response->json();

        // Update sessions
        WorkSession::whereIn('id', $sessionIds)->update([
            'havunadmin_invoice_id' => $invoice['id'],
            'status' => 'invoiced',
        ]);

        return $invoice;
    }

    /**
     * Register expense (e.g., medication purchase)
     */
    public function createExpense(
        string $description,
        float $amount,
        float $vatRate = 21,
        ?string $supplier = null,
        ?string $sourceReference = null
    ): ?array {
        $response = $this->client()->post('/expenses', [
            'source' => 'havunvet',
            'source_reference' => $sourceReference,
            'category' => $this->defaultCategory,
            'date' => now()->toDateString(),
            'description' => $description,
            'amount' => $amount,
            'vat_rate' => $vatRate,
            'supplier' => $supplier,
        ]);

        if ($response->failed()) {
            Log::error('HavunAdmin: Failed to create expense', [
                'description' => $description,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    /**
     * Get invoice status
     */
    public function getInvoice(int $invoiceId): ?array
    {
        $response = $this->client()->get("/invoices/{$invoiceId}");

        if ($response->failed()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Check if connection works
     */
    public function ping(): bool
    {
        try {
            $this->getToken();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
