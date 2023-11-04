<?php

namespace Onexer\FilamentTreeTable\Actions;

use Closure;
use Filament\Actions\StaticAction;
use Onexer\FilamentTreeTable\TreeTable;
use Illuminate\Database\Eloquent\Model;

class ViewAction extends Action
{
    protected ?Closure $mutateRecordDataUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'view';
    }

    public function mutateRecordDataUsing(?Closure $callback): static
    {
        $this->mutateRecordDataUsing = $callback;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::view.single.label'));

        $this->modalHeading(fn(): string => __('filament-actions::view.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitAction(false);
        $this->modalCancelAction(fn(StaticAction $action) => $action->label(__('filament-actions::view.single.modal.actions.close.label')));

        $this->color('gray');

        $this->icon('heroicon-m-eye');

        $this->disabledForm();

        $this->fillForm(function (Model $record, TreeTable $treeTable): array {
            if ($translatableContentDriver = $treeTable->makeTranslatableContentDriver()) {
                $data = $translatableContentDriver->getRecordAttributesToArray($record);
            } else {
                $data = $record->attributesToArray();
            }

            if ($this->mutateRecordDataUsing) {
                $data = $this->evaluate($this->mutateRecordDataUsing, ['data' => $data]);
            }

            return $data;
        });

        $this->action(static function (): void {
        });
    }
}
