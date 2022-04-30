<?php
/**
 * ReversibleStep.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts;

interface ReversibleStep extends Step
{
    /**
     * Reverses the step.
     *
     * @return void
     */
    public function down(): void;
}
