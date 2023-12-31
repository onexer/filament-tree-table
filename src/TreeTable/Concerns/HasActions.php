<?php

namespace Onexer\FilamentTreeTable\TreeTable\Concerns;

use Closure;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Onexer\FilamentTreeTable\Actions\Action;
use Onexer\FilamentTreeTable\Actions\ActionGroup;
use Onexer\FilamentTreeTable\Enums\ActionsPosition;

trait HasActions
{
    /**
     * @var array<Action | ActionGroup>
     */
    protected array $actions = [];

    /**
     * @var array<string, Action>
     */
    protected array $flatActions = [];

    protected string|Closure|null $actionsColumnLabel = null;

    protected string|Closure|null $actionsAlignment = null;

    protected ActionsPosition|Closure|null $actionsPosition = null;

    /**
     * @param  array<Action | ActionGroup> | ActionGroup  $actions
     */
    public function actions(array|ActionGroup $actions, ActionsPosition|string|Closure|null $position = null): static
    {
        foreach (Arr::wrap($actions) as $action) {
            $action->treeTable($this);

            if ($action instanceof ActionGroup) {
                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                if (!$action->getDropdownPlacement()) {
                    $action->dropdownPlacement('bottom-end');
                }

                $this->mergeCachedFlatActions($flatActions);
            } elseif ($action instanceof Action) {
                $action->defaultSize(ActionSize::Small);
                $action->defaultView($action::LINK_VIEW);

                $this->cacheAction($action);
            } else {
                throw new InvalidArgumentException('TreeTable actions must be an instance of '.Action::class.' or '.ActionGroup::class.'.');
            }

            $this->actions[] = $action;
        }

        $position && $this->actionsPosition($position);

        return $this;
    }

    /**
     * @return array<string, Action>
     */
    public function getFlatActions(): array
    {
        return $this->flatActions;
    }

    /**
     * @param  array<string, Action>  $actions
     */
    protected function mergeCachedFlatActions(array $actions): void
    {
        $this->flatActions = [
            ...$this->flatActions,
            ...$actions,
        ];
    }

    protected function cacheAction(Action $action): void
    {
        $this->flatActions[$action->getName()] = $action;
    }

    public function actionsPosition(ActionsPosition|Closure|null $position = null): static
    {
        $this->actionsPosition = $position;

        return $this;
    }

    public function actionsColumnLabel(string|Closure|null $label): static
    {
        $this->actionsColumnLabel = $label;

        return $this;
    }

    public function actionsAlignment(string|Closure|null $alignment = null): static
    {
        $this->actionsAlignment = $alignment;

        return $this;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param  string | array<string>  $name
     */
    public function getAction(string|array $name): ?Action
    {
        if (is_string($name) && str($name)->contains('.')) {
            $name = explode('.', $name);
        }

        if (is_array($name)) {
            $firstName        = array_shift($name);
            $modalActionNames = $name;

            $name = $firstName;
        }

        $mountedRecord = $this->getLivewire()->getMountedTreeTableActionRecord();

        $action = $this->getFlatActions()[$name] ?? null;

        if (!$action) {
            return null;
        }

        return $this->getMountableModalActionFromAction(
            $action->record($mountedRecord),
            modalActionNames: $modalActionNames ?? [],
            parentActionName: $name,
            mountedRecord: $mountedRecord,
        );
    }

    /**
     * @param  array<string>  $modalActionNames
     */
    protected function getMountableModalActionFromAction(Action $action, array $modalActionNames, string $parentActionName, ?Model $mountedRecord = null): ?Action
    {
        foreach ($modalActionNames as $modalActionName) {
            $action = $action->getMountableModalAction($modalActionName);

            if (!$action) {
                return null;
            }

            if ($action instanceof Action) {
                $action->record($mountedRecord);
            }

            $parentActionName = $modalActionName;
        }

        if (!$action instanceof Action) {
            return null;
        }

        return $action;
    }

    public function hasAction(string $name): bool
    {
        return array_key_exists($name, $this->getFlatActions());
    }

    public function getActionsPosition(): ActionsPosition
    {
        $position = $this->evaluate($this->actionsPosition);

        if ($position) {
            return $position;
        }

        if (!($this->getContentGrid() || $this->hasColumnsLayout())) {
            return ActionsPosition::AfterColumns;
        }

        return ActionsPosition::AfterContent;
    }

    public function getActionsAlignment(): ?string
    {
        return $this->evaluate($this->actionsAlignment);
    }

    public function getActionsColumnLabel(): ?string
    {
        return $this->evaluate($this->actionsColumnLabel);
    }
}
