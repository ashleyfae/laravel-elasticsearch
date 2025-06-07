<?php
/**
 * CanGetModelByAliasAndId.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2025, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands\Traits;

use Ashleyfae\LaravelElasticsearch\Traits\IndexableModelFromAlias;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait CanGetModelByAliasAndId
{
    use IndexableModelFromAlias;

    protected function getModel() : Model
    {
        $modelClass = $this->getIndexableFromAliasName($this->argument('model'));

        return $modelClass::findOrFail($this->argument('id'));
    }
}
