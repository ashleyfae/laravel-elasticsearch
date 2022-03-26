<?php
/**
 * ClauseBuilderInterface.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Contracts;

use Ashleyfae\LaravelElasticsearch\Enums\SearchClauseType;

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

    /**
     * Adds a new search clause.
     *
     * @param  array  $args
     * @param  SearchClauseType  $type
     *
     * @return static
     */
    public function addClause(array $args, SearchClauseType $type = SearchClauseType::Must): static;

    /**
     * Limits the number of results.
     *
     * @param  int  $numberResults
     *
     * @return static
     */
    public function take(int $numberResults): static;

    /**
     * Number of results to take per page. (also sets the offset)
     *
     * @param  int  $numberPerPage
     *
     * @return static
     */
    public function takePerPage(int $numberPerPage): static;
}
