<div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="shrink-0 bg-emerald-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Actieve patiënten</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['patients'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Behandelingen deze maand</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['treatments_this_month'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Afspraken vandaag</dt>
                            <dd class="text-lg font-semibold text-gray-900">{{ $stats['appointments_today'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Appointments -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Afspraken vandaag</h3>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse($todayAppointments as $appointment)
                    <li class="px-4 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $appointment->scheduled_at->format('H:i') }} -
                                    {{ $appointment->patient?->name ?? 'Onbekend' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $appointment->type_label }} - {{ $appointment->patient?->owner_name }}
                                </p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $appointment->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $appointment->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $appointment->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $appointment->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $appointment->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $appointment->status_label }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-gray-500">
                        Geen afspraken vandaag
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Upcoming Vaccinations -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Vaccinatie herinneringen</h3>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse($upcomingVaccinations as $vaccination)
                    <li class="px-4 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $vaccination->patient->name }} - {{ $vaccination->vaccine_name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $vaccination->patient->owner_name }}
                                </p>
                            </div>
                            <span class="text-sm {{ $vaccination->is_due ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                {{ $vaccination->next_due_date->format('d-m-Y') }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-gray-500">
                        Geen vaccinaties gepland
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Follow-ups -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Follow-up behandelingen</h3>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse($followUps as $treatment)
                    <li class="px-4 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $treatment->patient->name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ Str::limit($treatment->diagnosis, 50) }}
                                </p>
                            </div>
                            <span class="text-sm text-gray-500">
                                {{ $treatment->follow_up_date->format('d-m-Y') }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-gray-500">
                        Geen follow-ups gepland
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Low Stock Medications -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Lage voorraad medicijnen</h3>
            </div>
            <ul class="divide-y divide-gray-200">
                @forelse($lowStockMedications as $medication)
                    <li class="px-4 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $medication->name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $medication->strength }} {{ $medication->dosage_form }}
                                </p>
                            </div>
                            <span class="text-sm text-red-600 font-medium">
                                {{ $medication->stock_quantity }} {{ $medication->stock_unit }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-gray-500">
                        Alle medicijnen op voorraad
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
