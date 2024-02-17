<?php

namespace Onexer\FilamentTreeTable\Actions;

use Illuminate\Database\Eloquent\Model;
use Onexer\FilamentTreeTable\TreeTable;

class CollapseAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'collapse';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->name('collapse');
        $this->hiddenLabel();
        $this->icon('heroicon-o-bars-arrow-up');
        $this->badge(fn(Model $record) => $record->children_count);
        $this->hidden(fn(Model $record, TreeTable $treeTable) => $record->children_count == 0 || !$treeTable->getLivewire()->inChain($record->id));
        $this->action(fn(Model $record, TreeTable $treeTable) => $treeTable->getLivewire()->removeFromChain($record->id));
    }
}
