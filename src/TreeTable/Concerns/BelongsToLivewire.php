<?php

namespace Onexer\FilamentTreeTable\TreeTable\Concerns;

use Filament\Support\Contracts\TranslatableContentDriver;
use Onexer\FilamentTreeTable\Contracts\HasTreeTable;

trait BelongsToLivewire
{
    protected HasTreeTable $livewire;

    public function livewire(HasTreeTable $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function getLivewire(): HasTreeTable
    {
        return $this->livewire;
    }

    public function makeTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return $this->getLivewire()->makeFilamentTranslatableContentDriver();
    }
}
