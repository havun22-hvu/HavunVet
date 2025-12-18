<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $patient?->exists ? 'Patiënt bewerken' : 'Nieuwe patiënt' }}
        </h1>
    </div>

    <form wire:submit="save" class="space-y-6">
        <!-- Owner Selection -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Eigenaar</h2>

            @if($selectedOwner)
                <!-- Selected owner display -->
                <div class="flex items-center justify-between p-4 bg-emerald-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $selectedOwner->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $selectedOwner->phone }}
                            @if($selectedOwner->city) | {{ $selectedOwner->city }} @endif
                        </p>
                    </div>
                    <button type="button" wire:click="clearOwner" class="text-sm text-red-600 hover:text-red-800">
                        Wijzig
                    </button>
                </div>
            @elseif($showNewOwnerForm)
                <!-- New owner form -->
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-medium text-gray-900">Nieuwe eigenaar</h3>
                        <button type="button" wire:click="toggleNewOwnerForm" class="text-sm text-gray-600 hover:text-gray-800">
                            Annuleren
                        </button>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="new_owner_name" class="block text-sm font-medium text-gray-700">Naam *</label>
                            <input type="text" wire:model="new_owner_name" id="new_owner_name"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                            @error('new_owner_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="new_owner_phone" class="block text-sm font-medium text-gray-700">Telefoon</label>
                            <input type="tel" wire:model="new_owner_phone" id="new_owner_phone"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>
                        <div>
                            <label for="new_owner_email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model="new_owner_email" id="new_owner_email"
                                   class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>
                    </div>
                </div>
            @else
                <!-- Owner search -->
                <div class="relative">
                    <label for="ownerSearch" class="block text-sm font-medium text-gray-700 mb-1">Zoek eigenaar</label>
                    <input type="text" wire:model.live.debounce.300ms="ownerSearch" id="ownerSearch"
                           class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"
                           placeholder="Zoek op naam, telefoon of email...">

                    @if(count($ownerResults) > 0)
                        <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm">
                            @foreach($ownerResults as $owner)
                                <li wire:click="selectOwner({{ $owner['id'] }})"
                                    class="relative cursor-pointer select-none py-2 px-3 hover:bg-emerald-50">
                                    <span class="block font-medium">{{ $owner['name'] }}</span>
                                    <span class="block text-gray-500">
                                        {{ $owner['phone'] ?? '' }}
                                        @if($owner['city']) | {{ $owner['city'] }} @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @error('owner_id') <p class="mt-1 text-red-600 text-sm">{{ $message }}</p> @enderror

                <div class="mt-3">
                    <button type="button" wire:click="toggleNewOwnerForm" class="text-sm text-emerald-600 hover:text-emerald-800">
                        + Nieuwe eigenaar aanmaken
                    </button>
                </div>
            @endif
        </div>

        <!-- Patient Information -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Patiënt gegevens</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Naam dier *</label>
                    <input type="text" wire:model="name" id="name"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                    @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="species" class="block text-sm font-medium text-gray-700">Diersoort *</label>
                    <select wire:model="species" id="species"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        <option value="dog">Hond</option>
                        <option value="cat">Kat</option>
                        <option value="rabbit">Konijn</option>
                        <option value="bird">Vogel</option>
                        <option value="rodent">Knaagdier</option>
                        <option value="reptile">Reptiel</option>
                        <option value="horse">Paard</option>
                        <option value="other">Overig</option>
                    </select>
                    @error('species') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="breed" class="block text-sm font-medium text-gray-700">Ras</label>
                    <input type="text" wire:model="breed" id="breed"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Geboortedatum</label>
                    <input type="date" wire:model="date_of_birth" id="date_of_birth"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                </div>
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Geslacht</label>
                    <select wire:model="gender" id="gender"
                            class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        <option value="unknown">Onbekend</option>
                        <option value="male">Mannelijk</option>
                        <option value="female">Vrouwelijk</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="neutered" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600">
                        <span class="ml-2 text-sm text-gray-700">Gecastreerd/Gesteriliseerd</span>
                    </label>
                </div>
                <div>
                    <label for="chip_number" class="block text-sm font-medium text-gray-700">Chipnummer</label>
                    <input type="text" wire:model="chip_number" id="chip_number"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                </div>
                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700">Gewicht (kg)</label>
                    <input type="number" step="0.1" wire:model="weight" id="weight"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                </div>
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700">Kleur/Vacht</label>
                    <input type="text" wire:model="color" id="color"
                           class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                </div>
            </div>

            <div class="mt-4">
                <label for="notes" class="block text-sm font-medium text-gray-700">Notities</label>
                <textarea wire:model="notes" id="notes" rows="3"
                          class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"></textarea>
            </div>

            <div class="mt-4">
                <label for="photo" class="block text-sm font-medium text-gray-700">Foto</label>
                <input type="file" wire:model="photo" id="photo" accept="image/*"
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <a href="{{ $patient?->exists ? route('patients.show', $patient) : route('patients.index') }}"
               class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Annuleren
            </a>
            <button type="submit"
                    class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                Opslaan
            </button>
        </div>
    </form>
</div>
