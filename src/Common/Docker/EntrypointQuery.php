<?php

/**
 * Read container-startup config without bootstrapping OpenEMR.
 *
 * src/Common/Docker/EntrypointQuery.php is the canonical copy and the only one
 * PHPStan analyses. Each image carries a byte-identical copy under
 * docker/<image>/utilities/ because a Docker build context is rooted at
 * docker/<image>/ and cannot COPY from src/. Change the canonical copy and copy
 * it over the other three; the utilities.bats suite for each image fails the
 * build if they drift.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Docker;

final class EntrypointQuery
{
    public static function isConfigured(string $sqlconfPath): int
    {
        $loaded = self::loadSqlconf($sqlconfPath);
        if (!$loaded['present']) {
            return 0;
        }

        $config = $loaded['config'];
        return isset($config) && $config ? 1 : 0;
    }

    public static function configFlag(string $sqlconfPath): string
    {
        $loaded = self::loadSqlconf($sqlconfPath);
        if (!$loaded['present'] || $loaded['config'] === null) {
            return '';
        }
        if (is_scalar($loaded['config'])) {
            return (string) $loaded['config'];
        }

        return '';
    }

    public static function schemaVersion(string $versionPhpPath): int
    {
        if (!is_file($versionPhpPath) || !is_readable($versionPhpPath)) {
            return 0;
        }

        $v_database = 0;
        require $versionPhpPath;

        // The included version file may replace the initial integer with a numeric string.
        /** @var int|string $v_database */
        return (int) $v_database;
    }

    public static function exportSqlconf(string $sqlconfPath): string
    {
        $loaded = self::loadSqlconf($sqlconfPath);
        $configured = isset($loaded['config']) && $loaded['config'] ? '1' : '0';
        if (!$loaded['present'] || $configured !== '1') {
            return self::shellAssign([
                'EP_CONFIGURED' => '0',
                'EP_HOST' => '',
                'EP_PORT' => '',
                'EP_LOGIN' => '',
                'EP_PASS' => '',
                'EP_DBASE' => '',
            ]);
        }

        return self::shellAssign([
            'EP_CONFIGURED' => '1',
            'EP_HOST' => $loaded['host'],
            'EP_PORT' => $loaded['port'],
            'EP_LOGIN' => $loaded['login'],
            'EP_PASS' => $loaded['pass'],
            'EP_DBASE' => $loaded['dbase'],
        ]);
    }

    public static function iniGet(string $key): string
    {
        $value = ini_get($key);
        if ($value === false) {
            return '';
        }

        return $value;
    }

    /**
     * @return array{present: bool, host: string, port: string, login: string, pass: string, dbase: string, config: mixed}
     */
    private static function loadSqlconf(string $sqlconfPath): array
    {
        $empty = [
            'present' => false,
            'host' => '',
            'port' => '',
            'login' => '',
            'pass' => '',
            'dbase' => '',
            'config' => null,
        ];
        if ($sqlconfPath === '' || !is_file($sqlconfPath) || !is_readable($sqlconfPath)) {
            return $empty;
        }

        $host = '';
        $port = '';
        $login = '';
        $pass = '';
        $dbase = '';
        $config = null;
        require $sqlconfPath;
        $defined = get_defined_vars();

        return [
            'present' => true,
            'host' => self::asString($defined['host'] ?? ''),
            'port' => self::asString($defined['port'] ?? ''),
            'login' => self::asString($defined['login'] ?? ''),
            'pass' => self::asString($defined['pass'] ?? ''),
            'dbase' => self::asString($defined['dbase'] ?? ''),
            'config' => $defined['config'] ?? null,
        ];
    }

    private static function asString(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /**
     * @param array<string, string> $values
     */
    private static function shellAssign(array $values): string
    {
        $out = '';
        foreach ($values as $name => $value) {
            $out .= $name . '=' . escapeshellarg($value) . "\n";
        }

        return $out;
    }
}
