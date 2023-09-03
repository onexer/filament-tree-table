<?php

namespace Onexer\FilamentTreeTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrashedFilter extends TernaryFilter
{
    public static function getDefaultName(): ?string
    {
        return 'trashed';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-tree-table::table.filters.trashed.label'));

        $this->placeholder(__('filament-tree-table::table.filters.trashed.without_trashed'));

        $this->trueLabel(__('filament-tree-table::table.filters.trashed.with_trashed'));

        $this->falseLabel(__('filament-tree-table::table.filters.trashed.only_trashed'));

        $this->queries(
            true: fn($query) => $query->withTrashed(),
            false: fn($query) => $query->onlyTrashed(),
            blank: fn($query) => $query->withoutTrashed(),
        );

        $this->baseQuery(fn(Builder $query) => $query->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]));

        $this->indicateUsing(function (array $state): array {
            if ($state['value'] ?? null) {
                return [$this->getTrueLabel()];
            }

            if (blank($state['value'] ?? null)) {
                return [];
            }

            return [$this->getFalseLabel()];
        });
    }
}
