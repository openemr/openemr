<?php

/**
 * Normalized claims from a verified ID token.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman
 * @copyright Copyright (c) 2026 Tamir Suliman
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

final readonly class OidcIdTokenClaims
{
    public function __construct(
        public string $issuer,
        public string $subject,
        public string $email,
        public string $preferredUsername,
        public string $givenName,
        public string $familyName,
        public string $name,
        /** @var array<string, mixed> */
        public array $raw,
    ) {
        if ($this->issuer === '' || $this->subject === '') {
            throw new OidcRpException('ID token is missing issuer or subject');
        }
    }

    public function username(string $claim): string
    {
        if ($claim !== '' && isset($this->raw[$claim]) && is_string($this->raw[$claim]) && trim($this->raw[$claim]) !== '') {
            return trim($this->raw[$claim]);
        }
        if ($this->preferredUsername !== '') {
            return $this->preferredUsername;
        }
        return $this->email;
    }

    public function firstName(): string
    {
        if ($this->givenName !== '') {
            return $this->givenName;
        }
        if ($this->name !== '') {
            $parts = preg_split('/\s+/', $this->name) ?: [];
            return $parts[0] ?? '';
        }
        return $this->username('preferred_username');
    }

    public function lastName(): string
    {
        if ($this->familyName !== '') {
            return $this->familyName;
        }
        if ($this->name !== '') {
            $parts = preg_split('/\s+/', $this->name) ?: [];
            if (count($parts) > 1) {
                return (string) array_pop($parts);
            }
        }
        return '';
    }
}
