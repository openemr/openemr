<?php

/**
 * PKCE S256 verifier and challenge.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman
 * @copyright Copyright (c) 2026 Tamir Suliman
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use OpenEMR\Common\Utils\HttpUtils;

final readonly class OidcPkce
{
    public function __construct(
        public string $verifier,
        public string $challenge,
    ) {
        if ($this->verifier === '' || $this->challenge === '') {
            throw new OidcRpException('PKCE verifier and challenge are required');
        }
    }

    public static function create(): self
    {
        $verifier = HttpUtils::base64url_encode(random_bytes(32));
        $challenge = HttpUtils::base64url_encode(hash('sha256', $verifier, true));
        return new self($verifier, $challenge);
    }
}
