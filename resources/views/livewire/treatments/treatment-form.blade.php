<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $treatment?->exists ? 'Behandeling bewerken' : 'Nieuwe behandeling' }}
        </h1>
        <p class="text-gray-500">{{ $patient->name }} - {{ $patient->owner_name }}</p>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Basic Info -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Behandeling</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Datum *</label>
                    <input type="date" wire:model="date" id="date"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                    @error('date') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="work_location_id" class="block text-sm font-medium text-gray-700">Locatie</label>
                    <select wire:model="work_location_id" id="work_location_id"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        <option value="">Selecteer...</option>
                        @foreach($workLocations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="veterinarian" class="block text-sm font-medium text-gray-700">Dierenarts</label>
                    <input type="text" wire:model="veterinarian" id="veterinarian"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                </div>
            </div>

            <div class="mt-4">
                <label for="complaint" class="block text-sm font-medium text-gray-700">Klacht / Reden bezoek</label>
                <input type="text" wire:model="complaint" id="complaint"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
            </div>

            <div class="mt-4">
                <label for="anamnesis" class="block text-sm font-medium text-gray-700">Anamnese</label>
                <textarea wire:model="anamnesis" id="anamnesis" rows="2"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"></textarea>
            </div>

            <div class="mt-4">
                <label for="examination" class="block text-sm font-medium text-gray-700">Lichamelijk onderzoek</label>
                <textarea wire:model="examination" id="examination" rows="2"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"></textarea>
            </div>

            <div class="mt-4">
                <label for="diagnosis" class="block text-sm font-medium text-gray-700">Diagnose</label>
                <textarea wire:model="diagnosis" id="diagnosis" rows="2"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"></textarea>
            </div>

            <div class="mt-4">
                <label for="treatment_description" class="block text-sm font-medium text-gray-700">Behandeling / Therapie</label>
                <textarea wire:model="treatment_description" id="treatment_description" rows="3"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"></textarea>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="flex items-center">
                    <input type="checkbox" wire:model="follow_up_needed" id="follow_up_needed"
                           class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600">
                    <label for="follow_up_needed" class="ml-2 text-sm text-gray-700">Follow-up nodig</label>
                </div>
                @if($follow_up_needed)
                    <div>
                        <label for="follow_up_date" class="block text-sm font-medium text-gray-700">Follow-up datum</label>
                        <input type="date" wire:model="follow_up_date" id="follow_up_date"
                               class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                    </div>
                @endif
            </div>
        </div>

        <!-- Treatment Items (for invoice) -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-medium text-gray-900">Factuurregels</h2>
                <button type="button" wire:click="addItem"
                        class="inline-flex items-center rounded-md bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Regel toevoegen
                </button>
            </div>

            @if(count($items) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Omschrijving</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">Aantal</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Prijs</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-24">BTW %</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-28">Totaal</th>
                                <th class="px-3 py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($items as $index => $item)
                                <tr>
                                    <td class="px-3 py-2">
                                        <input type="text" wire:model="items.{{ $index }}.description"
                                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" wire:model="items.{{ $index }}.quantity"
                                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" wire:model="items.{{ $index }}.unit_price"
                                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <select wire:model="items.{{ $index }}.vat_rate"
                                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                                            <option value="21">21%</option>
                                            <option value="9">9%</option>
                                            <option value="0">0%</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-900">
                                        &euro; {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-sm">Nog geen factuurregels toegevoegd</p>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('patients.show', $patient) }}"
               class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Annuleren
            </a>
            <button type="submit"
                    class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                Opslaan
            </button>
            @if(count($items) > 0 && $patient->havunadmin_customer_id)
                <button type="button" wire:click="saveAndInvoice"
                        class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Opslaan & Factureren
                </button>
            @endif
        </div>
    </form>
</div>
