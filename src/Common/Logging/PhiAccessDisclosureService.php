<?php

/**
 * Accounting of disclosures.
 *
 * This is the HIPAA disclosure list in extended_log. It is separate from the
 * immutable event log: a disclosure entry can be corrected or removed.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

final class PhiAccessDisclosureService
{
    public function record(
        mixed $date,
        mixed $event,
        mixed $patientId,
        mixed $recipient,
        mixed $description,
        mixed $user,
    ): void {
        sqlInsertClean_audit(
            'INSERT INTO extended_log (date, event, user, recipient, patient_id, description) VALUES (?, ?, ?, ?, ?, ?)',
            [
                self::text($date),
                self::text($event),
                self::text($user),
                self::text($recipient),
                self::id($patientId),
                self::text($description),
            ],
        );
    }

    public function update(
        mixed $date,
        mixed $event,
        mixed $recipient,
        mixed $description,
        mixed $id,
    ): void {
        sqlInsertClean_audit(
            'UPDATE extended_log SET event = ?, date = ?, recipient = ?, description = ? WHERE id = ?',
            [self::text($event), self::text($date), self::text($recipient), self::text($description), self::id($id)],
        );
    }

    public function delete(mixed $id): void
    {
        sqlInsertClean_audit('DELETE FROM extended_log WHERE id = ?', [self::id($id)]);
    }

    private static function text(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (string) $value;
        }

        return '';
    }

    private static function id(mixed $value): int|string
    {
        if (is_int($value) || is_string($value)) {
            return $value;
        }

        return 0;
    }
}
