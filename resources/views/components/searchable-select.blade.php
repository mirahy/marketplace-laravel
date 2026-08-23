@props(['options' => [], 'selected' => null, 'placeholder' => 'Selecione'])

<div class="relative" x-data="{
        open: false,
        query: '',
        label: '{{ addslashes($placeholder) }}',
        init() {
            this.syncLabel();
        },
        norm(text) {
            return text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        },
        filteredOptions() {
            return Array.from($refs.select.options).filter(o => o.value !== '' && this.norm(o.text).includes(this.norm(this.query)));
        },
        syncLabel() {
            const opt = this.$refs.select.selectedOptions[0];
            this.label = (opt && opt.value !== '') ? opt.text : '{{ addslashes($placeholder) }}';
        },
        choose(value) {
            this.$refs.select.value = value;
            this.$refs.select.dispatchEvent(new Event('change'));
            this.syncLabel();
            this.open = false;
            this.query = '';
        },
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <select x-ref="select" {{ $attributes }} class="hidden">
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected((string) $value === (string) $selected)>{{ $optionLabel }}</option>
        @endforeach
    </select>

    <button type="button" @click="open = ! open"
        class="mt-1 w-full flex items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-left">
        <span x-text="label" :class="{ 'text-gray-400': label === '{{ addslashes($placeholder) }}' }"></span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" x-cloak
        class="absolute z-20 mt-1 w-full bg-white border border-gray-100 rounded-md shadow-lg">
        <div class="p-2 border-b border-gray-100">
            <input type="text" x-model="query" @click.stop placeholder="Pesquisar..."
                class="w-full rounded-md border-gray-300 text-sm">
        </div>
        <ul class="max-h-56 overflow-y-auto py-1">
            <template x-for="opt in filteredOptions()" :key="opt.value">
                <li @click="choose(opt.value)" x-text="opt.text"
                    class="px-3 py-2 text-sm cursor-pointer hover:bg-orange-50"></li>
            </template>
            <li x-show="filteredOptions().length === 0"
                class="px-3 py-2 text-sm text-gray-400">Nenhum resultado</li>
        </ul>
    </div>
</div>
