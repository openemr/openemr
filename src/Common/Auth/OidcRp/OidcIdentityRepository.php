<?php

/**
 * Persists issuer/subject bindings to local OpenEMR users.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use OpenEMR\Common\Database\QueryUtils;
use Psr\Clock\ClockInterface;

final readonly class OidcIdentityRepository
{
    public function __construct(private ClockInterface $clock)
    {
    }

    public function findUserId(string $issuer, string $subject): ?int
    {
        $row = QueryUtils::querySingleRow(
            'SELECT `user_id` FROM `oidc_external_identity` WHERE BINARY `issuer` = ? AND BINARY `subject` = ?',
            [$issuer, $subject],
        );
        if ($row === false) {
            return null;
        }
        $userId = $row['user_id'] ?? null;
        if (!is_numeric($userId)) {
            return null;
        }
        return (int) $userId;
    }

    public function bind(int $userId, OidcIdTokenClaims $claims): void
    {
        $existingId = $this->findUserId($claims->issuer, $claims->subject);
        $now = $this->clock->now()->format('Y-m-d H:i:s');
        if ($existingId === $userId) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `oidc_external_identity` SET `username` = ?, `email` = ?, `last_login` = ? WHERE BINARY `issuer` = ? AND BINARY `subject` = ?',
                [$claims->username('preferred_username'), $claims->email !== '' ? $claims->email : null, $now, $claims->issuer, $claims->subject],
            );
            return;
        }
        if ($existingId !== null) {
            throw new OidcRpException('This identity is already linked to another OpenEMR user');
        }

        QueryUtils::sqlInsert(
            'INSERT INTO `oidc_external_identity` (`user_id`, `issuer`, `subject`, `username`, `email`, `created_at`, `last_login`) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $userId,
                $claims->issuer,
                $claims->subject,
                $claims->username('preferred_username'),
                $claims->email !== '' ? $claims->email : null,
                $now,
                $now,
            ],
        );
    }
}
