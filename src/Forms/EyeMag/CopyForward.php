<?php

/**
 * Field selection for eye exam copy-forward requests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

/**
 * Decides which columns of a prior eye exam record a copy-forward request
 * carries into today's chart.
 */
final class CopyForward
{
    /**
     * Selects one prior exam as a single flat row spanning every `form_eye_*` table.
     */
    public const RECORD_QUERY = <<<'SQL'
        SELECT *, form_encounter.date AS encounter_date
        FROM forms, form_encounter, form_eye_base,
             form_eye_hpi, form_eye_ros, form_eye_vitals,
             form_eye_acuity, form_eye_refraction, form_eye_biometrics,
             form_eye_external, form_eye_antseg, form_eye_postseg,
             form_eye_neuro, form_eye_locking
        WHERE forms.deleted != '1'
          AND forms.formdir = 'eye_mag'
          AND forms.encounter = form_encounter.encounter
          AND forms.form_id = form_eye_base.id
          AND forms.form_id = form_eye_hpi.id
          AND forms.form_id = form_eye_ros.id
          AND forms.form_id = form_eye_vitals.id
          AND forms.form_id = form_eye_acuity.id
          AND forms.form_id = form_eye_refraction.id
          AND forms.form_id = form_eye_biometrics.id
          AND forms.form_id = form_eye_external.id
          AND forms.form_id = form_eye_antseg.id
          AND forms.form_id = form_eye_postseg.id
          AND forms.form_id = form_eye_neuro.id
          AND forms.form_id = form_eye_locking.id
          AND forms.pid = ?
          AND forms.form_id = ?
        SQL;

    /**
     * Fields carried forward by a whole-form copy. Zone fields overlap (the tear
     * film measurements belong to both the external and anterior segment exams),
     * so the union is deduplicated.
     *
     * @return list<string>
     */
    public static function allFields(): array
    {
        $fields = array_merge(...array_map(static fn(Zone $zone): array => $zone->fields(), Zone::cases()));
        $fields[] = 'IMP';

        return array_values(array_unique($fields));
    }

    /**
     * Reads the named fields out of an exam record, defaulting anything the
     * record does not carry to null.
     *
     * The record is a raw database row, which promises nothing about its keys,
     * so this takes it as loosely as the database hands it over.
     *
     * @param array<array-key, mixed> $record
     * @param list<string> $fields
     * @return array<string, mixed>
     */
    public static function pick(array $record, array $fields): array
    {
        return array_combine(
            $fields,
            array_map(static fn(string $field): mixed => $record[$field] ?? null, $fields),
        );
    }
}
