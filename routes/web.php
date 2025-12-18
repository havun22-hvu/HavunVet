<?php

use App\Livewire\Appointments\AppointmentIndex;
use App\Livewire\Dashboard;
use App\Livewire\Owners\OwnerIndex;
use App\Livewire\Owners\OwnerShow;
use App\Livewire\Patients\PatientForm;
use App\Livewire\Patients\PatientIndex;
use App\Livewire\Patients\PatientShow;
use App\Livewire\Treatments\TreatmentForm;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', Dashboard::class)->name('dashboard');

// Owners (Eigenaren)
Route::get('/owners', OwnerIndex::class)->name('owners.index');
Route::get('/owners/{owner}', OwnerShow::class)->name('owners.show');

// Patients (Patiënten)
Route::get('/patients', PatientIndex::class)->name('patients.index');
Route::get('/patients/create', PatientForm::class)->name('patients.create');
Route::get('/patients/{patient}', PatientShow::class)->name('patients.show');
Route::get('/patients/{patient}/edit', PatientForm::class)->name('patients.edit');

// Treatments (Behandelingen)
Route::get('/patients/{patient}/treatments/create', TreatmentForm::class)->name('treatments.create');
Route::get('/patients/{patient}/treatments/{treatment}/edit', TreatmentForm::class)->name('treatments.edit');

// Appointments (Afspraken)
Route::get('/appointments', AppointmentIndex::class)->name('appointments.index');
