<?php

/**
 * Optional database account used only to append audit rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final readonly class AuditDatabaseCredential
{
    private function __construct(
        public string $user,
        public string $password,
    ) {
    }

    public static function create(string $user, string $password): self
    {
        return new self($user, $password);
    }

    public static function path(string $siteDir): string
    {
        return $siteDir . '/documents/logs_and_misc/audit-db.json';
    }

    public static function read(string $siteDir): ?self
    {
        $path = self::path($siteDir);
        if (!is_file($path)) {
            return null;
        }
        $raw = file_get_contents($path);
        if (!is_string($raw)) {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }
        $user = $decoded['user'] ?? null;
        $password = $decoded['password'] ?? null;
        if (!is_string($user) || $user === '' || !is_string($password) || $password === '') {
            return null;
        }

        return new self($user, $password);
    }

    public function write(string $siteDir): void
    {
        $directory = $siteDir . '/documents/logs_and_misc';
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to store the audit database account.');
        }
        $encoded = json_encode(['user' => $this->user, 'password' => $this->password]);
        if (!is_string($encoded)) {
            throw new \RuntimeException('Unable to store the audit database account.');
        }
        if (file_put_contents(self::path($siteDir), $encoded) === false) {
            throw new \RuntimeException('Unable to store the audit database account.');
        }
        chmod(self::path($siteDir), 0640);
    }
}
