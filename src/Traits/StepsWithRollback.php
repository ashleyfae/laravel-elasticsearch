<?php
/**
 * StepsWithRollback.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Traits;

use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts\ReversibleStep;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts\Step;

trait StepsWithRollback
{
    /** @var Step[]|ReversibleStep[] */
    protected array $completedSteps = [];

    protected function addCompletedStep(Step|ReversibleStep $step): void
    {
        $this->completedSteps[] = $step;
    }

    protected function rollbackSteps(): void
    {
        foreach (array_reverse($this->completedSteps) as $step) {
            if ($step instanceof ReversibleStep) {
                $step->down();
            }
        }
    }
}
