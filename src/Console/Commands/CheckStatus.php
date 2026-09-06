<?php
/**
 * CheckStatus.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Console\Command;

class CheckStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Confirms that Laravel can connect to Elasticsearch.';

    public function __construct(protected Client $client)
    {
        parent::__construct();
    }

    /**
     * Executes the command.
     * @throws NoNodeAvailableException|ClientResponseException|ServerResponseException
     */
    public function handle()
    {
        if ($this->client->ping([])) {
            $this->line('Successfully connected.');
        } else {
            $this->error('Failed to connect.');
        }
    }
}
