<x-filament-tree-table::cell
    :attributes="\Filament\Support\prepare_inherited_attributes($attributes)"
>
    <div class="whitespace-nowrap px-3 py-4">
        {{ $slot }}
    </div>
</x-filament-tree-table::cell>
