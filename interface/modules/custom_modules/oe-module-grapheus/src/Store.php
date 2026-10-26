<?php

/**
 * The module's own tables: each clinician's encrypted Grapheus key, the
 * practice key for the Assistant, the link between a recording and an
 * encounter, and settings.
 *
 * @package   Grapheus
 * @copyright Copyright (c) 2026 Exetazo Health
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace Exetazo\Grapheus;

use OpenEMR\BC\ServiceContainer;

final class Store
{
    public const DEFAULT_SERVER = 'https://scribe.exetazohealth.com';

    public static function setting(string $name): string
    {
        $row = Db::one("SELECT `value` FROM `grapheus_settings` WHERE `name` = ?", [$name]);
        return Val::str($row['value'] ?? '');
    }

    public static function setSetting(string $name, string $value): void
    {
        Db::exec("REPLACE INTO `grapheus_settings` (`name`, `value`) VALUES (?, ?)", [$name, $value]);
    }

    public static function server(): string
    {
        $s = self::setting('server');
        return preg_match('#^https://[a-z0-9.-]+(:\d+)?$#i', $s) === 1 ? $s : self::DEFAULT_SERVER;
    }

    private static function encrypt(string $value): string
    {
        return ServiceContainer::getCrypto()->encryptForDatabase($value);
    }

    private static function decrypt(string $value): string
    {
        return ServiceContainer::getCrypto()->decryptFromDatabase($value);
    }

    public static function saveKey(int $userId, string $key, string $email): void
    {
        Db::exec(
            "REPLACE INTO `grapheus_keys` (`user_id`, `key_enc`, `email`, `connected_at`) VALUES (?, ?, ?, NOW())",
            [$userId, self::encrypt($key), mb_substr($email, 0, 255)]
        );
    }

    /**
     * @return array{key: string, email: string}|null
     */
    public static function key(int $userId): ?array
    {
        $row = Db::one("SELECT `key_enc`, `email` FROM `grapheus_keys` WHERE `user_id` = ?", [$userId]);
        $enc = Val::str($row['key_enc'] ?? '');
        if ($enc === '') {
            return null;
        }
        $key = self::decrypt($enc);
        return $key !== '' ? ['key' => $key, 'email' => Val::str($row['email'] ?? '')] : null;
    }

    public static function forget(int $userId): void
    {
        Db::exec("DELETE FROM `grapheus_keys` WHERE `user_id` = ?", [$userId]);
    }

    /** The practice account the Assistant uses for everyone (connected by an administrator). */
    public static function savePracticeKey(string $key, string $email): void
    {
        self::setSetting('practice_key_enc', self::encrypt($key));
        self::setSetting('practice_email', mb_substr($email, 0, 255));
    }

    /**
     * @return array{key: string, email: string}|null
     */
    public static function practiceKey(): ?array
    {
        $enc = self::setting('practice_key_enc');
        if ($enc === '') {
            return null;
        }
        $key = self::decrypt($enc);
        return $key !== '' ? ['key' => $key, 'email' => self::setting('practice_email')] : null;
    }

    public static function forgetPractice(): void
    {
        Db::exec("DELETE FROM `grapheus_settings` WHERE `name` IN ('practice_key_enc', 'practice_email')");
    }

    public static function link(string $visitId, int $pid, int $encounter, int $userId): void
    {
        Db::exec(
            "INSERT IGNORE INTO `grapheus_links` (`visit_id`, `pid`, `encounter`, `user_id`, `created_at`) VALUES (?, ?, ?, ?, NOW())",
            [$visitId, $pid, $encounter, $userId]
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function linksFor(int $pid, int $encounter): array
    {
        $out = [];
        foreach (Db::all("SELECT * FROM `grapheus_links` WHERE `pid` = ? AND `encounter` = ?", [$pid, $encounter]) as $row) {
            $out[Val::str($row['visit_id'] ?? '')] = $row;
        }
        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function linkOf(string $visitId): ?array
    {
        return Db::one("SELECT * FROM `grapheus_links` WHERE `visit_id` = ?", [$visitId]);
    }

    /**
     * @param array<string, mixed> $summary
     */
    public static function markApplied(string $visitId, int $pid, int $encounter, int $userId, array $summary): void
    {
        self::link($visitId, $pid, $encounter, $userId);
        Db::exec(
            "UPDATE `grapheus_links` SET `applied_at` = NOW(), `applied_by` = ?, `summary` = ?, `pid` = ?, `encounter` = ? WHERE `visit_id` = ?",
            [$userId, (string) json_encode($summary), $pid, $encounter, $visitId]
        );
    }
}
