<?php

declare(strict_types=1);

namespace OpenEMR\Modules;

interface ModuleInterface
{
    /**
     * The `type` needed in a module's `composer.json`
     *
     * @internal
     */
    final public const COMPOSER_TYPE = 'openemr-module';

    /**
     * The module's `extra` in `composer.json` needs the fully-qualified name of
     * the class that implements `ModuleInterface`.
     *
     * @internal
     */
    final public const ENTRYPOINT_KEY = 'openemr-module-entrypoint';
}
