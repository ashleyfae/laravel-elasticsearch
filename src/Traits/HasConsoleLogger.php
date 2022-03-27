<?php
/**
 * HasConsoleLogger.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   GPL2+
 */

namespace Ashleyfae\LaravelElasticsearch\Traits;

use Illuminate\Console\Command;

trait HasConsoleLogger
{
    /** @var Command console command, for writing output */
    protected Command $command;

    protected function hasConsole(): bool
    {
        return isset($this->command);
    }

    public function setConsole(Command $command): static
    {
        $this->command = $command;

        return $this;
    }

    protected function log(string $message): void
    {
        if ($this->hasConsole()) {
            $this->command->line($message);
        }
    }
}
