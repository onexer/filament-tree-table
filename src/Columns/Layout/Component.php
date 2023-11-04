<?php

namespace Onexer\FilamentTreeTable\Columns\Layout;

use Closure;
use Filament\Support\Components\ViewComponent;
use Filament\Support\Concerns\HasExtraAttributes;
use Onexer\FilamentTreeTable\Columns\Column;
use Onexer\FilamentTreeTable\Columns\Concerns\BelongsToLayout;
use Onexer\FilamentTreeTable\Columns\Concerns\BelongsToTreeTable;
use Onexer\FilamentTreeTable\Columns\Concerns\CanBeHidden;
use Onexer\FilamentTreeTable\Columns\Concerns\CanGrow;
use Onexer\FilamentTreeTable\Columns\Concerns\CanSpanColumns;
use Onexer\FilamentTreeTable\Columns\Concerns\HasRecord;
use Onexer\FilamentTreeTable\Columns\Concerns\HasRowLoopObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Conditionable;

class Component extends ViewComponent
{
    use BelongsToLayout;
    use BelongsToTreeTable;
    use CanBeHidden;
    use CanSpanColumns;
    use CanGrow;
    use HasRecord;
    use HasRowLoopObject;
    use Conditionable;
    use HasExtraAttributes;

    protected string $evaluationIdentifier = 'layout';

    protected string $viewIdentifier = 'layout';

    /**
     * @var array<Column | Component> | Closure
     */
    protected array|Closure $components = [];

    protected bool $isCollapsible = false;

    protected bool|Closure $isCollapsed = true;

    /**
     * @param  array<Column | Component> | Closure  $schema
     */
    public function schema(array|Closure $schema): static
    {
        $this->components($schema);

        return $this;
    }

    /**
     * @param  array<Column | Component> | Closure  $components
     */
    public function components(array|Closure $components): static
    {
        $this->components = $components;

        return $this;
    }

    public function collapsed(bool|Closure $condition = true): static
    {
        $this->collapsible();
        $this->isCollapsed = $condition;

        return $this;
    }

    public function collapsible(bool $condition = true): static
    {
        $this->isCollapsible = $condition;

        return $this;
    }

    public function isCollapsed(): bool
    {
        return (bool) $this->evaluate($this->isCollapsed);
    }

    /**
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        $columns = [];

        foreach ($this->getComponents() as $component) {
            if ($component instanceof Column) {
                $columns[$component->getName()] = $component;

                continue;
            }

            $columns = [
                ...$columns,
                ...$component->getColumns(),
            ];
        }

        return $columns;
    }

    /**
     * @return array<Column | Component>
     */
    public function getComponents(): array
    {
        return array_map(function (Component|Column $component): Component|Column {
            return $component->layout($this);
        }, $this->evaluate($this->components));
    }

    public function isCollapsible(): bool
    {
        return $this->isCollapsible;
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
