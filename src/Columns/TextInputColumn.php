<?php

namespace Onexer\FilamentTreeTable\Columns;

use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasInputMode;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Concerns\HasStep;
use Onexer\FilamentTreeTable\Columns\Contracts\Editable;

class TextInputColumn extends Column implements Editable
{
    use Concerns\CanBeValidated;
    use Concerns\CanUpdateState;
    use HasExtraInputAttributes;
    use HasInputMode;
    use HasPlaceholder;
    use HasStep;

    /**
     * @var string
     */
    protected string $view = 'filament-tree-table::columns.text-input-column';

    protected string|Closure|null $type = null;

    public function type(string|Closure|null $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->evaluate($this->type) ?? 'text';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disabledClick();
    }
}
