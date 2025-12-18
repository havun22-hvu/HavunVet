<div>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Patiënten</h1>
        <a href="{{ route('patients.create') }}" class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Nieuwe patiënt
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="search" class="sr-only">Zoeken</label>
                <input type="text" wire:model.live.debounce.300ms="search" id="search"
                       class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6"
                       placeholder="Zoek op naam, eigenaar of chipnummer...">
            </div>
            <div>
                <select wire:model.live="species"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6">
                    <option value="">Alle diersoorten</option>
                    @foreach($speciesList as $s)
                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center">
                <input type="checkbox" wire:model.live="showDeceased" id="showDeceased"
                       class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600">
                <label for="showDeceased" class="ml-2 text-sm text-gray-700">Toon overleden</label>
            </div>
        </div>
    </div>

    <!-- Patient List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Patiënt
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Eigenaar
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Soort / Ras
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Leeftijd
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Acties</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($patients as $patient)
                    <tr class="{{ $patient->deceased_at ? 'bg-gray-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 shrink-0">
                                    @if($patient->photo_path)
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($patient->photo_path) }}" alt="">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                            <span class="text-emerald-700 font-medium text-sm">{{ substr($patient->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $patient->name }}
                                        @if($patient->deceased_at)
                                            <span class="text-gray-500">(†)</span>
                                        @endif
                                    </div>
                                    @if($patient->chip_number)
                                        <div class="text-sm text-gray-500">{{ $patient->chip_number }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($patient->owner)
                                <a href="{{ route('owners.show', $patient->owner) }}" class="text-sm text-emerald-600 hover:text-emerald-800">{{ $patient->owner->name }}</a>
                                <div class="text-sm text-gray-500">{{ $patient->owner->phone }}</div>
                            @else
                                <span class="text-sm text-gray-400">Geen eigenaar</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ ucfirst($patient->species) }}</div>
                            <div class="text-sm text-gray-500">{{ $patient->breed }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $patient->age ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('patients.show', $patient) }}" class="text-emerald-600 hover:text-emerald-900">
                                Bekijk
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Geen patiënten gevonden
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($patients->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $patients->links() }}
            </div>
        @endif
    </div>
</div>
