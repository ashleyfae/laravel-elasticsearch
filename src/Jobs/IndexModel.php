<?php
/**
 * IndexModel.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Jobs;

use Ashleyfae\LaravelElasticsearch\Exceptions\InvalidModelException;
use Ashleyfae\LaravelElasticsearch\Exceptions\ModelDoesNotExistException;
use Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer;
use Ashleyfae\LaravelElasticsearch\Traits\Indexable;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexModel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  Model&Indexable  $model
     */
    public function __construct(protected Model $model)
    {

    }

    /**
     * @throws InvalidModelException
     * @throws ModelDoesNotExistException
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function handle(DocumentIndexer $documentIndexer)
    {
        $documentIndexer->setModel($this->model)->index();
    }
}
