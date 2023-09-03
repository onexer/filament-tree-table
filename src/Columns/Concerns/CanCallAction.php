<?php

namespace Onexer\FilamentTreeTable\Columns\Concerns;

use Closure;
use Onexer\FilamentTreeTable\Actions\Action;
use Onexer\FilamentTreeTable\Contracts\HasTreeTable;
use Illuminate\Database\Eloquent\Model;

trait CanCallAction
{
    protected Closure|Action|string|null $action = null;

    public function action(Closure|Action|string|null $action): static
    {
        if (is_string($action)) {
            $action = function (HasTreeTable $livewire, ?Model $record) use ($action) {
                if ($record) {
                    return $livewire->{$action}($record);
                }

                return $livewire->{$action}();
            };
        }

        $this->action = $action;

        return $this;
    }

    public function getAction(): Closure|Action|null
    {
        return $this->action;
    }
}
