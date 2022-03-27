<?php
/**
 * IndexableModelFromAlias.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

trait IndexableModelFromAlias
{
    /**
     * @return Model&Indexable
     * @throws \Exception
     */
    protected function getIndexableFromAliasName(string $aliasName): Model
    {
        $class = Relation::getMorphedModel($aliasName);
        if (is_null($class) || ! class_exists($class)) {
            throw new \Exception('Model not found.');
        }

        return new $class;
    }
}
