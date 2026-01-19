<?php

/**
 * interface/modules/zend_modules/module/Immunization/src/Immunization/Model/ImmunizationTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Bindia Nandakumar <bindia@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Immunization\Model;

use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Application\Model\ApplicationTable;

class ImmunizationTable extends AbstractTableGateway
{
    public $tableGateway;
    protected $applicationTable;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $adapter = \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->applicationTable = new ApplicationTable();
    }

    /**
     * function codeslist()
     * Codes List
     */
    public function codeslist()
    {
        $sql = "SELECT id, CONCAT('CVX:',CODE) AS NAME FROM codes LEFT JOIN code_types ct ON codes.code_type = ct.ct_id WHERE ct.ct_key='CVX' ORDER BY NAME";
        $result = $this->applicationTable->zQuery($sql);
        return $result;
    }

    /**
     * function immunized patient details
     *
     * @param type $form_data
     * @return type
     */
    public function immunizedPatientDetails($form_data, $getCount = null)
    {
        $query_data = [];
        $query_codes = $form_data['query_codes'];
        $from_date = $form_data['form_from_date'];
        $to_date = $form_data['form_to_date'];
        $form_get_hl7 = $form_data['form_get_hl7'];
        $query_pids = $form_data['query_pids'];
        $fdate = '';
        $todate = '';
        $query =
            "SELECT " .
            "i.patient_id AS patientid, " .
            "p.language, " .
            "i.cvx_code , ";
        if ($form_get_hl7 === 'true') {
            $query .=
                "DATE_FORMAT(p.DOB,'%Y%m%d') AS DOB, " .
                "p.pubpid, " .
                "CONCAT(IF(p.street IS NULL,'',p.street), '^^', IF(p.city IS NULL,'',p.city), '^', IF(p.state IS NULL,'',p.state), '^', IF(p.postal_code IS NULL,'',p.postal_code) ,'^', IF(l.option_id IS NULL,'',l.notes)) AS address, " .
                "p.country_code, " .
                "p.phone_home, " .
                "p.phone_biz, " .
                "p.status, " .
                "p.sex, " .
                "p.ethnoracial, " .
                "p.race, " .
                "p.ethnicity, " .
                "p.guardiansname, " .
                "p.guardianrelationship, " .
                "p.guardiansex, " .
                "p.guardianphone, " .
                "p.guardianworkphone, " .
                "p.email, " .
                "p.publicity_code, " .
                "p.imm_reg_status, " .
                "p.protect_indicator, " .
                "DATE_FORMAT(p.prot_indi_effdate,'%Y%m%d') AS protection_effective_date, " .
                "DATE_FORMAT(p.publ_code_eff_date,'%Y%m%d') AS publicity_code_effective_date, " .
                "DATE_FORMAT(p.imm_reg_stat_effdate,'%Y%m%d') AS immunization_registry_status_effective_date, " .
                "CONCAT(IF(p.guardianaddress IS NULL,'',p.guardianaddress), '^', '','^',IF(p.guardiancity IS NULL,'',p.guardiancity), '^', IF(p.guardianstate IS NULL,'',p.guardianstate), '^', IF(p.guardianpostalcode IS NULL,'',p.guardianpostalcode) ,'^', IF(l.option_id IS NULL,'',l.notes),'^','L', '^', '', '^', '', '^', '') AS guardian_address, " .
                "p.ss, " .
                "c.code_text, " .
                "c.code, " .
                "c.code_type, " .
                "DATE_FORMAT(i.vis_date,'%Y%m%d') AS immunizationdate, " .
                "DATE_FORMAT(i.administered_date,'%Y%m%d') AS administered_date, " .
                "i.lot_number AS lot_number, " .
                "i.manufacturer AS manufacturer, " .
                "i.administration_site, " .
                "CONCAT(IF(p.lname IS NULL,'',p.lname), '^', p.fname , '^', IF(p.mname IS NULL,'',p.mname)) AS patientname, " .
                "f.id," .
                "f.name AS fac_name, " .
                "f.facility_code," .
                "i.administered_by_id," .
                "i.note," .
                "CONCAT (IF(u.npi IS NULL,'',u.npi),'^',u.lname,'^',u.fname,'^',IF(u.mname IS NULL,'',u.mname)) as primary_care_provider_details, " .
                "l.notes AS country_code, " .
                "l1.notes AS route_code, " .
                "i.route, " .
                "CONCAT(u.lname,'^',u.fname,'^',IF(u.mname IS NULL,'',u.mname),'^','','^',IF(u.title IS NULL,'',u.title),'^','','^','','^','TX','^','L','^','','^','','^','','^','','^','') AS providername, " .
                "CONCAT(f.id,'^',IF(f.street IS NULL,'',SUBSTRING(f.`street`,1,20)),'^',IF(f.`city` IS NULL,'',f.`city`),'^',IF(f.`state` IS NULL,'',f.`state`),'^',IF(f.`postal_code` IS NULL,'',f.`postal_code`),'^',IF(f.`country_code` IS NULL,'',f.`country_code`)) AS facility_address, " .
                "u.id AS users_id, " .
                "i.created_by, " .
                "i.ordering_provider, " .
                "CONCAT(u1.lname,'^',u1.fname,'^',IF(u1.mname IS NULL,'',u1.mname)) AS entered_by_name, " .
                "CONCAT(u2.lname,'^',u2.fname,'^',IF(u2.mname IS NULL,'',u2.mname)) AS ordering_provider_name, " .
                "i.administered_by_id,i.note,i.information_source,DATE_FORMAT(i.expiration_date,'%Y%m%d') AS expiration_date,i.refusal_reason,i.completion_status,";
        } else {
            $query .= "CONCAT(IF(p.fname IS NULL,'',p.fname),' ',IF(p.mname IS NULL,'',p.mname),' ',IF(p.lname IS NULL,'',p.lname)) AS  patientname, " . "i.vis_date AS immunizationdate, ";
        }

        $query .=
            "i.id AS immunizationid, c.code_text_short AS immunizationtitle, c.code_text,i.amount_administered AS administered_amount, i.amount_administered_unit AS administered_unit " .
            "FROM (immunizations AS i, patient_data AS p, codes AS c) " .
            "LEFT JOIN code_types ct ON c.code_type = ct.ct_id " .
            "LEFT JOIN users AS u ON i.administered_by_id = u.id " .
            "LEFT JOIN facility AS f ON f.id = u.facility_id " .
            "LEFT JOIN list_options l ON l.option_id = p.country_code AND l.list_id='country' " .
            "LEFT JOIN list_options l1 ON l1.option_id = i.route AND l1.list_id='drug_route' " .
            "LEFT JOIN list_options l2 ON l2.option_id = p.guardiancountry AND l2.list_id='country' " .
            "LEFT JOIN users AS u1 ON i.created_by = u1.id " .
            "LEFT JOIN users AS u2 ON i.ordering_provider = u2.id " .
            "WHERE " .
            "ct.ct_key='CVX' and ";
        if ($from_date != 0) {
            $query .= "i.vis_date >= ? ";
            $query_data[] = $from_date;
        }

        if ($from_date != 0 and $to_date != 0) {
            $query .= " and ";
        }

        if ($to_date != 0) {
            $query .= "i.vis_date <= ? ";
            $query_data[] = $to_date;
        }

        if ($from_date != 0 or $to_date != 0) {
            $query .= " and ";
        }

        $query .= "i.patient_id=p.pid and " .
            $query_codes .
            $query_pids . "i.cvx_code = c.code ORDER BY i.patient_id, i.id";

        // Merge code_bind_values into query_data to use parameterized queries
        if (!empty($form_data['code_bind_values'])) {
            $query_data = array_merge($query_data, $form_data['code_bind_values']);
        }

        // Merge pid_bind_values into query_data to use parameterized queries
        if (!empty($form_data['pid_bind_values'])) {
            $query_data = array_merge($query_data, $form_data['pid_bind_values']);
        }

        if ($getCount) {
            $result = $this->applicationTable->zQuery($query, $query_data);
            $resCount = $result->count();
            return $resCount;
        }

        $query .= " LIMIT " . \Application\Plugin\CommonPlugin::escapeLimit($form_data['limit_start']) . "," . \Application\Plugin\CommonPlugin::escapeLimit($form_data['results']);
        $result = $this->applicationTable->zQuery($query, $query_data);
        return $result;
    }

    public function getNotes($option_id, $list_id)
    {
        if ($option_id) {
            $query = "SELECT
                          notes
                        FROM
                          list_options
                        WHERE list_id = ?
                          AND option_id = ?
                          AND activity = ?";
            $result = $this->applicationTable->zQuery($query, [$list_id, $option_id, 1]);
            $res_cur = $result->current();
        }

        return $res_cur['notes'];
    }

    /**
     * function getImmunizationObservationResultsData function to get immunization observation data
     *
     * @param type $pid
     * @param type $id
     * @return type Array $val
     */
    public function getImmunizationObservationResultsData($pid, $id)
    {
        $sql = " SELECT
                       *
                     FROM
                       immunization_observation
                     WHERE imo_pid = ?
                       AND imo_im_id = ?";
        $result = $this->applicationTable->zQuery($sql, [$pid, $id]);
        foreach ($result as $row) {
            $val[] = $row;
        }

        return $val;
    }

    public function getCodes($option_id, $list_id)
    {
        if ($option_id) {
            $query = "SELECT
                          codes
                        FROM
                          list_options
                        WHERE list_id = ?
                          AND option_id = ?
                          AND activity = ?";
            $result = $this->applicationTable->zQuery($query, [$list_id, $option_id, 1]);
            $res_cur = $result->current();
        }

        $codes = explode(":", (string) $res_cur['codes']);
        return $codes[1];
    }
}
