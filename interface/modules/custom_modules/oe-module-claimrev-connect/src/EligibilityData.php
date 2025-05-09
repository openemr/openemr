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

    namespace OpenEMR\Modules\ClaimRevConnector;

    use OpenEMR\Modules\ClaimRevConnector\ValueMapping;

class EligibilityData
{
    public function __construct()
    {
    }

    public static function getPatientIdFromAppointment($eid)
    {
        $sql = "SELECT 
                pc_pid 
                ,DATE_FORMAT(pc_eventDate, '%Y-%m-%d') as appointmentDate
                ,pc_facility as facilityId
                ,pc_aid as providerId
                from openemr_postcalendar_events 
                WHERE pc_eid = ? 
            LIMIT 1";
        $sqlarr = array($eid);
        $result = sqlStatement($sql, $sqlarr);
        if (sqlNumRows($result) == 1) {
            foreach ($result as $row) {
                //return $row["pc_pid"];
                return $row;
            }
        }
        return null;
    }
    public static function removeEligibilityCheck($pid, $payer_responsibility)
    {
        $sql = "DELETE FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ? ";
        $sqlarr = array($pid,$payer_responsibility);
        $result = sqlStatement($sql, $sqlarr);
    }
    public static function getEligibilityCheckByStatus($status)
    {
        $sql = "SELECT * FROM mod_claimrev_eligibility WHERE status = ?";
        $sqlarr = array($status);

        $result = sqlStatement($sql, $sqlarr);
        return $result;
    }
    public static function getEligibilityResults($status, $minutes)
    {
        $sql = "SELECT * FROM mod_claimrev_eligibility WHERE status = ? AND TIMESTAMPDIFF(MINUTE,last_checked,NOW()) >= ?";
        $sqlarr = array($status,$minutes);
        $result = sqlStatement($sql, $sqlarr);
        return $result;
    }
    public static function getEligibilityResult($pid, $payer_responsibility)
    {
        $pr = ValueMapping::mapPayerResponsibility($payer_responsibility);
        $sql = "SELECT status, coalesce(last_checked,create_date) as last_update,response_json,eligibility_json,individual_json,response_message  FROM mod_claimrev_eligibility WHERE pid = ? AND payer_responsibility = ? LIMIT 1";
        $res = sqlStatement($sql, array($pid,$pr));
        return $res;
    }

    public static function updateEligibilityRecord($id, $status, $request_json, $response_json, $updateLastChecked, $responseMessage, $raw271, $eligibility_json, $individual_json)
    {
        $sql = "UPDATE mod_claimrev_eligibility SET status = ? ";

        $sqlarr = array($status);
        if ($updateLastChecked) {
            $sql = $sql . ",last_checked = NOW() ";
        }
        if ($response_json != null) {
            $sql = $sql . " ,response_json = ?";
            array_push($sqlarr, $response_json);
        }
        if ($request_json != null) {
            $sql = $sql . " ,request_json = ?";
            array_push($sqlarr, $request_json);
        }
        if ($responseMessage != null) {
            $sql = $sql . " ,response_message = ?";
            array_push($sqlarr, $responseMessage);
        }
        if ($raw271 != null) {
                $sql = $sql . " ,raw271 = ? ";
                array_push($sqlarr, $raw271);
        }
        if ($eligibility_json != null) {
            $sql = $sql . " ,eligibility_json = ?";
            array_push($sqlarr, $eligibility_json);
        }
        if ($individual_json != null) {
            $sql = $sql . " ,individual_json = ?";
            array_push($sqlarr, $individual_json);
        }

        $sql = $sql . " WHERE id = ?";
        array_push($sqlarr, $id);
        sqlStatement($sql, $sqlarr);
    }

    public static function getSubscriberData($pid = 0, $pr = "")
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

            $ary = array($pid);

        if ($pr != "") {
            $query = $query . " AND i.type = ?";
            array_push($ary, $pr);
        }
            $query = $query . " order by i.date desc LIMIT 1";

            $res = sqlStatement($query, $ary);
            return $res;
    }

    public static function getRequiredInsuranceData($pid = 0)
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

        $ary = array($pid);
        $res = sqlStatement($query, $ary);

        return $res;
    }
    public static function getFacilityData($fid)
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

        $ary = array($fid);
        $result = sqlStatement($query, $ary);

        if (sqlNumRows($result) == 1) {
            foreach ($result as $row) {
                return $row;
            }
        }

        return null;
    }

    public static function getPatientData($pid = 0)
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

        $ary = array($pid);
        $result = sqlStatement($query, $ary);

        if (sqlNumRows($result) == 1) {
            foreach ($result as $row) {
                return $row;
            }
        }

        return null;
    }

    public static function getProviderData($pid = 0)
    {
        $query = "SELECT
                        d.lname as provider_lname,
                        d.fname as provider_fname,
                        d.npi as provider_npi,
                        d.upin as provider_pin           
                    FROM users AS d                    
                    WHERE d.id = ?
                    LIMIT 1";

        $ary = array($pid);
        $result = sqlStatement($query, $ary);

        if (sqlNumRows($result) == 1) {
            foreach ($result as $row) {
                return $row;
            }
        }

        return null;
    }

    public static function getInsuranceData($pid = 0, $pr = "")
    {
        $query = "SELECT
			i.type as payer_responsibility           
			FROM insurance_data AS i
            WHERE i.pid = ? ";
        $ary = array($pid);

        if ($pr != "") {
            $query = $query . " AND i.type = ?";
            array_push($ary, $pr);
        }
        $res = sqlStatement($query, $ary);

        return $res;
    }
}
