<?php

declare(strict_types=1);

use Firehed\Container\Compiler;
use OpenEMR\Modules\Manager\ManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;

// TODO: remove these functions and have them live somewhere better

/**
 * @param class-string $commandClass
 */
function readCommandName(string $commandClass): string
{
    $rc = new ReflectionClass($commandClass);
    $attrs = $rc->getAttributes(AsCommand::class);
    assert(count($attrs) === 1);
    $info = $attrs[0]->newInstance();
    return $info->name;
}

function getModuleManager(): ManagerInterface
{
    $b = new Compiler('vendor/module_manager.php');
    $b->addFile('config/modules.php');
    $c = $b->build();
    return $c->get(ManagerInterface::class);
}
