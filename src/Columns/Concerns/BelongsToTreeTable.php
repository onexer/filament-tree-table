<?php

namespace Onexer\FilamentTreeTable\Columns\Concerns;

use Onexer\FilamentTreeTable\Contracts\HasTreeTable;
use Onexer\FilamentTreeTable\TreeTable;

trait BelongsToTreeTable
{
    protected ?TreeTable $treeTable = null;

    public function treeTable(?TreeTable $treeTable): static
    {
        $this->treeTable = $treeTable;

        return $this;
    }

    public function getLivewire(): HasTreeTable
    {
        return $this->getTreeTable()->getLivewire();
    }

    public function getTreeTable(): TreeTable
    {
        return $this->treeTable ?? $this->getLayout()->getTreeTable();
    }
}
