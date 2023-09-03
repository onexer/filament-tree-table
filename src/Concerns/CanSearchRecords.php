<?php

namespace Onexer\FilamentTreeTable\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

trait CanSearchRecords
{
    /**
     * @var array<string, string | array<string, string | null> | null>
     */
    public array $treeTableColumnSearches = [];

    /**
     * @var ?string
     */
    public $treeTableSearch = '';

    public function hasTreeTableSearch(): bool
    {
        return filled($this->treeTableSearch);
    }

    public function resetTreeTableSearch(): void
    {
        $this->treeTableSearch = '';
        $this->updatedTreeTableSearch();
    }

    public function updatedTreeTableSearch(): void
    {
        if ($this->getTreeTable()->persistsSearchInSession()) {
            session()->put(
                $this->getTreeTableSearchSessionKey(),
                $this->treeTableSearch,
            );
        }

        $this->resetPage();
    }

    public function getTreeTableSearchSessionKey(): string
    {
        $table = class_basename($this::class);

        return "treeTables.{$table}_search";
    }

    public function resetTreeTableColumnSearch(string $column): void
    {
        $this->updatedTreeTableColumnSearches(null, $column);
    }

    /**
     * @param  string | null  $value
     */
    public function updatedTreeTableColumnSearches($value = null, ?string $key = null): void
    {
        if (blank($value) && filled($key)) {
            Arr::forget($this->treeTableColumnSearches, $key);
        }

        if ($this->getTreeTable()->persistsColumnSearchesInSession()) {
            session()->put(
                $this->getTreeTableColumnSearchesSessionKey(),
                $this->treeTableColumnSearches,
            );
        }

        $this->resetPage();
    }

    public function getTreeTableColumnSearchesSessionKey(): string
    {
        $table = class_basename($this::class);

        return "treeTables.{$table}_column_search";
    }

    public function resetTreeTableColumnSearches(): void
    {
        $this->treeTableColumnSearches = [];
        $this->updatedTreeTableColumnSearches();
    }

    public function getTreeTableSearchIndicator(): string
    {
        return __('filament-tree-table::table.fields.search.indicator').': '.$this->getTreeTableSearch();
    }

    /**
     * @return ?string
     */
    public function getTreeTableSearch()
    {
        return $this->treeTableSearch;
    }

    /**
     * @return array<string, string>
     */
    public function getTreeTableColumnSearchIndicators(): array
    {
        $indicators = [];

        foreach ($this->getTreeTable()->getColumns() as $column) {
            if ($column->isHidden()) {
                continue;
            }

            if (!$column->isIndividuallySearchable()) {
                continue;
            }

            $columnName = $column->getName();

            $search = Arr::get($this->treeTableColumnSearches, $columnName);

            if (blank($search)) {
                continue;
            }

            $indicators[$columnName] = "{$column->getLabel()}: {$search}";
        }

        return $indicators;
    }

    /**
     * @return array<string, string | null>
     */
    public function getTreeTableColumnSearches(): array
    {
        // Example input of `$this->tableColumnSearches`:
        // [
        //     'number' => '12345 ',
        //     'customer' => [
        //         'name' => ' john Smith',
        //     ],
        // ]

        // The `$this->tableColumnSearches` array is potentially nested.
        // So, we iterate through it deeply:
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($this->treeTableColumnSearches),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $searches = [];
        $path     = [];

        foreach ($iterator as $key => $value) {
            $path[$iterator->getDepth()] = $key;

            if (is_array($value)) {
                continue;
            }

            // Nested array keys are flattened into `dot.syntax`.
            $searches[implode('.', array_slice($path, 0, $iterator->getDepth() + 1))] = trim($value);
        }

        return $searches;

        // Example output:
        // [
        //     'number' => '12345',
        //     'customer.name' => 'john smith',
        // ]
    }

    protected function applySearchToTreeTableQuery(Builder $query): Builder
    {
        $this->applyColumnSearchesToTreeTableQuery($query);
        $this->applyGlobalSearchToTreeTableQuery($query);

        return $query;
    }

    protected function applyColumnSearchesToTreeTableQuery(Builder $query): Builder
    {
        foreach ($this->getTreeTableColumnSearches() as $column => $search) {
            if (blank($search)) {
                continue;
            }

            $column = $this->getTreeTable()->getColumn($column);

            if (!$column) {
                continue;
            }

            if ($column->isHidden()) {
                continue;
            }

            if (!$column->isIndividuallySearchable()) {
                continue;
            }

            foreach (explode(' ', $search) as $searchWord) {
                $query->where(function (Builder $query) use ($column, $searchWord) {
                    $isFirst = true;

                    $column->applySearchConstraint(
                        $query,
                        $searchWord,
                        $isFirst,
                    );
                });
            }
        }

        return $query;
    }

    protected function applyGlobalSearchToTreeTableQuery(Builder $query): Builder
    {
        $search = trim($this->getTreeTableSearch());

        if (blank($search)) {
            return $query;
        }

        foreach (explode(' ', $search) as $searchWord) {
            $query->where(function (Builder $query) use ($searchWord) {
                $isFirst = true;

                foreach ($this->getTreeTable()->getColumns() as $column) {
                    if ($column->isHidden()) {
                        continue;
                    }

                    if (!$column->isGloballySearchable()) {
                        continue;
                    }

                    $column->applySearchConstraint(
                        $query,
                        $searchWord,
                        $isFirst,
                    );
                }
            });
        }

        return $query;
    }

    /**
     * @param  array<string, string | array<string, string | null> | null>  $searches
     * @return array<string, string | array<string, string | null> | null>
     */
    protected function castTreeTableColumnSearches(array $searches): array
    {
        return array_map(
            fn($search): array|string => is_array($search) ?
                $this->castTreeTableColumnSearches($search) :
                strval($search),
            $searches,
        );
    }
}
