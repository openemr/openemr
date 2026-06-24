<?php

declare(strict_types=1);

use Firehed\Container\AutoDetect;

// This assumes dotenv has already run

return AutoDetect::instance('src/Plugins/config');
