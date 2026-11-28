<?php

/**
 * Puts a patient's questionnaire answers in one of the patient's encounters.
 *
 * Staff reviewing answers a patient gave outside a visit (a portal questionnaire document, or a
 * response a patient app sent) choose which encounter, if any, they belong to. This lists the
 * encounters to choose from and files a Questionnaire Assessment form that is in no encounter
 * into the one chosen.
 *
 * Only encounters the signed-in user can add forms to are offered or filed into: as on the
 * encounter screens, an encounter with a sensitivity is one the user's `sensitivities` ACL
 * must allow.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

final readonly class QuestionnaireEncounterFiling
{
    /** How many of a patient's latest encounters staff choose from. */
    public const CHOICES = 10;

    /** How much of an encounter's reason is shown. */
    private const REASON_SHOWN = 60;

    /** @var \Closure(string): bool */
    private \Closure $allowsSensitivity;

    /**
     * @param ?\Closure(string): bool $allowsSensitivity whether the signed-in user may open an
     *        encounter with this sensitivity; by default the user's `sensitivities` ACL, as the
     *        encounter screens check it
     */
    public function __construct(?\Closure $allowsSensitivity = null)
    {
        $this->allowsSensitivity = $allowsSensitivity
            ?? static fn(string $sensitivity): bool => AclMain::aclCheckCore('sensitivities', $sensitivity);
    }

    /**
     * The patient's latest encounters the signed-in user can open, newest first.
     *
     * @return list<array{encounter: int, date: string, reason: string}>
     */
    public function latestEncounters(int $pid): array
    {
        // The sensitivities the patient's encounters use that the user may open; an encounter
        // with none is open to all. Filtered in the query so the list still holds CHOICES.
        $allowed = array_values(array_filter(
            array_map(
                self::text(...),
                QueryUtils::fetchTableColumn(
                    "SELECT DISTINCT `sensitivity` FROM `form_encounter` WHERE `pid` = ? AND `sensitivity` IS NOT NULL AND `sensitivity` <> ''",
                    'sensitivity',
                    [$pid]
                )
            ),
            fn(string $sensitivity): bool => $sensitivity !== '' && ($this->allowsSensitivity)($sensitivity)
        ));
        $allowedSql = $allowed === [] ? '' : ' OR `sensitivity` IN (' . implode(', ', array_fill(0, count($allowed), '?')) . ')';
        $records = QueryUtils::fetchRecords(
            'SELECT `encounter`, `date`, `reason` FROM `form_encounter` WHERE `pid` = ?'
            . " AND (`sensitivity` IS NULL OR `sensitivity` = ''" . $allowedSql . ')'
            // An integer constant, not request text; the driver cannot bind LIMIT.
            . ' ORDER BY `date` DESC, `encounter` DESC LIMIT ' . self::CHOICES,
            [$pid, ...$allowed]
        );

        return array_values(array_filter(array_map(self::encounter(...), $records)));
    }

    /**
     * The patient's encounter with this uuid; null when it is not one of the patient's or the
     * signed-in user cannot open it.
     *
     * @return ?array{encounter: int, date: string, reason: string}
     */
    public function encounterByUuid(int $pid, string $uuid): ?array
    {
        if (!UuidRegistry::isValidStringUUID($uuid)) {
            return null;
        }
        $records = QueryUtils::fetchRecords(
            'SELECT `encounter`, `date`, `reason`, `sensitivity` FROM `form_encounter` WHERE `uuid` = ? AND `pid` = ?',
            [UuidRegistry::uuidToBytes($uuid), $pid]
        );
        if ($records === [] || !$this->opens($records[0]['sensitivity'] ?? null)) {
            return null;
        }

        return self::encounter($records[0]);
    }

    public function isPatientEncounter(int $pid, int $encounter): bool
    {
        $found = QueryUtils::fetchSingleValue(
            'SELECT `encounter` FROM `form_encounter` WHERE `encounter` = ? AND `pid` = ?',
            'encounter',
            [$encounter, $pid]
        );

        return self::int($found) === $encounter;
    }

    /**
     * Whether answers can be filed in this encounter: it is one of the patient's, and the
     * signed-in user can open it.
     */
    public function canFileIn(int $pid, int $encounter): bool
    {
        $record = QueryUtils::querySingleRow(
            'SELECT `encounter`, `sensitivity` FROM `form_encounter` WHERE `encounter` = ? AND `pid` = ?',
            [$encounter, $pid]
        );

        return is_array($record)
            && self::int($record['encounter'] ?? null) === $encounter
            && $this->opens($record['sensitivity'] ?? null);
    }

    /**
     * Files the patient's Questionnaire Assessment form, which is in no encounter, and the
     * response it shows into one of the patient's encounters. Both change or neither does.
     *
     * @param int $formId form_questionnaire_assessments.id
     * @param int $encounter form_encounter.encounter
     * @throws \InvalidArgumentException when the encounter is not the patient's or the user cannot
     *         open it, or the form is not the patient's, is deleted, or is already in an encounter
     */
    public function fileForm(int $formId, int $pid, int $encounter): void
    {
        if ($encounter <= 0 || !$this->canFileIn($pid, $encounter)) {
            throw new \InvalidArgumentException('The encounter is not one of the patient\'s');
        }
        QueryUtils::inTransaction(function () use ($formId, $encounter, $pid): void {
            // Read with a lock inside the transaction: a competing filing of the same form waits
            // here, then finds it already in an encounter and is refused.
            $form = QueryUtils::querySingleRow(
                'SELECT f.`id`, a.`response_id` FROM `forms` AS f'
                . ' JOIN `form_questionnaire_assessments` AS a ON a.`id` = f.`form_id`'
                . " WHERE f.`formdir` = 'questionnaire_assessments' AND f.`form_id` = ? AND f.`pid` = ?"
                . ' AND f.`deleted` = 0 AND f.`encounter` = 0 FOR UPDATE',
                [$formId, $pid]
            );
            $formsId = is_array($form) ? self::int($form['id'] ?? null) : null;
            if (!is_array($form) || $formsId === null) {
                throw new \InvalidArgumentException("The form is not one of the patient's forms waiting for an encounter");
            }
            $responseId = $form['response_id'] ?? null;

            // FHIR reads name the encounter of a filed form by its uuid.
            $this->giveUuidIfMissing($pid, $encounter);
            QueryUtils::sqlStatementThrowException('UPDATE `forms` SET `encounter` = ? WHERE `id` = ?', [$encounter, $formsId]);
            if (is_string($responseId) && $responseId !== '') {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `questionnaire_response` SET `encounter` = ? WHERE `response_id` = ? AND `patient_id` = ?',
                    [$encounter, $responseId, $pid]
                );
            }
        });
    }

    /**
     * Gives the patient's encounter a uuid when it has none, so that answers filed in it can be
     * referred to as in that encounter. Starts no transaction of its own: called inside the
     * caller's, it is undone with it.
     */
    public function giveUuidIfMissing(int $pid, int $encounter): void
    {
        $uuid = QueryUtils::fetchSingleValue(
            'SELECT `uuid` FROM `form_encounter` WHERE `encounter` = ? AND `pid` = ?',
            'uuid',
            [$encounter, $pid]
        );
        if (is_string($uuid) && $uuid !== '') {
            return;
        }
        QueryUtils::sqlStatementThrowException(
            'UPDATE `form_encounter` SET `uuid` = ? WHERE `encounter` = ? AND `pid` = ? AND `uuid` IS NULL',
            [(new UuidRegistry(['table_name' => 'form_encounter']))->createUuid(), $encounter, $pid]
        );
    }

    /**
     * An encounter row as staff are shown it: its number, its date (Y-m-d) and the start of the
     * first line of its reason.
     *
     * @param array<array-key, mixed> $record
     * @return ?array{encounter: int, date: string, reason: string}
     */
    private static function encounter(array $record): ?array
    {
        $encounter = self::int($record['encounter'] ?? null) ?? 0;
        if ($encounter <= 0) {
            return null;
        }
        $reason = trim(strtok(trim(self::text($record['reason'] ?? null)), "\r\n") ?: '');
        if (mb_strlen($reason) > self::REASON_SHOWN) {
            $reason = rtrim(mb_substr($reason, 0, self::REASON_SHOWN)) . '…';
        }

        return [
            'encounter' => $encounter,
            'date' => substr(self::text($record['date'] ?? null), 0, 10),
            'reason' => $reason,
        ];
    }

    /**
     * Whether the signed-in user can open an encounter with this sensitivity; one with none is
     * open to all.
     */
    private function opens(mixed $sensitivity): bool
    {
        $sensitivity = self::text($sensitivity);

        return $sensitivity === '' || ($this->allowsSensitivity)($sensitivity);
    }

    private static function int(mixed $value): ?int
    {
        return is_int($value) ? $value : (is_string($value) && ctype_digit($value) ? intval($value) : null);
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : (is_int($value) ? (string) $value : '');
    }
}
