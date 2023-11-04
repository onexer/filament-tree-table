<?php

namespace Onexer\FilamentTreeTable\TreeTable\Concerns;

use Closure;

trait CanPollRecords
{
    protected string|Closure|null $pollingInterval = null;

    public function poll(string|Closure|null $interval = '10s'): static
    {
        $this->pollingInterval = $interval;

        return $this;
    }

    public function getPollingInterval(): ?string
    {
        return $this->evaluate($this->pollingInterval);
    }
}
