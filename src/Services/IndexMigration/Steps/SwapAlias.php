<?php
/**
 * SwapAlias.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps;

use Ashleyfae\LaravelElasticsearch\Services\IndexManager;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts\ReversibleStep;

class SwapAlias implements ReversibleStep
{
    protected string $aliasName;
    protected string $previousIndexName;
    protected string $newIndexName;

    public function __construct(protected IndexManager $indexManager)
    {

    }

    public function setAliasName(string $aliasName): static
    {
        $this->aliasName = $aliasName;

        return $this;
    }

    public function setPreviousIndexName(string $previousIndexName): static
    {
        $this->previousIndexName = $previousIndexName;

        return $this;
    }

    public function setNewIndexName(string $newIndexName): static
    {
        $this->newIndexName = $newIndexName;

        return $this;
    }

    public function up(): static
    {
       $this->indexManager->swapAlias(
           alias: $this->aliasName,
           removeAliasFrom: $this->previousIndexName,
           addAliasTo: $this->newIndexName
       );

       return $this;
    }

    public function down(): void
    {
        $this->indexManager->swapAlias(
            alias: $this->aliasName,
            removeAliasFrom: $this->newIndexName,
            addAliasTo: $this->previousIndexName
        );
    }
}
