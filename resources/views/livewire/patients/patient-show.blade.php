<div>
    <!-- Header -->
    <div class="mb-6 sm:flex sm:items-center sm:justify-between">
        <div class="flex items-center">
            @if($patient->photo_path)
                <img class="h-16 w-16 rounded-full object-cover mr-4" src="{{ Storage::url($patient->photo_path) }}" alt="">
            @else
                <div class="h-16 w-16 rounded-full bg-emerald-100 flex items-center justify-center mr-4">
                    <span class="text-emerald-700 font-bold text-2xl">{{ substr($patient->name, 0, 1) }}</span>
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ $patient->name }}
                    @if($patient->deceased_at)
                        <span class="text-gray-500 text-lg">(overleden {{ $patient->deceased_at->format('d-m-Y') }})</span>
                    @endif
                </h1>
                <p class="text-gray-500">{{ ucfirst($patient->species) }} {{ $patient->breed ? '- ' . $patient->breed : '' }}</p>
            </div>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-2">
            <a href="{{ route('patients.edit', $patient) }}"
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Bewerken
            </a>
            <a href="{{ route('treatments.create', $patient) }}"
               class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                Nieuwe behandeling
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Patient Info -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Details Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Gegevens</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Leeftijd</dt>
                        <dd class="text-sm text-gray-900">{{ $patient->age ?? 'Onbekend' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Geslacht</dt>
                        <dd class="text-sm text-gray-900">{{ $patient->gender_label }}</dd>
                    </div>
                    @if($patient->weight)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Gewicht</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->weight }} kg</dd>
                        </div>
                    @endif
                    @if($patient->chip_number)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Chipnummer</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $patient->chip_number }}</dd>
                        </div>
                    @endif
                    @if($patient->color)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Kleur</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->color }}</dd>
                        </div>
                    @endif
                    @if($patient->allergies && count($patient->allergies) > 0)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Allergieën</dt>
                            <dd class="text-sm text-red-600">{{ implode(', ', $patient->allergies) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Owner Card -->
            @if($patient->owner)
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Eigenaar</h2>
                        <a href="{{ route('owners.show', $patient->owner) }}" class="text-sm text-emerald-600 hover:text-emerald-800">
                            Bekijk
                        </a>
                    </div>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Naam</dt>
                            <dd class="text-sm text-gray-900">{{ $patient->owner->name }}</dd>
                        </div>
                        @if($patient->owner->phone)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Telefoon</dt>
                                <dd class="text-sm text-gray-900">
                                    <a href="tel:{{ $patient->owner->phone }}" class="text-emerald-600 hover:text-emerald-800">
                                        {{ $patient->owner->phone }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        @if($patient->owner->email)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="text-sm text-gray-900">
                                    <a href="mailto:{{ $patient->owner->email }}" class="text-emerald-600 hover:text-emerald-800">
                                        {{ $patient->owner->email }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        @if($patient->owner->full_address)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Adres</dt>
                                <dd class="text-sm text-gray-900">{{ $patient->owner->full_address }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif
        </div>

        <!-- Treatments & History -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Upcoming Appointments -->
            @if($patient->appointments->count() > 0)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Komende afspraken</h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        @foreach($patient->appointments as $appointment)
                            <li class="px-4 py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $appointment->scheduled_at->format('d-m-Y H:i') }}
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $appointment->type_label }}</p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-{{ $appointment->status_color }}-100 text-{{ $appointment->status_color }}-800">
                                        {{ $appointment->status_label }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Treatments -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Behandelhistorie</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    @forelse($patient->treatments as $treatment)
                        <li class="px-4 py-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $treatment->date->format('d-m-Y') }}
                                        @if($treatment->complaint)
                                            - {{ $treatment->complaint }}
                                        @endif
                                    </p>
                                    @if($treatment->diagnosis)
                                        <p class="text-sm text-gray-600 mt-1">
                                            <span class="font-medium">Diagnose:</span> {{ $treatment->diagnosis }}
                                        </p>
                                    @endif
                                    @if($treatment->treatment_description)
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ Str::limit($treatment->treatment_description, 150) }}
                                        </p>
                                    @endif
                                </div>
                                <div class="ml-4 flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $treatment->status === 'invoiced' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $treatment->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $treatment->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $treatment->status_label }}
                                    </span>
                                    <a href="{{ route('treatments.edit', [$patient, $treatment]) }}" class="text-emerald-600 hover:text-emerald-800 text-sm">
                                        Bewerken
                                    </a>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-gray-500">
                            Nog geen behandelingen geregistreerd
                        </li>
                    @endforelse
                </ul>
            </div>

            <!-- Vaccinations -->
            @if($patient->vaccinations->count() > 0)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Vaccinaties</h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        @foreach($patient->vaccinations as $vaccination)
                            <li class="px-4 py-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $vaccination->vaccine_name }}</p>
                                        <p class="text-sm text-gray-500">
                                            Toegediend: {{ $vaccination->administered_at->format('d-m-Y') }}
                                        </p>
                                    </div>
                                    @if($vaccination->next_due_date)
                                        <span class="text-sm {{ $vaccination->is_due ? 'text-red-600 font-medium' : ($vaccination->is_due_soon ? 'text-yellow-600' : 'text-gray-500') }}">
                                            Volgende: {{ $vaccination->next_due_date->format('d-m-Y') }}
                                        </span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
