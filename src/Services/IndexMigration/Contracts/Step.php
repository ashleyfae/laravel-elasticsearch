<?php
/**
 * Step.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts;

interface Step
{
    /**
     * Executes the step.
     *
     * @return static
     */
    public function up(): static;
}
