<?php
/**
 * ResultFormatterInterface.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Contracts;

use Illuminate\Pagination\AbstractPaginator;

interface ResultFormatterInterface
{
    /**
     * Sets the model name to use in {@see formatHit}
     *
     * @param  string  $modelName
     *
     * @return $this
     */
    public function forModel(string $modelName): static;

    /**
     * Formats all results.
     *
     * @param  array  $results
     *
     * @return array
     */
    public function format(array $results): array;

    /**
     * Formats an individual result.
     *
     * @param  array  $hit
     *
     * @return mixed
     */
    public function formatHit(array $hit): mixed;

    /**
     * Paginates the results.
     *
     * @param  array  $results
     * @param  int  $perPage
     * @param  int|null  $totalResults
     *
     * @return AbstractPaginator
     */
    public function paginate(array $results, int $perPage, int $totalResults = null): AbstractPaginator;
}
