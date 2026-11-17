<?php

/**
 * Saves the immunization form on the patient's Immunizations page: an edit updates the
 * immunization's row in place, and a new immunization is inserted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Immunization;

use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use Psr\Clock\ClockInterface;

final readonly class ImmunizationFormSaver
{
    // Columns written from the form in both cases. The if(?,?,NULL) wrappers
    // are kept exactly as they were, so stored values do not change: MySQL
    // reads the condition as a number, so it stores NULL unless the value
    // reads as a nonzero number.
    private const FORM_COLUMNS = "administered_date = if(?,?,NULL),
            manufacturer = ?,
            lot_number = ?,
            administered_by_id = if(?,?,NULL),
            administered_by = if(?,?,NULL),
            education_date = if(?,?,NULL),
            vis_date = if(?,?,NULL),
            note = ?,
            amount_administered = ?,
            amount_administered_unit = ?,
            expiration_date = if(?,?,NULL),
            route = ?,
            administration_site = ?,
            completion_status = ?,
            information_source = ?,
            refusal_reason = ?,
            reason_code = ?,
            reason_description = ?,
            ordering_provider = ?,
            encounter_id = ?";

    // The page shows one of these: the custom immunization list when use_custom_immun_list is on, or on
    // an edit of a shot with no CVX code; the CVX code otherwise. The one not shown isn't posted. An edit
    // leaves both as stored while the one shown is unchanged; once the one shown changes to a value, the
    // other no longer describes the shot and is emptied, as the page's own script does (see shownCodes()).
    private const SHOWN_ONE_AT_A_TIME = ['immunization_id' => 'form_immunization_id', 'cvx_code' => 'cvx_code'];

    /** @var \Closure(int, int): ?array<mixed> */
    private \Closure $storedRow;

    /** @var \Closure(string, list<mixed>): void */
    private \Closure $update;

    /** @var \Closure(string, list<mixed>): int */
    private \Closure $insert;

    private ClockInterface $clock;

    /**
     * @param ?\Closure(int, int): ?array<mixed>   $storedRow        the immunization's immunization_id and cvx_code, or
     *                                                               null when the id isn't one of this patient's
     * @param ?\Closure(string, list<mixed>): void $update           runs an UPDATE
     * @param ?\Closure(string, list<mixed>): int  $insert           runs an INSERT and returns the new row's id
     * @param ?ClockInterface                      $clock            the new row's create_date
     */
    public function __construct(?\Closure $storedRow = null, ?\Closure $update = null, ?\Closure $insert = null, ?ClockInterface $clock = null)
    {
        $this->storedRow = $storedRow ?? static fn(int $immunizationId, int $pid): ?array => QueryUtils::querySingleRow(
            "SELECT immunization_id, cvx_code FROM immunizations WHERE id = ? AND patient_id = ?",
            [$immunizationId, $pid]
        ) ?: null;
        $this->update = $update ?? static function (string $sql, array $binds): void {
            QueryUtils::sqlStatementThrowException($sql, $binds);
        };
        $this->insert = $insert ?? QueryUtils::sqlInsert(...);
        $this->clock = $clock ?? new SystemClock(new DateTimeZone(date_default_timezone_get()));
    }

    /**
     * @param array<mixed> $form   the submitted form ($_GET on the Immunizations page)
     * @param ?int         $userId the signed-in user, recorded as the row's creator and last updater
     * @return ?int the immunization's id; null, with nothing written, when the form's id is not one
     *              of this patient's immunizations, or not an id at all
     */
    public function save(array $form, int $pid, ?int $userId): ?int
    {
        // The form posts 0 (or nothing) for a new immunization, and the row's
        // id when one is being edited. An edit updates that row in place: a
        // REPLACE INTO deletes and re-inserts it, which drops the columns this
        // statement does not name (added_erroneously, external_id) and resets
        // the ones it always sets (created_by, create_date).
        //
        // An id that is neither (letters, a sign, a list) is refused with
        // nothing written, rather than read as 0 and saved as a new one.
        $id = $form['id'] ?? '';
        $idText = is_scalar($id) ? trim((string) $id) : null;
        if ($idText === null || ($idText !== '' && !ctype_digit($idText))) {
            return null;
        }
        $immunizationId = (int) $idText;

        $formBindArray = [
            self::text($form, 'administered_date'), self::text($form, 'administered_date'),
            self::text($form, 'manufacturer'),
            self::text($form, 'lot_number'),
            self::text($form, 'administered_by_id'), self::text($form, 'administered_by_id'),
            self::text($form, 'administered_by'), self::text($form, 'administered_by'),
            self::text($form, 'education_date'), self::text($form, 'education_date'),
            self::text($form, 'vis_date'), self::text($form, 'vis_date'),
            self::text($form, 'note'),
            self::text($form, 'immuniz_amt_adminstrd'),
            self::text($form, 'form_drug_units'),
            self::text($form, 'immuniz_exp_date'), self::text($form, 'immuniz_exp_date'),
            self::text($form, 'immuniz_route'),
            self::text($form, 'immuniz_admin_ste'),
            self::text($form, 'immuniz_completion_status'),
            self::text($form, 'immunization_informationsource'),
            self::text($form, 'immunization_refusal_reason'),
            self::text($form, 'reason_code'),
            // The reason code's description, which the page posts as reason_code_text.
            self::text($form, 'reason_code_text'),
            self::text($form, 'ordered_by_id'),
            self::text($form, 'encounter_id'),
        ];

        $stored = null;
        if ($immunizationId > 0) {
            // Refuse an id that is not this patient's, the same way the edit
            // screen does, so the observation results are never written
            // against an immunization that does not exist. Affected rows will
            // not do here: MySQL reports none when a save changes nothing.
            $stored = ($this->storedRow)($immunizationId, $pid);
            if ($stored === null) {
                return null;
            }
        }
        $shown = self::shownCodes($form, $stored);
        $shownColumns = implode('', array_map(fn(string $column): string => "$column = ?, ", array_keys($shown)));

        if ($stored !== null) {
            // The uuid is left alone: it identifies this immunization outside
            // OpenEMR, so an edit must not change or clear it.
            ($this->update)(
                "UPDATE immunizations SET " . $shownColumns . self::FORM_COLUMNS . ", updated_by = ? WHERE id = ? AND patient_id = ?",
                [...array_values($shown), ...$formBindArray, $userId, $immunizationId, $pid]
            );
            return $immunizationId;
        }

        $uuid = $form['uuid'] ?? null;
        return ($this->insert)(
            "INSERT INTO immunizations SET uuid = ?, " . $shownColumns . self::FORM_COLUMNS . ",
                patient_id = ?,
                created_by = ?,
                updated_by = ?,
                create_date = ?",
            [
                is_string($uuid) && UuidRegistry::isValidStringUUID($uuid) ? UuidRegistry::uuidToBytes($uuid) : null,
                ...array_values($shown),
                ...$formBindArray,
                $pid,
                $userId,
                $userId,
                $this->clock->now()->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * The custom list's immunization and the CVX code to write: both for a new
     * immunization; on an edit, none while the one the page showed is
     * unchanged (both stay as stored); once it changes to a value, it and the
     * other emptied; once it changes to none, it alone.
     *
     * @param array<mixed>  $form
     * @param ?array<mixed> $stored the row's immunization_id and cvx_code, or null for a new immunization
     * @return array<string, string> column => value, in SHOWN_ONE_AT_A_TIME's order
     */
    private static function shownCodes(array $form, ?array $stored): array
    {
        $shown = [];
        foreach (self::SHOWN_ONE_AT_A_TIME as $column => $key) {
            if ($stored === null || array_key_exists($key, $form)) {
                $shown[$column] = self::text($form, $key);
            }
        }
        // A change is judged against the value the page showed (shown_code_original), so a save from a window
        // opened before another window changed the code doesn't count that change as its own; without it,
        // against the stored row.
        $changed = false;
        foreach ($shown as $column => $value) {
            $was = count($shown) === 1 && array_key_exists('shown_code_original', $form)
                ? self::text($form, 'shown_code_original')
                : ($stored[$column] ?? null);
            $changed = $changed || ($stored !== null && self::code($value) !== self::code($was));
        }
        if ($stored === null) {
            return $shown;
        }
        if (!$changed) {
            return [];
        }
        // Changed to none (the blank choice): the shown one is emptied and the other kept, as the page's own
        // script clears the CVX code only when an immunization is picked.
        if (count($shown) === 1 && self::code(reset($shown)) === '') {
            return $shown;
        }
        $codes = [];
        foreach (array_keys(self::SHOWN_ONE_AT_A_TIME) as $column) {
            $codes[$column] = $shown[$column] ?? '';
        }
        return $codes;
    }

    /**
     * A stored or posted code for comparing: trimmed, and empty for none (NULL,
     * or 0 in the integer immunization_id column).
     */
    private static function code(mixed $value): string
    {
        $text = is_scalar($value) ? trim((string) $value) : '';
        return ltrim($text, '0') === '' ? '' : $text;
    }

    /**
     * A form value, trimmed; empty when it is missing or not a single value.
     *
     * @param array<mixed> $form
     */
    private static function text(array $form, string $key): string
    {
        $value = $form[$key] ?? null;
        return is_scalar($value) ? trim((string) $value) : '';
    }
}
