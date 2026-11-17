<?php

/**
 * Who asked to send a Direct message, as the audit and message logs record it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class PhiMailRequester
{
    /**
     * @param ?string $username the audit log's user
     * @param ?string $group the audit log's group
     * @param ?int $userId the message log's user
     */
    public function __construct(
        public ?string $username,
        public ?string $group,
        public ?int $userId,
    ) {
    }

    /**
     * The signed-in user.
     */
    public static function fromSession(SessionInterface $session): self
    {
        $username = $session->get('authUser');
        $group = $session->get('authProvider');
        $userId = $session->get('authUserID');

        return new self(
            username: is_string($username) ? $username : null,
            group: is_string($group) ? $group : null,
            userId: match (true) {
                is_int($userId) => $userId,
                is_string($userId) && ctype_digit($userId) => (int) $userId,
                default => null,
            },
        );
    }

    /**
     * The same request, made on behalf of another user.
     */
    public function onBehalfOf(string $username, int $userId): self
    {
        return new self($username, $this->group, $userId);
    }
}
