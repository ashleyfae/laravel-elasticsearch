<?php
/**
 * ResultFormatter.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Services\Search;

use Ashleyfae\LaravelElasticsearch\Contracts\ResultFormatterInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;

class ResultFormatter implements ResultFormatterInterface
{
    public array $hits;
    protected string $modelName;

    /** @inheritDoc */
    public function format(array $results): array
    {
        $this->hits = Arr::get($results, 'hits.hits', []);

        $formattedHits = [];
        foreach ($this->hits as $hit) {
            $formattedHits[] = $this->formatHit($hit);
        }

        return array_filter($formattedHits);
    }

    /** @inheritDoc */
    public function formatHit(array $hit): mixed
    {
        /** @var Model $model */
        $model = new $this->modelName;
        $model->id = Arr::get($hit, '_id');

        foreach (Arr::get($hit, '_source') as $propertyName => $value) {
            $model->{$propertyName} = $value;
        }

        return $model;
    }

    /** @inheritDoc */
    public function paginate(array $results, int $perPage, int $totalResults = null): AbstractPaginator
    {
        $formattedHits = $this->format($results);

        if (is_null($totalResults)) {
            return $this->makeSimplePaginator($formattedHits, $perPage);
        } else {
            return $this->makeLengthAwarePaginator($formattedHits, $perPage, $totalResults);
        }
    }

    /** @inheritDoc */
    protected function makeSimplePaginator(array $formattedHits, int $perPage): Paginator
    {
        $paginator = new Paginator($formattedHits, $perPage, Paginator::resolveCurrentPage(), [
            'path'  => Paginator::resolveCurrentPath(),
            'query' => request()->query(),
        ]);
        $paginator->hasMorePagesWhen(count($formattedHits) >= $perPage);

        return $paginator;
    }

    protected function makeLengthAwarePaginator(
        array $formattedHits,
        int $perPage,
        int $totalResults
    ): LengthAwarePaginator {
        return new LengthAwarePaginator(
            $formattedHits,
            $totalResults,
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path'  => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    public function forModel(string $modelName): static
    {
        $this->modelName = $modelName;

        return $this;
    }
}
