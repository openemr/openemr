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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

final readonly class ImmunizationFormSaver
{
    // Columns written from the form in both cases. The if(?,?,NULL) wrappers
    // are kept exactly as they were, so stored values do not change: MySQL
    // reads the condition as a number, so it stores NULL unless the value
    // starts with a nonzero digit.
    private const FORM_COLUMNS = "administered_date = if(?,?,NULL),
            immunization_id = ?,
            cvx_code = ?,
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

    /** @var \Closure(int, int): bool */
    private \Closure $belongsToPatient;

    /** @var \Closure(string, list<mixed>): void */
    private \Closure $update;

    /** @var \Closure(string, list<mixed>): int */
    private \Closure $insert;

    /**
     * @param ?\Closure(int, int): bool            $belongsToPatient whether an immunization id is one of this patient's
     * @param ?\Closure(string, list<mixed>): void $update           runs an UPDATE
     * @param ?\Closure(string, list<mixed>): int  $insert           runs an INSERT and returns the new row's id
     */
    public function __construct(?\Closure $belongsToPatient = null, ?\Closure $update = null, ?\Closure $insert = null)
    {
        $this->belongsToPatient = $belongsToPatient ?? static fn(int $immunizationId, int $pid): bool => QueryUtils::fetchSingleValue(
            "SELECT id FROM immunizations WHERE id = ? AND patient_id = ?",
            'id',
            [$immunizationId, $pid]
        ) !== null;
        $this->update = $update ?? static function (string $sql, array $binds): void {
            QueryUtils::sqlStatementThrowException($sql, $binds);
        };
        $this->insert = $insert ?? QueryUtils::sqlInsert(...);
    }

    /**
     * @param array<mixed> $form   the submitted form ($_GET on the Immunizations page)
     * @param mixed        $userId the signed-in user, recorded as the row's creator and last updater
     * @return ?int the immunization's id; null, with nothing written, when the form's id is not one
     *              of this patient's immunizations
     */
    public function save(array $form, int $pid, mixed $userId): ?int
    {
        // The form posts an empty id for a new immunization, and the row's id
        // when one is being edited. An edit updates that row in place: a
        // REPLACE INTO deletes and re-inserts it, which drops the columns this
        // statement does not name (added_erroneously, external_id) and resets
        // the ones it always sets (created_by, create_date).
        $immunizationId = (int) self::text($form, 'id');

        $formBindArray = [
            self::text($form, 'administered_date'), self::text($form, 'administered_date'),
            self::text($form, 'form_immunization_id'),
            self::text($form, 'cvx_code'),
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
            self::text($form, 'reason_description'),
            self::text($form, 'ordered_by_id'),
            self::text($form, 'encounter_id'),
        ];

        if ($immunizationId > 0) {
            // Refuse an id that is not this patient's, the same way the edit
            // screen does, so the observation results are never written
            // against an immunization that does not exist. Affected rows will
            // not do here: MySQL reports none when a save changes nothing.
            if (!($this->belongsToPatient)($immunizationId, $pid)) {
                return null;
            }
            // The uuid is left alone: it identifies this immunization outside
            // OpenEMR, so an edit must not change or clear it.
            ($this->update)(
                "UPDATE immunizations SET " . self::FORM_COLUMNS . ", updated_by = ? WHERE id = ? AND patient_id = ?",
                [...$formBindArray, $userId, $immunizationId, $pid]
            );
            return $immunizationId;
        }

        $uuid = $form['uuid'] ?? null;
        return ($this->insert)(
            "INSERT INTO immunizations SET uuid = ?, " . self::FORM_COLUMNS . ",
                patient_id = ?,
                created_by = ?,
                updated_by = ?,
                create_date = now()",
            [
                is_string($uuid) && UuidRegistry::isValidStringUUID($uuid) ? UuidRegistry::uuidToBytes($uuid) : null,
                ...$formBindArray,
                $pid,
                $userId,
                $userId,
            ]
        );
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
