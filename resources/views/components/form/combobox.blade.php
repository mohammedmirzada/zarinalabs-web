@props([
    'options' => [],
    'placeholder' => 'Choose one',
    'model' => null,     // the Livewire property this control writes to
    'id' => null,
    'required' => false, // required fields hide the "clear" row (no empty value)
    'live' => true,      // false = defer the write (like plain wire:model)
])

{{--
    Custom dropdown that replaces a native <select>. Alpine only handles the open/close
    and keyboard UI; the value lives in Livewire ($wire). Set $live=false to defer the
    write (a form field) instead of reacting at once (a filter).
    Accessible: listbox role, arrow/enter/escape/home/end keys.
--}}
<div
    x-data="combobox({
        options: @js($options),
        placeholder: @js($placeholder),
        model: @js($model),
        required: @js((bool) $required),
        live: @js((bool) $live),
    })"
    x-id="['combobox-list']"
    class="relative mt-1"
    @keydown.escape.stop="close()"
    @click.outside="close()"
>
    <button
        type="button"
        @if ($id) id="{{ $id }}" @endif
        @click="toggle()"
        @keydown.arrow-down.prevent="open ? move(1) : openList()"
        @keydown.arrow-up.prevent="open ? move(-1) : openList()"
        @keydown.enter.prevent="open ? chooseActive() : openList()"
        @keydown.home.prevent="open && (active = 0)"
        @keydown.end.prevent="open && (active = keys.length - 1)"
        :aria-expanded="open"
        :aria-controls="$id('combobox-list')"
        aria-haspopup="listbox"
        {{ $attributes->merge([
            'class' => 'flex w-full items-center justify-between rounded-lg border border-line bg-white '
                . 'px-3 py-2 text-left text-ink transition-colors hover:border-brand '
                . 'focus:border-brand focus:ring-1 focus:ring-brand focus:outline-none',
        ]) }}
    >
        <span x-text="label" :class="{ 'text-ink/50': ! $wire.get(model) }" class="truncate"></span>
        <x-heroicon-s-chevron-down class="ml-2 size-4 shrink-0 text-ink/50 transition-transform"
                                   ::class="open && 'rotate-180'" />
    </button>

    <ul
        x-show="open"
        x-cloak
        x-transition.origin.top.duration.120ms
        :id="$id('combobox-list')"
        role="listbox"
        class="absolute z-40 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-line bg-white py-1 shadow-lg"
    >
        {{-- The placeholder / "all" option clears the value. Hidden on required fields. --}}
        <li
            x-show="! required"
            role="option"
            :aria-selected="! $wire.get(model)"
            @click="choose('')"
            @mouseenter="active = -1"
            :class="active === -1 ? 'bg-paper' : ''"
            class="cursor-pointer px-3 py-2 text-sm text-ink/70"
        >
            <span x-text="placeholder"></span>
        </li>

        <template x-for="(key, i) in keys" :key="key">
            <li
                role="option"
                :aria-selected="$wire.get(model) === key"
                @click="choose(key)"
                @mouseenter="active = i"
                :class="active === i ? 'bg-paper' : ''"
                class="flex cursor-pointer items-center justify-between px-3 py-2 text-sm text-ink"
            >
                <span x-text="options[key]"></span>
                <x-heroicon-s-check class="size-4 text-brand" x-show="$wire.get(model) === key" />
            </li>
        </template>
    </ul>
</div>
