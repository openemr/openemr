<?php
declare(strict_types=1);

namespace ParagonIE\MultiFactor;

/**
 * Interface MultiFactorInterface
 *
 * All MFA solutions should implement this interface.
 *
 * @package ParagonIE\MultiFactor
 */
interface MultiFactorInterface
{
    /**
     * This should return a one-time password the user should enter.
     *
     * @return string
     */
    public function generateCode(): string;

    /**
     * This should validate a code for a particular user.
     *
     * @param string $code
     * @return bool
     */
    public function validateCode(string $code): bool;
}
