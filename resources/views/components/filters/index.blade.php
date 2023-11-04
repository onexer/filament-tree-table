@props([
    'form',
])

<div {{ $attributes->class(['fi-ta-filters grid gap-y-4']) }}>
    <div class="flex items-center justify-between">
        <h4
            class="text-base font-semibold leading-6 text-gray-950 dark:text-white"
        >
            {{ __('filament-tree-table::table.filters.heading') }}
        </h4>

        <x-filament::link
            color="danger"
            tag="button"
            wire:click="resetTreeTableFiltersForm"
            wire:loading.remove.delay=""
            wire:target="treeTableFilters,resetTreeTableFiltersForm"
        >
            {{ __('filament-tree-table::table.filters.actions.reset.label') }}
        </x-filament::link>

        <x-filament::loading-indicator
            wire:loading.delay=""
            wire:target="treeTableFilters,resetTreeTableFiltersForm"
            class="h-5 w-5 text-gray-400 dark:text-gray-500"
        />
    </div>

    {{ $form }}
</div>
