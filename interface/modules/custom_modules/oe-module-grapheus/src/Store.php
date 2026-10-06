<?php

/**
 * The module's own tables: each clinician's encrypted Grapheus key, the link
 * between a recording and an encounter, and settings.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Exetazo\Grapheus;

use OpenEMR\Common\Crypto\CryptoGen;

class Store
{
    public const DEFAULT_SERVER = 'https://scribe.exetazohealth.com';

    public static function server(): string
    {
        $row = sqlQuery("SELECT `value` FROM `grapheus_settings` WHERE `name` = 'server'");
        $s = trim((string) ($row['value'] ?? ''));
        return preg_match('#^https://[a-z0-9.-]+(:\d+)?$#i', $s) ? $s : self::DEFAULT_SERVER;
    }

    public static function saveKey(int $userId, string $key, string $email): void
    {
        $enc = (new CryptoGen())->encryptStandard($key);
        sqlStatement(
            "REPLACE INTO `grapheus_keys` (`user_id`, `key_enc`, `email`, `connected_at`) VALUES (?, ?, ?, NOW())",
            [$userId, $enc, mb_substr($email, 0, 255)]
        );
    }

    /** @return array{key:string, email:string}|null */
    public static function key(int $userId): ?array
    {
        $row = sqlQuery("SELECT `key_enc`, `email` FROM `grapheus_keys` WHERE `user_id` = ?", [$userId]);
        if (empty($row['key_enc'])) {
            return null;
        }
        $key = (new CryptoGen())->decryptStandard($row['key_enc']);
        return $key ? ['key' => $key, 'email' => (string) $row['email']] : null;
    }

    /** The practice account the Assistant uses for everyone (connected by an administrator). */
    public static function savePracticeKey(string $key, string $email): void
    {
        $enc = (new CryptoGen())->encryptStandard($key);
        sqlStatement("REPLACE INTO grapheus_settings (`name`, `value`) VALUES ('practice_key_enc', ?), ('practice_email', ?)", [$enc, mb_substr($email, 0, 255)]);
    }

    /** @return array{key:string, email:string}|null */
    public static function practiceKey(): ?array
    {
        $k = sqlQuery("SELECT `value` FROM grapheus_settings WHERE `name` = 'practice_key_enc'");
        if (empty($k['value'])) {
            return null;
        }
        $e = sqlQuery("SELECT `value` FROM grapheus_settings WHERE `name` = 'practice_email'");
        $key = (new CryptoGen())->decryptStandard($k['value']);
        return $key ? ['key' => $key, 'email' => (string) ($e['value'] ?? '')] : null;
    }

    public static function forgetPractice(): void
    {
        sqlStatement("DELETE FROM grapheus_settings WHERE `name` IN ('practice_key_enc', 'practice_email')");
    }

    public static function forget(int $userId): void
    {
        sqlStatement("DELETE FROM `grapheus_keys` WHERE `user_id` = ?", [$userId]);
    }

    public static function link(string $visitId, int $pid, int $encounter, int $userId): void
    {
        sqlStatement(
            "INSERT IGNORE INTO `grapheus_links` (`visit_id`, `pid`, `encounter`, `user_id`, `created_at`) VALUES (?, ?, ?, ?, NOW())",
            [$visitId, $pid, $encounter, $userId]
        );
    }

    public static function linksFor(int $pid, int $encounter): array
    {
        $out = [];
        $res = sqlStatement("SELECT * FROM `grapheus_links` WHERE `pid` = ? AND `encounter` = ?", [$pid, $encounter]);
        while ($row = sqlFetchArray($res)) {
            $out[$row['visit_id']] = $row;
        }
        return $out;
    }

    public static function linkOf(string $visitId): ?array
    {
        $row = sqlQuery("SELECT * FROM `grapheus_links` WHERE `visit_id` = ?", [$visitId]);
        return $row ?: null;
    }

    public static function markApplied(string $visitId, int $pid, int $encounter, int $userId, array $summary): void
    {
        self::link($visitId, $pid, $encounter, $userId);
        sqlStatement(
            "UPDATE `grapheus_links` SET `applied_at` = NOW(), `applied_by` = ?, `summary` = ?, `pid` = ?, `encounter` = ? WHERE `visit_id` = ?",
            [$userId, json_encode($summary), $pid, $encounter, $visitId]
        );
    }
}
