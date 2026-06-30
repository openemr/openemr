<?php

/**
 * Generates the UDS patient-characteristics tables for a reporting year.
 *
 * Orchestration only: it pulls the patient cohort from an injected
 * ReportingPatientSource, maps each patient to the per-table records via the
 * pure factory, feeds the aggregators, and runs the cross-table reconciliation.
 * Because the source is an interface, this whole pipeline is unit-testable with
 * an in-memory source; production injects the database-backed repository.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final class UdsReportGenerator
{
    private UdsPatientRecordFactory $recordFactory;
    private Table3aReportBuilder $table3aBuilder;
    private Table3bReportBuilder $table3bBuilder;
    private ZipCodeTableReportBuilder $zipBuilder;
    private Table4ReportBuilder $table4Builder;
    private CrossTableReconciliation $reconciliation;

    public function __construct(
        private ReportingPatientSource $source,
        ?UdsPatientRecordFactory $recordFactory = null,
        ?Table3aReportBuilder $table3aBuilder = null,
        ?Table3bReportBuilder $table3bBuilder = null,
        ?ZipCodeTableReportBuilder $zipBuilder = null,
        ?Table4ReportBuilder $table4Builder = null,
        ?CrossTableReconciliation $reconciliation = null,
    ) {
        $this->recordFactory = $recordFactory ?? new UdsPatientRecordFactory();
        $this->table3aBuilder = $table3aBuilder ?? new Table3aReportBuilder();
        $this->table3bBuilder = $table3bBuilder ?? new Table3bReportBuilder();
        $this->zipBuilder = $zipBuilder ?? new ZipCodeTableReportBuilder();
        $this->table4Builder = $table4Builder ?? new Table4ReportBuilder();
        $this->reconciliation = $reconciliation ?? new CrossTableReconciliation();
    }

    public function generateForYear(int $year): UdsPatientCharacteristicsReport
    {
        $cohort = $this->source->cohortForYear($year);

        /** @var list<Table3aPatientRecord> $table3aRecords */
        $table3aRecords = [];
        /** @var list<Table3bPatientRecord> $table3bRecords */
        $table3bRecords = [];
        /** @var list<ZipCodeTablePatientRecord> $zipRecords */
        $zipRecords = [];
        /** @var list<Table4PatientRecord> $table4Records */
        $table4Records = [];

        foreach ($cohort as $pid) {
            $patient = $this->source->load($pid, $year);

            $table3a = $this->recordFactory->table3a($patient);
            if ($table3a !== null) {
                $table3aRecords[] = $table3a;
            }

            $table3bRecords[] = $this->recordFactory->table3b($patient);
            $zipRecords[] = $this->recordFactory->zip($patient);

            $table4 = $this->recordFactory->table4($patient);
            if ($table4 !== null) {
                $table4Records[] = $table4;
            }
        }

        $table3a = $this->table3aBuilder->build($table3aRecords);
        $table3b = $this->table3bBuilder->build($table3bRecords);
        $zip = $this->zipBuilder->build($zipRecords);
        $table4 = $this->table4Builder->build($table4Records);

        return new UdsPatientCharacteristicsReport(
            year: $year,
            cohortSize: count($cohort),
            table3a: $table3a,
            table3b: $table3b,
            zipCodeTable: $zip,
            table4: $table4,
            reconciliation: $this->reconciliation->reconcile($table3a, $table3b, $zip, $table4),
        );
    }
}
