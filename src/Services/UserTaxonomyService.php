<?php

/**
 * Additional provider taxonomy codes for a user.
 *
 * users.taxonomy stays the user's primary taxonomy. A provider who practices in more than one specialty
 * (for example internal medicine and cardiology) keeps the other codes in users_taxonomy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;

final class UserTaxonomyService
{
    /** list_options list that carries the NUCC provider taxonomy names. */
    public const NAME_LIST_ID = 'us-core-provider-specialty';

    /**
     * A NUCC Health Care Provider Taxonomy code: 10 characters, three digits, six letters or digits, then X.
     */
    public static function normalize(string $raw): ?string
    {
        $code = strtoupper(trim($raw));
        return preg_match('/^[0-9]{3}[A-Z0-9]{6}X$/', $code) === 1 ? $code : null;
    }

    /**
     * Turn posted values into the list to store. Blank entries are skipped, codes are upper-cased,
     * duplicates and the primary taxonomy are dropped, and anything that is not a taxonomy code is returned
     * in invalid so the caller can tell the user.
     *
     * @param list<mixed> $raw
     * @return array{codes: list<string>, invalid: list<string>}
     */
    public static function cleanList(array $raw, string $primary = ''): array
    {
        $primary = strtoupper(trim($primary));
        $codes = [];
        $invalid = [];
        foreach ($raw as $value) {
            $value = is_scalar($value) ? trim((string) $value) : '';
            if ($value === '') {
                continue;
            }
            $code = self::normalize($value);
            if ($code === null) {
                $invalid[] = $value;
                continue;
            }
            if ($code === $primary || in_array($code, $codes, true)) {
                continue;
            }
            $codes[] = $code;
        }
        return ['codes' => $codes, 'invalid' => $invalid];
    }

    /**
     * The additional taxonomy codes of a user, in the order they were entered.
     *
     * @return list<string>
     */
    public function getExtras(int $userId): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `taxonomy` FROM `users_taxonomy` WHERE `user_id` = ? ORDER BY `seq`, `id`',
            [$userId]
        );
        $codes = [];
        foreach ($rows as $row) {
            if (is_string($row['taxonomy'])) {
                $codes[] = $row['taxonomy'];
            }
        }
        return $codes;
    }

    /**
     * Replace the additional taxonomy codes of a user. The codes must already be clean (see cleanList).
     *
     * @param list<string> $codes
     */
    public function replaceExtras(int $userId, array $codes): void
    {
        QueryUtils::inTransaction(function () use ($userId, $codes): void {
            QueryUtils::sqlStatementThrowException('DELETE FROM `users_taxonomy` WHERE `user_id` = ?', [$userId]);
            foreach ($codes as $seq => $code) {
                QueryUtils::sqlStatementThrowException(
                    'INSERT INTO `users_taxonomy` (`user_id`, `taxonomy`, `seq`) VALUES (?, ?, ?)',
                    [$userId, $code, $seq]
                );
            }
        });
    }

    /**
     * Save the additional codes posted from the user edit form: clean the list against the user's primary
     * taxonomy and replace the stored codes. When any posted value is not a taxonomy code nothing is saved,
     * so a mistyped edit cannot silently remove a code that was stored, and the invalid values are returned
     * for the caller to report.
     *
     * @param array<array-key, mixed> $posted
     * @return list<string> the posted values that are not taxonomy codes (empty when the list was saved)
     */
    public function saveExtras(int $userId, array $posted): array
    {
        $primary = QueryUtils::fetchSingleValue('SELECT `taxonomy` FROM `users` WHERE `id` = ?', 'taxonomy', [$userId]);
        $clean = self::cleanList(array_values($posted), is_string($primary) ? $primary : '');
        if ($clean['invalid'] !== []) {
            return $clean['invalid'];
        }
        $this->replaceExtras($userId, $clean['codes']);
        return [];
    }

    /**
     * Taxonomy names by code for the picker, or an empty array when the list is not installed.
     *
     * @return array<string, string>
     */
    public function getNames(): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT `option_id`, `title` FROM `list_options` WHERE `list_id` = ? AND `activity` = 1 ORDER BY `seq`, `title`',
            [self::NAME_LIST_ID]
        );
        $names = [];
        foreach ($rows as $row) {
            if (is_string($row['option_id']) && is_string($row['title'])) {
                $names[$row['option_id']] = $row['title'];
            }
        }
        return $names;
    }
}
