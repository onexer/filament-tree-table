<?php

namespace Onexer\FilamentTreeTable;

use Filament\Support\Components\ViewComponent;
use Onexer\FilamentTreeTable\Contracts\HasTreeTable;

class TreeTable extends ViewComponent
{
    use TreeTable\Concerns\BelongsToLivewire;
    use TreeTable\Concerns\CanDeferLoading;
    use TreeTable\Concerns\CanPollRecords;
    use TreeTable\Concerns\CanSearchRecords;
    use TreeTable\Concerns\HasActions;
    use TreeTable\Concerns\HasColumns;
    use TreeTable\Concerns\HasContent;
    use TreeTable\Concerns\HasEmptyState;
    use TreeTable\Concerns\HasFilters;
    use TreeTable\Concerns\HasHeader;
    use TreeTable\Concerns\HasHeaderActions;
    use TreeTable\Concerns\HasQuery;
    use TreeTable\Concerns\HasQueryStringIdentifier;
    use TreeTable\Concerns\HasRecordAction;
    use TreeTable\Concerns\HasRecordClasses;
    use TreeTable\Concerns\HasRecords;
    use TreeTable\Concerns\HasRecordUrl;

    public const LOADING_TARGETS = [
        'removeTreeTableFilter',
        'removeTreeTableFilters',
        'resetTreeTableFiltersForm',
        'treeTableColumnSearches',
        'treeTableFilters',
        'treeTableRecordsPerPage',
        'treeTableSearch',
    ];
    public static string $defaultCurrency = 'usd';
    public static string $defaultDateDisplayFormat = 'M j, Y';
    public static string $defaultDateTimeDisplayFormat = 'M j, Y H:i:s';
    public static string $defaultTimeDisplayFormat = 'H:i:s';
    protected string $view = 'filament-tree-table::index';
    protected string $viewIdentifier = 'tree-table';
    protected string $evaluationIdentifier = 'tree-table';

    final public function __construct(HasTreeTable $livewire)
    {
        $this->livewire($livewire);
    }

    public static function make(HasTreeTable $livewire): static
    {
        $static = app(static::class, ['livewire' => $livewire]);
        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->emptyStateDescription(function (TreeTable $treeTable): ?string {
            if (!$treeTable->hasAction('create')) {
                return null;
            }

            return __('filament-tree-table::table.empty.description', [
                'model' => $treeTable->getModelLabel(),
            ]);
        });
    }
}
