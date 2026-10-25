<?php

declare(strict_types=1);

namespace GeneralDermatology\Support;

use GeneralDermatology\Exception\GeneralDermatologyException;
use GeneralDermatology\Repository\GeneralDermatologyRepository;
use GeneralDermatology\Services\TemplateFormService;
use OpenEMR\Common\Database\QueryUtils;

/**
 * Ownership-aware access to general_dermatology rows.
 */
final class NoteAccessService
{
    public function __construct(
        private readonly FormContext $context,
        private readonly GeneralDermatologyRepository $repository = new GeneralDermatologyRepository(),
    ) {
    }

    public static function fromContext(FormContext $context): self
    {
        return new self($context, new GeneralDermatologyRepository());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    /**
     * Load a patient chart note only when it belongs to the active patient.
     *
     * @return array<string, mixed>
     */
    public function requirePatientNote(int $id, ?int $expectedPid = null): array
    {
        $pid = $expectedPid ?? $this->context->pid();
        if ($pid <= 0) {
            throw new GeneralDermatologyException('No active patient in session.');
        }

        $row = $this->repository->findPatientNote($id, $pid);
        if ($row === null) {
            // Distinguish missing vs wrong-owner / template.
            $any = $this->repository->findById($id);
            if ($any === null) {
                throw new GeneralDermatologyException('Dermatology note not found.');
            }
            if (TemplateFormService::isTemplateRow($any)) {
                throw new GeneralDermatologyException('Template rows cannot be opened as patient notes.');
            }
            throw new GeneralDermatologyException('Dermatology note does not belong to the active patient.');
        }

        return $row;
    }

    /**
     * Load a template row (named template), independent of patient pid.
     *
     * @return array<string, mixed>
     */
    public function requireTemplate(int $id): array
    {
        $row = $this->repository->findTemplate($id);
        if ($row === null) {
            throw new GeneralDermatologyException('Template not found.');
        }

        return $row;
    }

    /**
     * True when a row may be shown/edited as a patient note for the active patient.
     *
     * @param array<string, mixed> $row
     */
    public function patientOwnsRow(array $row, ?int $expectedPid = null): bool
    {
        $pid = $expectedPid ?? $this->context->pid();

        return self::rowBelongsToPatient($row, $pid);
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function rowBelongsToPatient(array $row, int $pid): bool
    {
        if (TemplateFormService::isTemplateRow($row)) {
            return false;
        }

        if ($pid <= 0) {
            return false;
        }

        return (int) ($row['pid'] ?? 0) === $pid;
    }

    /**
     * PDF/print path: form id must match patient and the visit encounter when present.
     *
     * @return array<string, mixed>
     */
    public function requirePatientNoteForVisit(int $formId, int $visitId): array
    {
        $row = $this->requirePatientNote($formId);
        $rowEncounter = (int) ($row['encounter'] ?? 0);
        if ($rowEncounter > 0 && $visitId > 0 && $rowEncounter !== $visitId) {
            throw new GeneralDermatologyException('Dermatology note does not match the requested visit.');
        }

        if ($visitId > 0) {
            $encounter = QueryUtils::querySingleRow(
                'SELECT pid FROM form_encounter WHERE encounter = ?',
                [$visitId]
            ) ?: [];
            if ($encounter !== [] && (int) ($encounter['pid'] ?? 0) !== (int) ($row['pid'] ?? 0)) {
                throw new GeneralDermatologyException('Visit does not belong to the note patient.');
            }
        }

        return $row;
    }

    public function repository(): GeneralDermatologyRepository
    {
        return $this->repository;
    }
}
