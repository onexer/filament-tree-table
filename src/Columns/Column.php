<?php

namespace Onexer\FilamentTreeTable\Columns;

use Filament\Support\Components\ViewComponent;
use Filament\Support\Concerns\HasAlignment;
use Filament\Support\Concerns\HasExtraAttributes;
use Onexer\FilamentTreeTable\Columns\Concerns\BelongsToLayout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Conditionable;

class Column extends ViewComponent
{
    use BelongsToLayout;
    use Concerns\BelongsToTreeTable;
    use Concerns\CanAggregateRelatedModels;
    use Concerns\CanBeDisabled;
    use Concerns\CanBeHidden;
    use Concerns\CanBeInline;
    use Concerns\CanBeSearchable;
    use Concerns\CanCallAction;
    use Concerns\CanGrow;
    use Concerns\CanOpenUrl;
    use Concerns\CanSpanColumns;
    use Concerns\CanWrapHeader;
    use Concerns\HasExtraCellAttributes;
    use Concerns\HasExtraHeaderAttributes;
    use Concerns\HasLabel;
    use Concerns\HasName;
    use Concerns\HasRecord;
    use Concerns\HasRowLoopObject;
    use Concerns\HasState;
    use Concerns\HasTooltip;
    use Concerns\InteractsWithTreeTableQuery;
    use Conditionable;
    use HasAlignment;
    use HasExtraAttributes;

    protected string $evaluationIdentifier = 'column';

    protected string $viewIdentifier = 'column';

    final public function __construct(string $name)
    {
        $this->name($name);
    }

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'livewire' => [$this->getLivewire()],
            'record' => [$this->getRecord()],
            'rowLoop' => [$this->getRowLoop()],
            'state' => [$this->getState()],
            'treeTable' => [$this->getTreeTable()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
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
