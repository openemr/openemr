<?php

declare(strict_types=1);

return [
    Firehed\SimpleLogger\Stdout::class,
    Psr\Log\LoggerInterface::class => Firehed\SimpleLogger\Stdout::class,
];
