<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

    namespace OpenEMR\Modules\ClaimRevConnector;

    use OpenEMR\Common\Database\QueryUtils;

/**
 * @phpstan-type AppointmentForEligibility array{pc_pid: int, appointmentDate: string, facilityId: int, providerId: int}
 * @phpstan-type InsuranceRow array{payer_responsibility: string}
 * @phpstan-type EligibilityResultRow array{status: string, last_update: string, response_json: ?string, eligibility_json: ?string, individual_json: ?string, response_message: ?string}
 */
class EligibilityData
{
    public function __construct()
    {
    }

    /**
     * @return AppointmentForEligibility|null
     */
    public static function getPatientIdFromAppointment(string $eid): ?array
    {
        $sql = "SELECT
                pc_pid
                ,DATE_FORMAT(pc_eventDate, '%Y-%m-%d') as appointmentDate
                ,pc_facility as facilityId
                ,pc_aid as providerId
                from openemr_postcalendar_events
                WHERE pc_eid = ?
            LIMIT 1";
        $rows = QueryUtils::fetchRecords($sql, [$eid]);
        if ($rows === []) {
            return null;
        }
        $row = $rows[0];
        return [
            'pc_pid' => TypeCoerce::asInt($row['pc_pid'] ?? 0),
            'appointmentDate' => TypeCoerce::asString($row['appointmentDate'] ?? ''),
            'facilityId' => TypeCoerce::asInt($row['facilityId'] ?? 0),
            'providerId' => TypeCoerce::asInt($row['providerId'] ?? 0),
        ];
    }

    public static function removeEligibilityCheck(int $pid, string $payer_responsibility): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ? ",
            [$pid, $payer_responsibility]
        );
    }

    /**
     * Remove every cached eligibility record for a patient (across all payer
     * responsibilities). Used by the "Reset" button on the eligibility tab to
     * give testers a clean slate.
     */
    public static function removeAllEligibilityForPatient(int $pid): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM mod_claimrev_eligibility WHERE pid = ?",
            [$pid]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function getEligibilityCheckByStatus(string $status): array
    {
        return self::asListOfRecords(QueryUtils::fetchRecords(
            "SELECT * FROM mod_claimrev_eligibility WHERE status = ?",
            [$status]
        ));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function getEligibilityResults(string $status, int $minutes): array
    {
        return self::asListOfRecords(QueryUtils::fetchRecords(
            "SELECT * FROM mod_claimrev_eligibility WHERE status = ? AND TIMESTAMPDIFF(MINUTE,last_checked,NOW()) >= ?",
            [$status, $minutes]
        ));
    }

    /**
     * @param list<array<mixed, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private static function asListOfRecords(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            /** @var array<string, mixed> $row */
            $out[] = $row;
        }
        return $out;
    }

    /**
     * @return list<EligibilityResultRow>
     */
    public static function getEligibilityResult(int $pid, string $payer_responsibility): array
    {
        $pr = ValueMapping::mapPayerResponsibility($payer_responsibility);
        $sql = "SELECT status, coalesce(last_checked,create_date) as last_update,response_json,eligibility_json,individual_json,response_message  FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ? LIMIT 1";
        $rows = QueryUtils::fetchRecords($sql, [$pid, $pr]);
        return array_map(static fn(array $r): array => [
            'status' => TypeCoerce::asString($r['status'] ?? ''),
            'last_update' => TypeCoerce::asString($r['last_update'] ?? ''),
            'response_json' => isset($r['response_json']) ? TypeCoerce::asString($r['response_json']) : null,
            'eligibility_json' => isset($r['eligibility_json']) ? TypeCoerce::asString($r['eligibility_json']) : null,
            'individual_json' => isset($r['individual_json']) ? TypeCoerce::asString($r['individual_json']) : null,
            'response_message' => isset($r['response_message']) ? TypeCoerce::asString($r['response_message']) : null,
        ], $rows);
    }

    /**
     * Get the existing eligibility record for merging.
     *
     * @return array<string, mixed>|null
     */
    public static function getExistingRecord(int $pid, string $payer_responsibility): ?array
    {
        $pr = ValueMapping::mapPayerResponsibility($payer_responsibility);
        $rows = QueryUtils::fetchRecords(
            "SELECT id, status, individual_json, response_json FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ? LIMIT 1",
            [$pid, $pr]
        );
        if ($rows === []) {
            return null;
        }
        /** @var array<string, mixed> $row */
        $row = $rows[0];
        return $row;
    }

    /**
     * @param int|string  $id
     * @param string|null $request_json
     * @param string|null $response_json
     * @param string|null $responseMessage
     * @param string|null $raw271
     * @param string|null $eligibility_json
     * @param string|null $individual_json
     */
    public static function updateEligibilityRecord(
        int|string $id,
        string $status,
        ?string $request_json,
        ?string $response_json,
        bool $updateLastChecked,
        ?string $responseMessage,
        ?string $raw271,
        ?string $eligibility_json,
        ?string $individual_json,
    ): void {
        $sql = "UPDATE mod_claimrev_eligibility SET status = ? ";

        $sqlarr = [$status];
        if ($updateLastChecked) {
            $sql .= ",last_checked = NOW() ";
        }
        if ($response_json !== null) {
            $sql .= " ,response_json = ?";
            $sqlarr[] = $response_json;
        }
        if ($request_json !== null) {
            $sql .= " ,request_json = ?";
            $sqlarr[] = $request_json;
        }
        if ($responseMessage !== null) {
            $sql .= " ,response_message = ?";
            $sqlarr[] = $responseMessage;
        }
        if ($raw271 !== null) {
            $sql .= " ,raw271 = ? ";
            $sqlarr[] = $raw271;
        }
        if ($eligibility_json !== null) {
            $sql .= " ,eligibility_json = ?";
            $sqlarr[] = $eligibility_json;
        }
        if ($individual_json !== null) {
            $sql .= " ,individual_json = ?";
            $sqlarr[] = $individual_json;
        }

        $sql .= " WHERE id = ?";
        $sqlarr[] = $id;
        QueryUtils::sqlStatementThrowException($sql, $sqlarr);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function getSubscriberData(int $pid = 0, string $pr = ""): array
    {
        $query = "SELECT
                c.name as payer_name
                , coalesce( c.eligibility_id, c.cms_id) as payerId
                , i.subscriber_lname
                , i.subscriber_fname
                , DATE_FORMAT(i.subscriber_DOB, '%Y-%m-%d') as subscriber_dob
                , i.policy_number
                , i.type
            from insurance_data i
            inner join insurance_companies as c ON (c.id = i.provider)
            where i.pid = ?";

        $ary = [$pid];

        if ($pr !== "") {
            $query .= " AND i.type = ?";
            $ary[] = $pr;
        }
        $query .= " order by i.date desc LIMIT 1";

        return self::asListOfRecords(QueryUtils::fetchRecords($query, $ary));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getRequiredInsuranceData(int $pid = 0): ?array
    {
        $query = "SELECT
                        d.facility_id,
                        f.pos_code,
                        f.facility_npi as facility_npi,
                        f.name as facility_name,
                        f.state as facility_state,
                        f.federal_ein as facility_ein,
                        d.lname as provider_lname,
                        d.fname as provider_fname,
                        d.npi as provider_npi,
                        d.upin as provider_pin,
                        p.lname,
                        p.fname,
                        p.mname,
                        DATE_FORMAT(p.dob, '%Y-%m-%d') as dob,
                        p.ss,
                        p.sex,
                        p.pid,
                        p.pubpid,
                        p.providerID,
                        p.email,
                        p.street,
                        p.city,
                        p.state,
                        p.postal_code
                    FROM patient_data AS p
                    LEFT JOIN users AS d on
                        p.providerID = d.id
                    INNER JOIN facility AS f on
                        f.id = d.facility_id
                    WHERE p.pid = ?
                    LIMIT 1";

        return self::firstRow(QueryUtils::fetchRecords($query, [$pid]));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getFacilityData(int $fid): ?array
    {
        $query = "SELECT
                        f.pos_code,
                        f.facility_npi as facility_npi,
                        f.name as facility_name,
                        f.state as facility_state,
                        f.federal_ein as facility_ein
                    FROM facility AS f
                    WHERE f.id = ?
                    LIMIT 1";

        return self::firstRow(QueryUtils::fetchRecords($query, [$fid]));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getPatientData(int $pid = 0): ?array
    {
        $query = "SELECT
                        p.lname,
                        p.fname,
                        p.mname,
                        DATE_FORMAT(p.dob, '%Y-%m-%d') as dob,
                        p.ss,
                        p.sex,
                        p.pid,
                        p.pubpid,
                        p.providerID,
                        p.email,
                        p.street,
                        p.city,
                        p.state,
                        p.postal_code,
                        f.id facility_id
                    FROM patient_data AS p
                    LEFT JOIN users AS d on
                        p.providerID = d.id
                    LEFT JOIN facility AS f on
                        f.id = d.facility_id
                    WHERE p.pid = ?
                    LIMIT 1";

        return self::firstRow(QueryUtils::fetchRecords($query, [$pid]));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getProviderData(int $pid = 0): ?array
    {
        $query = "SELECT
                        d.lname as provider_lname,
                        d.fname as provider_fname,
                        d.npi as provider_npi,
                        d.upin as provider_pin
                    FROM users AS d
                    WHERE d.id = ?
                    LIMIT 1";

        return self::firstRow(QueryUtils::fetchRecords($query, [$pid]));
    }

    /**
     * @param list<array<mixed, mixed>> $rows
     * @return array<string, mixed>|null
     */
    private static function firstRow(array $rows): ?array
    {
        if ($rows === []) {
            return null;
        }
        /** @var array<string, mixed> $row */
        $row = $rows[0];
        return $row;
    }

    /**
     * Get the eligibility record ID and raw271 for a patient + payer responsibility.
     *
     * @return array{id: int, raw271: string}|null
     */
    public static function getRaw271(string $pid, string $payerResponsibility): ?array
    {
        $pr = ValueMapping::mapPayerResponsibility($payerResponsibility);
        $sql = "SELECT id, raw271 FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ? AND raw271 IS NOT NULL AND raw271 != '' LIMIT 1";
        $rows = QueryUtils::fetchRecords($sql, [$pid, $pr]);
        if ($rows === []) {
            return null;
        }
        $row = $rows[0];
        return [
            'id' => TypeCoerce::asInt($row['id'] ?? 0),
            'raw271' => TypeCoerce::asString($row['raw271'] ?? ''),
        ];
    }

    /**
     * @return list<InsuranceRow>
     */
    public static function getInsuranceData(int $pid = 0, string $pr = ""): array
    {
        $query = "SELECT
			i.type as payer_responsibility
			FROM insurance_data AS i
            WHERE i.pid = ? ";
        $ary = [$pid];

        if ($pr !== '') {
            $query .= " AND i.type = ?";
            $ary[] = $pr;
        }
        $rows = QueryUtils::fetchRecords($query, $ary);
        return array_map(static fn(array $r): array => [
            'payer_responsibility' => TypeCoerce::asString($r['payer_responsibility'] ?? ''),
        ], $rows);
    }
}
