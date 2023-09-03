<?php

namespace Onexer\FilamentTreeTable\Concerns;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use function Livewire\invade;

trait HasRecords
{

    protected Collection|null $records = null;

    public function getTreeTableRecords(): Collection
    {
        if ($translatableContentDriver = $this->makeFilamentTranslatableContentDriver()) {
            $setRecordLocales = function (Collection $records) use ($translatableContentDriver): Collection {
                $records->transform(fn(Model $record) => $translatableContentDriver->setRecordLocale($record));

                return $records;
            };
        } else {
            $setRecordLocales = fn(Collection $records): Collection => $records;
        }

        if ($this->records) {
            return $setRecordLocales($this->records);
        }

        $query = $this->getFilteredSortedTableQuery();

        return $setRecordLocales($this->records = $this->hydratePivotRelationForTableRecords($query->get()));

    }

    public function getFilteredSortedTableQuery(): Builder
    {
        return $this->getFilteredTableQuery();
    }

    public function getFilteredTableQuery(): Builder
    {
        return $this->filterTableQuery($this->getTreeTable()->getQuery());
    }

    public function filterTableQuery(Builder $query): Builder
    {
        $this->applyFiltersToTreeTableQuery($query);

        $this->applySearchToTreeTableQuery($query);

        foreach ($this->getTreeTable()->getColumns() as $column) {
            if ($column->isHidden()) {
                continue;
            }

            $column->applyRelationshipAggregates($query);

            $column->applyEagerLoading($query);
        }

        return $query;
    }

    public function getTreeTableRecord(?string $key): ?Model
    {
        $record = $this->resolveTableRecord($key);

        if ($record && filled($this->getActiveTableLocale())) {
            $this->makeFilamentTranslatableContentDriver()->setRecordLocale($record);
        }

        return $record;
    }

    public function getTreeTableRecordKey(Model $record): string
    {
        $table = $this->getTreeTable();

        if (!($table->getRelationship() instanceof BelongsToMany && $table->allowsDuplicates())) {
            return $record->getKey();
        }

        /** @var BelongsToMany $relationship */
        $relationship = $table->getRelationship();

        $pivotClass   = $relationship->getPivotClass();
        $pivotKeyName = app($pivotClass)->getKeyName();

        return $record->getAttributeValue($pivotKeyName);
    }

    public function getAllTableRecordsCount(): int
    {
        if ($this->records instanceof LengthAwarePaginator) {
            return $this->records->total();
        }

        return $this->getFilteredTableQuery()->count();
    }

    protected function hydratePivotRelationForTableRecords(Collection|Paginator $records): Collection|Paginator
    {
        $table        = $this->getTreeTable();
        $relationship = $table->getRelationship();

        if ($table->getRelationship() instanceof BelongsToMany && !$table->allowsDuplicates()) {
            invade($relationship)->hydratePivotRelation($records->all());
        }

        return $records;
    }

    protected function resolveTableRecord(?string $key): ?Model
    {
        if ($key === null) {
            return null;
        }

        if (!($this->getTreeTable()->getRelationship() instanceof BelongsToMany)) {
            return $this->getFilteredTableQuery()->find($key);
        }

        /** @var BelongsToMany $relationship */
        $relationship = $this->getTreeTable()->getRelationship();

        $pivotClass   = $relationship->getPivotClass();
        $pivotKeyName = app($pivotClass)->getKeyName();

        $table = $this->getTreeTable();

        $this->applyFiltersToTreeTableQuery($relationship->getQuery());

        $query = $table->allowsDuplicates() ?
            $relationship->wherePivot($pivotKeyName, $key) :
            $relationship->where($relationship->getQualifiedRelatedKeyName(), $key);

        $record = $table->selectPivotDataInQuery($query)->first();

        return $record?->setRawAttributes($record->getRawOriginal());
    }

}
