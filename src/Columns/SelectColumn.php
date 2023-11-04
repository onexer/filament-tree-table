<?php

namespace Onexer\FilamentTreeTable\Columns;

use Filament\Forms\Components\Concerns\CanDisableOptions;
use Filament\Forms\Components\Concerns\CanSelectPlaceholder;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Onexer\FilamentTreeTable\Columns\Contracts\Editable;
use Illuminate\Validation\Rule;

class SelectColumn extends Column implements Editable
{
    use Concerns\CanBeValidated {
        getRules as baseGetRules;
    }
    use CanDisableOptions;
    use CanSelectPlaceholder;
    use Concerns\CanUpdateState;
    use HasExtraInputAttributes;
    use HasOptions;
    use HasPlaceholder;

    /**
     * @var view-string
     */
    protected string $view = 'filament-tree-table::columns.select-column';

    /**
     * @return array<array-key>
     */
    public function getRules(): array
    {
        return [
            ...$this->baseGetRules(),
            Rule::in(array_keys($this->getOptions())),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disabledClick();

        $this->placeholder(__('filament-forms::components.select.placeholder'));
    }
}
