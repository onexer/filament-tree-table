<?php

namespace Onexer\FilamentTreeTable\Actions;

use Illuminate\Database\Eloquent\Model;
use Onexer\FilamentTreeTable\TreeTable;

class ExpandAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'expand';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->name('expand');

        $this->label(null);
        $this->icon('heroicon-o-bars-arrow-down');
        $this->badge(fn(Model $record) => $record->children_count);
        $this->hidden(fn(Model $record, TreeTable $treeTable) => $record->children_count == 0 || $treeTable->getLivewire()->inChain($record->id));
        $this->action(fn(Model $record, TreeTable $treeTable) => $treeTable->getLivewire()->addToChain($record->id));
    }
}
