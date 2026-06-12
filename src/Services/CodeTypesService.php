<?php

declare(strict_types=1);

namespace OpenEMR\Services;

// There are many references to `OpenEMR\Services\CodeTypesService`; do
// a simple alias instead of a giant rename for now.
class_alias(CodeTypes\Service::class, CodeTypesService::class);
