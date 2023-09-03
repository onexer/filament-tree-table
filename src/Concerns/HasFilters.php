<?php

namespace Onexer\FilamentTreeTable\Concerns;

use Filament\Forms;
use Filament\Forms\Form;
use Onexer\FilamentTreeTable\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property Form $treeTableFilters
 */
trait HasFilters
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $treeTableFilters = null;

    public function removeTreeTableFilters(): void
    {
        $filters = $this->getTreeTable()->getFilters();

        foreach ($filters as $filterName => $filter) {
            $this->removeTreeTableFilter(
                $filterName,
                shouldTriggerUpdatedFiltersHook: false,
            );
        }

        $this->updatedTreeTableFilters();

        $this->resetTreeTableSearch();
        $this->resetTreeTableColumnSearches();
    }

    public function removeTreeTableFilter(string $filterName, ?string $field = null, bool $shouldTriggerUpdatedFiltersHook = true): void
    {
        $filter           = $this->getTreeTable()->getFilter($filterName);
        $filterResetState = $filter->getResetState();

        $filterFormGroup = $this->getTreeTableFiltersForm()->getComponents()[$filterName] ?? null;
        $filterFields    = $filterFormGroup?->getChildComponentContainer()->getFlatFields();

        if (filled($field) && array_key_exists($field, $filterFields)) {
            $filterFields = [$field => $filterFields[$field]];
        }

        foreach ($filterFields as $fieldName => $field) {
            $state = $field->getState();

            $field->state($filterResetState[$fieldName] ?? match (true) {
                is_array($state) => [],
                is_bool($state) => false,
                default => null,
            });
        }

        if (!$shouldTriggerUpdatedFiltersHook) {
            return;
        }

        $this->updatedTreeTableFilters();
    }

    public function getTreeTableFiltersForm(): Form
    {
        if ((!$this->isCachingForms) && $this->hasCachedForm('treeTableFiltersForm')) {
            return $this->getForm('treeTableFiltersForm');
        }

        return $this->makeForm()
            ->schema($this->getTreeTableFiltersFormSchema())
            ->columns($this->getTreeTable()->getFiltersFormColumns())
            ->model($this->getTreeTable()->getModel())
            ->statePath('treeTableFilters')
            ->live();
    }

    /**
     * @return array<string, Forms\Components\Group>
     */
    public function getTreeTableFiltersFormSchema(): array
    {
        $schema = [];

        foreach ($this->getTreeTable()->getFilters() as $filter) {
            $schema[$filter->getName()] = Forms\Components\Group::make()
                ->schema($filter->getFormSchema())
                ->statePath($filter->getName())
                ->columnSpan($filter->getColumnSpan())
                ->columnStart($filter->getColumnStart())
                ->columns($filter->getColumns());
        }

        return $schema;
    }

    public function updatedTreeTableFilters(): void
    {
        if ($this->getTreeTable()->persistsFiltersInSession()) {
            session()->put(
                $this->getTreeTableFiltersSessionKey(),
                $this->treeTableFilters,
            );
        }

        $this->resetPage();
    }

    public function getTreeTableFiltersSessionKey(): string
    {
        $table = class_basename($this::class);

        return "treeTables.{$table}_filters";
    }

    public function resetTreeTableFiltersForm(): void
    {
        $this->getTreeTableFiltersForm()->fill();

        $this->updatedTreeTableFilters();
    }

    public function getTreeTableFilterState(string $name): ?array
    {
        return $this->getTreeTableFiltersForm()->getRawState()[$this->parseTreeTableFilterName($name)] ?? null;
    }

    public function parseTreeTableFilterName(string $name): string
    {
        if (!class_exists($name)) {
            return $name;
        }

        if (!is_subclass_of($name, BaseFilter::class)) {
            return $name;
        }

        return $name::getDefaultName();
    }

    protected function applyFiltersToTreeTableQuery(Builder $query): Builder
    {
        $data = $this->getTreeTableFiltersForm()->getRawState();

        foreach ($this->getTreeTable()->getFilters() as $filter) {
            $filter->applyToBaseQuery(
                $query,
                $data[$filter->getName()] ?? [],
            );
        }

        return $query->where(function (Builder $query) use ($data) {
            foreach ($this->getTreeTable()->getFilters() as $filter) {
                $filter->apply(
                    $query,
                    $data[$filter->getName()] ?? [],
                );
            }
        });
    }
}
