<?php

namespace App\Livewire\Appointments;

use App\Models\Appointment;
use Carbon\Carbon;
use Livewire\Component;

class AppointmentIndex extends Component
{
    public string $view = 'day'; // day, week
    public string $date;
    public ?string $filterStatus = null;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function previousDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $this->date = Carbon::parse($this->date)->addDay()->toDateString();
    }

    public function today(): void
    {
        $this->date = now()->toDateString();
    }

    public function updateStatus(int $appointmentId, string $status): void
    {
        Appointment::find($appointmentId)?->update(['status' => $status]);
    }

    public function render()
    {
        $date = Carbon::parse($this->date);

        $query = Appointment::with(['patient', 'workLocation'])
            ->whereDate('scheduled_at', $date)
            ->when($this->filterStatus, fn ($q) => $q->status($this->filterStatus))
            ->orderBy('scheduled_at');

        return view('livewire.appointments.appointment-index', [
            'appointments' => $query->get(),
            'currentDate' => $date,
        ]);
    }
}
