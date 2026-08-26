<?php

namespace App\Command;

use App\CommandConfigurator\CommandConfigurator;

abstract class Command extends \Symfony\Component\Console\Command\Command
{
    public static function getDefaultName(): ?string
    {
        return null;
    }

    public function addCommandConfigurator(CommandConfigurator $commandConfigurator): void
    {
        $commandConfigurator->configure($this);
    }
}
