<?php
/**
 * SearchClauseType.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Enums;

enum SearchClauseType: string
{
    case Filter = 'filter';
    case Must = 'must';
    case MustNot = 'must_not';
    case Should = 'should';
}
