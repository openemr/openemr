<?php

/**
 * Puts a patient's questionnaire answers in one of the patient's encounters.
 *
 * Staff reviewing answers a patient gave outside a visit (a portal questionnaire document, or a
 * response a patient app sent) choose which encounter, if any, they belong to. This lists the
 * encounters to choose from and files a Questionnaire Assessment form that is in no encounter
 * into the one chosen.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

final readonly class QuestionnaireEncounterFiling
{
    /** How many of a patient's latest encounters staff choose from. */
    public const CHOICES = 10;

    /** How much of an encounter's reason is shown. */
    private const REASON_SHOWN = 60;

    /**
     * The patient's latest encounters, newest first.
     *
     * @return list<array{encounter: int, date: string, reason: string}>
     */
    public function latestEncounters(int $pid): array
    {
        $records = QueryUtils::fetchRecords(
            'SELECT `encounter`, `date`, `reason` FROM `form_encounter` WHERE `pid` = ?'
            // An integer constant, not request text; the driver cannot bind LIMIT.
            . ' ORDER BY `date` DESC, `encounter` DESC LIMIT ' . self::CHOICES,
            [$pid]
        );

        return array_values(array_filter(array_map(self::encounter(...), $records)));
    }

    /**
     * The patient's encounter with this uuid; null when it is not one of the patient's.
     *
     * @return ?array{encounter: int, date: string, reason: string}
     */
    public function encounterByUuid(int $pid, string $uuid): ?array
    {
        if (!UuidRegistry::isValidStringUUID($uuid)) {
            return null;
        }
        $records = QueryUtils::fetchRecords(
            'SELECT `encounter`, `date`, `reason` FROM `form_encounter` WHERE `uuid` = ? AND `pid` = ?',
            [UuidRegistry::uuidToBytes($uuid), $pid]
        );

        return $records === [] ? null : self::encounter($records[0]);
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
     * Files the patient's Questionnaire Assessment form, which is in no encounter, and the
     * response it shows into one of the patient's encounters. Both change or neither does.
     *
     * @param int $formId form_questionnaire_assessments.id
     * @param int $encounter form_encounter.encounter
     * @throws \InvalidArgumentException when the encounter is not the patient's, or the form is
     *         not the patient's, is deleted, or is already in an encounter
     */
    public function fileForm(int $formId, int $pid, int $encounter): void
    {
        if ($encounter <= 0 || !$this->isPatientEncounter($pid, $encounter)) {
            throw new \InvalidArgumentException('The encounter is not one of the patient\'s');
        }
        $form = QueryUtils::querySingleRow(
            'SELECT f.`id`, a.`response_id` FROM `forms` AS f'
            . ' JOIN `form_questionnaire_assessments` AS a ON a.`id` = f.`form_id`'
            . " WHERE f.`formdir` = 'questionnaire_assessments' AND f.`form_id` = ? AND f.`pid` = ?"
            . ' AND f.`deleted` = 0 AND f.`encounter` = 0',
            [$formId, $pid]
        );
        $formsId = is_array($form) ? self::int($form['id'] ?? null) : null;
        if (!is_array($form) || $formsId === null) {
            throw new \InvalidArgumentException('The form is not one of the patient\'s forms waiting for an encounter');
        }
        $responseId = $form['response_id'] ?? null;

        QueryUtils::inTransaction(static function () use ($formsId, $encounter, $responseId, $pid): void {
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

    private static function int(mixed $value): ?int
    {
        return is_int($value) ? $value : (is_string($value) && ctype_digit($value) ? intval($value) : null);
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? $value : (is_int($value) ? (string) $value : '');
    }
}
