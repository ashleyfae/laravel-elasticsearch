<?php
/**
 * CreateIndex.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Console\Commands;

use Illuminate\Console\Command;

class CreateIndex extends Command
{

    protected $signature = 'elastic:create-index {name : Model alias name.}';

}
