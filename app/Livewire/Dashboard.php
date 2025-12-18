<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Medication;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\Vaccination;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard', [
            'todayAppointments' => Appointment::with('patient')
                ->today()
                ->orderBy('scheduled_at')
                ->get(),
            'upcomingVaccinations' => Vaccination::with('patient')
                ->dueSoon()
                ->orderBy('next_due_date')
                ->limit(10)
                ->get(),
            'followUps' => Treatment::with('patient')
                ->needsFollowUp()
                ->orderBy('follow_up_date')
                ->limit(10)
                ->get(),
            'lowStockMedications' => Medication::lowStock()
                ->orderBy('stock_quantity')
                ->limit(10)
                ->get(),
            'stats' => [
                'patients' => Patient::alive()->count(),
                'treatments_this_month' => Treatment::whereMonth('date', now()->month)
                    ->whereYear('date', now()->year)
                    ->count(),
                'appointments_today' => Appointment::today()->count(),
            ],
        ]);
    }
}
