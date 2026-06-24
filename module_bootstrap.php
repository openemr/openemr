<?php

declare(strict_types=1);

use Firehed\Container\AutoDetect;

// This assumes dotenv has already run

$b = AutoDetect::getBuilder(compiledOutputPath: 'vendor/compiledModuleContainer.php');
$b->addDirectory('src/Plugins/config/');
return $b->build();
