<?php

namespace App\Livewire\Owners;

use App\Models\Owner;
use Livewire\Component;
use Livewire\WithPagination;

class OwnerIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = false;
    public ?Owner $editing = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $phone2 = '';
    public string $postal_code = '';
    public string $house_number = '';
    public string $address = '';
    public string $city = '';
    public string $ubn = '';
    public string $notes = '';
    public bool $active = true;

    public string $lookupError = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'house_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'ubn' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'active' => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editing', 'name', 'email', 'phone', 'phone2', 'postal_code', 'house_number', 'address', 'city', 'ubn', 'notes', 'active', 'lookupError']);
        $this->active = true;
        $this->showForm = true;
    }

    public function edit(Owner $owner): void
    {
        $this->editing = $owner;
        $this->name = $owner->name;
        $this->email = $owner->email ?? '';
        $this->phone = $owner->phone ?? '';
        $this->phone2 = $owner->phone2 ?? '';
        $this->postal_code = $owner->postal_code ?? '';
        $this->house_number = $owner->house_number ?? '';
        $this->address = $owner->address ?? '';
        $this->city = $owner->city ?? '';
        $this->ubn = $owner->ubn ?? '';
        $this->notes = $owner->notes ?? '';
        $this->active = $owner->active;
        $this->lookupError = '';
        $this->showForm = true;
    }

    public function lookupAddress(): void
    {
        $this->lookupError = '';

        $postcode = strtoupper(str_replace(' ', '', $this->postal_code));
        if (!preg_match('/^[1-9][0-9]{3}[A-Z]{2}$/', $postcode)) {
            $this->lookupError = 'Ongeldige postcode (formaat: 1234AB)';
            return;
        }

        if (empty($this->house_number)) {
            $this->lookupError = 'Vul een huisnummer in';
            return;
        }

        $houseNumber = preg_replace('/[^0-9]/', '', $this->house_number);

        try {
            // Use PDOK Locatieserver (Dutch government, free, reliable)
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get("https://api.pdok.nl/bzk/locatieserver/search/v3_1/free", [
                    'q' => "postcode:{$postcode} AND huisnummer:{$houseNumber}",
                    'fq' => 'type:adres',
                    'rows' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $docs = $data['response']['docs'] ?? [];

                if (count($docs) > 0) {
                    $doc = $docs[0];
                    $this->address = $doc['straatnaam'] ?? '';
                    $this->city = $doc['woonplaatsnaam'] ?? '';
                    $this->postal_code = substr($postcode, 0, 4) . ' ' . substr($postcode, 4, 2);
                } else {
                    $this->lookupError = 'Adres niet gevonden';
                }
            } else {
                $this->lookupError = 'Adres niet gevonden';
            }
        } catch (\Exception $e) {
            $this->lookupError = 'Adres opzoeken mislukt';
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editing) {
            $this->editing->update($data);
            session()->flash('success', 'Eigenaar bijgewerkt.');
        } else {
            Owner::create($data);
            session()->flash('success', 'Eigenaar toegevoegd.');
        }

        $this->showForm = false;
        $this->reset(['editing']);
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->reset(['editing', 'lookupError']);
    }

    public function render()
    {
        $owners = Owner::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->withCount('patients')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.owners.owner-index', [
            'owners' => $owners,
        ]);
    }
}
