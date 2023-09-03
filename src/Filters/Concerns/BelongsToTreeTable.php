<?php

namespace Onexer\FilamentTreeTable\Filters\Concerns;

use Onexer\FilamentTreeTable\Contracts\HasTreeTable;
use Onexer\FilamentTreeTable\TreeTable;

trait BelongsToTreeTable
{
    protected TreeTable $table;

    public function treeTable(TreeTable $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(): array
    {
        return $this->getLivewire()->getTreeTableFilterState($this->getName()) ?? [];
    }

    public function getLivewire(): HasTreeTable
    {
        return $this->getTreeTable()->getLivewire();
    }

    public function getTreeTable(): TreeTable
    {
        return $this->table;
    }
}
