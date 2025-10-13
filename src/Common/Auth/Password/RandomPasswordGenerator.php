<?php

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Password;

use Webmozart\Assert\Assert;

/**
 * RandomPasswordGenerator
 *
 * Note, that we have 4 requirements at PasswordStrengthChecker
 * and so passwords should have length >= 4 even when there are no minimal requirement
 *
 * @see PasswordStrengthChecker
 *
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class RandomPasswordGenerator
{
    private const CHARS = [
        '0123456789',
        'abcdefghijklmnopqrstuvwxyz',
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        '!@#$%^&*()-_=+[]{}|;:,.<>?/~',
    ];

    public function __construct(
        private null|int $minimalLength = null,
        private null|int $maximumLength = null,
    ) {
        $this->minimalLength = $minimalLength ?? (((int) $GLOBALS['gbl_minimum_password_length']) ?: 9);
        $this->maximumLength = $maximumLength ?? (((int) $GLOBALS['gbl_maximum_password_length']) ?: 72);
    }

    public function generatePassword(int $length = 16): string
    {
        Assert::true(0 === $this->maximumLength || $this->maximumLength >= $this->minimalLength, sprintf(
            'Maximum length must be at least %d (greater than minimal length), got %d',
            $this->minimalLength,
            $this->maximumLength,
        ));

        Assert::true(0 === $this->maximumLength || $this->maximumLength >= 4, sprintf(
            'Maximum length must be at least 4, got %d',
            $this->maximumLength,
        ));

        $length = max($length, $this->minimalLength); // Ensure length not lower than minimal
        $length = 0 === $this->maximumLength ? $length : min($this->maximumLength, $length); // Ensure length not greater than maximum

        Assert::greaterThanEq($length, 4, sprintf(
            'Password length must be at least 4, got %d',
            $length,
        ));

        $password = [];
        for ($i = 0; $i < $length; ++$i) {
            $chars = self::CHARS[$i % count(self::CHARS)];
            $password[] = $chars[random_int(0, strlen($chars) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }
}
