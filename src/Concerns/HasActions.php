<?php

namespace Onexer\FilamentTreeTable\Concerns;

use Filament\Forms\Form;
use Filament\Support\Exceptions\Cancel;
use Filament\Support\Exceptions\Halt;
use Onexer\FilamentTreeTable\Actions\Action;
use Onexer\FilamentTreeTable\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Model;
use function Livewire\store;

/**
 * @property Form $mountedTreeTableActionForm
 */
trait HasActions
{
    /**
     * @var array<string> | null
     */
    public ?array $mountedTreeTableActions = [];

    /**
     * @var array<string, array<string, mixed>> | null
     */
    public ?array $mountedTreeTableActionsData = [];

    /**
     * @var int | string | null
     */
    public $mountedTreeTableActionRecord = null;

    protected ?Model $cachedMountedTreeTableActionRecord = null;

    protected int|string|null $cachedMountedTreeTableActionRecordKey = null;

    public function mountTreeTableAction(string $name, ?string $record = null): mixed
    {
        $this->mountedTreeTableActions[]     = $name;
        $this->mountedTreeTableActionsData[] = [];

        if (count($this->mountedTreeTableActions) === 1) {
            $this->mountedTreeTableActionRecord($record);
        }

        $action = $this->getMountedTreeTableAction();

        if (!$action) {
            $this->unmountTreeTableAction();

            return null;
        }

        if (filled($record) && ($action->getRecord() === null)) {
            $this->unmountTreeTableAction();

            return null;
        }

        if ($action->isDisabled()) {
            $this->unmountTreeTableAction();

            return null;
        }

        $this->cacheMountedTreeTableActionForm();

        try {
            $hasForm = $this->mountedTreeTableActionHasForm();

            if ($hasForm) {
                $action->callBeforeFormFilled();
            }

            $action->mount([
                'form' => $this->getMountedTreeTableActionForm(),
            ]);

            if ($hasForm) {
                $action->callAfterFormFilled();
            }
        } catch (Halt $exception) {
            return null;
        } catch (Cancel $exception) {
            $this->unmountTreeTableAction(shouldCancelParentActions: false);

            return null;
        }

        if (!$this->mountedTreeTableActionShouldOpenModal()) {
            return $this->callMountedTreeTableAction();
        }

        $this->resetErrorBag();

        $this->openTreeTableActionModal();

        return null;
    }

    public function getMountedTreeTableAction(): ?Action
    {
        if (!count($this->mountedTreeTableActions ?? [])) {
            return null;
        }

        return $this->getTreeTable()->getAction($this->mountedTreeTableActions);
    }

    public function unmountTreeTableAction(bool $shouldCancelParentActions = true): void
    {
        $action = $this->getMountedTreeTableAction();

        if (!($shouldCancelParentActions && $action)) {
            $this->popMountedTreeTableAction();
        } elseif ($action->shouldCancelAllParentActions()) {
            $this->resetMountedTreeTableActionProperties();
        } else {
            $parentActionToCancelTo = $action->getParentActionToCancelTo();

            while (true) {
                $recentlyClosedParentAction = $this->popMountedTreeTableAction();

                if (
                    blank($parentActionToCancelTo) ||
                    ($recentlyClosedParentAction === $parentActionToCancelTo)
                ) {
                    break;
                }
            }
        }

        if (!count($this->mountedTreeTableActions)) {
            $this->closeTreeTableActionModal();

            $action?->record(null);
            $this->mountedTreeTableActionRecord(null);

            return;
        }

        $this->cacheMountedTreeTableActionForm();

        $this->resetErrorBag();

        $this->openTreeTableActionModal();
    }

    public function getMountedTreeTableActionForm(): ?Form
    {
        $action = $this->getMountedTreeTableAction();

        if (!$action) {
            return null;
        }

        if ((!$this->isCachingForms) && $this->hasCachedForm('mountedTreeTableActionForm')) {
            return $this->getForm('mountedTreeTableActionForm');
        }

        return $action->getForm(
            $this->makeForm()
                ->model($this->getMountedTreeTableActionRecord() ?? $this->getTreeTable()->getModel())
                ->statePath('mountedTreeTableActionsData.'.array_key_last($this->mountedTreeTableActionsData))
                ->operation(implode('.', $this->mountedTreeTableActions)),
        );
    }

    public function getMountedTreeTableActionRecord(): ?Model
    {
        $recordKey = $this->getMountedTreeTableActionRecordKey();

        if ($this->cachedMountedTreeTableActionRecord && ($this->cachedMountedTreeTableActionRecordKey === $recordKey)) {
            return $this->cachedMountedTreeTableActionRecord;
        }

        $this->cachedMountedTreeTableActionRecordKey = $recordKey;

        return $this->cachedMountedTreeTableActionRecord = $this->getTreeTableRecord($recordKey);
    }

    public function getMountedTreeTableActionRecordKey(): int|string|null
    {
        return $this->mountedTreeTableActionRecord;
    }

    public function mountedTreeTableActionHasForm(): bool
    {
        return (bool) count($this->getMountedTreeTableActionForm()?->getComponents() ?? []);
    }

    public function mountedTreeTableActionShouldOpenModal(): bool
    {
        $action = $this->getMountedTreeTableAction();

        if ($action->isModalHidden()) {
            return false;
        }

        return $action->getModalDescription() ||
            $action->getModalContent() ||
            $action->getModalContentFooter() ||
            $action->getInfolist() ||
            $this->mountedTreeTableActionHasForm();
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function callMountedTreeTableAction(array $arguments = []): mixed
    {
        $action = $this->getMountedTreeTableAction();

        if (!$action) {
            return null;
        }

        if (filled($this->mountedTreeTableActionRecord) && ($action->getRecord() === null)) {
            return null;
        }

        if ($action->isDisabled()) {
            return null;
        }

        $action->arguments($arguments);

        $form = $this->getMountedTreeTableActionForm();

        $result = null;

        try {
            if ($this->mountedTreeTableActionHasForm()) {
                $action->callBeforeFormValidated();

                $action->formData($form->getState());

                $action->callAfterFormValidated();
            }

            $action->callBefore();

            $result = $action->call([
                'form' => $form,
            ]);

            $result = $action->callAfter() ?? $result;
        } catch (Halt $exception) {
            return null;
        } catch (Cancel $exception) {
        }

        $action->resetArguments();
        $action->resetFormData();

        if (store($this)->has('redirect')) {
            return $result;
        }

        $this->unmountTreeTableAction();

        return $result;
    }

    public function mountedTreeTableActionRecord(int|string|null $record): void
    {
        $this->mountedTreeTableActionRecord = $record;
    }

    protected function popMountedTreeTableAction(): ?string
    {
        try {
            return array_pop($this->mountedTreeTableActions);
        } finally {
            array_pop($this->mountedTreeTableActionsData);
        }
    }

    protected function resetMountedTreeTableActionProperties(): void
    {
        $this->mountedTreeTableActions     = [];
        $this->mountedTreeTableActionsData = [];
    }

    protected function closeTreeTableActionModal(): void
    {
        $this->dispatch('close-modal', id: "{$this->getId()}-tree-table-action");
    }

    protected function cacheMountedTreeTableActionForm(): void
    {
        $this->cacheForm(
            'mountedTreeTableActionForm',
            fn() => $this->getMountedTreeTableActionForm(),
        );
    }

    protected function openTreeTableActionModal(): void
    {
        $this->dispatch('open-modal', id: "{$this->getId()}-tree-table-action");
    }

    protected function configureTreeTableAction(Action $action): void
    {
    }
}
