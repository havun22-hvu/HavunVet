<?php

namespace App\Livewire\Owners;

use App\Models\Owner;
use App\Models\Patient;
use Livewire\Component;

class OwnerShow extends Component
{
    public Owner $owner;
    public bool $showPatientForm = false;

    // Patient form fields
    public string $patient_name = '';
    public string $species = 'dog';
    public string $breed = '';
    public ?string $date_of_birth = null;
    public string $gender = 'unknown';
    public bool $neutered = false;
    public string $chip_number = '';
    public ?float $weight = null;
    public string $color = '';
    public string $coat_type = '';
    public string $patient_notes = '';

    protected function rules(): array
    {
        return [
            'patient_name' => 'required|string|max:255',
            'species' => 'required|string|max:50',
            'breed' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,unknown',
            'neutered' => 'boolean',
            'chip_number' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0|max:500',
            'color' => 'nullable|string|max:100',
            'coat_type' => 'nullable|string|max:100',
            'patient_notes' => 'nullable|string',
        ];
    }

    public function mount(Owner $owner): void
    {
        $this->owner = $owner;
    }

    public function createPatient(): void
    {
        $this->reset(['patient_name', 'species', 'breed', 'date_of_birth', 'gender', 'neutered', 'chip_number', 'weight', 'color', 'coat_type', 'patient_notes']);
        $this->species = 'dog';
        $this->gender = 'unknown';
        $this->showPatientForm = true;
    }

    public function savePatient(): void
    {
        $data = $this->validate();

        $this->owner->patients()->create([
            'name' => $data['patient_name'],
            'species' => $data['species'],
            'breed' => $data['breed'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'neutered' => $data['neutered'],
            'chip_number' => $data['chip_number'],
            'weight' => $data['weight'],
            'color' => $data['color'],
            'coat_type' => $data['coat_type'],
            'notes' => $data['patient_notes'],
        ]);

        session()->flash('success', 'Patiënt toegevoegd.');
        $this->showPatientForm = false;
        $this->owner->refresh();
    }

    public function cancelPatient(): void
    {
        $this->showPatientForm = false;
    }

    public function render()
    {
        return view('livewire.owners.owner-show', [
            'patients' => $this->owner->patients()->orderBy('name')->get(),
        ]);
    }
}
