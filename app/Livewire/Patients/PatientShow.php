<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use Livewire\Component;

class PatientShow extends Component
{
    public Patient $patient;

    public function mount(Patient $patient): void
    {
        $this->patient = $patient->load([
            'treatments' => fn ($q) => $q->latest('date')->limit(10),
            'vaccinations' => fn ($q) => $q->latest('administered_at'),
            'prescriptions' => fn ($q) => $q->latest()->limit(10),
            'appointments' => fn ($q) => $q->upcoming()->limit(5),
        ]);
    }

    public function markDeceased(): void
    {
        $this->patient->update(['deceased_at' => now()]);
        session()->flash('message', 'Patiënt gemarkeerd als overleden.');
    }

    public function render()
    {
        return view('livewire.patients.patient-show');
    }
}
