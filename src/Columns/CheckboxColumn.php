<?php

namespace Onexer\FilamentTreeTable\Columns;

use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Onexer\FilamentTreeTable\Columns\Contracts\Editable;

class CheckboxColumn extends Column implements Editable
{
    use Concerns\CanBeValidated;
    use Concerns\CanUpdateState;
    use HasExtraInputAttributes;

    /**
     * @var view-string
     */
    protected string $view = 'filament-tree-table::columns.checkbox-column';

    protected function setUp(): void
    {
        parent::setUp();

        $this->disabledClick();

        $this->rules(['boolean']);
    }
}
