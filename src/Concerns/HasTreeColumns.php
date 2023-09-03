<?php

namespace Onexer\FilamentTreeTable\Concerns;

use Closure;
use Onexer\FilamentTreeTable\Columns\Column;
use Onexer\FilamentTreeTable\Columns\Contracts\Editable;
use Onexer\FilamentTreeTable\Columns\Layout\Component as ColumnLayoutComponent;
use Illuminate\Validation\ValidationException;

trait HasTreeColumns
{
    public function callTableColumnAction(string $name, string $recordKey): mixed
    {
        $record = $this->getTreeTableRecord($recordKey);

        if (!$record) {
            return null;
        }

        $column = $this->getTreeTable()->getColumn($name);

        if (!$column) {
            return null;
        }

        if ($column->isHidden()) {
            return null;
        }

        $action = $column->getAction();

        if (!($action instanceof Closure)) {
            return null;
        }

        return $column->record($record)->evaluate($action);
    }

    public function updateTableColumnState(string $column, string $record, mixed $input): mixed
    {
        $column = $this->getTreeTable()->getColumn($column);

        if (!($column instanceof Editable)) {
            return null;
        }

        $record = $this->getTreeTableRecord($record);

        if (!$record) {
            return null;
        }

        $column->record($record);

        if ($column->isDisabled()) {
            return null;
        }

        try {
            $column->validate($input);
        } catch (ValidationException $exception) {
            return [
                'error' => $exception->getMessage(),
            ];
        }

        return $column->updateState($input);
    }
}
