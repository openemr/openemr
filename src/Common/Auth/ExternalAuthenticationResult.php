<?php

/**
 * Result returned by a trusted external authentication provider after it has
 * validated a remote identity and mapped it to an OpenEMR user.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth;

final readonly class ExternalAuthenticationResult
{
    public function __construct(
        public int $userId,
        public string $providerId,
    ) {
        if ($userId < 1) {
            throw new \InvalidArgumentException('External authentication user ID must be positive.');
        }

        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{0,63}$/', $providerId)) {
            throw new \InvalidArgumentException('External authentication provider ID is invalid.');
        }
    }
}
