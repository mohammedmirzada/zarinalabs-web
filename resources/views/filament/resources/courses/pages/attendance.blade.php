{{-- The panel disables blade-icon components, so icons use the @svg directive here. --}}
<x-filament-panels::page>
    @if ($this->registrations->isEmpty() || $this->sessions->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 px-6 py-16 text-center">
            <p class="font-medium">Nothing to show yet</p>
            <p class="mt-2 text-sm text-gray-500">
                This course needs at least one session and one registered student.
            </p>
        </div>
    @else
        <p class="text-sm text-gray-500">
            Click a cell to mark a student present or absent. Course completion is your decision.
        </p>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-white px-4 py-3 text-left font-medium">Student</th>

                        @foreach ($this->sessions as $session)
                            <th class="px-4 py-3 text-center font-medium whitespace-nowrap">
                                {{ $session->session_date->format('j M') }}
                                <span class="block text-xs font-normal text-gray-500">
                                    {{ $session->session_date->format('D') }}
                                </span>
                            </th>
                        @endforeach

                        <th class="px-4 py-3 text-center font-medium">Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach ($this->registrations as $registration)
                        <tr wire:key="row-{{ $registration->id }}">
                            <td class="sticky left-0 z-10 bg-white px-4 py-3 whitespace-nowrap">
                                <span class="font-medium">{{ $registration->user->name }}</span>
                                <span class="block text-xs text-gray-500">{{ $registration->user->email }}</span>
                            </td>

                            @foreach ($this->sessions as $session)
                                @php($present = $this->isPresent($registration->id, $session->id))

                                <td class="px-4 py-3 text-center">
                                    <button type="button"
                                            wire:click="toggle({{ $registration->id }}, {{ $session->id }})"
                                            wire:key="cell-{{ $registration->id }}-{{ $session->id }}"
                                            title="{{ $present ? 'Present. Click to clear.' : 'Absent. Click to mark present.' }}"
                                            class="inline-flex size-8 items-center justify-center rounded-lg border transition-colors
                                                {{ $present
                                                    ? 'border-primary-600 bg-primary-600 text-white hover:bg-primary-700'
                                                    : 'border-gray-300 text-gray-300 hover:border-primary-600 hover:text-primary-600' }}">
                                        @if ($present)
                                            @svg('heroicon-s-check', 'size-5')
                                        @else
                                            @svg('heroicon-s-minus', 'size-5')
                                        @endif
                                    </button>
                                </td>
                            @endforeach

                            <td class="px-4 py-3 text-center font-medium whitespace-nowrap">
                                {{ $this->sessions->filter(fn ($session) => $this->isPresent($registration->id, $session->id))->count() }}
                                / {{ $this->sessions->count() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
