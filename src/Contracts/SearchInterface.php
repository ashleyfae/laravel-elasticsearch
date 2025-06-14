<?php
/**
 * SearchInterface.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Contracts;

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

interface SearchInterface
{
    /**
     * Resets the search query.
     *
     * @return $this
     */
    public function query(): static;

    /**
     * Executes the query and returns formatted results.
     *
     * @return Collection
     */
    public function get(): Collection;

    /**
     * Executes the query with pagination and returns a paginator.
     *
     * @param  int  $perPage
     * @param  bool  $lengthAware
     *
     * @return AbstractPaginator
     */
    public function paginate(int $perPage = 24, bool $lengthAware = false): AbstractPaginator;

    /**
     * Executes the query and returns the raw results.
     *
     * @return array
     */
    public function executeQuery(): array;

    /**
     * Returns the total number of results from this query.
     *
     * @return int
     */
    public function total(): int;

    /**
     * Sets the formatter to use for results.
     *
     * @param  ResultFormatterInterface  $formatter
     *
     * @return $this
     */
    public function setFormatter(ResultFormatterInterface $formatter): static;

    /**
     * Sets the clause builder.
     *
     * @param  ClauseBuilderInterface  $clauseBuilder
     *
     * @return $this
     */
    public function setClauseBuilder(ClauseBuilderInterface $clauseBuilder): static;

    /**
     * Gets the body of the last request.
     *
     * @return string
     */
    public function getLastRequest() : string;
}
