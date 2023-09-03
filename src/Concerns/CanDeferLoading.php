<?php

namespace Onexer\FilamentTreeTable\Concerns;

trait CanDeferLoading
{
    public bool $isTableLoaded = false;

    public function loadTable(): void
    {
        $this->isTableLoaded = true;
    }

    public function isTableLoaded(): bool
    {
        if (!$this->getTreeTable()->isLoadingDeferred()) {
            return true;
        }

        return $this->isTableLoaded;
    }
}
