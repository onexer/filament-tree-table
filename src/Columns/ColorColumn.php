<?php

namespace Onexer\FilamentTreeTable\Columns;

class ColorColumn extends Column
{
    use Concerns\CanBeCopied;

    /**
     * @var view-string
     */
    protected string $view = 'filament-tree-table::columns.color-column';
}
