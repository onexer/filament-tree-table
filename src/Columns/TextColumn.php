<?php

namespace Onexer\FilamentTreeTable\Columns;

use Closure;
use Onexer\FilamentTreeTable\Columns\TextColumn\TextColumnSize;
use Onexer\FilamentTreeTable\Contracts\HasTreeTable;
use stdClass;

class TextColumn extends Column
{
    use Concerns\CanBeCopied;
    use Concerns\CanFormatState;
    use Concerns\HasColor;
    use Concerns\HasDescription;
    use Concerns\HasFontFamily;
    use Concerns\HasIcon;
    use Concerns\HasWeight;

    /**
     * @var string
     */
    protected string $view = 'filament-tree-table::columns.text-column';

    protected bool|Closure $canWrap = false;

    protected bool|Closure $isBadge = false;

    protected bool|Closure $isBulleted = false;

    protected bool|Closure $isListWithLineBreaks = false;

    protected int|Closure|null $listLimit = null;

    protected TextColumnSize|string|Closure|null $size = null;

    public function badge(bool|Closure $condition = true): static
    {
        $this->isBadge = $condition;

        return $this;
    }

    public function bulleted(bool|Closure $condition = true): static
    {
        $this->isBulleted = $condition;

        return $this;
    }

    public function listWithLineBreaks(bool|Closure $condition = true): static
    {
        $this->isListWithLineBreaks = $condition;

        return $this;
    }

    public function limitList(int|Closure|null $limit = 3): static
    {
        $this->listLimit = $limit;

        return $this;
    }

    public function rowIndex(bool $isFromZero = false): static
    {
        $this->state(static function (HasTreeTable $livewire, stdClass $rowLoop) use ($isFromZero): string {
            $rowIndex = $rowLoop->{$isFromZero ? 'index' : 'iteration'};

            return (string) $rowIndex;
            // TODO: Fix this
//            return (string) ($rowIndex + ($livewire->getTreeTableRecordsPerPage() * ($livewire->getTablePage() - 1)));
        });

        return $this;
    }

    public function wrap(bool|Closure $condition = true): static
    {
        $this->canWrap = $condition;

        return $this;
    }

    public function size(TextColumnSize|string|Closure|null $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getSize(mixed $state): TextColumnSize|string|null
    {
        return $this->evaluate($this->size, [
            'state' => $state,
        ]);
    }

    public function canWrap(): bool
    {
        return (bool) $this->evaluate($this->canWrap);
    }

    public function isBadge(): bool
    {
        return (bool) $this->evaluate($this->isBadge);
    }

    public function isListWithLineBreaks(): bool
    {
        return $this->evaluate($this->isListWithLineBreaks) || $this->isBulleted();
    }

    public function isBulleted(): bool
    {
        return (bool) $this->evaluate($this->isBulleted);
    }

    public function getListLimit(): ?int
    {
        return $this->evaluate($this->listLimit);
    }
}
