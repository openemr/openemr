<?php

/**
 * Daily Summary Report Service
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Reports\DailySummary;

use League\Csv\Bom;
use League\Csv\Writer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Utils\DateFormatterUtils;

class DailySummaryService
{
    /**
     * @return list<array<mixed>>
     */
    public function fetchAppointmentsSummary(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        /** @var list<mixed> $sqlBindArray */
        $sqlBindArray = [$fromDate, $toDate];

        $query = "SELECT DATE(pc_eventDate) as eventDate,
                         name as facilityName,
                         CONCAT(u.fname, ' ', u.lname) as providerName,
                         u.id as providerId,
                         f.id as facilityId,
                         COUNT(*) as totalAppointments
                  FROM openemr_postcalendar_events pc
                  LEFT JOIN facility f ON pc.pc_facility = f.id
                  LEFT JOIN users u ON pc.pc_aid = u.id
                  WHERE DATE(pc.pc_eventDate) BETWEEN ? AND ?";

        if ($facilityId !== null) {
            $query .= " AND pc.pc_facility = ?";
            $sqlBindArray[] = $facilityId;
        }

        if ($providerId !== null) {
            $query .= " AND pc.pc_aid = ?";
            $sqlBindArray[] = $providerId;
        }

        $query .= " GROUP BY DATE(pc.pc_eventDate), f.id, u.id
                   ORDER BY DATE(pc.pc_eventDate) ASC, f.name ASC, u.lname ASC";

        return QueryUtils::fetchRecords($query, $sqlBindArray);
    }

    /**
     * @return list<array<mixed>>
     */
    public function fetchNewPatientsSummary(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        /** @var list<mixed> $sqlBindArray */
        $sqlBindArray = [$fromDate, $toDate];

        $query = "SELECT DATE(pc.pc_eventDate) as eventDate,
                         f.name as facilityName,
                         CONCAT(u.fname, ' ', u.lname) as providerName,
                         u.id as providerId,
                         f.id as facilityId,
                         COUNT(*) as newPatients
                  FROM openemr_postcalendar_events pc
                  LEFT JOIN facility f ON pc.pc_facility = f.id
                  LEFT JOIN users u ON pc.pc_aid = u.id
                  WHERE pc.pc_title = 'New Patient'
                    AND DATE(pc.pc_eventDate) BETWEEN ? AND ?";

        if ($facilityId !== null) {
            $query .= " AND pc.pc_facility = ?";
            $sqlBindArray[] = $facilityId;
        }

        if ($providerId !== null) {
            $query .= " AND pc.pc_aid = ?";
            $sqlBindArray[] = $providerId;
        }

        $query .= " GROUP BY DATE(pc.pc_eventDate), f.id, u.id
                   ORDER BY DATE(pc.pc_eventDate) ASC, f.name ASC, u.lname ASC";

        return QueryUtils::fetchRecords($query, $sqlBindArray);
    }

    /**
     * @return list<array<mixed>>
     */
    public function fetchVisitsSummary(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        /** @var list<mixed> $sqlBindArray */
        $sqlBindArray = [$fromDate, $toDate];

        $query = "SELECT DATE(fe.date) as visitDate,
                         f.name as facilityName,
                         CONCAT(u.fname, ' ', u.lname) as providerName,
                         u.id as providerId,
                         f.id as facilityId,
                         COUNT(DISTINCT fe.encounter) as totalVisits
                  FROM form_encounter fe
                  LEFT JOIN facility f ON fe.facility_id = f.id
                  LEFT JOIN users u ON fe.provider_id = u.id
                  WHERE DATE(fe.date) BETWEEN ? AND ?";

        if ($facilityId !== null) {
            $query .= " AND fe.facility_id = ?";
            $sqlBindArray[] = $facilityId;
        }

        if ($providerId !== null) {
            $query .= " AND fe.provider_id = ?";
            $sqlBindArray[] = $providerId;
        }

        $query .= " GROUP BY DATE(fe.date), f.id, u.id
                   ORDER BY DATE(fe.date) ASC, f.name ASC, u.lname ASC";

        return QueryUtils::fetchRecords($query, $sqlBindArray);
    }

    /**
     * @return list<array<mixed>>
     */
    public function fetchFinancialSummary(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        /** @var list<mixed> $sqlBindArray */
        $sqlBindArray = [$fromDate, $toDate];

        $query = "SELECT DATE(b.date) as billDate,
                         f.name as facilityName,
                         CONCAT(u.fname, ' ', u.lname) as providerName,
                         u.id as providerId,
                         f.id as facilityId,
                         SUM(b.fee) as totalCharges,
                         COUNT(DISTINCT b.encounter) as encounterCount
                  FROM billing b
                  LEFT JOIN form_encounter fe ON b.encounter = fe.encounter AND b.pid = fe.pid
                  LEFT JOIN facility f ON fe.facility_id = f.id
                  LEFT JOIN users u ON fe.provider_id = u.id
                  WHERE b.activity = 1
                    AND b.code_type NOT IN ('COPAY', 'PAYMENT')
                    AND DATE(b.date) BETWEEN ? AND ?";

        if ($facilityId !== null) {
            $query .= " AND fe.facility_id = ?";
            $sqlBindArray[] = $facilityId;
        }

        if ($providerId !== null) {
            $query .= " AND fe.provider_id = ?";
            $sqlBindArray[] = $providerId;
        }

        $query .= " GROUP BY DATE(b.date), f.id, u.id
                   ORDER BY DATE(b.date) ASC, f.name ASC, u.lname ASC";

        return QueryUtils::fetchRecords($query, $sqlBindArray);
    }

    /**
     * @return list<array<mixed>>
     */
    public function fetchPaymentsSummary(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        /** @var list<mixed> $sqlBindArray */
        $sqlBindArray = [$fromDate, $toDate];

        $query = "SELECT DATE(p.dtime) as paymentDate,
                         f.name as facilityName,
                         CONCAT(u.fname, ' ', u.lname) as providerName,
                         u.id as providerId,
                         f.id as facilityId,
                         SUM(p.amount1) as totalPaid,
                         COUNT(DISTINCT p.encounter) as paymentCount
                  FROM payments p
                  LEFT JOIN form_encounter fe ON p.encounter = fe.encounter AND p.pid = fe.pid
                  LEFT JOIN facility f ON fe.facility_id = f.id
                  LEFT JOIN users u ON fe.provider_id = u.id
                  WHERE DATE(p.dtime) BETWEEN ? AND ?";

        if ($facilityId !== null) {
            $query .= " AND fe.facility_id = ?";
            $sqlBindArray[] = $facilityId;
        }

        if ($providerId !== null) {
            $query .= " AND fe.provider_id = ?";
            $sqlBindArray[] = $providerId;
        }

        $query .= " GROUP BY DATE(p.dtime), f.id, u.id
                   ORDER BY DATE(p.dtime) ASC, f.name ASC, u.lname ASC";

        return QueryUtils::fetchRecords($query, $sqlBindArray);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function calculateAgingAnalysis(?int $facilityId = null, ?int $providerId = null): array
    {
        return [];
    }

    /**
     * @param list<array<mixed>> $appointments
     * @param list<array<mixed>> $visits
     * @param list<array<mixed>> $financial
     * @param list<array<mixed>> $payments
     * @return array{
     *     total_appointments: int,
     *     total_visits: int,
     *     total_charges: float,
     *     total_paid: float,
     *     total_balance: float,
     *     collection_rate: float,
     *     no_show_rate: float,
     *     average_charge_per_visit: float|int,
     *     average_payment_per_visit: float|int
     * }
     */
    public function calculateMetrics(
        array $appointments,
        array $visits,
        array $financial,
        array $payments
    ): array {
        $totalAppointments = $this->sumNumericField($appointments, 'totalAppointments');
        $totalVisits = $this->sumNumericField($visits, 'totalVisits');
        $totalCharges = $this->sumNumericField($financial, 'totalCharges');
        $totalPaid = $this->sumNumericField($payments, 'totalPaid');

        $totalBalance = $totalCharges - $totalPaid;
        $collectionRate = $totalCharges > 0 ? ($totalPaid / $totalCharges) * 100 : 0.0;
        $noShowRate = $totalAppointments > 0
            ? (($totalAppointments - $totalVisits) / $totalAppointments) * 100
            : 0.0;

        return [
            'total_appointments' => (int) $totalAppointments,
            'total_visits' => (int) $totalVisits,
            'total_charges' => $totalCharges,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
            'collection_rate' => round($collectionRate, 2),
            'no_show_rate' => round($noShowRate, 2),
            'average_charge_per_visit' => $totalVisits > 0 ? round($totalCharges / $totalVisits, 2) : 0,
            'average_payment_per_visit' => $totalVisits > 0 ? round($totalPaid / $totalVisits, 2) : 0,
        ];
    }

    /**
     * @param list<array<mixed>> $appointments
     * @param list<array<mixed>> $visits
     * @param list<array<mixed>> $financial
     * @param list<array<mixed>> $payments
     * @return array<string, array<string, float|int|string>>
     */
    public function calculateProviderMetrics(
        array $appointments,
        array $visits,
        array $financial,
        array $payments
    ): array {
        /** @var array<string, array<string, float|string>> $providers */
        $providers = [];

        foreach ($appointments as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            $provider = $this->stringField($row, 'providerName', 'Unknown');
            $this->addToBucket($providers, $provider, 'appointments', $this->numericField($row, 'totalAppointments'));
        }

        foreach ($visits as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            $provider = $this->stringField($row, 'providerName', 'Unknown');
            $this->addToBucket($providers, $provider, 'visits', $this->numericField($row, 'totalVisits'));
        }

        foreach ($financial as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            $provider = $this->stringField($row, 'providerName', 'Unknown');
            $this->addToBucket($providers, $provider, 'charges', $this->numericField($row, 'totalCharges'));
        }

        foreach ($payments as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            $provider = $this->stringField($row, 'providerName', 'Unknown');
            $this->addToBucket($providers, $provider, 'paid', $this->numericField($row, 'totalPaid'));
        }

        $result = [];
        foreach ($providers as $name => $provider) {
            $charges = $this->asFloat($provider['charges'] ?? 0);
            $paid = $this->asFloat($provider['paid'] ?? 0);
            $appointmentsCount = $this->asFloat($provider['appointments'] ?? 0);
            $visitsCount = $this->asFloat($provider['visits'] ?? 0);
            $displayName = is_string($provider['name'] ?? null) ? $provider['name'] : $name;

            $result[$name] = [
                'name' => $displayName,
                'appointments' => $appointmentsCount,
                'visits' => $visitsCount,
                'charges' => $charges,
                'paid' => $paid,
                'balance' => $charges - $paid,
                'collection_rate' => $charges > 0 ? (($paid / $charges) * 100) : 0.0,
                'no_show_rate' => $appointmentsCount > 0
                    ? ((($appointmentsCount - $visitsCount) / $appointmentsCount) * 100)
                    : 0.0,
            ];
        }

        return $result;
    }

    public function getPercentageChange(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0.0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 2);
    }

    /**
     * @param list<array<mixed>> $appointments
     * @param list<array<mixed>> $newPatients
     * @param list<array<mixed>> $visits
     * @param list<array<mixed>> $financial
     * @param list<array<mixed>> $payments
     * @return array<string, array<string, array<string, array<string, float|int>>>>
     */
    public function mergeDataByDimensions(
        array $appointments,
        array $newPatients,
        array $visits,
        array $financial,
        array $payments
    ): array {
        /** @var array<string, array<string, array<string, array<string, float>>>> $merged */
        $merged = [];

        foreach ($appointments as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            [$date, $facility, $provider] = $this->dimensionKeys($row, 'eventDate');
            $this->addToDimension($merged, $date, $facility, $provider, 'appointments', $this->numericField($row, 'totalAppointments'));
        }

        foreach ($newPatients as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            [$date, $facility, $provider] = $this->dimensionKeys($row, 'eventDate');
            $this->addToDimension($merged, $date, $facility, $provider, 'newPatients', $this->numericField($row, 'newPatients'));
        }

        foreach ($visits as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            [$date, $facility, $provider] = $this->dimensionKeys($row, 'visitDate');
            $this->addToDimension($merged, $date, $facility, $provider, 'visits', $this->numericField($row, 'totalVisits'));
        }

        foreach ($financial as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            [$date, $facility, $provider] = $this->dimensionKeys($row, 'billDate');
            $this->addToDimension($merged, $date, $facility, $provider, 'charges', $this->numericField($row, 'totalCharges'));
        }

        foreach ($payments as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            [$date, $facility, $provider] = $this->dimensionKeys($row, 'paymentDate');
            $this->addToDimension($merged, $date, $facility, $provider, 'paid', $this->numericField($row, 'totalPaid'));
        }

        $result = [];
        foreach ($merged as $date => $dateData) {
            foreach ($dateData as $facility => $facilityData) {
                foreach ($facilityData as $provider => $providerData) {
                    $charges = $this->asFloat($providerData['charges'] ?? 0);
                    $paid = $this->asFloat($providerData['paid'] ?? 0);
                    $appointmentsCount = $this->asFloat($providerData['appointments'] ?? 0);
                    $visitsCount = $this->asFloat($providerData['visits'] ?? 0);
                    $newPatientsCount = $this->asFloat($providerData['newPatients'] ?? 0);

                    $result[$date][$facility][$provider] = [
                        'appointments' => $appointmentsCount,
                        'newPatients' => $newPatientsCount,
                        'visits' => $visitsCount,
                        'charges' => $charges,
                        'paid' => $paid,
                        'balance' => $charges - $paid,
                        'collection_rate' => $charges > 0 ? (($paid / $charges) * 100) : 0.0,
                        'no_show_rate' => $appointmentsCount > 0
                            ? ((($appointmentsCount - $visitsCount) / $appointmentsCount) * 100)
                            : 0.0,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Export report data to CSV and stream the download.
     *
     * @param array<string, array<string, array<string, array<string, float|int>>>>|null $reportData
     * @param array<string, float|int> $summaryMetrics
     * @param list<array<string, float|int|string>> $providerMetrics
     */
    public function exportToCsv(
        ?array $reportData,
        array $summaryMetrics,
        array $providerMetrics,
        string $fromDate,
        string $toDate
    ): void {
        $csv = Writer::fromString('');
        $csv->setOutputBOM(Bom::Utf8);

        $csv->insertOne(['Daily Summary Report']);
        $fromFormatted = DateFormatterUtils::oeFormatShortDate($fromDate);
        $toFormatted = DateFormatterUtils::oeFormatShortDate($toDate);
        $fromLabel = is_string($fromFormatted) ? $fromFormatted : $fromDate;
        $toLabel = is_string($toFormatted) ? $toFormatted : $toDate;
        $csv->insertOne(['Report Period', $fromLabel . ' to ' . $toLabel]);
        $csv->insertOne([]);

        $csv->insertOne(['SUMMARY METRICS']);
        $csv->insertOne([
            'Total Appointments',
            'Total Visits',
            'Total Charges',
            'Total Paid',
            'Outstanding Balance',
            'Collection Rate %',
            'No-Show Rate %',
        ]);
        $csv->insertOne([
            $summaryMetrics['total_appointments'] ?? 0,
            $summaryMetrics['total_visits'] ?? 0,
            $summaryMetrics['total_charges'] ?? 0,
            $summaryMetrics['total_paid'] ?? 0,
            $summaryMetrics['total_balance'] ?? 0,
            $summaryMetrics['collection_rate'] ?? 0,
            $summaryMetrics['no_show_rate'] ?? 0,
        ]);
        $csv->insertOne([]);

        $csv->insertOne(['PROVIDER PRODUCTIVITY']);
        $csv->insertOne([
            'Provider',
            'Appointments',
            'Visits',
            'No-Show %',
            'Charges',
            'Paid',
            'Balance',
            'Collection %',
        ]);

        foreach ($providerMetrics as $provider) {
            $csv->insertOne([
                is_string($provider['name'] ?? null) ? $provider['name'] : '',
                $provider['appointments'] ?? 0,
                $provider['visits'] ?? 0,
                round($this->asFloat($provider['no_show_rate'] ?? 0), 2),
                $provider['charges'] ?? 0,
                $provider['paid'] ?? 0,
                $provider['balance'] ?? 0,
                round($this->asFloat($provider['collection_rate'] ?? 0), 2),
            ]);
        }
        $csv->insertOne([]);

        $csv->insertOne(['DAILY DETAIL']);
        $csv->insertOne([
            'Date',
            'Facility',
            'Provider',
            'Appointments',
            'New Patients',
            'Visits',
            'Charges',
            'Paid',
            'Balance',
            'Collection %',
        ]);

        if ($reportData !== null) {
            foreach ($reportData as $date => $dateData) {
                foreach ($dateData as $facility => $facilityData) {
                    foreach ($facilityData as $provider => $metrics) {
                        $csv->insertOne([
                            $date,
                            $facility,
                            $provider,
                            $metrics['appointments'] ?? 0,
                            $metrics['newPatients'] ?? 0,
                            $metrics['visits'] ?? 0,
                            $metrics['charges'] ?? 0,
                            $metrics['paid'] ?? 0,
                            $metrics['balance'] ?? 0,
                            round($this->asFloat($metrics['collection_rate'] ?? 0), 2),
                        ]);
                    }
                }
            }
        }

        $csv->download('daily_summary_' . date('Y-m-d_H-i-s') . '.csv');
    }

    /**
     * @param list<array<mixed>> $rows
     */
    private function sumNumericField(array $rows, string $field): float
    {
        $sum = 0.0;
        foreach ($rows as $item) {
            /** @var array<string|int, mixed> $row */
            $row = $item;
            $sum += $this->numericField($row, $field);
        }

        return $sum;
    }

    /**
     * @param array<string|int, mixed> $item
     */
    private function numericField(array $item, string $field): float
    {
        return $this->asFloat($item[$field] ?? 0);
    }

    private function asFloat(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }

    /**
     * @param array<string|int, mixed> $item
     */
    private function stringField(array $item, string $field, string $default = ''): string
    {
        $value = $item[$field] ?? null;
        return is_string($value) && $value !== '' ? $value : $default;
    }

    /**
     * @param array<string|int, mixed> $item
     * @return array{0: string, 1: string, 2: string}
     */
    private function dimensionKeys(array $item, string $dateField): array
    {
        return [
            $this->stringField($item, $dateField, date('Y-m-d')),
            $this->stringField($item, 'facilityName', 'Unknown'),
            $this->stringField($item, 'providerName', 'Unknown'),
        ];
    }

    /**
     * @param array<string, array<string, float|string>> $providers
     */
    private function addToBucket(array &$providers, string $provider, string $metric, float $amount): void
    {
        if (!isset($providers[$provider])) {
            $providers[$provider] = [
                'name' => $provider,
                'appointments' => 0.0,
                'visits' => 0.0,
                'charges' => 0.0,
                'paid' => 0.0,
            ];
        }

        $current = $this->asFloat($providers[$provider][$metric] ?? 0);
        $providers[$provider][$metric] = $current + $amount;
    }

    /**
     * @param array<string, array<string, array<string, array<string, float>>>> $merged
     */
    private function addToDimension(
        array &$merged,
        string $date,
        string $facility,
        string $provider,
        string $metric,
        float $amount
    ): void {
        if (!isset($merged[$date][$facility][$provider])) {
            $merged[$date][$facility][$provider] = [
                'appointments' => 0.0,
                'newPatients' => 0.0,
                'visits' => 0.0,
                'charges' => 0.0,
                'paid' => 0.0,
            ];
        }

        $current = $this->asFloat($merged[$date][$facility][$provider][$metric] ?? 0);
        $merged[$date][$facility][$provider][$metric] = $current + $amount;
    }
}
