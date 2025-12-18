<div>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Agenda</h1>
    </div>

    <!-- Date Navigation -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <button wire:click="previousDay" class="p-2 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                </svg>
            </button>
            <div class="text-center">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $currentDate->translatedFormat('l') }}
                </h2>
                <p class="text-gray-500">{{ $currentDate->format('d F Y') }}</p>
            </div>
            <button wire:click="nextDay" class="p-2 text-gray-400 hover:text-gray-600">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div class="mt-3 text-center">
            <button wire:click="today" class="text-sm text-emerald-600 hover:text-emerald-800">
                Vandaag
            </button>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        @if($appointments->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($appointments as $appointment)
                    <li class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-20 text-center">
                                    <span class="text-lg font-semibold text-gray-900">
                                        {{ $appointment->scheduled_at->format('H:i') }}
                                    </span>
                                    <p class="text-xs text-gray-500">{{ $appointment->duration_minutes }} min</p>
                                </div>
                                <div class="ml-4 border-l border-gray-200 pl-4">
                                    @if($appointment->patient)
                                        <a href="{{ route('patients.show', $appointment->patient) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-emerald-600">
                                            {{ $appointment->patient->name }}
                                        </a>
                                        <p class="text-sm text-gray-500">
                                            {{ $appointment->patient->owner_name }}
                                            @if($appointment->contact_phone)
                                                - {{ $appointment->contact_phone }}
                                            @endif
                                        </p>
                                    @else
                                        <p class="text-sm font-medium text-gray-900">Geen patiënt</p>
                                    @endif
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                            {{ $appointment->type_label }}
                                        </span>
                                        @if($appointment->reason)
                                            <span class="text-xs text-gray-500">{{ $appointment->reason }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <select wire:change="updateStatus({{ $appointment->id }}, $event.target.value)"
                                        class="rounded-md border-0 py-1.5 text-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600
                                        {{ $appointment->status === 'completed' ? 'bg-green-50 text-green-700' : '' }}
                                        {{ $appointment->status === 'cancelled' || $appointment->status === 'no_show' ? 'bg-red-50 text-red-700' : '' }}
                                        {{ $appointment->status === 'in_progress' ? 'bg-yellow-50 text-yellow-700' : '' }}">
                                    <option value="scheduled" {{ $appointment->status === 'scheduled' ? 'selected' : '' }}>Gepland</option>
                                    <option value="confirmed" {{ $appointment->status === 'confirmed' ? 'selected' : '' }}>Bevestigd</option>
                                    <option value="arrived" {{ $appointment->status === 'arrived' ? 'selected' : '' }}>Aangekomen</option>
                                    <option value="in_progress" {{ $appointment->status === 'in_progress' ? 'selected' : '' }}>Bezig</option>
                                    <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>Afgerond</option>
                                    <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Geannuleerd</option>
                                    <option value="no_show" {{ $appointment->status === 'no_show' ? 'selected' : '' }}>Niet verschenen</option>
                                </select>
                                @if($appointment->patient)
                                    <a href="{{ route('treatments.create', $appointment->patient) }}"
                                       class="text-emerald-600 hover:text-emerald-800 text-sm">
                                        Behandeling
                                    </a>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Geen afspraken</h3>
                <p class="mt-1 text-sm text-gray-500">Er zijn geen afspraken voor deze dag.</p>
            </div>
        @endif
    </div>
</div>
