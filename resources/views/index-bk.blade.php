@php
    use Filament\Support\Enums\Alignment;
    use Onexer\FilamentTreeTable\Actions\Action;
    use Onexer\FilamentTreeTable\Actions\ActionGroup;
    use Onexer\FilamentTreeTable\Actions\BulkAction;
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
    $actionsAlignment = $getActionsAlignment();
    $actionsPosition = $getActionsPosition();
    $actionsColumnLabel = $getActionsColumnLabel();
    $columns = $getVisibleColumns();
    $collapsibleColumnsLayout = $getCollapsibleColumnsLayout();
    $content = $getContent();
    $contentGrid = $getContentGrid();
    $contentFooter = $getContentFooter();
    $filterIndicators = [
        ...($hasSearch() ? ['resetTableSearch' => $getSearchIndicator()] : []),
        ...collect($getColumnSearchIndicators())
            ->mapWithKeys(fn (string $indicator, string $column): array => [
                "resetTableColumnSearch('{$column}')" => $indicator,
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
    $hasColumnsLayout = $hasColumnsLayout();
    $header = $getHeader();
    $headerActions = array_filter(
        $getHeaderActions(),
        fn (Action | BulkAction | ActionGroup $action): bool => $action->isVisible(),
    );
    $headerActionsPosition = $getHeaderActionsPosition();
    $heading = $getHeading();
    $bulkActions = array_filter(
        $getBulkActions(),
        fn (BulkAction | ActionGroup $action): bool => $action->isVisible(),
    );
    $description = $getDescription();
    $isReordering = $isReordering();
    $isColumnSearchVisible = $isSearchableByColumn();
    $isGlobalSearchVisible = $isSearchable();
    $isSelectionEnabled = $isSelectionEnabled();
    $recordCheckboxPosition = $getRecordCheckboxPosition();
    $isLoaded = $isLoaded();
    $hasFilters = $isFilterable();
    $filtersLayout = $getFiltersLayout();
    $filtersTriggerAction = $getFiltersTriggerAction();
    $hasFiltersDropdown = $hasFilters && ($filtersLayout === FiltersLayout::Dropdown);
    $hasFiltersAboveContent = $hasFilters && in_array($filtersLayout, [FiltersLayout::AboveContent, FiltersLayout::AboveContentCollapsible]);
    $hasFiltersAboveContentCollapsible = $hasFilters && ($filtersLayout === FiltersLayout::AboveContentCollapsible);
    $hasFiltersBelowContent = $hasFilters && ($filtersLayout === FiltersLayout::BelowContent);
    $hasColumnToggleDropdown = $hasToggleableColumns();
    $hasHeader = $header || $heading || $description || ($headerActions && (! $isReordering)) || $isGlobalSearchVisible || $hasFilters || count($filterIndicators) || $hasColumnToggleDropdown;
    $hasHeaderToolbar = $isGlobalSearchVisible || $hasFiltersDropdown || $hasColumnToggleDropdown;
    $pluralModelLabel = $getPluralModelLabel();
    $records = $isLoaded ? $getRecords() : null;
    $allSelectableRecordsCount = ($isSelectionEnabled && $isLoaded) ? $getAllSelectableRecordsCount() : null;
    $columnsCount = count($columns);
    $reorderRecordsTriggerAction = $getReorderRecordsTriggerAction($isReordering);
    $toggleColumnsTriggerAction = $getToggleColumnsTriggerAction();

    if (count($actions) && (! $isReordering)) {
        $columnsCount++;
    }

    if ($isSelectionEnabled || $isReordering) {
        $columnsCount++;
    }

    $getHiddenClasses = function (Onexer\FilamentTreeTable\Columns\Column $column): ?string {
        if ($breakpoint = $column->getHiddenFrom()) {
            return match ($breakpoint) {
                'sm' => 'sm:hidden',
                'md' => 'md:hidden',
                'lg' => 'lg:hidden',
                'xl' => 'xl:hidden',
                '2xl' => '2xl:hidden',
            };
        }

        if ($breakpoint = $column->getVisibleFrom()) {
            return match ($breakpoint) {
                'sm' => 'hidden sm:table-cell',
                'md' => 'hidden md:table-cell',
                'lg' => 'hidden lg:table-cell',
                'xl' => 'hidden xl:table-cell',
                '2xl' => 'hidden 2xl:table-cell',
            };
        }

        return null;
    };
@endphp

<div
    @if (! $isLoaded)
        wire:init="loadTable"
    @endif
    x-data="{

        isLoading: false,

        selectedRecords: [],

        shouldCheckUniqueSelection: true,

        init: function () {

            $watch('selectedRecords', () => {
                if (! this.shouldCheckUniqueSelection) {
                    this.shouldCheckUniqueSelection = true

                    return
                }

                this.selectedRecords = [...new Set(this.selectedRecords)]

                this.shouldCheckUniqueSelection = false
            })
        },

        mountBulkAction: function (name) {
            $wire.set('selectedTableRecords', this.selectedRecords, false)
            $wire.mountTableBulkAction(name)
        },

        toggleSelectRecordsOnPage: function () {
            const keys = this.getRecordsOnPage()

            if (this.areRecordsSelected(keys)) {
                this.deselectRecords(keys)

                return
            }

            this.selectRecords(keys)
        },

        getRecordsOnPage: function () {
            const keys = []

            for (checkbox of $el.getElementsByClassName('fi-ta-record-checkbox')) {
                keys.push(checkbox.value)
            }

            return keys
        },

        selectRecords: function (keys) {
            for (key of keys) {
                if (this.isRecordSelected(key)) {
                    continue
                }

                this.selectedRecords.push(key)
            }
        },

        deselectRecords: function (keys) {
            for (key of keys) {
                let index = this.selectedRecords.indexOf(key)

                if (index === -1) {
                    continue
                }

                this.selectedRecords.splice(index, 1)
            }
        },

        selectAllRecords: async function () {
            this.isLoading = true

            this.selectedRecords = await $wire.getAllSelectableTableRecordKeys()

            this.isLoading = false
        },

        deselectAllRecords: function () {
            this.selectedRecords = []
        },

        isRecordSelected: function (key) {
            return this.selectedRecords.includes(key)
        },

        areRecordsSelected: function (keys) {
            return keys.every((key) => this.isRecordSelected(key))
        },

    }"
    @class([
        'fi-ta',
        'animate-pulse' => $records === null,
    ])
>
    <x-filament-tree-table::container>
        <div
            @if (! $hasHeader) x-cloak @endif
        x-bind:hidden="! (@js($hasHeader) || (selectedRecords.length && @js(count($bulkActions))))"
            x-show="@js($hasHeader) || (selectedRecords.length && @js(count($bulkActions)))"
            class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10"
        >
            @if ($header)
                {{ $header }}
            @elseif (($heading || $description || $headerActions) && ! $isReordering)
                <x-filament-tree-table::header
                    :actions="$isReordering ? [] : $headerActions"
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
            x-show="@js($hasHeaderToolbar) || (selectedRecords.length && @js(count($bulkActions)))"
                class="fi-ta-header-toolbar flex items-center justify-between gap-3 px-4 py-3 sm:px-6"
            >

                @if ($isGlobalSearchVisible || $hasFiltersDropdown || $hasColumnToggleDropdown)
                    <div
                        @class([
                            'ms-auto flex items-center',
                            'gap-x-3' => ! ($filtersTriggerAction->isIconButton() && $toggleColumnsTriggerAction->isIconButton()),
                            'gap-x-4' => $filtersTriggerAction->isIconButton() && $toggleColumnsTriggerAction->isIconButton(),
                        ])
                    >
                        @if ($isGlobalSearchVisible)
                            <x-filament-tree-table::search-field/>
                        @endif

                        @if ($hasFiltersDropdown || $hasColumnToggleDropdown)
                            @if ($hasFiltersDropdown)
                                <x-filament-tree-table::filters.dropdown
                                    :form="$getFiltersForm()"
                                    :indicators-count="count(\Illuminate\Support\Arr::flatten($filterIndicators))"
                                    :max-height="$getFiltersFormMaxHeight()"
                                    :trigger-action="$filtersTriggerAction"
                                    :width="$getFiltersFormWidth()"
                                />
                            @endif

                            @if ($hasColumnToggleDropdown)
                                <x-filament-tree-table::column-toggle.dropdown
                                    :form="$getColumnToggleForm()"
                                    :max-height="$getColumnToggleFormMaxHeight()"
                                    :trigger-action="$toggleColumnsTriggerAction"
                                    :width="$getColumnToggleFormWidth()"
                                />
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if ($isSelectionEnabled && $isLoaded)
            <x-filament-tree-table::selection.indicator
                :all-selectable-records-count="$allSelectableRecordsCount"
                :colspan="$columnsCount"
                x-bind:hidden="! selectedRecords.length"
                x-show="selectedRecords.length"
            />
        @endif

        @if (count($filterIndicators))
            <x-filament-tree-table::filters.indicators
                :indicators="$filterIndicators"
            />
        @endif

        <div
            @if ($pollingInterval = $getPollingInterval())
                wire:poll.{{ $pollingInterval }}
            @endif
            @class([
                'fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10',
                '!border-t-0' => ! $hasHeader,
            ])
        >
            @if (($content || $hasColumnsLayout) && ($records !== null) && count($records))
                @if ($content)
                    {{ $content->with(['records' => $records]) }}
                @else
                    <x-filament::grid
                        :default="$contentGrid['default'] ?? 1"
                        :sm="$contentGrid['sm'] ?? null"
                        :md="$contentGrid['md'] ?? null"
                        :lg="$contentGrid['lg'] ?? null"
                        :xl="$contentGrid['xl'] ?? null"
                        :two-xl="$contentGrid['2xl'] ?? null"
                        x-on:end.stop="$wire.reorderTable($event.target.sortable.toArray())"
                        x-sortable
                        @class([
                            'fi-ta-content-grid gap-4 p-4 sm:px-6' => $contentGrid,
                            'pt-0' => $contentGrid && $this->getTableGrouping(),
                            'gap-y-px bg-gray-200 dark:bg-white/5' => ! $contentGrid,
                        ])
                    >
                        @php
                            $previousRecord = null;
                            $previousRecordGroupKey = null;
                            $previousRecordGroupTitle = null;
                        @endphp

                        @foreach ($records as $record)
                            @php
                                $recordAction = $getRecordAction($record);
                                $recordKey = $getRecordKey($record);
                                $recordUrl = $getRecordUrl($record);
                                $recordGroupKey = $group?->getKey($record);
                                $recordGroupTitle = $group?->getTitle($record);

                                $collapsibleColumnsLayout?->record($record);
                                $hasCollapsibleColumnsLayout = (bool) $collapsibleColumnsLayout?->isVisible();
                            @endphp

                            <div
                                @if ($hasCollapsibleColumnsLayout)
                                    x-data="{ isCollapsed: @js($collapsibleColumnsLayout->isCollapsed()) }"
                                x-init="$dispatch('collapsible-table-row-initialized')"
                                x-bind:class="isCollapsed && 'fi-collapsed'"
                                x-on:collapse-all-table-rows.window="isCollapsed = true"
                                x-on:expand-all-table-rows.window="isCollapsed = false"
                                @endif
                                wire:key="{{ $this->getId() }}.treeTable.records.{{ $recordKey }}"
                                @class([
                                    'fi-ta-record relative h-full bg-white transition duration-75 dark:bg-gray-900',
                                    'hover:bg-gray-50 dark:hover:bg-white/5' => ($recordUrl || $recordAction) && (! $contentGrid),
                                    'hover:bg-gray-50 dark:hover:bg-white/10 dark:hover:ring-white/20' => ($recordUrl || $recordAction) && $contentGrid,
                                    'rounded-xl shadow-sm ring-1 ring-gray-950/5' => $contentGrid,
                                    ...$getRecordClasses($record),
                                ])
                                x-bind:class="{
                                    'hidden':
                                        {{ $group?->isCollapsible() ? 'true' : 'false' }} &&
                                        isGroupCollapsed('{{ $recordGroupTitle }}'),
                                    {{ ($contentGrid ? '\'bg-gray-50 dark:bg-white/10 dark:ring-white/20\'' : '\'bg-gray-50 dark:bg-white/5 before:absolute before:start-0 before:inset-y-0 before:w-0.5 before:bg-primary-600 dark:before:bg-primary-500\'') . ': isRecordSelected(\'' . $recordKey . '\')' }},
                                    {{ $contentGrid ? '\'bg-white dark:bg-white/5 dark:ring-white/10\': ! isRecordSelected(\'' . $recordKey . '\')' : '\'\':\'\'' }},
                                }"
                            >
                                @php
                                    $hasItemBeforeRecordContent = ($isSelectionEnabled && $isRecordSelectable($record));
                                    $isRecordCollapsible = $hasCollapsibleColumnsLayout;
                                    $hasItemAfterRecordContent = $isRecordCollapsible;
                                    $recordHasActions = count($actions);

                                    $recordContentHorizontalPaddingClasses = \Illuminate\Support\Arr::toCssClasses([
                                        'ps-3' => (! $contentGrid) && $hasItemBeforeRecordContent,
                                        'ps-4 sm:ps-6' => (! $contentGrid) && (! $hasItemBeforeRecordContent),
                                        'pe-3' => (! $contentGrid) && $hasItemAfterRecordContent,
                                        'pe-4 sm:pe-6 md:pe-3' => (! $contentGrid) && (! $hasItemAfterRecordContent),
                                        'ps-2' => $contentGrid && $hasItemBeforeRecordContent,
                                        'ps-4' => $contentGrid && (! $hasItemBeforeRecordContent),
                                        'pe-2' => $contentGrid && $hasItemAfterRecordContent,
                                        'pe-4' => $contentGrid && (! $hasItemAfterRecordContent),
                                    ]);

                                    $recordActionsClasses = \Illuminate\Support\Arr::toCssClasses([
                                        'md:ps-3' => (! $contentGrid),
                                        'ps-3' => (! $contentGrid) && $hasItemBeforeRecordContent,
                                        'ps-4 sm:ps-6' => (! $contentGrid) && (! $hasItemBeforeRecordContent),
                                        'pe-3' => (! $contentGrid) && $hasItemAfterRecordContent,
                                        'pe-4 sm:pe-6' => (! $contentGrid) && (! $hasItemAfterRecordContent),
                                        'ps-2' => $contentGrid && $hasItemBeforeRecordContent,
                                        'ps-4' => $contentGrid && (! $hasItemBeforeRecordContent),
                                        'pe-2' => $contentGrid && $hasItemAfterRecordContent,
                                        'pe-4' => $contentGrid && (! $hasItemAfterRecordContent),
                                    ]);
                                @endphp

                                <div
                                    @class([
                                        'flex items-center',
                                        'ps-1 sm:ps-3' => (! $contentGrid) && $hasItemBeforeRecordContent,
                                        'pe-1 sm:pe-3' => (! $contentGrid) && $hasItemAfterRecordContent,
                                        'ps-1' => $contentGrid && $hasItemBeforeRecordContent,
                                        'pe-1' => $contentGrid && $hasItemAfterRecordContent,
                                    ])
                                >
                                    @if ($isSelectionEnabled && $isRecordSelectable($record))
                                        <x-filament-tree-table::selection.checkbox
                                            :label="__('filament-tree-table::table.fields.bulk_select_record.label', ['key' => $recordKey])"
                                            :value="$recordKey"
                                            x-model="selectedRecords"
                                            class="fi-ta-record-checkbox mx-3 my-4"
                                        />
                                    @endif

                                    @php
                                        $recordContentClasses = \Illuminate\Support\Arr::toCssClasses([
                                            $recordContentHorizontalPaddingClasses,
                                            'block w-full',
                                        ]);
                                    @endphp

                                    <div
                                        @class([
                                            'flex w-full flex-col gap-y-3 py-4',
                                            'md:flex-row md:items-center' => ! $contentGrid,
                                        ])
                                    >
                                        <div class="flex-1">
                                            @if ($recordUrl)
                                                <a
                                                    href="{{ $recordUrl }}"
                                                    class="{{ $recordContentClasses }}"
                                                >
                                                    <x-filament-tree-table::columns.layout
                                                        :components="$getColumnsLayout()"
                                                        :record="$record"
                                                        :record-key="$recordKey"
                                                        :row-loop="$loop"
                                                    />
                                                </a>
                                            @elseif ($recordAction)
                                                @php
                                                    $recordWireClickAction = $getAction($recordAction)
                                                        ? "mountTreeTableAction('{$recordAction}', '{$recordKey}')"
                                                        : $recordWireClickAction = "{$recordAction}('{$recordKey}')";
                                                @endphp

                                                <button
                                                    type="button"
                                                    wire:click="{{ $recordWireClickAction }}"
                                                    wire:loading.attr="disabled"
                                                    wire:target="{{ $recordWireClickAction }}"
                                                    class="{{ $recordContentClasses }}"
                                                >
                                                    <x-filament-tree-table::columns.layout
                                                        :components="$getColumnsLayout()"
                                                        :record="$record"
                                                        :record-key="$recordKey"
                                                        :row-loop="$loop"
                                                    />
                                                </button>
                                            @else
                                                <div
                                                    class="{{ $recordContentClasses }}"
                                                >
                                                    <x-filament-tree-table::columns.layout
                                                        :components="$getColumnsLayout()"
                                                        :record="$record"
                                                        :record-key="$recordKey"
                                                        :row-loop="$loop"
                                                    />
                                                </div>
                                            @endif

                                            @if ($hasCollapsibleColumnsLayout)
                                                <div
                                                    x-collapse
                                                    x-show="! isCollapsed"
                                                    class="{{ $recordContentHorizontalPaddingClasses }} mt-3"
                                                >
                                                    {{ $collapsibleColumnsLayout->viewData(['recordKey' => $recordKey]) }}
                                                </div>
                                            @endif
                                        </div>

                                        @if ($recordHasActions)
                                            <x-filament-tree-table::actions
                                                :actions="$actions"
                                                :alignment="(! $contentGrid) ? 'start md:end' : Alignment::Start"
                                                :record="$record"
                                                wrap="-sm"
                                                :class="$recordActionsClasses"
                                            />
                                        @endif
                                    </div>

                                    @if ($isRecordCollapsible)
                                        <x-filament::icon-button
                                            color="gray"
                                            icon-alias="tables::columns.collapse-button"
                                            icon="heroicon-m-chevron-down"
                                            x-on:click="isCollapsed = ! isCollapsed"
                                            class="mx-1 my-2 shrink-0"
                                            x-bind:class="{ 'rotate-180': isCollapsed }"
                                        />
                                    @endif
                                </div>
                            </div>

                            @php
                                $previousRecordGroupKey = $recordGroupKey;
                                $previousRecordGroupTitle = $recordGroupTitle;
                                $previousRecord = $record;
                            @endphp
                        @endforeach
                    </x-filament::grid>
                @endif

                @if (($content || $hasColumnsLayout) && $contentFooter)
                    {{
                        $contentFooter->with([
                            'columns' => $columns,
                            'records' => $records,
                        ])
                    }}
                @endif

            @elseif (($records !== null) && count($records))
                <x-filament-tree-table::table :reorderable="$isReorderable">
                    <x-slot name="header">

                        @if (count($actions) && $actionsPosition === ActionsPosition::BeforeCells)
                            @if ($actionsColumnLabel)
                                <x-filament-tree-table::header-cell>
                                    {{ $actionsColumnLabel }}
                                </x-filament-tree-table::header-cell>
                            @else
                                <th class="w-1"></th>
                            @endif
                        @endif

                        @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells)
                            <x-filament-tree-table::selection.cell tag="th">
                                <x-filament-tree-table::selection.checkbox
                                    :label="__('filament-tree-table::table.fields.bulk_select_page.label')"
                                    x-bind:checked="
                                            const recordsOnPage = getRecordsOnPage()

                                            if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                                $el.checked = true

                                                return 'checked'
                                            }

                                            $el.checked = false

                                            return null
                                        "
                                    x-on:click="toggleSelectRecordsOnPage"
                                />
                            </x-filament-tree-table::selection.cell>
                        @endif

                        @if (count($actions) && $actionsPosition === ActionsPosition::BeforeColumns)
                            @if ($actionsColumnLabel)
                                <x-filament-tree-table::header-cell>
                                    {{ $actionsColumnLabel }}
                                </x-filament-tree-table::header-cell>
                            @else
                                <th class="w-1"></th>
                            @endif
                        @endif


                        @foreach ($columns as $column)
                            <x-filament-tree-table::header-cell
                                :actively-sorted="$getSortColumn() === $column->getName()"
                                :alignment="$column->getAlignment()"
                                :name="$column->getName()"
                                :sortable="$column->isSortable()"
                                :sort-direction="$getSortDirection()"
                                :wrap="$column->isHeaderWrapped()"
                                :attributes="
                                    prepare_inherited_attributes($column->getExtraHeaderAttributeBag())
                                        ->class([
                                            'fi-table-header-cell-' . str($column->getName())->camel()->kebab(),
                                            $getHiddenClasses($column),
                                        ])
                                "
                            >
                                {{ $column->getLabel() }}
                            </x-filament-tree-table::header-cell>
                        @endforeach

                        @if (! $isReordering)
                            @if (count($actions) && $actionsPosition === ActionsPosition::AfterColumns)
                                @if ($actionsColumnLabel)
                                    <x-filament-tree-table::header-cell
                                        :alignment="Alignment::Right"
                                    >
                                        {{ $actionsColumnLabel }}
                                    </x-filament-tree-table::header-cell>
                                @else
                                    <th class="w-1"></th>
                                @endif
                            @endif

                            @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells)
                                <x-filament-tree-table::selection.cell tag="th">
                                    <x-filament-tree-table::selection.checkbox
                                        :label="__('filament-tree-table::table.fields.bulk_select_page.label')"
                                        x-bind:checked="
                                            const recordsOnPage = getRecordsOnPage()

                                            if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                                $el.checked = true

                                                return 'checked'
                                            }

                                            $el.checked = false

                                            return null
                                        "
                                        x-on:click="toggleSelectRecordsOnPage"
                                    />
                                </x-filament-tree-table::selection.cell>
                            @endif

                            @if (count($actions) && $actionsPosition === ActionsPosition::AfterCells)
                                @if ($actionsColumnLabel)
                                    <x-filament-tree-table::header-cell
                                        :alignment="Alignment::Right"
                                    >
                                        {{ $actionsColumnLabel }}
                                    </x-filament-tree-table::header-cell>
                                @else
                                    <th class="w-1"></th>
                                @endif
                            @endif
                        @endif
                    </x-slot>

                    @if ($isColumnSearchVisible)
                        <x-filament-tree-table::row>
                            @if ($isReordering)
                                <td></td>
                            @else
                                @if (count($actions) && in_array($actionsPosition, [ActionsPosition::BeforeCells, ActionsPosition::BeforeColumns]))
                                    <td></td>
                                @endif

                                @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells)
                                    <td></td>
                                @endif
                            @endif

                            @foreach ($columns as $column)
                                <x-filament-tree-table::cell
                                    @class([
                                        'fi-table-individual-search-cell-' . str($column->getName())->camel()->kebab(),
                                        'px-3 py-2',
                                    ])
                                >
                                    @if ($column->isIndividuallySearchable())
                                        <x-filament-tree-table::search-field
                                            wire-model="tableColumnSearches.{{ $column->getName() }}"
                                        />
                                    @endif
                                </x-filament-tree-table::cell>
                            @endforeach

                            @if (! $isReordering)
                                @if (count($actions) && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells]))
                                    <td></td>
                                @endif

                                @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells)
                                    <td></td>
                                @endif
                            @endif
                        </x-filament-tree-table::row>
                    @endif

                    @if (($records !== null) && count($records))
                        @php
                            $previousRecord = null;
                            $previousRecordGroupKey = null;
                            $previousRecordGroupTitle = null;
                        @endphp

                        @foreach ($records as $record)
                            @php
                                $recordAction = $getRecordAction($record);
                                $recordKey = $getRecordKey($record);
                                $recordUrl = $getRecordUrl($record);
                                $recordGroupKey = $group?->getKey($record);
                                $recordGroupTitle = $group?->getTitle($record);
                            @endphp

                            @php
                                $previousRecord = $record;
                                $previousRecordGroupKey = $recordGroupKey;
                                $previousRecordGroupTitle = $recordGroupTitle;
                            @endphp
                        @endforeach

                        @if ($contentFooter)
                            <x-slot name="footer">
                                {{
                                    $contentFooter->with([
                                        'columns' => $columns,
                                        'records' => $records,
                                    ])
                                }}
                            </x-slot>
                        @endif
                    @endif
                </x-filament-tree-table::table>
            @elseif ($records === null)
                <div class="h-32"></div>
            @elseif ($emptyState = $getEmptyState())
                {{ $emptyState }}
            @else
                <tr>
                    <td colspan="{{ $columnsCount }}">
                        <x-filament-tree-table::empty-state
                            :actions="$getEmptyStateActions()"
                            :description="$getEmptyStateDescription()"
                            :heading="$getEmptyStateHeading()"
                            :icon="$getEmptyStateIcon()"
                        />
                    </td>
                </tr>
            @endif
        </div>

        @if ($records instanceof Paginator && ((! ($records instanceof LengthAwarePaginator)) || $records->total()))
            <x-filament::pagination
                :page-options="$getPaginationPageOptions()"
                :paginator="$records"
                class="px-3 py-3 sm:px-6"
            />
        @endif

        @if ($hasFiltersBelowContent)
            <x-filament-tree-table::filters
                :form="$getFiltersForm()"
                class="p-4 sm:px-6"
            />
        @endif
    </x-filament-tree-table::container>

    <!-- humaid -->
    @include('filament-tree-table::modals')
    <!-- humaid -->
</div>
