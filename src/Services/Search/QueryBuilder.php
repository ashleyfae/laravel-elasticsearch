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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class QueryBuilder implements SearchInterface
{
    use HasIndexableModel {
        forModel as traitForModel;
    }

    protected ClauseBuilderInterface $clauseBuilder;
    protected ResultFormatterInterface $formatter;

    /**
     * @var int Total number of results from this query.
     */
    public int $totalNumberResults = 0;

    protected string $indexName;
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
     * @return array
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
            Log::debug($this->elasticClient->transport->lastConnection->getLastRequestInfo()['request']['body']);

            throw $e;
        }
    }

    /**
     * Makes the query args.
     *
     * @return array
     */
    #[ArrayShape(['index' => "string", 'body' => "array", 'routing' => "mixed"])]
    protected function makeQueryArgs(): array
    {
        $args = [
            'index' => $this->indexName,
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

    /** @inheritDoc */
    public function setIndex(string $indexName): static
    {
        $this->indexName = $indexName;

        return $this;
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

    /**
     * Overrides the forModel() trait method so we can also set the index at the same time.
     *
     * @param  Model  $model
     *
     * @return $this
     * @throws \Ashleyfae\LaravelElasticsearch\Exceptions\InvalidModelException
     */
    public function forModel(Model $model): static
    {
        $this->traitForModel($model);

        return $this->setIndex(
            $this->elasticIndexRepository->getIndexByIndexableType($model->getMorphClass())->read_alias
        );
    }
}
