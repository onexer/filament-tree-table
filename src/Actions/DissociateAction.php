<?php

namespace Onexer\FilamentTreeTable\Actions;

use Filament\Actions\Concerns\CanCustomizeProcess;
use Onexer\FilamentTreeTable\TreeTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DissociateAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'dissociate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::dissociate.single.label'));

        $this->modalHeading(fn(): string => __('filament-actions::dissociate.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitActionLabel(__('filament-actions::dissociate.single.modal.actions.dissociate.label'));

        $this->successNotificationTitle(__('filament-actions::dissociate.single.notifications.dissociated.title'));

        $this->color('danger');

        $this->icon('heroicon-m-x-mark');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-x-mark');

        $this->action(function (): void {
            $this->process(function (Model $record, TreeTable $treeTable): void {
                /** @var BelongsTo $inverseRelationship */
                $inverseRelationship = $treeTable->getInverseRelationshipFor($record);

                $inverseRelationship->dissociate();
                $record->save();
            });

            $this->success();
        });
    }
}
