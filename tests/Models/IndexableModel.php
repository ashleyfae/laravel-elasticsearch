<?php
/**
 * IndexableModel.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Models;

use Ashleyfae\LaravelElasticsearch\Traits\Indexable;
use Illuminate\Database\Eloquent\Model;

class IndexableModel extends Model
{
    use Indexable;
}
