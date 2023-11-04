<?php

namespace Onexer\FilamentTreeTable\Actions;

use Closure;
use Filament\Actions\ActionGroup as BaseActionGroup;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Actions\Contracts\HasRecord;
use Onexer\FilamentTreeTable\Actions\Contracts\HasTreeTable;
use Onexer\FilamentTreeTable\TreeTable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array<Action> $actions
 */
class ActionGroup extends BaseActionGroup implements HasRecord, HasTreeTable
{
    use InteractsWithRecord;

    public function record(Model|Closure|null $record): static
    {
        $this->record = $record;

        foreach ($this->actions as $action) {
            if (!$action instanceof HasRecord) {
                continue;
            }

            $action->record($record);
        }

        return $this;
    }

    public function treeTable(TreeTable $treeTable): static
    {
        foreach ($this->actions as $action) {
            if (!$action instanceof HasTreeTable) {
                continue;
            }
            $action->treeTable($treeTable);
        }

        return $this;
    }
}
