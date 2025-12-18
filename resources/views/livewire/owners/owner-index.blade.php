<div>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Eigenaren</h1>
        <button wire:click="create" class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Eigenaar toevoegen
        </button>
    </div>

    <!-- Search -->
    <div class="mb-4">
        <input type="text" wire:model.live.debounce.300ms="search"
               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 placeholder:text-center focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
               placeholder="Zoek eigenaar, dier, plaats, postcode, telefoon...">
    </div>

    <!-- Form Modal -->
    @if($showForm)
        <div class="fixed inset-0 bg-gray-500/75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $editing ? 'Eigenaar bewerken' : 'Nieuwe eigenaar' }}
                    </h3>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">Naam *</label>
                            <input type="text" wire:model="name" id="name"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Telefoon 1</label>
                            <input type="tel" wire:model="phone" id="phone"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div>
                            <label for="phone2" class="block text-sm font-medium text-gray-700">Telefoon 2</label>
                            <input type="tel" wire:model="phone2" id="phone2"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model="email" id="email"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <!-- Postcode lookup -->
                        <div class="sm:col-span-2 p-4 bg-gray-50 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Adres opzoeken</label>
                            <div class="flex gap-2">
                                <div class="flex-1">
                                    <input type="text" wire:model="postal_code" id="postal_code"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
                                           placeholder="Postcode (1234AB)">
                                </div>
                                <div class="w-24">
                                    <input type="text" wire:model="house_number" id="house_number"
                                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
                                           placeholder="Nr.">
                                </div>
                                <button type="button" wire:click="lookupAddress"
                                        class="inline-flex items-center rounded-md bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                                    <span wire:loading.remove wire:target="lookupAddress">Zoeken</span>
                                    <span wire:loading wire:target="lookupAddress">...</span>
                                </button>
                            </div>
                            @if($lookupError)
                                <p class="text-red-600 text-sm mt-1">{{ $lookupError }}</p>
                            @endif
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-700">Straat</label>
                            <input type="text" wire:model="address" id="address"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">Plaats</label>
                            <input type="text" wire:model="city" id="city"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div>
                            <label for="ubn" class="block text-sm font-medium text-gray-700">UBN (fokkers)</label>
                            <input type="text" wire:model="ubn" id="ubn"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
                                   placeholder="Optioneel">
                        </div>

                        <div class="flex items-end">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="active"
                                       class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600">
                                <span class="ml-2 text-sm text-gray-700">Actief</span>
                            </label>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notities</label>
                            <textarea wire:model="notes" id="notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="cancel"
                                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Annuleren
                        </button>
                        <button type="submit"
                                class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                            Opslaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Owners List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Naam</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adres</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Patiënten</th>
                    <th scope="col" class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($owners as $owner)
                    <tr class="{{ !$owner->active ? 'bg-gray-50 opacity-60' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('owners.show', $owner) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">
                                {{ $owner->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($owner->phone)
                                <div class="text-sm text-gray-900">{{ $owner->phone }}</div>
                            @endif
                            @if($owner->email)
                                <div class="text-sm text-gray-500">{{ $owner->email }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $owner->city }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                {{ $owner->patients_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <button wire:click="edit({{ $owner->id }})" class="text-emerald-600 hover:text-emerald-800">
                                Bewerken
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            @if($search)
                                Geen eigenaren gevonden voor "{{ $search }}"
                            @else
                                Nog geen eigenaren toegevoegd
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($owners->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $owners->links() }}
            </div>
        @endif
    </div>
</div>
