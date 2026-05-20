<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

/**
 * @phpstan-type AppointmentRow array{
 *     pc_eid: int,
 *     appointmentDate: string,
 *     pc_startTime: string,
 *     pc_pid: int,
 *     pc_facility: int,
 *     pc_aid: int,
 *     pc_apptstatus: string,
 *     pc_title: string,
 *     fname: string,
 *     lname: string,
 *     mname: string,
 *     dob: string,
 *     sex: string,
 *     pid: int,
 *     facility_name: string,
 *     provider_name: string,
 *     elig_status: ?string,
 *     elig_payer_responsibility: ?string,
 *     elig_last_checked: ?string,
 *     elig_response_message: ?string,
 *     elig_eligibility_json: ?string,
 *     elig_individual_json: ?string
 * }
 * @phpstan-type FacilityRow array{id: int, name: string}
 * @phpstan-type ProviderRow array{id: int, provider_name: string}
 */
class AppointmentsPage
{
    /**
     * @return list<AppointmentRow>
     */
    public static function getUpcomingAppointments(string $startDate, string $endDate, ?string $facilityId = null, ?string $providerId = null, string $eligibilityFilter = 'all'): array
    {
        $sql = "SELECT
                    e.pc_eid,
                    DATE_FORMAT(e.pc_eventDate, '%Y-%m-%d') as appointmentDate,
                    e.pc_startTime,
                    e.pc_pid,
                    e.pc_facility,
                    e.pc_aid,
                    e.pc_apptstatus,
                    e.pc_title,
                    p.fname,
                    p.lname,
                    p.mname,
                    DATE_FORMAT(p.dob, '%Y-%m-%d') as dob,
                    p.sex,
                    p.pid,
                    f.name as facility_name,
                    CONCAT(u.fname, ' ', u.lname) as provider_name,
                    elig.status as elig_status,
                    elig.payer_responsibility as elig_payer_responsibility,
                    coalesce(elig.last_checked, elig.create_date) as elig_last_checked,
                    elig.response_message as elig_response_message,
                    elig.eligibility_json as elig_eligibility_json,
                    elig.individual_json as elig_individual_json
                FROM openemr_postcalendar_events AS e
                LEFT JOIN patient_data AS p ON e.pc_pid = p.pid
                LEFT JOIN facility AS f ON e.pc_facility = f.id
                LEFT JOIN users AS u ON e.pc_aid = u.id
                LEFT JOIN mod_claimrev_eligibility AS elig ON (
                    elig.pid = e.pc_pid
                    AND elig.payer_responsibility = 'P'
                )
                WHERE e.pc_eventDate >= ?
                AND e.pc_eventDate <= ?";

        $sqlarr = [$startDate, $endDate];

        if ($facilityId !== null && $facilityId !== '') {
            $sql .= " AND e.pc_facility = ?";
            $sqlarr[] = $facilityId;
        }

        if ($providerId !== null && $providerId !== '') {
            $sql .= " AND e.pc_aid = ?";
            $sqlarr[] = $providerId;
        }

        // Apply eligibility status filter
        $staleAge = TypeCoerce::asInt(OEGlobalsBag::getInstance()->get(GlobalConfig::CONFIG_ENABLE_RESULTS_ELIGIBILITY) ?? 30, 30);
        if ($staleAge < 1) {
            $staleAge = 30;
        }

        switch ($eligibilityFilter) {
            case 'needs_attention':
                $sql .= " AND (elig.id IS NULL OR elig.status IN ('error', 'senderror') OR DATEDIFF(NOW(), COALESCE(elig.last_checked, elig.create_date)) >= ?)";
                $sqlarr[] = $staleAge;
                break;
            case 'not_checked':
                $sql .= " AND elig.id IS NULL";
                break;
            case 'stale':
                $sql .= " AND elig.id IS NOT NULL AND elig.status NOT IN ('waiting', 'creating') AND DATEDIFF(NOW(), COALESCE(elig.last_checked, elig.create_date)) >= ?";
                $sqlarr[] = $staleAge;
                break;
            case 'active_coverage':
                $sql .= " AND elig.status = 'SUCCESS'";
                break;
            // 'all' — no additional filter
        }

        $sql .= " ORDER BY e.pc_eventDate ASC, e.pc_startTime ASC";

        $rows = QueryUtils::fetchRecords($sql, $sqlarr);
        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'pc_eid' => TypeCoerce::asInt($r['pc_eid'] ?? 0),
                'appointmentDate' => TypeCoerce::asString($r['appointmentDate'] ?? ''),
                'pc_startTime' => TypeCoerce::asString($r['pc_startTime'] ?? ''),
                'pc_pid' => TypeCoerce::asInt($r['pc_pid'] ?? 0),
                'pc_facility' => TypeCoerce::asInt($r['pc_facility'] ?? 0),
                'pc_aid' => TypeCoerce::asInt($r['pc_aid'] ?? 0),
                'pc_apptstatus' => TypeCoerce::asString($r['pc_apptstatus'] ?? ''),
                'pc_title' => TypeCoerce::asString($r['pc_title'] ?? ''),
                'fname' => TypeCoerce::asString($r['fname'] ?? ''),
                'lname' => TypeCoerce::asString($r['lname'] ?? ''),
                'mname' => TypeCoerce::asString($r['mname'] ?? ''),
                'dob' => TypeCoerce::asString($r['dob'] ?? ''),
                'sex' => TypeCoerce::asString($r['sex'] ?? ''),
                'pid' => TypeCoerce::asInt($r['pid'] ?? 0),
                'facility_name' => TypeCoerce::asString($r['facility_name'] ?? ''),
                'provider_name' => TypeCoerce::asString($r['provider_name'] ?? ''),
                'elig_status' => isset($r['elig_status']) ? TypeCoerce::asString($r['elig_status']) : null,
                'elig_payer_responsibility' => isset($r['elig_payer_responsibility']) ? TypeCoerce::asString($r['elig_payer_responsibility']) : null,
                'elig_last_checked' => isset($r['elig_last_checked']) ? TypeCoerce::asString($r['elig_last_checked']) : null,
                'elig_response_message' => isset($r['elig_response_message']) ? TypeCoerce::asString($r['elig_response_message']) : null,
                'elig_eligibility_json' => isset($r['elig_eligibility_json']) ? TypeCoerce::asString($r['elig_eligibility_json']) : null,
                'elig_individual_json' => isset($r['elig_individual_json']) ? TypeCoerce::asString($r['elig_individual_json']) : null,
            ];
        }
        return $result;
    }

    public static function runEligibilityForAppointment(string $eid): void
    {
        $appointmentData = EligibilityData::getPatientIdFromAppointment($eid);
        if ($appointmentData === null) {
            return;
        }

        $pid = $appointmentData['pc_pid'];
        $eventDate = $appointmentData['appointmentDate'];
        $facilityId = $appointmentData['facilityId'];
        $providerId = $appointmentData['providerId'];

        $insurance = EligibilityData::getInsuranceData($pid);
        foreach ($insurance as $row) {
            $pr = $row['payer_responsibility'];
            $formattedPr = ValueMapping::mapPayerResponsibility($pr);
            EligibilityData::removeEligibilityCheck($pid, $formattedPr);
            $requestObjects = EligibilityObjectCreator::buildObject($pid, $pr, $eventDate !== '' ? $eventDate : null, $facilityId !== 0 ? $facilityId : null, $providerId !== 0 ? $providerId : null);
            EligibilityObjectCreator::saveToDatabase($requestObjects, $pid);
        }
    }

    /**
     * @return list<FacilityRow>
     */
    public static function getFacilities(): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT id, name FROM facility WHERE service_location = 1 ORDER BY name"
        );
        return array_map(static fn(array $r): array => [
            'id' => TypeCoerce::asInt($r['id'] ?? 0),
            'name' => TypeCoerce::asString($r['name'] ?? ''),
        ], $rows);
    }

    /**
     * @return list<ProviderRow>
     */
    public static function getProviders(): array
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT id, CONCAT(fname, ' ', lname) as provider_name
                FROM users
                WHERE authorized = 1
                AND active = 1
                AND npi IS NOT NULL
                AND npi != ''
                ORDER BY lname, fname"
        );
        return array_map(static fn(array $r): array => [
            'id' => TypeCoerce::asInt($r['id'] ?? 0),
            'provider_name' => TypeCoerce::asString($r['provider_name'] ?? ''),
        ], $rows);
    }

    /**
     * @return list<\stdClass>|null
     */
    public static function getEligibilitySummary(?string $eligJson): ?array
    {
        if ($eligJson === null || $eligJson === '') {
            return null;
        }

        $individual = json_decode($eligJson);
        if (!is_object($individual) || !property_exists($individual, 'eligibility') || !is_iterable($individual->eligibility)) {
            return null;
        }

        $results = [];
        foreach ($individual->eligibility as $eligibilityData) {
            if (!is_object($eligibilityData)) {
                continue;
            }
            $summary = new \stdClass();
            $summary->status = '';
            $summary->payerName = '';
            $summary->subscriberId = '';
            $summary->insuranceType = '';

            if (property_exists($eligibilityData, 'status')) {
                $summary->status = $eligibilityData->status;
            }
            if (property_exists($eligibilityData, 'payerInfo') && is_object($eligibilityData->payerInfo) && property_exists($eligibilityData->payerInfo, 'payerName')) {
                $summary->payerName = $eligibilityData->payerInfo->payerName;
            }
            if (property_exists($eligibilityData, 'subscriberId')) {
                $summary->subscriberId = $eligibilityData->subscriberId;
            }
            if (property_exists($eligibilityData, 'insuranceType')) {
                $summary->insuranceType = $eligibilityData->insuranceType;
            }

            $results[] = $summary;
        }

        return $results;
    }
}
