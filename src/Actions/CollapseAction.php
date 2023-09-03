<?php

namespace Onexer\FilamentTreeTable\Actions;

use Onexer\FilamentTreeTable\TreeTable;
use SmartEntity\Legislation\Library\Models\Article;

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
        $this->label(__('filament-tree-table::actions.collapse.single.label'));
        $this->icon('heroicon-o-bars-arrow-up');
        $this->badge(fn(Article $record) => $record->children_count);
        $this->hidden(fn(Article $record, TreeTable $treeTable) => $record->children_count == 0 || !$treeTable->getLivewire()->inChain($record->id));
        $this->action(fn(Article $record, TreeTable $treeTable) => $treeTable->getLivewire()->removeFromChain($record->id));
    }
}
