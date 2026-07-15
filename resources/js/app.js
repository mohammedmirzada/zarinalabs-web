// Livewire bundles and starts Alpine. We only register our own Alpine data
// components on the alpine:init hook — never import or start Alpine ourselves.
document.addEventListener('alpine:init', () => {
    // Custom dropdown that replaces a native <select> and writes to a Livewire
    // property live. See resources/views/components/form/combobox.blade.php.
    window.Alpine.data('combobox', ({ options, placeholder, model, required = false, live = true }) => ({
        open: false,
        active: -1,
        options,
        placeholder,
        model,
        required,
        live,

        get keys() {
            return Object.keys(this.options);
        },

        // Lowest index the keyboard may reach: 0 when required (no clear row), else -1.
        get floor() {
            return this.required ? 0 : -1;
        },

        get label() {
            const value = this.$wire.get(this.model);
            return value && this.options[value] ? this.options[value] : this.placeholder;
        },

        toggle() {
            this.open ? this.close() : this.openList();
        },

        openList() {
            this.open = true;
            // Highlight the current value, or the first reachable row when none is set.
            const value = this.$wire.get(this.model);
            this.active = value ? this.keys.indexOf(value) : this.floor;
        },

        close() {
            this.open = false;
        },

        move(step) {
            const last = this.keys.length - 1;
            let next = this.active + step;
            if (next < this.floor) next = last;   // wrap past the top back to the end
            if (next > last) next = this.floor;   // wrap past the end back to the top
            this.active = next;
        },

        choose(key) {
            // Filters set live so results react at once; form fields defer like wire:model.
            this.$wire.set(this.model, key, this.live);
            this.close();
        },

        // Commit whatever the keyboard has highlighted. active === -1 is the
        // clear row (unreachable on required fields); otherwise map index to key.
        chooseActive() {
            this.choose(this.active === -1 ? '' : this.keys[this.active]);
        },
    }));
});
