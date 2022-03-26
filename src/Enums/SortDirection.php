<?php
/**
 * SortDirection.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Enums;

enum SortDirection: string
{
    case Ascending = 'asc';
    case Descending = 'desc';
}
