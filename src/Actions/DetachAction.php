<?php

namespace Onexer\FilamentTreeTable\Actions;

use Filament\Actions\Concerns\CanCustomizeProcess;
use Onexer\FilamentTreeTable\TreeTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DetachAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'detach';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::detach.single.label'));

        $this->modalHeading(fn(): string => __('filament-actions::detach.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitActionLabel(__('filament-actions::detach.single.modal.actions.detach.label'));

        $this->successNotificationTitle(__('filament-actions::detach.single.notifications.detached.title'));

        $this->color('danger');

        $this->icon('heroicon-m-x-mark');

        $this->requiresConfirmation();

        $this->modalIcon('heroicon-o-x-mark');

        $this->action(function (): void {
            $this->process(function (Model $record, TreeTable $treeTable): void {
                /** @var BelongsToMany $relationship */
                $relationship = $treeTable->getRelationship();

                if ($treeTable->allowsDuplicates()) {
                    $record->{$relationship->getPivotAccessor()}->delete();
                } else {
                    $relationship->detach($record);
                }
            });

            $this->success();
        });
    }
}
