@php
    use Filament\Support\Enums\Alignment;
    use Onexer\FilamentTreeTable\Actions\Action;
    use Onexer\FilamentTreeTable\Actions\ActionGroup;
    use Onexer\FilamentTreeTable\Columns\Column;
    use Onexer\FilamentTreeTable\Enums\ActionsPosition;
    use Onexer\FilamentTreeTable\Enums\FiltersLayout;
    use Onexer\FilamentTreeTable\Enums\RecordCheckboxPosition;
    use Onexer\FilamentTreeTable\Filters\BaseFilter;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;
    use Illuminate\Contracts\Pagination\Paginator;
    use Illuminate\Database\Query\Builder;
    use function Filament\Support\prepare_inherited_attributes;

    $actions = $getActions();
    $records = $isLoaded ? $getRecords() : null;
        $header = $getHeader();
    $headerActions = array_filter(
        $getHeaderActions(),
        fn (Action | ActionGroup $action): bool => $action->isVisible(),
    );
    $description = $getDescription();
    $isGlobalSearchVisible = $isSearchable();
    $hasFilters = $isFilterable();
    $filtersLayout = $getFiltersLayout();
    $filtersTriggerAction = $getFiltersTriggerAction();
    $hasFiltersDropdown = $hasFilters && ($filtersLayout === FiltersLayout::Dropdown);
    $hasFiltersAboveContent = $hasFilters && in_array($filtersLayout, [FiltersLayout::AboveContent, FiltersLayout::AboveContentCollapsible]);
    $hasFiltersAboveContentCollapsible = $hasFilters && ($filtersLayout === FiltersLayout::AboveContentCollapsible);
    $hasFiltersBelowContent = $hasFilters && ($filtersLayout === FiltersLayout::BelowContent);

    $filterIndicators = [
        ...($hasSearch() ? ['resetTreeTableSearch' => $getSearchIndicator()] : []),
        ...collect($getColumnSearchIndicators())
            ->mapWithKeys(fn (string $indicator, string $column): array => [
                "resetTreeTableColumnSearch('{$column}')" => $indicator,
            ])
            ->all(),
        ...array_reduce(
            $getFilters(),
            fn (array $carry, BaseFilter $filter): array => [
                ...$carry,
                ...collect($filter->getIndicators())
                    ->mapWithKeys(fn (string $label, int | string $field) => [
                        "removeTreeTableFilter('{$filter->getName()}'" . (is_string($field) ? ' , \'' . $field . '\'' : null) . ')' => $label,
                    ])
                    ->all(),
            ],
            [],
        ),
    ];

        $filterIndicators = [
        ...($hasSearch() ? ['resetTableSearch' => $getSearchIndicator()] : []),
        ...collect($getColumnSearchIndicators())
            ->mapWithKeys(fn (string $indicator, string $column): array => [
                "resetTableColumnSearch('$column')" => $indicator,
            ])
            ->all(),
        ...array_reduce(
            $getFilters(),
            fn (array $carry, BaseFilter $filter): array => [
                ...$carry,
                ...collect($filter->getIndicators())
                    ->mapWithKeys(fn (string $label, int | string $field) => [
                        "removeTreeTableFilter('{$filter->getName()}'" . (is_string($field) ? ' , \'' . $field . '\'' : null) . ')' => $label,
                    ])
                    ->all(),
            ],
            [],
        ),
    ];
    $headerActionsPosition = $getHeaderActionsPosition();
    $heading = $getHeading();

    $hasHeader = $header || $heading || $description || $headerActions  || $isGlobalSearchVisible || $hasFilters || count($filterIndicators);
    $hasHeaderToolbar = $isGlobalSearchVisible || $hasFiltersDropdown;
    $columns = $getVisibleColumns();
@endphp

<div
    @if (! $isLoaded)
        wire:init="loadTable"
    @endif
    x-data="{
        isLoading: false,
    }"
    @class([
        'fi-ta',
        'animate-pulse' => $records === null,
    ])
>
    <x-filament-tree-table::container>

        <div
            @if (! $hasHeader) x-cloak @endif
        x-show="@js($hasHeader)"
            class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10"
        >
            @if ($header)
                {{ $header }}
            @elseif ($heading || $description || $headerActions)
                <x-filament-tree-table::header
                    :actions="$headerActions"
                    :actions-position="$headerActionsPosition"
                    :description="$description"
                    :heading="$heading"
                />
            @endif

            @if ($hasFiltersAboveContent)
                <div
                    x-data="{ areFiltersOpen: @js(! $hasFiltersAboveContentCollapsible) }"
                    @class([
                        'grid px-4 sm:px-6',
                        'py-4' => ! $hasFiltersAboveContentCollapsible,
                        'gap-y-3 py-2.5 sm:gap-y-1 sm:py-3' => $hasFiltersAboveContentCollapsible,
                    ])
                >
                    @if ($hasFiltersAboveContentCollapsible)
                        <span
                            x-on:click="areFiltersOpen = ! areFiltersOpen"
                            @class([
                                'ms-auto inline-flex',
                                '-mx-2' => $filtersTriggerAction->isIconButton(),
                            ])
                        >
                            {{ $filtersTriggerAction->badge(count(\Illuminate\Support\Arr::flatten($filterIndicators))) }}
                        </span>
                    @endif

                    <x-filament-tree-table::filters
                        :form="$getFiltersForm()"
                        x-show="areFiltersOpen"
                        @class([
                            'py-1 sm:py-3' => $hasFiltersAboveContentCollapsible,
                        ])
                    />
                </div>
            @endif

            <div
                @if (! $hasHeaderToolbar) x-cloak @endif
            x-show="@js($hasHeaderToolbar)"
                class="fi-ta-header-toolbar flex items-center justify-between gap-3 px-4 py-3 sm:px-6"
            >

                @if ($isGlobalSearchVisible || $hasFiltersDropdown)
                    <div
                        @class([
                            'ms-auto flex items-center',
                            'gap-x-3' => ! $filtersTriggerAction->isIconButton(),
                            'gap-x-4' => $filtersTriggerAction->isIconButton(),
                        ])
                    >
                        @if ($isGlobalSearchVisible)
                            <x-filament-tree-table::search-field/>
                        @endif

                        @if ($hasFiltersDropdown)
                            <x-filament-tree-table::filters.dropdown
                                :form="$getFiltersForm()"
                                :indicators-count="count(\Illuminate\Support\Arr::flatten($filterIndicators))"
                                :max-height="$getFiltersFormMaxHeight()"
                                :trigger-action="$filtersTriggerAction"
                                :width="$getFiltersFormWidth()"
                            />
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if (count($filterIndicators))
            <x-filament-tree-table::filters.indicators
                :indicators="$filterIndicators"
            />
        @endif

        <div class="m-2">
            @include('filament-tree-table::records', [
                'records' => $records,
                'parent_id' => null,
                'level' => 1,
                'previous_chain' => '',
            ])
        </div>
        {{--    @foreach ($records as $record)--}}
        {{--        @include('filament-tree-table::record', ['records' =>$records])--}}
        {{--    @endforeach--}}


    </x-filament-tree-table::container>
    @include('filament-tree-table::modals')
</div>

