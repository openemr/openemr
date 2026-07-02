<?php

declare(strict_types=1);

namespace OpenEMR\BC;

enum DeprecationMode
{
    /**
     * Emits a E_USER_DEPRECATED warning, which will be logged.
     */
    case Warning;
    /**
     * Throws an exception. Recommended during development for immediate
     * feedback.
     */
    case Error;
}
