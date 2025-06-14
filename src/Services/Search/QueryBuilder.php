<?php
/**
 * QueryBuilder.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Services\Search;

use Ashleyfae\LaravelElasticsearch\Contracts\ClauseBuilderInterface;
use Ashleyfae\LaravelElasticsearch\Contracts\ResultFormatterInterface;
use Ashleyfae\LaravelElasticsearch\Contracts\SearchInterface;
use Ashleyfae\LaravelElasticsearch\Repositories\ElasticIndexRepository;
use Ashleyfae\LaravelElasticsearch\Traits\HasIndexableModel;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Exception;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class QueryBuilder implements SearchInterface
{
    use HasIndexableModel {
        setModel as traitForModel;
    }

    protected ClauseBuilderInterface $clauseBuilder;
    protected ResultFormatterInterface $formatter;

    /**
     * @var int Total number of results from this query.
     */
    public int $totalNumberResults = 0;

    protected mixed $routing;

    public function __construct(protected Client $elasticClient, protected ElasticIndexRepository $elasticIndexRepository)
    {

    }

    /** @inheritDoc */
    public function query(): static
    {
        $this->clauseBuilder->reset();

        return $this;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function get(): Collection
    {
        return collect($this->formatter->forModel($this->model::class)->format(
            $this->executeQuery()
        ));
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function paginate(int $perPage = 24, bool $lengthAware = false): AbstractPaginator
    {
        $this->clauseBuilder->takePerPage($perPage);

        $results = $this->executeQuery();

        return $this->formatter->forModel($this->model::class)->paginate(
            results: $results,
            perPage: $perPage,
            totalResults: $lengthAware ? $this->total() : null
        );
    }

    /**
     * Executes the query and returns raw results.
     *
     * @throws Exception
     */
    public function executeQuery(): array
    {
        try {
            $results = $this->elasticClient->search($this->makeQueryArgs());

            $this->totalNumberResults = Arr::get($results, 'hits.total.value', 0);

            return $results;
        } catch (Missing404Exception $e) {
            return [];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            Log::debug($this->getLastRequest());

            throw $e;
        }
    }

    public function getLastRequest() : string
    {
        return $this->elasticClient->transport->lastConnection->getLastRequestInfo()['request']['body'];
    }

    /**
     * Makes the query args.
     *
     * @return array
     */
    #[ArrayShape(['index' => "string", 'body' => "array", 'routing' => "mixed"])]
    public function makeQueryArgs(): array
    {
        $args = [
            'index' => $this->elasticIndex->read_alias,
            'body'  => $this->clauseBuilder->getBody(),
        ];

        if (! empty($this->routing)) {
            $args['routing'] = $this->routing;
        }

        return $args;
    }

    /** @inheritDoc */
    public function total(): int
    {
        return $this->totalNumberResults;
    }

    /**
     * Sets the routing value.
     *
     * @param  mixed  $routing
     *
     * @return $this
     */
    public function setRouting(mixed $routing): static
    {
        $this->routing = $routing;

        return $this;
    }

    /** @inheritDoc */
    public function setFormatter(ResultFormatterInterface $formatter): static
    {
        $this->formatter = $formatter;

        return $this;
    }

    /** @inheritDoc */
    public function setClauseBuilder(ClauseBuilderInterface $clauseBuilder): static
    {
        $this->clauseBuilder = $clauseBuilder;

        return $this;
    }
}
