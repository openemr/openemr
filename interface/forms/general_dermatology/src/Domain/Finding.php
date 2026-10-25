<?php

declare(strict_types=1);

namespace GeneralDermatology\Domain;

use GeneralDermatology\Catalog\LocationCatalog;
use GeneralDermatology\Catalog\ProcedureCatalog;

/**
 * Immutable view of one treatment finding / plan entry.
 */
final readonly class Finding
{
    /**
     * @param array<string, mixed> $raw
     * @param array{id: string, label: string, laterality: string, view: string, markers: list<array{x: float, y: float}>} $location
     */
    public function __construct(
        public string $findingLocation,
        public array $location,
        public string $dxCode,
        public string $dxDescription,
        public string $severity,
        public string $plan,
        public string $planDescription,
        public string $procedureKey,
        public string $procedureLabel,
        public array $raw,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        $label = trim((string) ($row['finding_location'] ?? ''));
        $location = $row['location'] ?? null;
        if (!is_array($location)) {
            $location = [
                'id' => LocationCatalog::idFromLabel($label),
                'label' => $label,
                'laterality' => LocationCatalog::inferLaterality($label),
                'view' => LocationCatalog::inferView($label),
                'markers' => [],
            ];
        } else {
            $location = [
                'id' => (string) ($location['id'] ?? LocationCatalog::idFromLabel($label)),
                'label' => (string) ($location['label'] ?? $label),
                'laterality' => (string) ($location['laterality'] ?? LocationCatalog::inferLaterality($label)),
                'view' => (string) ($location['view'] ?? LocationCatalog::inferView($label)),
                'markers' => is_array($location['markers'] ?? null) ? $location['markers'] : [],
            ];
        }

        $dxCode = trim((string) ($row['dx_s'] ?? ''));
        $dxDesc = $row['dx_desc'] ?? '';
        if (is_array($dxDesc)) {
            $dxDesc = implode(', ', array_map('strval', $dxDesc));
        }
        $dxDesc = trim((string) $dxDesc);

        $procedureKey = trim((string) ($row['procedure_plans'] ?? ''));
        $procedureLabel = $procedureKey !== '' ? ProcedureCatalog::labelFor($procedureKey) : '';

        $severity = trim((string) ($row['severity_drop'] ?? $row['severity'] ?? ''));

        return new self(
            findingLocation: $label,
            location: $location,
            dxCode: $dxCode,
            dxDescription: $dxDesc,
            severity: $severity,
            plan: trim((string) ($row['plan'] ?? '')),
            planDescription: trim((string) ($row['plan_desc'] ?? '')),
            procedureKey: $procedureKey,
            procedureLabel: $procedureLabel,
            raw: $row,
        );
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<self>
     */
    public static function listFromArrays(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $out[] = self::fromArray($row);
            }
        }

        return $out;
    }

    public function isEmpty(): bool
    {
        return $this->findingLocation === ''
            && $this->dxCode === ''
            && $this->plan === ''
            && $this->procedureKey === '';
    }
}
