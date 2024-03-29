<?php
/**
 * ClauseBuilder.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Services\Search;

use Ashleyfae\LaravelElasticsearch\Contracts\ClauseBuilderInterface;
use Ashleyfae\LaravelElasticsearch\Enums\SearchClauseType;
use Ashleyfae\LaravelElasticsearch\Enums\SearchRangeComparison;
use Ashleyfae\LaravelElasticsearch\Enums\SortDirection;
use Illuminate\Support\Traits\Conditionable;

class ClauseBuilder implements ClauseBuilderInterface
{
    use Conditionable;

    /**
     * @var array Body params.
     */
    protected array $body = [];

    /** @inheritDoc */
    public function reset(): static
    {
        $this->body = [];

        return $this;
    }

    /** @inheritDoc */
    public function getBody(): array
    {
        return $this->body;
    }

    /** @inheritDoc */
    public function take(int $numberResults): static
    {
        $this->body['size'] = $numberResults;

        return $this;
    }

    /** @inheritDoc */
    public function takePerPage(int $numberPerPage): static
    {
        $this->take($numberPerPage);

        $this->body['from'] = $numberPerPage * (request('page', 1) - 1);

        return $this;
    }

    /** @inheritDoc */
    public function addClause(array $args, SearchClauseType $type = SearchClauseType::Must): static
    {
        $this->body['query']['bool'][$type->value][] = $args;

        return $this;
    }

    /**
     * Adds a new filter clause.
     *
     * @param  array  $args
     *
     * @return $this
     */
    public function addFilter(array $args): static
    {
        $this->addClause($args, SearchClauseType::Filter);

        return $this;
    }

    /**
     * Adds a term clause.
     *
     * If an array is provided, any *one* value in the array must match -- not all. To match all values
     * {@see addAllTermsClause}
     *
     * @param  string  $termName
     * @param  mixed  $value
     * @param  SearchClauseType  $type
     *
     * @return $this
     */
    public function addTermClause(
        string $termName,
        mixed $value,
        SearchClauseType $type = SearchClauseType::Filter
    ): static {
        $keyName = is_array($value) ? 'terms' : 'term';

        $this->addClause([
            $keyName => [
                $termName => $value,
            ],
        ], $type);

        return $this;
    }

    /**
     * Adds a clause for an array of terms, where *all* term values in the array must match.
     *
     * @param  string  $termName
     * @param  array  $values
     *
     * @return $this
     */
    public function addAllTermsClause(string $termName, array $values): static
    {
        foreach ($values as $value) {
            $this->addTermClause($termName, $value);
        }

        return $this;
    }

    /**
     * Adds a terms aggregate.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-terms-aggregation.html
     *
     * @param  string  $aggregateName
     * @param  string  $fieldName
     * @param  int  $size  Maximum number of results.
     *
     * @return $this
     */
    public function addTermsAggregate(string $aggregateName, string $fieldName, int $size = 10): static
    {
        $this->body['aggs'][$aggregateName] = [
            'terms' => [
                'field' => $fieldName,
            ],
        ];

        return $this;
    }

    /**
     * Adds a range clause.
     *
     * @param  string  $fieldName
     * @param  SearchRangeComparison  $comparison
     * @param  mixed  $value
     * @param  SearchClauseType  $type
     *
     * @return $this
     */
    public function addRangeClause(
        string $fieldName,
        SearchRangeComparison $comparison,
        mixed $value,
        SearchClauseType $type = SearchClauseType::Must
    ): static {
        $this->addClause([
            'range' => [
                $fieldName => [
                    $comparison->value => $value,
                ],
            ],
        ], $type);

        return $this;
    }

    /**
     * Adds a new sort.
     *
     * @param  string  $fieldName
     * @param  SortDirection  $direction
     *
     * @return $this
     */
    public function addSort(string $fieldName, SortDirection $direction = SortDirection::Ascending): static
    {
        $this->body['sort'][] = [
            $fieldName => ['order' => $direction->value],
        ];

        return $this;
    }

    /**
     * Sorts the results by score.
     *
     * @param  SortDirection  $direction
     *
     * @return $this
     */
    public function sortByScore(SortDirection $direction = SortDirection::Descending): static
    {
        $this->addSort('_score', $direction);

        return $this;
    }

    /**
     * Adds highlighting based on the search query.
     *
     * @param  object[]|array[]  $fields Field(s) to highlight, keyed by field name.
     * @param array $settings Additional highlighting settings.
     *
     * @return $this
     */
    public function addHighlighting(array $fields, array $settings = []): static
    {
        if (! isset($this->body['highlight']['fields'])) {
            $this->body['highlight']['fields'] = [];
        }

        $fields = array_map(function($field) {
            if (empty($field) && is_array($field)) {
                return (object) $field;
            }

            return $field;
        }, $fields);

        $this->body['highlight'] = array_merge($settings, [
            'fields' => array_merge($this->body['highlight']['fields'], $fields)
        ]);

        return $this;
    }
}
