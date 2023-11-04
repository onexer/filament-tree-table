<?php

namespace Onexer\FilamentTreeTable\Concerns;

use Closure;
use Filament\Forms;
use Onexer\FilamentTreeTable\Actions\Action;
use Onexer\FilamentTreeTable\TreeTable;
use Illuminate\Support\Str;

trait InteractsWithTreeTable
{
    use CanDeferLoading;
    use CanSearchRecords;
    use HasActions;
    use HasTreeColumns;
    use HasFilters;
    use HasRecords;

    /**
     * units chain
     *
     * @var array
     */
    public array $visible_chain = [];
    protected TreeTable $treeTable;
    protected bool $hasTreeTableModalRendered = false;
    protected bool $shouldMountInteractsWithTreeTable = false;

    /**
     * Add new unit to list chain
     *
     * @param  string  $id
     * @noinspection PhpUnused
     */
    public function addToChain(string $id): void
    {
        if (!in_array($id, $this->visible_chain)) {
            $this->visible_chain[] = $id;
        }
    }

    /**
     * check if unit in chain
     *
     * @param  string  $id
     * @return bool
     * @noinspection PhpUnused
     */
    public function inChain(string $id): bool
    {
        return in_array($id, $this->visible_chain);
    }

    /**
     * Remove unit from units list
     *
     * @param  string  $id
     * @noinspection PhpUnused
     */
    public function removeFromChain(string $id): void
    {
        $this->visible_chain = collect($this->visible_chain)->filter(function ($r) use ($id) {
            return !Str::startsWith($r, $id);
        })->toArray();
    }


    public function bootedInteractsWithTreeTable(): void
    {
        $this->treeTable = Action::configureUsing(
            Closure::fromCallable([$this, 'configureTreeTableAction']),
            fn(): TreeTable => $this->treeTable($this->makeTreeTable()),
        );


        $this->cacheForm('treeTableFiltersForm', $this->getTreeTableFiltersForm());

        if (!$this->shouldMountInteractsWithTreeTable) {
            return;
        }

        $shouldPersistFiltersInSession = $this->getTreeTable()->persistsFiltersInSession();
        $filtersSessionKey             = $this->getTreeTableFiltersSessionKey();

        if (!count($this->treeTableFilters ?? [])) {
            $this->treeTableFilters = null;
        }

        if (($this->treeTableFilters === null) && $shouldPersistFiltersInSession && session()->has($filtersSessionKey)) {
            $this->treeTableFilters = [
                ...($this->treeTableFilters ?? []),
                ...(session()->get($filtersSessionKey) ?? []),
            ];
        }

        $this->getTreeTableFiltersForm()->fill($this->treeTableFilters);

        if ($shouldPersistFiltersInSession) {
            session()->put(
                $filtersSessionKey,
                $this->treeTableFilters,
            );
        }

        $shouldPersistSearchInSession = $this->getTreeTable()->persistsSearchInSession();
        $searchSessionKey             = $this->getTreeTableSearchSessionKey();

        if (blank($this->treeTableSearch) && $shouldPersistSearchInSession && session()->has($searchSessionKey)) {
            $this->treeTableSearch = session()->get($searchSessionKey);
        }

        $this->treeTableSearch = strval($this->treeTableSearch);

        if ($shouldPersistSearchInSession) {
            session()->put(
                $searchSessionKey,
                $this->treeTableSearch,
            );
        }

        $shouldPersistColumnSearchesInSession = $this->getTreeTable()->persistsColumnSearchesInSession();
        $columnSearchesSessionKey             = $this->getTreeTableColumnSearchesSessionKey();

        if ((blank($this->treeTableColumnSearches) || ($this->treeTableColumnSearches === [])) && $shouldPersistColumnSearchesInSession && session()->has($columnSearchesSessionKey)) {
            $this->treeTableColumnSearches = session()->get($columnSearchesSessionKey) ?? [];
        }

        $this->treeTableColumnSearches = $this->castTreeTableColumnSearches(
            $this->treeTableColumnSearches ?? [],
        );

        if ($shouldPersistColumnSearchesInSession) {
            session()->put(
                $columnSearchesSessionKey,
                $this->treeTableColumnSearches,
            );
        }
    }

    public function getTreeTable(): TreeTable
    {
        return $this->treeTable;
    }

    public function mountInteractsWithTreeTable(): void
    {
        $this->shouldMountInteractsWithTreeTable = true;
    }

    public function getIdentifiedTableQueryStringPropertyNameFor(string $property): string
    {
        if (filled($identifier = $this->getTreeTable()->getQueryStringIdentifier())) {
            return $identifier.ucfirst($property);
        }

        return $property;
    }

    public function getActiveTableLocale(): ?string
    {
        return null;
    }

    public function resetPage(): void
    {
    }

    protected function makeTreeTable(): TreeTable
    {
        return TreeTable::make($this);
    }

    protected function getTableQueryStringIdentifier(): ?string
    {
        return null;
    }

    /**
     * @return array<string, Forms\Form>
     */
    protected function getInteractsWithTreeTableForms(): array
    {
        return [
            'mountedTreeTableActionForm' => $this->getMountedTreeTableActionForm(),
        ];
    }
}
