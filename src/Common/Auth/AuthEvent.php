<?php

/**
 * Value object representing an authentication audit event type.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aanand Sreekumaran Nair Jayakumari
 * @copyright Copyright (c) 2026 Aanand Sreekumaran Nair Jayakumari
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth;

final readonly class AuthEvent
{
    public function __construct(public string $value)
    {
        if ($value === '') {
            throw new \DomainException('AuthEvent value cannot be empty');
        }
    }

    public static function login(): self
    {
        return new self('login');
    }

    public static function mfa(): self
    {
        return new self('mfa');
    }

    public static function password(): self
    {
        return new self('password');
    }

    public static function logout(): self
    {
        return new self('logout');
    }

    public static function auth(): self
    {
        return new self('auth');
    }
}
