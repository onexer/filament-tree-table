<?php

namespace Onexer\FilamentTreeTable\TreeTable\Concerns;

use Closure;
use Onexer\FilamentTreeTable\Actions\Action;
use Onexer\FilamentTreeTable\Actions\ActionGroup;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use InvalidArgumentException;

trait HasEmptyState
{
    protected View|Htmlable|Closure|null $emptyState = null;

    protected string|Htmlable|Closure|null $emptyStateDescription = null;

    protected string|Htmlable|Closure|null $emptyStateHeading = null;

    protected string|Closure|null $emptyStateIcon = null;

    /**
     * @var array<Action | ActionGroup>
     */
    protected array $emptyStateActions = [];

    public function emptyStateDescription(string|Htmlable|Closure|null $description): static
    {
        $this->emptyStateDescription = $description;

        return $this;
    }

    public function emptyState(View|Htmlable|Closure|null $emptyState): static
    {
        $this->emptyState = $emptyState;

        return $this;
    }

    /**
     * @param  array<Action | ActionGroup> | ActionGroup  $actions
     */
    public function emptyStateActions(array|ActionGroup $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {
            $action->treeTable($this);

            if ($action instanceof ActionGroup) {
                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                $this->mergeCachedFlatActions($flatActions);
            } elseif ($action instanceof Action) {
                $this->cacheAction($action);
            } else {
                throw new InvalidArgumentException('TreeTable empty state actions must be an instance of '.Action::class.' or '.ActionGroup::class.'.');
            }

            $this->emptyStateActions[] = $action;
        }

        return $this;
    }

    public function emptyStateHeading(string|Htmlable|Closure|null $heading): static
    {
        $this->emptyStateHeading = $heading;

        return $this;
    }

    public function emptyStateIcon(string|Closure|null $icon): static
    {
        $this->emptyStateIcon = $icon;

        return $this;
    }

    public function getEmptyState(): View|Htmlable|null
    {
        return $this->evaluate($this->emptyState);
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getEmptyStateActions(): array
    {
        return $this->emptyStateActions;
    }

    public function getEmptyStateDescription(): string|Htmlable|null
    {
        return $this->evaluate($this->emptyStateDescription);
    }

    public function getEmptyStateHeading(): string|Htmlable
    {
        return $this->evaluate($this->emptyStateHeading) ?? __('filament-tree-table::table.empty.heading', [
            'model' => $this->getPluralModelLabel(),
        ]);
    }

    public function getEmptyStateIcon(): string
    {
        return $this->evaluate($this->emptyStateIcon) ?? 'heroicon-o-x-mark';
    }
}
