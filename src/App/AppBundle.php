<?php

namespace App;

use App\DependencyInjection\CommandPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(
            new CommandPass(
                'app.console',
                'app.console.command_loader',
                'app.console.command'
            )
        );
    }
}
