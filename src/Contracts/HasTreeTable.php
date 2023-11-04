<?php

namespace Onexer\FilamentTreeTable\Contracts;

use Filament\Forms\Form;
use Filament\Support\Contracts\TranslatableContentDriver;
use Onexer\FilamentTreeTable\Actions\Action;
use Onexer\FilamentTreeTable\TreeTable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface HasTreeTable
{
    public function callTableColumnAction(string $name, string $recordKey): mixed;

    public function getActiveTableLocale(): ?string;


    public function getAllTableRecordsCount(): int;

    /**
     * @return array<string, mixed> | null
     */
    public function getTreeTableFilterState(string $name): ?array;

    public function parseTreeTableFilterName(string $name): string;

    public function getMountedTreeTableAction(): ?Action;

    public function getMountedTreeTableActionForm(): ?Form;

    public function getMountedTreeTableActionRecord(): ?Model;

    public function getMountedTreeTableActionRecordKey(): int|string|null;

    public function getTreeTable(): TreeTable;

    public function getTreeTableFiltersForm(): Form;

    public function getTreeTableRecords(): Collection|Paginator;

    public function getTreeTableRecord(?string $key): ?Model;

    public function getTreeTableRecordKey(Model $record): string;

    public function mountedTreeTableActionRecord(int|string|null $record): void;

    public function isTableLoaded(): bool;

    public function hasTreeTableSearch(): bool;

    public function resetTreeTableSearch(): void;

    public function resetTreeTableColumnSearch(string $column): void;

    public function getTreeTableSearchIndicator(): string;

    /**
     * @return array<string, string>
     */
    public function getTreeTableColumnSearchIndicators(): array;

    public function getFilteredTableQuery(): Builder;

    public function getFilteredSortedTableQuery(): Builder;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver;
}
