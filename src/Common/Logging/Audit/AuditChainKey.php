<?php

/**
 * The site key used to link audit checksums.
 *
 * The key lives in the site document directory, with the other runtime
 * secrets, and is not part of the database. A copy of the database without
 * this file cannot rebuild a broken chain.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final readonly class AuditChainKey
{
    private function __construct(private string $key)
    {
    }

    public function value(): string
    {
        return $this->key;
    }

    public static function fromSiteDirectory(string $siteDir): self
    {
        if ($siteDir === '' || !is_dir($siteDir)) {
            throw new \RuntimeException('The site directory is not available for the audit chain key.');
        }

        $directory = $siteDir . '/documents/logs_and_misc';
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to store the audit chain key.');
        }

        $path = $directory . '/audit-chain.key';
        $existing = self::read($path);
        if ($existing !== null) {
            return new self($existing);
        }

        $created = self::create($path);
        if ($created !== null) {
            return new self($created);
        }

        $existing = self::read($path);
        if ($existing === null) {
            throw new \RuntimeException('Unable to store the audit chain key.');
        }

        return new self($existing);
    }

    private static function read(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if (!is_string($raw)) {
            throw new \RuntimeException('The audit chain key file is not usable.');
        }

        $key = trim($raw);
        if (strlen($key) !== 64 || !ctype_xdigit($key)) {
            throw new \RuntimeException('The audit chain key file is not usable.');
        }

        return $key;
    }

    private static function create(string $path): ?string
    {
        $handle = fopen($path, 'x');
        if ($handle === false) {
            return null;
        }

        $key = bin2hex(random_bytes(32));
        $written = fwrite($handle, $key);
        fclose($handle);
        if ($written !== 64) {
            unlink($path);
            throw new \RuntimeException('Unable to store the audit chain key.');
        }

        chmod($path, 0640);

        return $key;
    }
}
