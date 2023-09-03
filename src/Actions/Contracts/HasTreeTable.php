<?php

namespace Onexer\FilamentTreeTable\Actions\Contracts;

use Onexer\FilamentTreeTable\TreeTable;

interface HasTreeTable
{
    public function treeTable(TreeTable $treeTable): static;
}
