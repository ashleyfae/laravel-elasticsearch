<?php
/**
 * ClauseBuilderInterface.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Contracts;

interface ClauseBuilderInterface
{
    /**
     * Resets the query.
     *
     * @return $this
     */
    public function reset(): static;

    /**
     * Returns the body params.
     *
     * @return array
     */
    public function getBody(): array;
}
