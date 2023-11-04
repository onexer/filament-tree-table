<?php

namespace Onexer\FilamentTreeTable\Actions;

use Exception;
use Filament\Actions\Concerns\HasMountableArguments;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Actions\Contracts\Groupable;
use Filament\Actions\Contracts\HasRecord;
use Filament\Actions\MountableAction;
use Filament\Actions\StaticAction;
use Illuminate\Database\Eloquent\Model;
use Onexer\FilamentTreeTable\Actions\Contracts\HasTreeTable;

class Action extends MountableAction implements Groupable, HasRecord, HasTreeTable
{
    use Concerns\BelongsToTreeTable;
    use HasMountableArguments;
    use InteractsWithRecord;

    public function getLivewireCallMountedActionName(): string
    {
        return 'callMountedTreeTableAction';
    }

    /**
     * @throws Exception
     */
    public function getLivewireClickHandler(): ?string
    {
        if (!$this->isLivewireClickHandlerEnabled()) {
            return null;
        }

        if (is_string($this->action)) {
            return $this->action;
        }

        if ($record = $this->getRecord()) {
            $recordKey = $this->getLivewire()->getTreeTableRecordKey($record);

            return "mountTreeTableAction('{$this->getName()}', '{$recordKey}')";
        }

        return "mountTreeTableAction('{$this->getName()}')";
    }

    public function getRecordTitle(?Model $record = null): string
    {
        $record ??= $this->getRecord();

        return $this->getCustomRecordTitle($record) ?? $this->getTreeTable()->getRecordTitle($record);
    }

    public function getRecordTitleAttribute(): ?string
    {
        return $this->getCustomRecordTitleAttribute() ?? $this->getTreeTable()->getRecordTitleAttribute();
    }

    public function getModelLabel(): string
    {
        return $this->getCustomModelLabel() ?? $this->getTreeTable()->getModelLabel();
    }

    public function getPluralModelLabel(): string
    {
        return $this->getCustomPluralModelLabel() ?? $this->getTreeTable()->getPluralModelLabel();
    }

    public function prepareModalAction(StaticAction $action): StaticAction
    {
        $action = parent::prepareModalAction($action);

        if (!$action instanceof Action) {
            return $action;
        }

        return $action
            ->treeTable($this->getTreeTable())
            ->record($this->getRecord());
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'model' => [$this->getModel()],
            'parentRecord' => [$this->getParentRecord()],
            'record' => [$this->getRecord()],
            'treeTable' => [$this->getTreeTable()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    public function getModel(): string
    {
        return $this->getCustomModel() ?? $this->getTreeTable()->getModel();
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        $record = $this->getRecord();

        if (!$record) {
            return parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType);
        }

        return match ($parameterType) {
            Model::class, $record::class => [$record],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
