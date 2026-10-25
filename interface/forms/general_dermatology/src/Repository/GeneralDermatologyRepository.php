<?php

declare(strict_types=1);

namespace GeneralDermatology\Repository;

use GeneralDermatology\Domain\TreatmentDocument;
use OpenEMR\Common\Database\QueryUtils;

/**
 * Persistence access for the canonical general_dermatology table.
 *
 * Does not read the legacy form_general_dermatology blob table.
 */
final class GeneralDermatologyRepository
{
    public const TABLE = 'general_dermatology';

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT * FROM `general_dermatology` WHERE `id` = ?',
            [$id]
        ) ?: [];

        return $row === [] ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findPatientNote(int $id, int $pid): ?array
    {
        if ($id <= 0 || $pid <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT * FROM `general_dermatology`
             WHERE `id` = ?
               AND `pid` = ?
               AND IFNULL(`general_dermatology_form_template_name`, \'\') = \'\'',
            [$id, $pid]
        ) ?: [];

        return $row === [] ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findTemplate(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $row = QueryUtils::querySingleRow(
            'SELECT * FROM `general_dermatology`
             WHERE `id` = ?
               AND IFNULL(`general_dermatology_form_template_name`, \'\') <> \'\'',
            [$id]
        ) ?: [];

        return $row === [] ? null : $row;
    }

    /**
     * @return array{schema_version: int, findings: list<array<string, mixed>>}
     */
    public function getTreatmentDocument(int $id): array
    {
        if ($id <= 0) {
            return TreatmentDocument::emptyDocument();
        }

        $row = QueryUtils::querySingleRow(
            'SELECT `treatment` FROM `general_dermatology` WHERE `id` = ?',
            [$id]
        ) ?: [];

        return TreatmentDocument::fromStorage($row['treatment'] ?? null);
    }

    /**
     * Findings list for Twig/PDF consumers.
     *
     * @return list<array<string, mixed>>
     */
    public function getTreatmentFindings(int $id): array
    {
        return TreatmentDocument::findingsForUi($this->getTreatmentDocument($id));
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function listActiveTemplatesForDropdown(): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT id, general_dermatology_form_template_name AS name
             FROM general_dermatology
             WHERE IFNULL(general_dermatology_form_template_name, '') <> ''
               AND IFNULL(template_status, 'active') = 'active'
             ORDER BY general_dermatology_form_template_name ASC",
            []
        ) ?: [];

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => (string) ($row['name'] ?? ''),
            ];
        }

        return $out;
    }
}
