<?php

namespace App\Livewire\Patients;

use App\Models\Owner;
use App\Models\Patient;
use Livewire\Component;
use Livewire\WithFileUploads;

class PatientForm extends Component
{
    use WithFileUploads;

    public ?Patient $patient = null;

    // Owner selection
    public ?int $owner_id = null;
    public ?Owner $selectedOwner = null;
    public string $ownerSearch = '';
    public array $ownerResults = [];
    public bool $showNewOwnerForm = false;

    // New owner fields
    public string $new_owner_name = '';
    public string $new_owner_phone = '';
    public string $new_owner_email = '';

    // Patient fields
    public string $name = '';
    public string $species = 'dog';
    public string $breed = '';
    public ?string $date_of_birth = null;
    public string $gender = 'unknown';
    public bool $neutered = false;
    public string $chip_number = '';
    public ?float $weight = null;
    public string $color = '';
    public array $allergies = [];
    public string $notes = '';
    public $photo = null;

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255',
            'breed' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,unknown',
            'neutered' => 'boolean',
            'chip_number' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric|min:0|max:999',
            'color' => 'nullable|string|max:255',
            'allergies' => 'array',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ];

        if ($this->showNewOwnerForm) {
            $rules['new_owner_name'] = 'required|string|max:255';
            $rules['new_owner_phone'] = 'nullable|string|max:20';
            $rules['new_owner_email'] = 'nullable|email|max:255';
        } else {
            $rules['owner_id'] = 'required|exists:owners,id';
        }

        return $rules;
    }

    protected $messages = [
        'owner_id.required' => 'Selecteer een eigenaar of maak een nieuwe aan.',
    ];

    public function mount(?Patient $patient = null): void
    {
        if ($patient?->exists) {
            $this->patient = $patient;
            $this->owner_id = $patient->owner_id;
            $this->selectedOwner = $patient->owner;
            $this->name = $patient->name;
            $this->species = $patient->species;
            $this->breed = $patient->breed ?? '';
            $this->date_of_birth = $patient->date_of_birth?->format('Y-m-d');
            $this->gender = $patient->gender;
            $this->neutered = $patient->neutered;
            $this->chip_number = $patient->chip_number ?? '';
            $this->weight = $patient->weight;
            $this->color = $patient->color ?? '';
            $this->allergies = $patient->allergies ?? [];
            $this->notes = $patient->notes ?? '';
        }
    }

    public function updatedOwnerSearch(): void
    {
        if (strlen($this->ownerSearch) < 2) {
            $this->ownerResults = [];
            return;
        }

        $this->ownerResults = Owner::search($this->ownerSearch)
            ->active()
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectOwner(int $ownerId): void
    {
        $this->owner_id = $ownerId;
        $this->selectedOwner = Owner::find($ownerId);
        $this->ownerSearch = '';
        $this->ownerResults = [];
        $this->showNewOwnerForm = false;
    }

    public function clearOwner(): void
    {
        $this->owner_id = null;
        $this->selectedOwner = null;
    }

    public function toggleNewOwnerForm(): void
    {
        $this->showNewOwnerForm = !$this->showNewOwnerForm;
        if ($this->showNewOwnerForm) {
            $this->owner_id = null;
            $this->selectedOwner = null;
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        // Create new owner if needed
        if ($this->showNewOwnerForm) {
            $owner = Owner::create([
                'name' => $this->new_owner_name,
                'phone' => $this->new_owner_phone,
                'email' => $this->new_owner_email,
            ]);
            $this->owner_id = $owner->id;
        }

        $patientData = [
            'owner_id' => $this->owner_id,
            'name' => $data['name'],
            'species' => $data['species'],
            'breed' => $data['breed'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'neutered' => $data['neutered'],
            'chip_number' => $data['chip_number'],
            'weight' => $data['weight'],
            'color' => $data['color'],
            'allergies' => $this->allergies,
            'notes' => $data['notes'],
        ];

        if ($this->photo) {
            $patientData['photo_path'] = $this->photo->store('patients', 'public');
        }

        if ($this->patient?->exists) {
            $this->patient->update($patientData);
            session()->flash('success', 'Patiënt bijgewerkt.');
        } else {
            $patient = Patient::create($patientData);
            session()->flash('success', 'Patiënt aangemaakt.');
            $this->redirect(route('patients.show', $patient));
            return;
        }

        $this->redirect(route('patients.show', $this->patient));
    }

    public function render()
    {
        return view('livewire.patients.patient-form');
    }
}
