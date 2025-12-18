<div>
    <div class="mb-6">
        <a href="{{ route('owners.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Terug naar eigenaren</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Owner Info -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">{{ $owner->name }}</h2>

                <dl class="space-y-3">
                    @if($owner->phone)
                        <div>
                            <dt class="text-sm text-gray-500">Telefoon 1</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                <a href="tel:{{ $owner->phone }}" class="text-emerald-600 hover:text-emerald-800">{{ $owner->phone }}</a>
                            </dd>
                        </div>
                    @endif
                    @if($owner->phone2)
                        <div>
                            <dt class="text-sm text-gray-500">Telefoon 2</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                <a href="tel:{{ $owner->phone2 }}" class="text-emerald-600 hover:text-emerald-800">{{ $owner->phone2 }}</a>
                            </dd>
                        </div>
                    @endif
                    @if($owner->email)
                        <div>
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                <a href="mailto:{{ $owner->email }}" class="text-emerald-600 hover:text-emerald-800">{{ $owner->email }}</a>
                            </dd>
                        </div>
                    @endif
                    @if($owner->full_address)
                        <div>
                            <dt class="text-sm text-gray-500">Adres</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                {{ $owner->address }} {{ $owner->house_number }}<br>
                                {{ $owner->postal_code }} {{ $owner->city }}
                            </dd>
                        </div>
                    @endif
                    @if($owner->notes)
                        <div>
                            <dt class="text-sm text-gray-500">Notities</dt>
                            <dd class="text-sm text-gray-900">{{ $owner->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Patients -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Patiënten</h3>
                    <button wire:click="createPatient" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                        <svg class="-ml-0.5 mr-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Patiënt toevoegen
                    </button>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse($patients as $patient)
                        <a href="{{ route('patients.show', $patient) }}" class="block px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-emerald-600">{{ $patient->name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ ucfirst($patient->species) }}
                                        @if($patient->breed) - {{ $patient->breed }} @endif
                                        @if($patient->age) | {{ $patient->age }} @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($patient->deceased_at)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                            Overleden
                                        </span>
                                    @endif
                                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center text-gray-500">
                            Nog geen patiënten voor deze eigenaar
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Form Modal -->
    @if($showPatientForm)
        <div class="fixed inset-0 bg-gray-500/75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Nieuwe patiënt voor {{ $owner->name }}</h3>
                </div>

                <form wire:submit="savePatient" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="patient_name" class="block text-sm font-medium text-gray-700">Naam *</label>
                            <input type="text" wire:model="patient_name" id="patient_name"
                                   class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                            @error('patient_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="species" class="block text-sm font-medium text-gray-700">Diersoort *</label>
                            <select wire:model="species" id="species"
                                    class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                                <option value="dog">Hond</option>
                                <option value="cat">Kat</option>
                                <option value="rabbit">Konijn</option>
                                <option value="bird">Vogel</option>
                                <option value="rodent">Knaagdier</option>
                                <option value="reptile">Reptiel</option>
                                <option value="other">Anders</option>
                            </select>
                        </div>

                        <div>
                            <label for="breed" class="block text-sm font-medium text-gray-700">Ras</label>
                            <input type="text" wire:model="breed" id="breed"
                                   class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Geboortedatum</label>
                            <input type="date" wire:model="date_of_birth" id="date_of_birth"
                                   class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Geslacht</label>
                            <select wire:model="gender" id="gender"
                                    class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                                <option value="unknown">Onbekend</option>
                                <option value="male">Mannelijk</option>
                                <option value="female">Vrouwelijk</option>
                            </select>
                        </div>

                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700">Kleur</label>
                            <input type="text" wire:model="color" id="color"
                                   class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div>
                            <label for="weight" class="block text-sm font-medium text-gray-700">Gewicht (kg)</label>
                            <input type="number" wire:model="weight" id="weight" step="0.1"
                                   class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div>
                            <label for="chip_number" class="block text-sm font-medium text-gray-700">Chipnummer</label>
                            <input type="text" wire:model="chip_number" id="chip_number"
                                   class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm">
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="neutered"
                                       class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600">
                                <span class="ml-2 text-sm text-gray-700">Gecastreerd/Gesteriliseerd</span>
                            </label>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="patient_notes" class="block text-sm font-medium text-gray-700">Notities</label>
                            <textarea wire:model="patient_notes" id="patient_notes" rows="3"
                                      class="mt-1 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="cancelPatient"
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
</div>
