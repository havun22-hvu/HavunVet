<?php

namespace App\Livewire\Patients;

use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;

class PatientIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $species = '';
    public bool $showDeceased = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'species' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSpecies(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Patient::query()
            ->with('owner')
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->species, fn ($q) => $q->species($this->species))
            ->when(!$this->showDeceased, fn ($q) => $q->alive())
            ->orderBy('name');

        return view('livewire.patients.patient-index', [
            'patients' => $query->paginate(20),
            'speciesList' => Patient::select('species')
                ->distinct()
                ->orderBy('species')
                ->pluck('species'),
        ]);
    }
}
