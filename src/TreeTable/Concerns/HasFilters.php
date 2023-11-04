<?php

namespace Onexer\FilamentTreeTable\TreeTable\Concerns;

use Closure;
use Filament\Forms\Form;
use Filament\Support\Enums\ActionSize;
use Onexer\FilamentTreeTable\Actions\Action;
use Onexer\FilamentTreeTable\Enums\FiltersLayout;
use Onexer\FilamentTreeTable\Filters\BaseFilter;

trait HasFilters
{
    /**
     * @var array<string, BaseFilter>
     */
    protected array $filters = [];

    /**
     * @var int | array<string, int | null> | Closure
     */
    protected int|array|Closure $filtersFormColumns = 1;

    protected string|Closure|null $filtersFormMaxHeight = null;

    protected string|Closure|null $filtersFormWidth = null;

    protected FiltersLayout|Closure|null $filtersLayout = null;

    protected ?Closure $modifyFiltersTriggerActionUsing = null;

    protected bool|Closure|null $persistsFiltersInSession = false;

    protected bool|Closure $shouldDeselectAllRecordsWhenFiltered = true;

    public function deselectAllRecordsWhenFiltered(bool|Closure $condition = true): static
    {
        $this->shouldDeselectAllRecordsWhenFiltered = $condition;

        return $this;
    }

    /**
     * @param  array<BaseFilter>  $filters
     */
    public function filters(array $filters, FiltersLayout|string|Closure|null $layout = null): static
    {
        foreach ($filters as $filter) {
            $filter->treeTable($this);

            $this->filters[$filter->getName()] = $filter;
        }

        if ($layout) {
            $this->filtersLayout($layout);
        }

        return $this;
    }

    public function filtersLayout(FiltersLayout|Closure|null $filtersLayout): static
    {
        $this->filtersLayout = $filtersLayout;

        return $this;
    }

    /**
     * @param  int | array<string, int | null> | Closure  $columns
     */
    public function filtersFormColumns(int|array|Closure $columns): static
    {
        $this->filtersFormColumns = $columns;

        return $this;
    }

    public function filtersFormMaxHeight(string|Closure|null $height): static
    {
        $this->filtersFormMaxHeight = $height;

        return $this;
    }

    public function filtersFormWidth(string|Closure|null $width): static
    {
        $this->filtersFormWidth = $width;

        return $this;
    }

    public function filtersTriggerAction(?Closure $callback): static
    {
        $this->modifyFiltersTriggerActionUsing = $callback;

        return $this;
    }

    public function persistFiltersInSession(bool|Closure $condition = true): static
    {
        $this->persistsFiltersInSession = $condition;

        return $this;
    }

    public function getFilter(string $name): ?BaseFilter
    {
        return $this->getFilters()[$name] ?? null;
    }

    /**
     * @return array<string, BaseFilter>
     */
    public function getFilters(): array
    {
        return array_filter(
            $this->filters,
            fn(BaseFilter $filter): bool => $filter->isVisible(),
        );
    }

    public function getFiltersForm(): Form
    {
        return $this->getLivewire()->getTreeTableFiltersForm();
    }

    public function getFiltersTriggerAction(): Action
    {
        $action = Action::make('openFilters')
            ->label(__('filament-tree-table::table.actions.filter.label'))
            ->iconButton()
            ->icon('heroicon-m-funnel')
            ->color('gray')
            ->livewireClickHandlerEnabled(false)
            ->treeTable($this);

        if ($this->modifyFiltersTriggerActionUsing) {
            $action = $this->evaluate($this->modifyFiltersTriggerActionUsing, [
                'action' => $action,
            ]) ?? $action;
        }

        if ($action->getView() === Action::BUTTON_VIEW) {
            $action->defaultSize(ActionSize::Small);
        }

        return $action;
    }

    public function getFiltersFormMaxHeight(): ?string
    {
        return $this->evaluate($this->filtersFormMaxHeight);
    }

    public function getFiltersFormWidth(): ?string
    {
        return $this->evaluate($this->filtersFormWidth) ?? match ($this->getFiltersFormColumns()) {
            2 => '2xl',
            3 => '4xl',
            4 => '6xl',
            default => null,
        };
    }

    /**
     * @return int | array<string, int | null>
     */
    public function getFiltersFormColumns(): int|array
    {
        return $this->evaluate($this->filtersFormColumns) ?? match ($this->getFiltersLayout()) {
            FiltersLayout::AboveContent, FiltersLayout::BelowContent => [
                'sm'  => 2,
                'lg'  => 3,
                'xl'  => 4,
                '2xl' => 5,
            ],
            default => 1,
        };
    }

    public function getFiltersLayout(): FiltersLayout
    {
        return $this->evaluate($this->filtersLayout) ?? FiltersLayout::Dropdown;
    }

    public function isFilterable(): bool
    {
        return (bool) count($this->getFilters());
    }

    public function persistsFiltersInSession(): bool
    {
        return (bool) $this->evaluate($this->persistsFiltersInSession);
    }
}
