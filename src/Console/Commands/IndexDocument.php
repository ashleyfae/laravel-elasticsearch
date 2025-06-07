<?php
/**
 * IndexDocument.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands;

use Ashleyfae\LaravelElasticsearch\Console\Commands\Traits\CanGetModelByAliasAndId;
use Ashleyfae\LaravelElasticsearch\Services\DocumentIndexer;
use Illuminate\Console\Command;

class IndexDocument extends Command
{
    use CanGetModelByAliasAndId;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:index-doc {model : Model alias name.} {id : ID of the corresponding model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes a single document.';

    public function __construct(protected DocumentIndexer $documentIndexer)
    {
        parent::__construct();
    }

    /**
     * Executes the command.
     */
    public function handle()
    {
        $this->documentIndexer->setModel($this->getModel())->index();

        $this->line('Index successful.');
    }
}
