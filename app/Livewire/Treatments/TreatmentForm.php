<?php

namespace App\Livewire\Treatments;

use App\Models\Patient;
use App\Models\Treatment;
use App\Models\TreatmentItem;
use App\Models\WorkLocation;
use App\Services\HavunAdminService;
use Livewire\Component;

class TreatmentForm extends Component
{
    public Patient $patient;
    public ?Treatment $treatment = null;

    public ?int $work_location_id = null;
    public string $date = '';
    public string $complaint = '';
    public string $anamnesis = '';
    public string $examination = '';
    public string $diagnosis = '';
    public string $treatment_description = '';
    public bool $follow_up_needed = false;
    public ?string $follow_up_date = null;
    public string $veterinarian = '';

    public array $items = [];

    protected function rules(): array
    {
        return [
            'work_location_id' => 'nullable|exists:work_locations,id',
            'date' => 'required|date',
            'complaint' => 'nullable|string',
            'anamnesis' => 'nullable|string',
            'examination' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_description' => 'nullable|string',
            'follow_up_needed' => 'boolean',
            'follow_up_date' => 'nullable|date|after_or_equal:date',
            'veterinarian' => 'nullable|string|max:255',
            'items' => 'array',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.vat_rate' => 'required|numeric|in:0,9,21',
        ];
    }

    public function mount(Patient $patient, ?Treatment $treatment = null): void
    {
        $this->patient = $patient;
        $this->date = now()->toDateString();

        if ($treatment?->exists) {
            $this->treatment = $treatment;
            $this->fill($treatment->toArray());
            $this->date = $treatment->date->toDateString();
            $this->follow_up_date = $treatment->follow_up_date?->toDateString();
            $this->items = $treatment->items->map(fn ($item) => [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'vat_rate' => $item->vat_rate,
            ])->toArray();
        }
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'description' => '',
            'quantity' => 1,
            'unit' => 'stuk',
            'unit_price' => 0,
            'vat_rate' => 21,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(): void
    {
        $data = $this->validate();
        unset($data['items']);

        if ($this->treatment?->exists) {
            $this->treatment->update($data);
            $treatment = $this->treatment;
        } else {
            $treatment = $this->patient->treatments()->create($data);
        }

        // Sync items
        $existingIds = collect($this->items)->pluck('id')->filter();
        $treatment->items()->whereNotIn('id', $existingIds)->delete();

        foreach ($this->items as $item) {
            if ($item['id']) {
                TreatmentItem::find($item['id'])->update($item);
            } else {
                $treatment->items()->create($item);
            }
        }

        session()->flash('success', 'Behandeling opgeslagen.');
        $this->redirect(route('patients.show', $this->patient));
    }

    public function saveAndInvoice(): void
    {
        $this->save();

        if ($this->treatment && $this->patient->havunadmin_customer_id) {
            try {
                $service = HavunAdminService::make();
                $invoice = $service->createInvoiceFromTreatment($this->treatment->fresh());

                if ($invoice) {
                    session()->flash('success', "Behandeling opgeslagen en factuur {$invoice['invoice_number']} aangemaakt.");
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Kon geen factuur aanmaken: ' . $e->getMessage());
            }
        }

        $this->redirect(route('patients.show', $this->patient));
    }

    public function render()
    {
        return view('livewire.treatments.treatment-form', [
            'workLocations' => WorkLocation::active()->orderBy('name')->get(),
        ]);
    }
}
