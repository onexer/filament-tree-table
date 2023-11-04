<?php

namespace Onexer\FilamentTreeTable\Actions;

use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Form;
use Onexer\FilamentTreeTable\Contracts\HasTreeTable;
use Onexer\FilamentTreeTable\TreeTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Arr;

class CreateAction extends Action
{
    use CanCustomizeProcess;

    protected bool|Closure $canCreateAnother = true;

    public static function getDefaultName(): ?string
    {
        return 'create';
    }

    /**
     * @deprecated Use `createAnother()` instead.
     */
    public function disableCreateAnother(bool|Closure $condition = true): static
    {
        $this->createAnother(fn(CreateAction $action): bool => !$action->evaluate($condition));

        return $this;
    }

    public function createAnother(bool|Closure $condition = true): static
    {
        $this->canCreateAnother = $condition;

        return $this;
    }

    public function canCreateAnother(): bool
    {
        return (bool) $this->evaluate($this->canCreateAnother);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(fn(): string => __('filament-actions::create.single.label', ['label' => $this->getModelLabel()]));

        $this->modalHeading(fn(): string => __('filament-actions::create.single.modal.heading', ['label' => $this->getModelLabel()]));

        $this->modalSubmitActionLabel(__('filament-actions::create.single.modal.actions.create.label'));

        $this->extraModalFooterActions(function (): array {
            return $this->canCreateAnother() ? [
                $this->makeModalSubmitAction('createAnother', ['another' => true])
                    ->label(__('filament-actions::create.single.modal.actions.create_another.label')),
            ] : [];
        });

        $this->successNotificationTitle(__('filament-actions::create.single.notifications.created.title'));

        $this->action(function (array $arguments, Form $form, HasTreeTable $livewire): void {
            $model = $this->getModel();

            $record = $this->process(function (array $data, TreeTable $treeTable) use ($model): Model {
                $relationship = $treeTable->getRelationship();

                $pivotData = [];

                if ($relationship instanceof BelongsToMany) {
                    $pivotColumns = $relationship->getPivotColumns();

                    $pivotData = Arr::only($data, $pivotColumns);
                    $data      = Arr::except($data, $pivotColumns);
                }

                if ($translatableContentDriver = $treeTable->makeTranslatableContentDriver()) {
                    $record = $translatableContentDriver->makeRecord($model, $data);
                } else {
                    $record = new $model();
                    $record->fill($data);
                }

                if (
                    (!$relationship) ||
                    $relationship instanceof HasManyThrough
                ) {
                    $record->save();

                    return $record;
                }

                if ($relationship instanceof BelongsToMany) {
                    $relationship->save($record, $pivotData);

                    return $record;
                }

                /** @phpstan-ignore-next-line */
                $relationship->save($record);

                return $record;
            });

            $this->record($record);
            $form->model($record)->saveRelationships();

            $livewire->mountedTreeTableActionRecord($record->getKey());

            if ($arguments['another'] ?? false) {
                $this->callAfter();
                $this->sendSuccessNotification();

                $this->record(null);

                // Ensure that the form record is anonymized so that relationships aren't loaded.
                $form->model($model);
                $livewire->mountedTreeTableActionRecord(null);

                $form->fill();

                $this->halt();

                return;
            }

            $this->success();
        });
    }
}
