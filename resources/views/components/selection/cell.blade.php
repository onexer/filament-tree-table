<x-filament-tree-table::cell
    :attributes="
        \Filament\Support\prepare_inherited_attributes($attributes)
            ->class(['w-1'])
    "
>
    <div class="px-3 py-4">
        {{ $slot }}
    </div>
</x-filament-tree-table::cell>
