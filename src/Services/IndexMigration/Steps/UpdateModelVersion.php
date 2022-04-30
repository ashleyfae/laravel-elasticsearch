<?php
/**
 * UpdateModelVersion.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Steps;

use Ashleyfae\LaravelElasticsearch\Models\ElasticIndex;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts\ReversibleStep;

class UpdateModelVersion implements ReversibleStep
{
    protected ElasticIndex $elasticIndex;
    protected int $previousVersion;
    protected int $newVersion;

    public function setElasticIndex(ElasticIndex $elasticIndex): static
    {
        $this->elasticIndex = $elasticIndex;

        return $this;
    }

    public function setPreviousVersion(int $previousVersion): static
    {
        $this->previousVersion = $previousVersion;

        return $this;
    }

    public function setNewVersion(int $newVersion): static
    {
        $this->newVersion = $newVersion;

        return $this;
    }

    public function up(): static
    {
        $this->elasticIndex->version_number = $this->newVersion;
        $this->elasticIndex->save();

        return $this;
    }

    public function down(): void
    {
        $this->elasticIndex->version_number = $this->previousVersion;
        $this->elasticIndex->save();
    }
}
