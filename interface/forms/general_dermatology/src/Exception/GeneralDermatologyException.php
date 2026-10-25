<?php

declare(strict_types=1);

namespace GeneralDermatology\Exception;

use RuntimeException;

/**
 * GeneralDermatologyException
 *
 * Module-scoped exception for the General Dermatology form.
 * Use this instead of generic \Exception so errors can be
 * identified as originating from this module.
 *
 * @package GeneralDermatology\Exception
 */
class GeneralDermatologyException extends RuntimeException
{
}
