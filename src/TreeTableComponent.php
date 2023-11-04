<?php

namespace Onexer\FilamentTreeTable;

use Filament\Forms;
use Livewire\Component;

abstract class TreeTableComponent extends Component implements Forms\Contracts\HasForms, Contracts\HasTreeTable
{
    use Forms\Concerns\InteractsWithForms;
    use Concerns\InteractsWithTreeTable;
}
