<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Riju K P <rijukp@zhservices.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>_
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

use Application\Listener\Listener;
use Application\Model\ApplicationTable;
use Carecoordination\Model\CarecoordinationTable;
use Documents\Plugin\Documents;
use Laminas\Db\Adapter\Driver\Pdo\Result;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Matrix\Exception;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\ORDataObject\ContactAddress;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\ContactService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\SearchComparator;
use OpenEMR\Services\Search\SearchFieldStatementResolver;
use OpenEMR\Services\Search\SearchQueryFragment;
use OpenEMR\Services\Utils\DateFormatterUtils;
use OpenEMR\Validators\ProcessingResult;

require_once(__DIR__ . "/../../../../../../../../custom/code_types.inc.php");
require_once(__DIR__ . "/../../../../../../../forms/vitals/report.php");
require_once($GLOBALS['fileroot'] . '/library/amc.php');

class EncounterccdadispatchTable extends AbstractTableGateway
{
    const CCDA_DOCUMENT_FOLDER = "CCDA";
    public $amc_num_result = [
        'medications' => 0,
        'allergies' => 0,
        'problems' => 0
    ];
    public $searchDateField;
    public $searchFromDate;
    public $searchToDate;
    public $searchFiltered = false;
    private $encounterFilterList = [];

    public function __construct()
    {
    }

    /**
     * @param $options
     * @return void
     */
    public function setOptions($pid, $encounter, $options)
    {
        // we keep from and to dates in order to handle the transaction table where we have to manually convert the dates
        // since they are stored as strings.
        $this->searchFromDate = null;
        $this->searchToDate = null;
        $this->encounterFilterList = [];

        $dateValues = [];

        if (!empty($options['date_start'])) {
            $searchFromDate = strtotime($options['date_start']);
            // date values for search fields have to be in ISO8601
            // we use DATE_ATOM to get an ISO8601 compatible date as DATE_ISO8601 does not actually conform to an ISO8601 date for php legacy purposes
            $dateStart = date(DATE_ATOM, $searchFromDate);
            if ($dateStart !== false) {
                $this->searchFromDate = $searchFromDate;
                $dateValues[] = SearchComparator::GREATER_THAN_OR_EQUAL_TO . $dateStart;
            } else {
                // TODO: do we want to log the invalid format
            }
        }

        if (!empty($options['date_end'])) {
            $searchToDate = strtotime($options['date_end']);
            // date values for search fields have to be in ISO8601
            // we use DATE_ATOM to get an ISO8601 compatible date as DATE_ISO8601 does not actually conform to an ISO8601 date for php legacy purposes
            $dateEnd = date(DATE_ATOM, $searchToDate);
            if ($dateEnd !== false) {
                $this->searchToDate = $searchToDate;
                $dateValues[] = SearchComparator::LESS_THAN_OR_EQUAL_TO . $dateEnd;
            } else {
                // TODO: do we want to log the invalid format
            }
        }
        if (!empty($dateValues)) {
            $this->searchDateField = new DateSearchField('search_date', $dateValues, DateSearchField::DATE_TYPE_DATETIME, true);
            $this->searchFiltered = !empty($options['filter_content'] ?? false);
            // Since if encounterFilterList is populated the section builders will always searchFiltered
            // regardless of $options['filter_content'] (Send Filtered button) without below! i.e if (!empty($this->encounterFilterList)) { ... }
            if ($this->searchFiltered) {
                $this->encounterFilterList = $this->getEncounterListForDateRange($pid, $encounter);
            }
        } else {
            if (!empty($encounter)) {
                // okay because encounter is passed in.
                $this->encounterFilterList = [intval($encounter)];
            }
            $this->searchFiltered = false;
        }
    }

    /**
     * @param $column
     * @return SearchQueryFragment
     */
    private function getDateQueryClauseForColumn($column): SearchQueryFragment
    {
        $searchField = $this->convertDateSearchFieldForColumn($this->searchDateField, $column);

        $queryClause = SearchFieldStatementResolver::resolveDateField($searchField);
        return $queryClause;
    }

    /**
     * @param DateSearchField $searchField
     * @param                 $column
     * @return DateSearchField
     */
    private function convertDateSearchFieldForColumn(DateSearchField $searchField, $column)
    {
        return new DateSearchField($column, $searchField->getValues(), $searchField->getDateType(), $searchField->isAnd());
    }

    /**
     * @param $race
     * @return array
     */
    public function resolveRace($race)
    {
        $appTable = new ApplicationTable();
        $res_cur = null;
        $query = "SELECT title, notes FROM list_options WHERE list_id='race' AND option_id=?";
        $option['race']['title'] = '';
        $option['race']['code'] = '';
        $option['race_cat']['title'] = '';
        $option['race_cat']['code'] = '';
        if (strpos($race, '|') !== false) {
            $first = explode('|', $race);
            foreach ($first as $i => $title) {
                $result = $appTable->zQuery($query, array($title));
                $r = $result->current();
                // ensure at least one
                if ($i == 0) {
                    $option['race']['title'] = $r['title'];
                    $option['race']['code'] = $r['notes'];
                }
                if (
                    in_array(
                        trim($r['title']),
                        ['American Indian or Alaska Native',
                        'Asian',
                        'Black or African American',
                        'Native Hawaiian or Other Pacific Islander',
                        'White']
                    )
                ) {
                    $option['race']['title'] = $r['title'];
                    $option['race']['code'] = $r['notes'];
                } else {
                    $option['race_cat']['title'] = $r['title'];
                    $option['race_cat']['code'] = $r['notes'];
                }
            }
        } elseif (!empty($race)) {
            $result = $appTable->zQuery($query, array($race));
            $r = $result->current();
            $option['race']['title'] = $r['title'] ?? '';
            $option['race']['code'] = $r['notes'] ?? '';
            $option['race_cat']['title'] = '';
            $option['race_cat']['code'] = '';
        }
        return $option;
    }

    /**
     * @param $pid
     * @return ContactAddress[]
     */
    public function getPreviousAddresses($pid): array
    {
        $address = new ContactService();
        return $address->getContactsForPatient($pid) ?? [];
    }

    /**
     * @param $pid
     * @return array
     */
    public function getPreviousNames($pid): array
    {
        $patientService = new PatientService();
        return $patientService->getPatientNameHistory($pid) ?? [];
    }

    /* Fetch Patient data from EMR
    * @param    $pid
    * @param    $encounter
    * @return   $patient_data   Patient Data in XML format
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getPatientdata($pid, $encounter): string
    {
        // Ensure we have UUIDs for patient identifier in document.
        UuidRegistry::createMissingUuidsForTables(['patient_data']);
        $query = "select patient_data.*, l1.notes AS race_code, l1.title as race_title, l2.notes AS ethnicity_code, l2.title as ethnicity_title, l3.title as religion
            , l3.notes as religion_code, l4.notes as language_code, l4.title as language_title
            ,patient_data.updated_by AS provenance_updated_by
            from patient_data
            left join list_options as l1 on l1.list_id=? AND l1.option_id=race
            left join list_options as l2 on l2.list_id=? AND l2.option_id=ethnicity
            left join list_options AS l3 ON l3.list_id=? AND l3.option_id=religion
            left join list_options AS l4 ON l4.list_id=? AND l4.option_id=language
                        where pid=?";
        $appTable = new ApplicationTable();
        $row = $appTable->zQuery($query, array('race', 'ethnicity', 'religious_affiliation', 'language', $pid));
        // Render previous names
        $names = $this->getPreviousNames($pid);
        $previous_names = "<previous_names>";
        foreach ($names as $n) {
            $end = !empty($n['previous_name_enddate'] ?? null) ? date("Y-m-d", strtotime($n['previous_name_enddate'])) : null;
            $previous_names .= "
            <prefix>" . xmlEscape($n['previous_name_prefix']) . "</prefix>
            <fname>" . xmlEscape($n['previous_name_first']) . "</fname>
            <mname>" . xmlEscape($n['previous_name_middle']) . "</mname>
            <lname>" . xmlEscape($n['previous_name_last']) . "</lname>
            <suffix>" . xmlEscape($n['previous_name_suffix']) . "</suffix>
            <end_date>" . xmlEscape($end) . "</end_date>
            ";
        }
        $previous_names .= "</previous_names>";

        // Render previous addresses
        $addresses = $this->getPreviousAddresses($pid);
        $previous_addresses = "<previous_addresses>";
        foreach ($addresses as $a) {
            $start = !empty($a['period_start'] ?? null) ? date("Y-m-d", strtotime($a['period_start'])) : null;
            $end = !empty($a['period_end'] ?? null) ? date("Y-m-d", strtotime($a['period_end'])) : null;
            $previous_addresses .= "<address>
            <use>" . xmlEscape($a['use'] ?? 'H') . "</use>
            <street>" . xmlEscape($a['line1'] ?? '') . "</street>
            <street>" . xmlEscape($a['line2'] ?? '') . "</street>
            <city>" . xmlEscape($a['city'] ?? '') . "</city>
            <state>" . xmlEscape($a['state'] ?? '') . "</state>
            <postalCode>" . xmlEscape($a['zip'] ?? '') . "</postalCode>
            <country>" . xmlEscape($a['country'] ?? '') . "</country>
            <period_start>" . xmlEscape($start) . "</period_start>
            <period_end>" . xmlEscape($end) . "</period_end>
            </address>
            ";
        }
        $previous_addresses .= "</previous_addresses>";

        foreach ($row as $result) {
            $pid_uuid = UuidRegistry::uuidToString($result['uuid']);
            $race = $this->resolveRace($result['race']);
            $provenanceRecord = [
                'author_id' => $result['provenance_updated_by']
                ,'time' => $result['date']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, null);
            $patient_data = "<patient>" . $provenanceXml . "
            <id>" . xmlEscape($result['pid']) . "</id>
            <uuid>" . xmlEscape($pid_uuid) . "</uuid>
            <encounter>" . xmlEscape($encounter) . "</encounter>
            <prefix>" . xmlEscape($result['title']) . "</prefix>
            <fname>" . xmlEscape($result['fname']) . "</fname>
            <mname>" . xmlEscape($result['mname']) . "</mname>
            <lname>" . xmlEscape($result['lname']) . "</lname>
            <suffix>" . xmlEscape($result['suffix']) . "</suffix>
            " . $previous_names . "
            <birth_fname>" . xmlEscape($result['birth_fname']) . "</birth_fname>
            <birth_mname>" . xmlEscape($result['birth_mname']) . "</birth_mname>
            <birth_lname>" . xmlEscape($result['birth_lname']) . "</birth_lname>
            <use>" . xmlEscape('HP') . "</use>
            <street>" . xmlEscape($result['street'] ?? '') . "</street>
            <street>" . xmlEscape($result['street_line_2'] ?? '') . "</street>
            <city>" . xmlEscape($result['city'] ?? '') . "</city>
            <state>" . xmlEscape($result['state'] ?? '') . "</state>
            <postalCode>" . xmlEscape($result['postal_code'] ?? '') . "</postalCode>
            <country>" . xmlEscape($result['country_code'] ?? '') . "</country>
            " . $previous_addresses . "
            <ssn>" . xmlEscape($result['ss'] ?: '') . "</ssn>
            <dob>" . xmlEscape(str_replace('-', '', $result['DOB'])) . "</dob>
            <gender>" . xmlEscape($result['sex']) . "</gender>
            <gender_code>" . xmlEscape(strtoupper(substr($result['sex'], 0, 1))) . "</gender_code>
            <status>" . xmlEscape($result['status'] ?: "") . "</status>
            <status_code>" . xmlEscape($result['status'] ? strtoupper(substr($result['status'], 0, 1)) : 0) . "</status_code>
            <phone_home>" . xmlEscape(($result['phone_home'] ?: '')) . "</phone_home>
            <phone_mobile>" . xmlEscape(($result['phone_cell'] ? $result['phone_cell'] : '')) . "</phone_mobile>
            <phone_work>" . xmlEscape(($result['phone_biz'] ?: '')) . "</phone_work>
            <phone_emergency>" . xmlEscape(($result['phone_contact'] ?: '')) . "</phone_emergency>
            <email>" . xmlEscape(($result['email'] ?: '')) . "</email>
            <religion>" . xmlEscape(Listener::z_xlt($result['religion'] ?: "")) . "</religion>
            <religion_code>" . xmlEscape($result['religion_code'] ?: '') . "</religion_code>
            <race>" . xmlEscape(Listener::z_xlt($race['race']['title'])) . "</race>
            <race_code>" . xmlEscape($race['race']['code']) . "</race_code>
            <race_group>" . xmlEscape(Listener::z_xlt($race['race_cat']['title'])) . "</race_group>
            <race_group_code>" . xmlEscape($race['race_cat']['code']) . "</race_group_code>
            <ethnicity>" . xmlEscape(Listener::z_xlt($result['ethnicity_title'])) . "</ethnicity>
            <ethnicity_code>" . xmlEscape($result['ethnicity_code']) . "</ethnicity_code>
            <language>" . xmlEscape(Listener::z_xlt($result['language_title'])) . "</language>
            <language_code>" . xmlEscape($result['language_code']) . "</language_code>
            </patient>
        <guardian>
            <fname>" . xmlEscape($result['fname'] ?? '') . "</fname>
            <lname>" . xmlEscape($result['lname'] ?? '') . "</lname>
            <code>" . xmlEscape($result['code'] ?? '') . "</code>
            <relation>" . xmlEscape($result['guardianrelationship']) . "</relation>
            <display_name>" . xmlEscape($result['guardiansname']) . "</display_name>
            <street>" . xmlEscape($result['guardianaddress']) . "</street>
            <city>" . xmlEscape($result['guardiancity']) . "</city>
            <state>" . xmlEscape($result['guardianstate']) . "</state>
            <postalCode>" . xmlEscape($result['guardianpostalcode']) . "</postalCode>
            <country>" . xmlEscape($result['guardiancountry']) . "</country>
            <telecom>" . xmlEscape($result['guardianphone']) . "</telecom>
        </guardian>";
        }

        return $patient_data ?? '';
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getProviderDetails($pid, $encounter)
    {
        $provider_details = '';
        if (!$encounter) {
            $query_enc = "SELECT encounter FROM form_encounter WHERE pid=? ORDER BY date DESC LIMIT 1";
            $appTable = new ApplicationTable();
            $res_enc = $appTable->zQuery($query_enc, array($pid));
            foreach ($res_enc as $row_enc) {
                $encounter = $row_enc['encounter'];
            }
        }

        $query = "SELECT * FROM form_encounter as fe
                        JOIN users AS u ON u.id =  fe.provider_id
                        JOIN facility AS f ON f.id = u.facility_id
                        WHERE fe.pid = ? AND fe.encounter = ?";
        $appTable = new ApplicationTable();
        $row = $appTable->zQuery($query, array($pid, $encounter));
        foreach ($row as $result) {
            $provider_details = "<encounter_provider>
                <facility_id>" . xmlEscape($result['id']) . "</facility_id>
                <facility_npi>" . xmlEscape($result['facility_npi']) . "</facility_npi>
                <facility_oid>" . xmlEscape($result['oid']) . "</facility_oid>
                <facility_name>" . xmlEscape($result['name']) . "</facility_name>
                <facility_phone>" . xmlEscape(($result['phone'] ? $result['phone'] : 0)) . "</facility_phone>
                <facility_fax>" . xmlEscape($result['fax']) . "</facility_fax>
                <facility_street>" . xmlEscape($result['street']) . "</facility_street>
                <facility_city>" . xmlEscape($result['city']) . "</facility_city>
                <facility_state>" . xmlEscape($result['state']) . "</facility_state>
                <facility_postal_code>" . xmlEscape($result['postal_code']) . "</facility_postal_code>
                <facility_country_code>" . xmlEscape($result['country_code']) . "</facility_country_code>
            </encounter_provider>
            ";
        }

        if (empty($provider_details)) {
            // so generator doesn't spit up with undefines.
            $provider_details = "<encounter_provider>
                <facility_id></facility_id>
                <facility_npi></facility_npi>
                <facility_oid></facility_oid>
                <facility_name></facility_name>
                <facility_phone></facility_phone>
                <facility_fax></facility_fax>
                <facility_street></facility_street>
                <facility_city></facility_city>
                <facility_state></facility_state>
                <facility_postal_code></facility_postal_code>
                <facility_country_code></facility_country_code>
            </encounter_provider>
            ";
        }
        return $provider_details;
    }

    /**
     * @param $recordAuthor
     * @param $pid
     * @param $encounter
     * @return array|null
     */
    public function getProvenanceForRecord($recordAuthor, $pid, $encounter)
    {
        if (empty($recordAuthor['author_id']) || !is_numeric($recordAuthor['author_id'])) {
            $details = $this->getDocumentAuthorRecord($pid, $encounter);
        } else {
            $details = $this->getDetails(intval($recordAuthor['author_id']));
        }

        if (empty($details)) {
            return null;
        }

        $setting = $this->getCarecoordinationModuleSettingValue('hie_force_latest_encounter_provenance_date');
        // we override our author date if we force the latest encounter date
        if (empty($recordAuthor['time']) || $setting == 'yes') {
            $time = $this->getAuthorDate($pid, $encounter);
        } else {
            $time = $recordAuthor['time'];
        }
        return [
            'author' => $details
            ,'time' => $time
        ];
    }

    /**
     * @param $recordAuthor
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getAuthorXmlForRecord($recordAuthor, $pid, $encounter)
    {
        $provenanceRecord = $this->getProvenanceForRecord($recordAuthor, $pid, $encounter);

        $time = $provenanceRecord['time'];
        $details = $provenanceRecord['author'];
        $uuid = UuidRegistry::uuidToString($details['uuid']);

        if (!empty($details['provider_role_code'])) {
            $type_code = $details['provider_role_code'];
            $type_title = $details['provider_role_title'] ?? '';
            $type_system = CodeTypesService::CODE_TYPE_OID_HEALTHCARE_PROVIDER_TAXONOMY;
            $type_system_name = "ValueSet Healthcare Provider Taxonomy (HIPAA)"; // this appears to be a subset of NUCC
        } else {
            $type_code = $details['physician_type'] ?? '';
            $type_title = $details['physician_type_code'] ?? '';
            $type_system = "SNOMED CT";
            $type_system_name = "SNOMED CT";
        }

        // <physician_type>" . xmlEscape($details['physician_type'] ?? '') . "</physician_type>
        // <physician_type_code>" . xmlEscape($details['physician_type_code'] ?? '') . "</physician_type_code>
        //     oidFacility = all.encounter_provider.facility_oid ? all.encounter_provider.facility_oid : "2.16.840.1.113883.19.5.99999.1";
        //    npiFacility = all.encounter_provider.facility_npi;
        $author = "
        <author>
        <time>" . xmlEscape($time ?? '') . "</time>
        <id>" . xmlEscape($uuid ?? '') . "</id>
        <physician_type>" . xmlEscape($type_title) . "</physician_type>
        <physician_type_code>" . xmlEscape($type_code) . "</physician_type_code>
        <physician_type_system>" . xmlEscape($type_system) . "</physician_type_system>
        <physician_type_system_name>" . xmlEscape($type_system_name) . "</physician_type_system_name>
        <streetAddressLine>" . xmlEscape($details['street'] ?? '') . "</streetAddressLine>
        <facility_oid>" . xmlEscape($details['facility_oid']) . "</facility_oid>
        <facility_npi>" . xmlEscape($details['facility_npi']) . "</facility_npi>
        <facility_name>" . xmlEscape($details['facility_name']) . "</facility_name>
        <city>" . xmlEscape($details['city'] ?? '') . "</city>
        <state>" . xmlEscape($details['state'] ?? '') . "</state>
        <postalCode>" . xmlEscape($details['zip'] ?? '') . "</postalCode>
        <country>" . xmlEscape($details['country'] ?? '') . "</country>
        <telecom>" . xmlEscape(trim(($details['phonew1'] ?? '') ? $details['phonew1'] : '')) . "</telecom>
        <fname>" . xmlEscape($details['fname'] ?? '') . "</fname>
        <lname>" . xmlEscape($details['lname'] ?? '') . "</lname>
        <npi>" . xmlEscape($details['npi'] ?? '') . "</npi>
        </author>";

        return $author;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return null
     */
    private function getDocumentAuthorRecord($pid, $encounter)
    {
        $details = $this->getDetails('hie_author_id');
        if (!$details && !empty($_SESSION['authUserID'])) {
            // function expects an int
            $details = $this->getDetails(intval($_SESSION['authUserID']));
        }
        if (!$details) {
            $providerId = $this->getProviderId($pid);
            if (!empty($providerId)) {
                $details = $this->getDetails(intval($providerId));
            }
            if (!$details) {
                $details = $this->getDetails('hie_primary_care_provider_id');
            }
        }
        if (!$details) {
            // at this point we really can't do anything as we can't provide an author piece
            (new SystemLogger())->errorLogCaller("Failed to find author for c-cda document, no hie_author_id, authUserID in session, or provider relationship");
            return null;
        }

        return $details;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getAuthor($pid, $encounter)
    {
        $details = $this->getDocumentAuthorRecord($pid, $encounter);
        if (empty($details)) {
            return;
        }
        $time = $this->getAuthorDate($pid, $encounter);
        $uuid = UuidRegistry::uuidToString($details['uuid']);

        if (!empty($details['provider_role_code'])) {
            $type_code = $details['provider_role_code'];
            $type_title = $details['provider_role_title'] ?? '';
            $type_system = CodeTypesService::CODE_TYPE_OID_HEALTHCARE_PROVIDER_TAXONOMY;
            $type_system_name = "ValueSet Healthcare Provider Taxonomy (HIPAA)"; // this appears to be a subset of NUCC
        } else {
            $type_code = $details['physician_type'] ?? '';
            $type_title = $details['physician_type_code'] ?? '';
            $type_system = "SNOMED CT";
            $type_system_name = "SNOMED CT";
        }

        // <physician_type>" . xmlEscape($details['physician_type'] ?? '') . "</physician_type>
        // <physician_type_code>" . xmlEscape($details['physician_type_code'] ?? '') . "</physician_type_code>
        $author = "
        <author>
        <time>" . xmlEscape($time ?? '') . "</time>
        <id>" . xmlEscape($uuid ?? '') . "</id>
        <physician_type>" . xmlEscape($type_title) . "</physician_type>
        <physician_type_code>" . xmlEscape($type_code) . "</physician_type_code>
        <physician_type_system>" . xmlEscape($type_system) . "</physician_type_system>
        <physician_type_system_name>" . xmlEscape($type_system_name) . "</physician_type_system_name>
        <streetAddressLine>" . xmlEscape($details['street'] ?? '') . "</streetAddressLine>
        <city>" . xmlEscape($details['city'] ?? '') . "</city>
        <state>" . xmlEscape($details['state'] ?? '') . "</state>
        <postalCode>" . xmlEscape($details['zip'] ?? '') . "</postalCode>
        <country>" . xmlEscape($details['country'] ?? '') . "</country>
        <telecom>" . xmlEscape(trim(($details['phonew1'] ?? '') ? $details['phonew1'] : '')) . "</telecom>
        <fname>" . xmlEscape($details['fname'] ?? '') . "</fname>
        <lname>" . xmlEscape($details['lname'] ?? '') . "</lname>
        <npi>" . xmlEscape($details['npi'] ?? '') . "</npi>
        </author>";

        return $author;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getAuthorDate($pid, $encounter)
    {
        // we allow providers to use the latest encounter date if they have the force flag set.
        $time = null;
        $setting = $this->getCarecoordinationModuleSettingValue('hie_force_latest_encounter_provenance_date');
        if ($setting == 'yes') {
            $encounter = $this->getLatestEncounter($pid);
            if (!empty($encounter)) {
                $encounterService = new EncounterService();
                $encounterRecord = ProcessingResult::extractDataArray($encounterService->getEncounterById($encounter));
                if (!empty($encounterRecord[0])) {
                    $time = $encounterRecord[0]['date'];
                }
            }
        }
        if (empty($time)) {
            $time = $this->getCarecoordinationModuleSettingValue('hie_author_date');
        }

        $time = !empty($time) ? date('Y-m-d H:i:sO', strtotime($time)) : date('Y-m-d H:i:sO');
        return $time;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getDataEnterer($pid, $encounter)
    {
        $data_enterer = '';
        $details = $this->getDetails('hie_data_enterer_id');

        $data_enterer = "
        <data_enterer>
            <streetAddressLine>" . xmlEscape($details['street'] ?? '') . "</streetAddressLine>
            <city>" . xmlEscape($details['city'] ?? '') . "</city>
            <state>" . xmlEscape($details['state'] ?? '') . "</state>
            <postalCode>" . xmlEscape($details['zip'] ?? '') . "</postalCode>
            <country>" . xmlEscape($details['country'] ?? '') . "</country>
            <telecom>" . xmlEscape((($details['phonew1'] ?? '') ? $details['phonew1'] : 0)) . "</telecom>
            <fname>" . xmlEscape($details['fname'] ?? '') . "</fname>
            <lname>" . xmlEscape($details['lname'] ?? '') . "</lname>
        </data_enterer>";

        return $data_enterer;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getInformant($pid, $encounter)
    {
        $informant = '';
        $details = $this->getDetails('hie_informant_id');
        $personal_informant = $this->getDetails('hie_personal_informant_id');

        $informant = "<informer>
            <streetAddressLine>" . xmlEscape($details['street'] ?? '') . "</streetAddressLine>
            <city>" . xmlEscape($details['city'] ?? '') . "</city>
            <state>" . xmlEscape($details['state'] ?? '') . "</state>
            <postalCode>" . xmlEscape($details['zip'] ?? '') . "</postalCode>
            <country>" . xmlEscape($details['country'] ?? '') . "</country>
            <telecom>" . xmlEscape((($details['phonew1'] ?? '') ? $details['phonew1'] : 0)) . "</telecom>
            <fname>" . xmlEscape($details['fname'] ?? '') . "</fname>
            <lname>" . xmlEscape($details['lname'] ?? '') . "</lname>
            <personal_informant>" . xmlEscape($this->getSettings('Carecoordination', 'hie_personal_informant_id')) . "</personal_informant>
        </informer>";

        return $informant;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getDocumentParticipants($pid, $encounter)
    {

        $participants = "<document_participants>";
        $participants .= $this->getDocumentReferralParticipant($pid, $encounter);
        $participants .= $this->getOfficeContact($pid, $encounter);
        $participants .= "</document_participants>";
        return $participants;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getDocumentReferralParticipant($pid, $encounter)
    {
        $participant = '';
        $records = $this->getReferralRecords($pid);
        $refer_date = date("Y-m-d");
        if (empty($records[0]['refer_from']) || !is_numeric($records[0]['refer_from'])) {
            // attempt to get the primary care physician for the patient and use that for the referral
            $providerId = $this->getProviderId($pid);
            if (empty($providerId)) {
                return $participant;
            } else {
                $providerId = $providerId;
            }
            $refer_date = $records[0]['refer_date'] ?? date("Y-m-d");
        } else {
            $providerId = $records[0]['refer_from'];
        }
        $details = $this->getDetails(intval($providerId));
        if (empty($details)) {
            return $participant;
        } else {
            $organization_uuid = UuidRegistry::uuidToString($details['facility_uuid']);
        }

        // referral date does not follow the global date settings.  It saves off as Y-m-d so we need to format from there
        $referralDate = \DateTime::createFromFormat("Y-m-d", $refer_date);
        if ($referralDate === false) {
            $referralDate = date('Y-m-d H:i:sO');
        } else {
            $referralDate = $referralDate->format('Y-m-d H:i:sO'); // we get it in the right format even though we have no time element...
        }

        $participant = "<participant>
            <date_time>" . xmlEscape($referralDate) . "</date_time>
            <fname>" . xmlEscape($details['fname']) . "</fname>
            <lname>" . xmlEscape($details['lname']) . "</lname>
            <organization>" . xmlEscape($details['organization']) . "</organization>
            <organization_id>" . xmlEscape($organization_uuid) . "</organization_id>
            <organization_npi>" . xmlEscape($details['facility_npi']) . "</organization_npi>
            <organization_taxonomy>" . xmlEscape($details['facility_taxonomy']) . "</organization_taxonomy>
            <organization_taxonomy_desc>" . xmlEscape($details['taxonomy_desc'] ?? '') . "</organization_taxonomy_desc>
            <street>" . xmlEscape($details['street']) . "</street>
            <city>" . xmlEscape($details['city']) . "</city>
            <state>" . xmlEscape($details['state']) . "</state>
            <postalCode>" . xmlEscape($details['zip']) . "</postalCode>
            <phonew1>" . xmlEscape($details['phonew1']) . "</phonew1>
            <address_use>WP</address_use>
            <type>REFB</type>
        </participant>";

        return $participant;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getOfficeContact($pid, $encounter)
    {
        $details = $this->getDetails('hie_office_contact');
        if (empty($details)) {
            return '';
        } else {
            $organization_uuid = UuidRegistry::uuidToString($details['facility_uuid']);
        }

        $time = $this->getAuthorDate($pid, $encounter);
        $officeContact = "<participant>
            <date_time>" . xmlEscape($time) . "</date_time>
            <fname>" . xmlEscape($details['fname']) . "</fname>
            <lname>" . xmlEscape($details['lname']) . "</lname>
            <organization>" . xmlEscape($details['organization']) . "</organization>
            <organization_id>" . xmlEscape($organization_uuid) . "</organization_id>
            <organization_npi>" . xmlEscape($details['facility_npi']) . "</organization_npi>
            <organization_taxonomy>" . xmlEscape($details['facility_taxonomy']) . "</organization_taxonomy>
            <organization_taxonomy_desc>" . xmlEscape($details['taxonomy_desc'] ?? '') . "</organization_taxonomy_desc>
            <street>" . xmlEscape($details['street']) . "</street>
            <city>" . xmlEscape($details['city']) . "</city>
            <state>" . xmlEscape($details['state']) . "</state>
            <postalCode>" . xmlEscape($details['zip']) . "</postalCode>
            <phonew1>" . xmlEscape($details['phonew1']) . "</phonew1>
            <address_use>WP</address_use>
            <type>CALLBCK</type>
        </participant>";

        return $officeContact;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getCustodian($pid, $encounter)
    {
        $custodian = '';
        $details = $this->getDetails('hie_custodian_id');

        $custodian = "<custodian>
            <streetAddressLine>" . xmlEscape($details['street'] ?? '') . "</streetAddressLine>
            <city>" . xmlEscape($details['city'] ?? '') . "</city>
            <state>" . xmlEscape($details['state'] ?? '') . "</state>
            <postalCode>" . xmlEscape($details['zip'] ?? '') . "</postalCode>
            <country>" . xmlEscape($details['country'] ?? '') . "</country>
            <telecom>" . xmlEscape((($details['phonew1'] ?? '') ? $details['phonew1'] : 0)) . "</telecom>
            <name>" . xmlEscape($details['organization'] ?? '') . "</name>
            <organization>" . xmlEscape($details['organization'] ?? '') . "</organization>
        </custodian>";

        return $custodian;
    }

    /**
     * @param $pid
     * @param $encounter
     * @param $recipients
     * @param $params
     * @return string
     */
    public function getInformationRecipient($pid, $encounter, $recipients, $params)
    {
        $information_recipient = '';
        $field_name = array();
        $details = $this->getDetails('hie_recipient_id');

        $appTable = new ApplicationTable();

        if ($recipients == 'hie') {
            $details['fname'] = 'MyHealth';
            $details['lname'] = '';
            $details['organization'] = '';
        } elseif ($recipients == 'emr_direct') {
            $query = "select fname, lname, organization, street, city, state, zip, phonew1, facility from users where email_direct = ?";
            $field_name[] = $params;
        } elseif ($recipients == 'patient') {
            $query = "select fname, lname from patient_data WHERE pid = ?";
            $field_name[] = $params;
        } else {
            if (!$params) {
                $params = $_SESSION['authUserID'];
            }
            $query = "select fname, lname, organization, street, city, state, zip, phonew1, facility from users where id = ?";
            $field_name[] = $params;
        }

        if ($recipients != 'hie') {
            $res = $appTable->zQuery($query, $field_name);
            $result = $res->current();
            if (empty($result['organization'])) {
                $result['organization'] = $result['facility'];
            }
            $details['fname'] = $result['fname'];
            $details['lname'] = $result['lname'];
            $details['organization'] = $result['organization'];
            $details['street'] = $result['street'];
            $details['city'] = $result['city'];
            $details['state'] = $result['state'];
            $details['zip'] = $result['zip'];
            $details['phonew1'] = $result['phonew1'];
        }

        $information_recipient = "<information_recipient>
        <fname>" . xmlEscape($details['fname']) . "</fname>
        <lname>" . xmlEscape($details['lname']) . "</lname>
        <organization>" . xmlEscape($details['organization']) . "</organization>
        <street>" . xmlEscape($details['street']) . "</street>
        <city>" . xmlEscape($details['city']) . "</city>
        <state>" . xmlEscape($details['state']) . "</state>
        <zip>" . xmlEscape($details['zip']) . "</zip>
        <phonew1>" . xmlEscape($details['phonew1']) . "</phonew1>
        </information_recipient>";

        return $information_recipient;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getLegalAuthenticator($pid, $encounter)
    {
        $legal_authenticator = '';
        $details = $this->getDetails('hie_legal_authenticator_id');

        $legal_authenticator = "<legal_authenticator>
            <streetAddressLine>" . xmlEscape($details['street'] ?? '') . "</streetAddressLine>
            <city>" . xmlEscape($details['city'] ?? '') . "</city>
            <state>" . xmlEscape($details['state'] ?? '') . "</state>
            <postalCode>" . xmlEscape($details['zip'] ?? '') . "</postalCode>
            <country>" . xmlEscape($details['country'] ?? '') . "</country>
            <telecom>" . xmlEscape((($details['phonew1'] ?? '') ? $details['phonew1'] : 0)) . "</telecom>
            <fname>" . xmlEscape($details['fname'] ?? '') . "</fname>
            <lname>" . xmlEscape($details['lname'] ?? '') . "</lname>
        </legal_authenticator>";

        return $legal_authenticator;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getAuthenticator($pid, $encounter)
    {
        $authenticator = '';
        $details = $this->getDetails('hie_authenticator_id');

        $authenticator = "<authenticator>
            <streetAddressLine>" . xmlEscape($details['street'] ?? '') . "</streetAddressLine>
            <city>" . xmlEscape($details['city'] ?? '') . "</city>
            <state>" . xmlEscape($details['state'] ?? '') . "</state>
            <postalCode>" . xmlEscape($details['zip'] ?? '') . "</postalCode>
            <country>" . xmlEscape($details['country'] ?? '') . "</country>
            <telecom>" . xmlEscape((($details['phonew1'] ?? '') ? $details['phonew1'] : 0)) . "</telecom>
            <fname>" . xmlEscape($details['fname'] ?? '') . "</fname>
            <lname>" . xmlEscape($details['lname'] ?? '') . "</lname>
        </authenticator>";

        return $authenticator;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getPrimaryCareProvider($pid, $encounter): string
    {
        // primary from demographics
        $getprovider = $this->getProviderId($pid);
        // @TODO I don't like this much. Should add date UI in care team assignments.
        $getprovider_status = $this->getPatientProviderStatus($pid) ?? null;
        $provider_since_date = !empty($getprovider_status['provider_since_date']) ? date('Y-m-d H:i:sO', strtotime($getprovider_status['provider_since_date'])) : date('Y-m-d H:i:sO');
        $provenanceXml = ""; // if we can't get the provenance information we'll just have to leave it as empty
        if (!empty($getprovider)) { // from patient_data
            $details = $this->getUserDetails($getprovider);
            $provenanceSql = "select updated_by AS provenance_updated_by, date AS date_modified FROM patient_data WHERE pid = ?";
            $provenanceRecord = [];
            $appTable = new ApplicationTable();
            $res = $appTable->zQuery($provenanceSql, array($pid));
            foreach ($res as $row) {
                $provenanceRecord = [
                    'author_id' => $row['provenance_updated_by']
                    ,'time' => $row['date_modified']
                ];
            }
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, $encounter);
        } else { // get from CCM setup
            $getprovider = $this->getCarecoordinationModuleSettingValue('hie_primary_care_provider_id');
            if (!empty($getprovider)) {
                $details = $this->getUserDetails($getprovider);
                $provenanceRecord = $this->getCarecoordinationProvenanceForField('hie_primary_care_provider_id');
                $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, $encounter);
            }
            $details = !empty($getprovider) ? $this->getUserDetails($getprovider) : null;
        }




        // Note for NPI: Many times a care team member may not have an NPI so instead of
        // an NPI OID use facility/document unique OID with user table reference for extension.
        $get_care_team_provider = explode("|", $this->getCareTeamProviderId($pid));
        if (empty($getprovider)) {
            // Last chance. Get the first care team member as primary.
            if (!empty($get_care_team_provider[0])) {
                $getprovider = $get_care_team_provider[0];
                $details = $this->getUserDetails($getprovider);
            }
        }
        if (!empty($details)) {
            $primary_care_provider = "
        <primary_care_provider>
          <provider>" . $provenanceXml . "
            <prefix>" . xmlEscape($details['title'] ?? '') . "</prefix>
            <fname>" . xmlEscape($details['fname'] ?? '') . "</fname>
            <lname>" . xmlEscape($details['lname'] ?? '') . "</lname>
            <speciality>" . xmlEscape($details['specialty'] ?? '') . "</speciality>
            <organization>" . xmlEscape($details['organization'] ?? '') . "</organization>
            <telecom>" . xmlEscape((($details['phonew1'] ?? '') ? $details['phonew1'] : 0)) . "</telecom>
            <street>" . xmlEscape($details['street'] ?? '') . "</street>
            <city>" . xmlEscape($details['city'] ?? '') . "</city>
            <state>" . xmlEscape($details['state'] ?? '') . "</state>
            <zip>" . xmlEscape($details['zip'] ?? '') . "</zip>
            <table_id>" . xmlEscape("provider-" . $getprovider ?? '') . "</table_id>
            <npi>" . xmlEscape($details['npi'] ?? '') . "</npi>
            <physician_type>" . xmlEscape($details['physician_type'] ?? '') . "</physician_type>
            <physician_type_code>" . xmlEscape($details['physician_type_code'] ?? '') . "</physician_type_code>
            <taxonomy>" . xmlEscape($details['taxonomy'] ?? '') . "</taxonomy>
            <taxonomy_description>" . xmlEscape($details['taxonomy_desc'] ?? '') . "</taxonomy_description>
            <provider_since>" . xmlEscape($provider_since_date ?: null) . "</provider_since>
          </provider>
        </primary_care_provider>";
        } else {
            $primary_care_provider = '';
        }

        $care_team_provider = "<care_team>" . $provenanceXml
            . "<is_active>" . ($getprovider_status['care_team_status'] ?? false) . "</is_active>";
        foreach ($get_care_team_provider as $team_member) {
            if ((int)$getprovider === (int)$team_member) {
                // primary should be a part of care team but just in case
                // I've kept primary separate. So either way, primary gets included.
                // in this case, we don't want to duplicate the provider.
                continue;
            }
            $details2 = $this->getUserDetails($team_member);
            if (empty($details2)) {
                continue;
            }
            $care_team_provider .= "<provider>
            <prefix>" . xmlEscape($details2['title']) . "</prefix>
            <fname>" . xmlEscape($details2['fname']) . "</fname>
            <lname>" . xmlEscape($details2['lname']) . "</lname>
            <speciality>" . xmlEscape($details2['specialty']) . "</speciality>
            <organization>" . xmlEscape($details2['organization']) . "</organization>
            <telecom>" . xmlEscape(($details2['phonew1'] ?: '')) . "</telecom>
            <street>" . xmlEscape($details2['street'] ?? '') . "</street>
            <city>" . xmlEscape($details2['city'] ?? '') . "</city>
            <state>" . xmlEscape($details2['state'] ?? '') . "</state>
            <zip>" . xmlEscape($details2['zip'] ?? '') . "</zip>
            <table_id>" . xmlEscape("provider-" . $team_member) . "</table_id>
            <npi>" . xmlEscape($details2['npi']) . "</npi>
            <physician_type>" . xmlEscape($details2['physician_type']) . "</physician_type>
            <physician_type_code>" . xmlEscape($details2['physician_type_code']) . "</physician_type_code>
            <taxonomy>" . xmlEscape($details2['taxonomy']) . "</taxonomy>
            <taxonomy_description>" . xmlEscape($details2['taxonomy_desc']) . "</taxonomy_description>
            <provider_since>" . xmlEscape($provider_since_date) . "</provider_since>
          </provider>
          ";
        }
        $care_team_provider .= "</care_team>
        ";
        return $primary_care_provider . $care_team_provider;
    }

    /*
    #******************************************************#
    #                  CONTINUITY OF CARE                  #
    #******************************************************#
    */
    /**
     * @param $pid
     * @return string
     */
    public function getAllergies($pid)
    {
        $allergies = '';
        $query = "SELECT l.id, l.title, l.begdate, l.enddate, lo.title AS observation,
            SUBSTRING(lo.codes, LOCATE(':',lo.codes)+1, LENGTH(lo.codes)) AS observation_code,
                        SUBSTRING(l.`diagnosis`,1,LOCATE(':',l.diagnosis)-1) AS code_type_real,
                        l.reaction, l.diagnosis, l.diagnosis AS code, author.id AS provenance_updated_by, l.modifydate
                        FROM lists AS l
                        LEFT JOIN list_options AS lo ON lo.list_id = ? AND lo.option_id = l.severity_al
                        left join users author ON l.user = author.username
                        WHERE l.type = ? AND l.pid = ?";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array('severity_ccda', 'allergy', $pid));

        $allergies = "<allergies>";
        foreach ($res as $row) {
            $split_codes = explode(';', $row['code']);
            // we go with the user the last modified the record as our provenance author
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['modifydate']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, null);
            foreach ($split_codes as $key => $single_code) {
                $code = $code_text = $code_rx = $code_text_rx = $code_snomed = $code_text_snomed = $reaction_text = $reaction_code = '';
                $get_code_details = explode(':', $single_code);

                if ($get_code_details[0] == 'RXNORM' || $get_code_details[0] == 'RXCUI') {
                    $code_rx = $get_code_details[1];
                    $code_text_rx = lookup_code_descriptions($single_code);
                } elseif ($get_code_details[0] == 'SNOMED' || $get_code_details[0] == 'SNOMED-CT') {
                    $code_snomed = $get_code_details[1];
                    $code_text_snomed = lookup_code_descriptions($row['code']);
                } else {
                    $code = $get_code_details[1];
                    $code_text = lookup_code_descriptions($single_code);
                }

                $active = $status_table = '';

                if ($row['enddate']) {
                    $active = 'completed';
                    $allergy_status = 'completed';
                    $status_table = 'Resolved';
                    $status_code = '73425007';
                } else {
                    $active = 'completed';
                    $allergy_status = 'active';
                    $status_table = 'Active';
                    $status_code = '55561003';
                }

                if ($row['reaction']) {
                    $reaction_text = (new CarecoordinationTable())->getListTitle($row['reaction'], 'reaction', '');
                    $reaction_code = (new CarecoordinationTable())->getCodes($row['reaction'], 'reaction');
                    $reaction_code = explode(':', $reaction_code);
                }

                $allergies .= "<allergy>" . $provenanceXml . "
                <id>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . $single_code)) . "</id>
                <sha_id>" . xmlEscape("36e3e930-7b14-11db-9fe1-0800200c9a66") . "</sha_id>
                <title>" . xmlEscape($row['title']) . ($single_code ? " [" . xmlEscape($single_code) . "]" : '') . "</title>
                <diagnosis_code>" . xmlEscape(($code ? $code : 0)) . "</diagnosis_code>
                <diagnosis>" . xmlEscape(($code_text ? Listener::z_xlt($code_text) : "")) . "</diagnosis>
                <rxnorm_code>" . xmlEscape(($code_rx ? $code_rx : 0)) . "</rxnorm_code>
                <rxnorm_code_text>" . xmlEscape(($code_text_rx ? Listener::z_xlt($code_text_rx) : "")) . "</rxnorm_code_text>
                <snomed_code>" . xmlEscape(($code_snomed ? $code_snomed : 0)) . "</snomed_code>
                <snomed_code_text>" . xmlEscape(($code_text_snomed ? Listener::z_xlt($code_text_snomed) : "")) . "</snomed_code_text>
                <status_table>" . ($status_table ? xmlEscape($status_table) : "") . "</status_table>
                <status>" . ($active ? xmlEscape($active) : "") . "</status>
                <allergy_status>" . ($allergy_status ? xmlEscape($allergy_status) : "") . "</allergy_status>
                <status_code>" . ($status_code ? xmlEscape($status_code) : 0) . "</status_code>
                <outcome>" . xmlEscape(($row['observation'] ? Listener::z_xlt($row['observation']) : "")) . "</outcome>
                <outcome_code>" . xmlEscape(($row['observation_code'] ? $row['observation_code'] : 0)) . "</outcome_code>
                <startdate>" . xmlEscape($row['begdate'] ? preg_replace('/-/', '', $row['begdate']) : "00000000") . "</startdate>
                <enddate>" . xmlEscape($row['enddate'] ? preg_replace('/-/', '', $row['enddate']) : "00000000") . "</enddate>
                <reaction_text>" . xmlEscape($reaction_text ? Listener::z_xlt($reaction_text) : "") . "</reaction_text>
                <reaction_code>" . xmlEscape($reaction_code[1] ?: '') . "</reaction_code>
                <reaction_code_type>" . xmlEscape(str_replace('-', ' ', $reaction_code[0]) ?: '') . "</reaction_code_type>
                <RxNormCode>" . xmlEscape($code_rx) . "</RxNormCode>
                <RxNormCode_text>" . xmlEscape(!empty($code_text_rx) ? $code_text_rx : $row['title']) . "</RxNormCode_text>
                </allergy>";
                $this->amc_num_result['allergies'] += 1;
            }
        }

        $allergies .= "</allergies>";
        $this->amc_num_result['allergies'] = $this->getAmcCount($pid, 'allergy', $this->amc_num_result['allergies']);
        return $allergies;
    }

    /**
     * @param $pid
     * @return string
     */
    public function getMedications($pid)
    {
        $medications = '';
        $query = "select l.id, l.date_added, l.start_date, l.drug, l.dosage, l.quantity, l.size, l.substitute, l.drug_info_erx, l.active, SUBSTRING(l3.codes, LOCATE(':',l3.codes)+1, LENGTH(l3.codes)) AS route_code,
                       l.rxnorm_drugcode, l1.title as unit, l1.codes as unit_code,l2.title as form,SUBSTRING(l2.codes, LOCATE(':',l2.codes)+1, LENGTH(l2.codes)) AS form_code, l3.title as route, l4.title as `interval`,
                       u.title, u.fname, u.lname, u.mname, u.npi, u.street, u.streetb, u.city, u.state, u.zip, u.phonew1, l.note
                       ,u.id AS provider_id, l.date_modified, l.updated_by AS provenance_updated_by
                       from prescriptions as l
                       left join list_options as l1 on l1.option_id=unit AND l1.list_id = ?
                       left join list_options as l2 on l2.option_id=form AND l2.list_id = ?
                       left join list_options as l3 on l3.option_id=route AND l3.list_id = ?
                       left join list_options as l4 on l4.option_id=`interval` AND l4.list_id = ?
                       left join users as u on u.id = l.provider_id
                       where l.patient_id = ? and l.active = 1";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array('drug_units', 'drug_form', 'drug_route', 'drug_interval', $pid));

        $medications = "<medications>";
        foreach ($res as $row) {
            if (!$row['rxnorm_drugcode']) {
                $row['rxnorm_drugcode'] = $this->generate_code($row['drug']);
            }
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by'] ?? $row['provider_id']
                ,'time' => $row['date_modified']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, null);
            $unit = $str = $active = '';

            if ($row['size'] > 0) {
                $unit = $row['size'] . " " . Listener::z_xlt($row['unit']) . " ";
            }

            $str = $unit . " " . Listener::z_xlt($row['route']) . " " . $row['dosage'] . " " . Listener::z_xlt($row['form'] . " " . $row['interval']);

            if ($row['active'] > 0) {
                $active = 'active';
            } else {
                $active = 'completed';
            }

            if ($row['start_date']) {
                $start_date = str_replace('-', '', $row['start_date']);
                $start_date_formatted = \Application\Model\ApplicationTable::fixDate($row['start_date'], $GLOBALS['date_display_format'], 'yyyy-mm-dd');
                ;
            }

            $medications .= "<medication>" . $provenanceXml . "
    <id>" . xmlEscape($row['id']) . "</id>
    <extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'])) . "</extension>
    <sha_extension>" . xmlEscape("cdbd33f0-6cde-11db-9fe1-0800200c9a66") . "</sha_extension>
    <performer_name>" . xmlEscape($row['fname'] . " " . $row['mname'] . " " . $row['lname']) . "</performer_name>
    <fname>" . xmlEscape($row['fname']) . "</fname>
    <mname>" . xmlEscape($row['mname']) . "</mname>
    <lname>" . xmlEscape($row['lname']) . "</lname>
    <title>" . xmlEscape($row['title']) . "</title>
    <npi>" . xmlEscape($row['npi']) . "</npi>
    <address>" . xmlEscape($row['street']) . "</address>
    <city>" . xmlEscape($row['city']) . "</city>
    <state>" . xmlEscape($row['state']) . "</state>
    <zip>" . xmlEscape($row['zip']) . "</zip>
    <work_phone>" . xmlEscape($row['phonew1']) . "</work_phone>
    <drug>" . xmlEscape($row['drug']) . "</drug>
    <direction>" . xmlEscape($str) . "</direction>
    <dosage>" . xmlEscape($row['dosage']) . "</dosage>
    <size>" . xmlEscape(($row['size'] ? $row['size'] : 0)) . "</size>
    <unit>" . xmlEscape(($row['unit'] ? preg_replace('/\s*/', '', Listener::z_xlt($row['unit'])) : '')) . "</unit>
    <unit_code>" . xmlEscape(($row['unit_code'] ? $row['unit_code'] : 0)) . "</unit_code>
    <form>" . xmlEscape(Listener::z_xlt($row['form'])) . "</form>
    <form_code>" . xmlEscape(Listener::z_xlt($row['form_code'])) . "</form_code>
    <route_code>" . xmlEscape($row['route_code'] ?: $row['route']) . "</route_code>
    <route>" . xmlEscape($row['route']) . "</route>
    <interval>" . xmlEscape(Listener::z_xlt($row['interval'])) . "</interval>
    <start_date>" . xmlEscape($start_date) . "</start_date>
    <start_date_formatted>" . xmlEscape($row['start_date']) . "</start_date_formatted>
    <end_date>" . xmlEscape('') . "</end_date>
    <status>" . xmlEscape($active) . "</status>
    <indications>" . xmlEscape($row['pres_erx_diagnosis_name'] ?? "") . "</indications>
    <indications_code>" . xmlEscape($row['pres_erx_diagnosis'] ?? 0) . "</indications_code>
    <instructions>" . xmlEscape($row['note']) . "</instructions>
    <rxnorm>" . xmlEscape($row['rxnorm_drugcode']) . "</rxnorm>
    <provider_id></provider_id>
    <provider_name></provider_name>
    </medication>";
            $this->amc_num_result['medications'] += 1;
        }

        $medications .= "</medications>";
        $this->amc_num_result['medications'] = $this->getAmcCount($pid, 'medication', $this->amc_num_result['medications']);
        return $medications;
    }

    /**
     * @param $pid
     * @return string
     */
    public function getProblemList($pid)
    {
        UuidRegistry::createMissingUuidsForTables(['lists']);
        $problem_lists = '';
        $query = "select l.*, author.id AS provenance_updated_by, lo.title as observation, lo.codes as observation_code, l.diagnosis AS code
    from lists AS l
    left join users author ON l.user = author.username
    left join list_options as lo on lo.option_id = l.outcome AND lo.list_id = ?
    where l.type = ? and l.pid = ? AND l.outcome != ?"; // patched out /* AND l.id NOT IN(SELECT list_id FROM issue_encounter WHERE pid = ?)*/
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array('outcome', 'medical_problem', $pid, 1));

        $problem_lists .= '<problem_lists>';
        foreach ($res as $row) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $split_codes = explode(';', $row['code']);
            // we go with the user the last modified the record as our provenance author
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['modifydate']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, null);
            foreach ($split_codes as $key => $single_code) {
                $get_code_details = explode(':', $single_code);
                $code_type = $get_code_details[0];
                $code_type = ($code_type == 'SNOMED' || $code_type == 'SNOMED-CT') ? "SNOMED CT" : "ICD-10-CM";
                $code = $get_code_details[1];
                $code_text = lookup_code_descriptions($single_code);

                $age = $this->getAge($pid, $row['begdate']);
                $start_date = str_replace('-', '', $row['begdate']);
                $end_date = str_replace('-', '', $row['enddate']);

                $status = $status_table = '';
                $start_date = $start_date ?: '0';
                $end_date = $end_date ?: '0';

                //Active - 55561003     Completed - 73425007
                if ($end_date) {
                    $status = 'completed';
                    $status_table = 'Resolved';
                    $status_code = '73425007';
                } else {
                    $status = 'active';
                    $status_table = 'Active';
                    $status_code = '55561003';
                }

                $observation = $row['observation'];
                $observation_code = explode(':', $row['observation_code']);
                $observation_code = $observation_code[1] ?? null;
                $problem_lists .= "<problem>" . $provenanceXml . "
                <problem_id>" . ($code ? xmlEscape($row['id']) : '') . "</problem_id>
                <extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'])) . "</extension>
                <sha_extension>" . xmlEscape($row['uuid']) . "</sha_extension>
                <title>" . xmlEscape(trim($row['title'])) . "</title>
                <code>" . ($code ? xmlEscape($code) : '') . "</code>
                <code_type>" . ($code ? xmlEscape($code_type) : '') . "</code_type>
                <code_text>" . xmlEscape(($code_text ?: '')) . "</code_text>
                <age>" . xmlEscape($age) . "</age>
                <start_date_table>" . xmlEscape($row['begdate']) . "</start_date_table>
                <start_date>" . xmlEscape($start_date) . "</start_date>
                <end_date>" . xmlEscape($end_date) . "</end_date>
                <status>" . xmlEscape($status) . "</status>
                <status_table>" . xmlEscape($status_table) . "</status_table>
                <status_code>" . xmlEscape($status_code) . "</status_code>
                <observation>" . xmlEscape(($observation ? Listener::z_xlt($observation) : "")) . "</observation>
                <observation_code>" . xmlEscape(($observation_code ?: '')) . "</observation_code>
                <diagnosis>" . xmlEscape($code ?: '') . "</diagnosis>
                </problem>";
                $this->amc_num_result['problems'] += 1;
            }
        }

        $problem_lists .= '</problem_lists>';
        $this->amc_num_result['problems'] = $this->getAmcCount($pid, 'medical_problem', $this->amc_num_result['problems']);
        return $problem_lists;
    }

    /**
     * @param $pid
     * @param $list_type
     * @param $current_count
     * @return mixed
     */
    private function getAmcCount($pid, $list_type, $current_count)
    {
        if (empty($current_count)) {
            $no_list_count = sqlQuery("select count(*) as cnt from lists_touch where pid = ? and type = ?", array($pid, $list_type));
            $list_count = sqlQuery("select count(*) as cnt from lists where pid = ? and type = ?", array($pid, $list_type));
            $current_count = $no_list_count['cnt'] + $list_count['cnt'];
        }
        return $current_count;
    }

    /**
     * @param $pid
     * @return string
     */
    public function getMedicalDeviceList($pid)
    {
        $medical_devices = '';
        $query = "select l.*, author.id AS provenance_updated_by, lo.title as observation, lo.codes as observation_code, l.diagnosis AS code
    from lists AS l
    left join users author ON l.user = author.username
    left join list_options as lo on lo.option_id = l.outcome AND lo.list_id = ?
    where l.type = ? and l.pid = ? AND l.outcome != ? AND l.id NOT IN(SELECT list_id FROM issue_encounter WHERE pid = ?)";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array('outcome', 'medical_device', $pid, 1, $pid));

        $medical_devices .= '<medical_devices>';
        foreach ($res as $row) {
            $split_codes = explode(';', $row['code']);
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['modifydate']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, null);
            foreach ($split_codes as $key => $single_code) {
                $get_code_details = explode(':', $single_code);
                $code_type = $get_code_details[0];
                $code_type = ($code_type == 'SNOMED' || $code_type == 'SNOMED-CT') ? "SNOMED CT" : "ICD-10-CM";
                $code = $get_code_details[1];
                $code_text = lookup_code_descriptions($single_code);

                $start_date = str_replace('-', '', $row['begdate']);
                $end_date = str_replace('-', '', $row['enddate']);

                $status = $status_table = '';
                $start_date = $start_date ?: '';
                $end_date = $end_date ?: '';

                //Active - 55561003     Completed - 73425007
                if ($end_date) {
                    $status = 'completed';
                    $status_table = 'Resolved';
                    $status_code = '73425007';
                } else {
                    $status = 'active';
                    $status_table = 'Active';
                    $status_code = '55561003';
                }

                $observation = $row['observation'];
                $observation_code = explode(':', $row['observation_code']);
                $observation_code = $observation_code[1] ?? '';

                $medical_devices .= "<device>" . $provenanceXml . "
                <extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'])) . "</extension>
                <sha_extension>" . xmlEscape($this->formatUid($_SESSION['site_id'] . $row['udi'])) . "</sha_extension>
                <title>" . xmlEscape($row['title']) . ($single_code ? " [" . xmlEscape($single_code) . "]" : '') . "</title>
                <code>" . ($code ? xmlEscape($code) : '') . "</code>
                <code_type>" . ($code ? xmlEscape($code_type) : '') . "</code_type>
                <code_text>" . xmlEscape(($code_text ?: '')) . "</code_text>
                <udi>" . xmlEscape($row['udi']) . "</udi>
                <start_date_table>" . xmlEscape($row['begdate']) . "</start_date_table>
                <start_date>" . xmlEscape($start_date) . "</start_date>
                <end_date>" . xmlEscape($end_date) . "</end_date>
                <status>" . xmlEscape($status) . "</status>
                <status_table>" . xmlEscape($status_table) . "</status_table>
                <status_code>" . xmlEscape($status_code) . "</status_code>
                <observation>" . xmlEscape(($observation ? Listener::z_xlt($observation) : "")) . "</observation>
                <observation_code>" . xmlEscape(($observation_code ?: '')) . "</observation_code>
                <diagnosis>" . xmlEscape($code ?: '') . "</diagnosis>
                </device>";
            }
        }

        $medical_devices .= '</medical_devices>';
        return $medical_devices;
    }

    /**
     * @param $pid
     * @return string
     */
    public function getImmunization($pid)
    {
        $immunizations = '';
        $query = "SELECT im.*, cd.code_text, DATE(administered_date) AS administered_date, 
            DATE_FORMAT(administered_date,'%Y%m%d') AS administered_formatted, lo.title as route_of_administration,
            u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.streetb, u.city, u.state, u.zip, u.phonew1,
            f.name, f.phone, SUBSTRING(lo.codes, LOCATE(':',lo.codes)+1, LENGTH(lo.codes)) AS route_code, lo.notes AS route_code_notes,
            im.updated_by AS provenance_updated_by, im.update_date
            FROM immunizations AS im
            LEFT JOIN codes AS cd ON cd.code = im.cvx_code
            JOIN code_types AS ctype ON ctype.ct_key = 'CVX' AND ctype.ct_id=cd.code_type
            LEFT JOIN list_options AS lo ON lo.list_id = 'drug_route' AND lo.option_id = im.route
            LEFT JOIN users AS u ON u.id = im.administered_by_id
            LEFT JOIN facility AS f ON f.id = u.facility_id
            WHERE im.patient_id=? AND added_erroneously = 0";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));

        $immunizations .= '<immunizations>';
        foreach ($res as $row) {
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['update_date']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, null);

            $immunizations .= "
        <immunization>" . $provenanceXml . "
        <extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'])) . "</extension>
        <sha_extension>" . xmlEscape("e6f1ba43-c0ed-4b9b-9f12-f435d8ad8f92") . "</sha_extension>
        <id>" . xmlEscape($row['id']) . "</id>
        <cvx_code>" . xmlEscape($row['cvx_code']) . "</cvx_code>
        <code_text>" . xmlEscape($row['code_text']) . "</code_text>
        <npi>" . xmlEscape($row['npi']) . "</npi>
        <administered_by>" . xmlEscape($row['administered_by']) . "</administered_by>
        <fname>" . xmlEscape($row['fname']) . "</fname>
        <mname>" . xmlEscape($row['mname']) . "</mname>
        <lname>" . xmlEscape($row['lname']) . "</lname>
        <title>" . xmlEscape($row['title']) . "</title>
        <address>" . xmlEscape($row['street']) . "</address>
        <city>" . xmlEscape($row['city']) . "</city>
        <state>" . xmlEscape($row['state']) . "</state>
        <zip>" . xmlEscape($row['zip']) . "</zip>
        <work_phone>" . xmlEscape($row['phonew1']) . "</work_phone>
        <administered_on>" . xmlEscape($row['administered_date']) . "</administered_on>
        <administered_formatted>" . xmlEscape($row['administered_formatted']) . "</administered_formatted>
        <note>" . xmlEscape($row['note']) . "</note>
        <route_of_administration>" . xmlEscape(Listener::z_xlt($row['route_of_administration'])) . "</route_of_administration>
        <route_code>" . (xmlEscape($row['route_code']) ?: xmlEscape($row['route_code_notes']) ) . "</route_code>
        <status>completed</status>
        <facility_name>" . xmlEscape($row['name']) . "</facility_name>
        <facility_phone>" . xmlEscape($row['phone']) . "</facility_phone>
        </immunization>";
        }

        $immunizations .= '</immunizations>';

        return $immunizations;
    }

    /**
     * @param $pid
     * @return string
     */
    public function getProcedures($pid, $encounter)
    {
        $wherCon = '';
        $sqlBindArray = [];
        $rows = [];
        if (!empty($this->encounterFilterList)) {
            $wherCon .= " b.encounter IN (" . implode(",", array_map('intval', $this->encounterFilterList)) . ") AND ";
        } elseif ($this->searchFiltered) {
            // if we are filtering our results, if there is no connected procedures to an encounter that fits within our
            // date range then we want to return an empty procedures list
            return "<procedures></procedures>";
        }
        $procedure = '';
        $query = "SELECT b.id, b.date as proc_date, b.code_text, b.code, b.code_type, fe.date,
    u.fname, u.lname, u.mname, u.npi, u.street, u.city, u.state, u.zip, u.id AS provenance_updated_by, u.phonew1,
    f.id as fid, f.name, f.phone, f.street as fstreet, f.city as fcity, f.state as fstate, f.postal_code as fzip, f.country_code, f.phone as fphone
    FROM billing as b
    LEFT JOIN code_types as ct on ct.ct_key
    LEFT JOIN codes as c on c.code = b.code AND c.code_type = ct.ct_id
    LEFT JOIN form_encounter as fe on fe.pid = b.pid AND fe.encounter = b.encounter
    LEFT JOIN users AS u ON u.id = b.provider_id
    LEFT JOIN facility AS f ON f.id = fe.facility_id
    WHERE $wherCon b.pid = ? and b.activity = ?";
        array_push($sqlBindArray, $pid, 1);
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, $sqlBindArray);
        foreach ($res as $row) {
            $rows[] = $row;
        }
        // Billing is not the only procedures source.
        $query_procedures = "SELECT
    poc.procedure_code AS code,
    poc.procedure_name AS code_text,
    poc.diagnoses,
    po.date_ordered,
    po.order_status,
    po.provider_id,
    fe.date,
    u.fname,
    u.lname,
    u.mname,
    u.npi,
    u.street,
    u.city,
    u.state,
    u.zip,
    u.id AS provenance_updated_by,
    u.phonew1,
    f.id AS fid,
    f.name,
    f.phone,
    f.street AS fstreet,
    f.city AS fcity,
    f.state AS fstate,
    f.postal_code AS fzip,
    f.country_code,
    f.phone AS fphone
    FROM procedure_order AS po
    JOIN procedure_order_code AS poc ON poc.procedure_order_id = po.procedure_order_id
    LEFT JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id
    LEFT JOIN users AS u ON u.id = po.provider_id 
    LEFT JOIN facility AS f ON f.id = fe.facility_id
    WHERE $wherCon (po.procedure_order_type = 'order' OR po.procedure_order_type = 'procedure') AND po.patient_id = ? AND po.activity = ?";
        // same bindings
        $res_proc = $appTable->zQuery($query_procedures, $sqlBindArray);
        foreach ($res_proc as $row) {
            $rows[] = $row;
        }
        $procedure = '<procedures>';
        foreach ($rows as $row) {
            $tmp = explode(':', $row['code']);
            if (count($tmp ?? []) === 2) {
                $row['code_type'] = $tmp[0];
                $row['code'] = $tmp[1];
            }
            if (empty($row['code_text'] ?? null)) {
                $row['code_text'] = (new CodeTypesService())->resolveCode($row['code'], $row['code_type'])['code_text'] ?? null;
            }
            $provenanceRecord = [
            'author_id' => $row['provenance_updated_by'], 'time' => $row['proc_date']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, $encounter);
            $procedure .= "<procedure>" . $provenanceXml . "
            <extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['encounter'])) . "</extension>
            <sha_extension>" . xmlEscape("d68b7e32-7810-4f5b-9cc2-acd54b0fd85d") . "</sha_extension>
            <description>" . xmlEscape($row['code_text']) . "</description>
            <code>" . xmlEscape($row['code']) . "</code>
            <code_type>" . xmlEscape($row['code_type']) . "</code_type>
            <date>" . xmlEscape(substr($row['date'], 0, 10)) . "</date>
            <npi>" . xmlEscape($row['npi']) . "</npi>
            <fname>" . xmlEscape($row['fname']) . "</fname>
            <mname>" . xmlEscape($row['mname']) . "</mname>
            <lname>" . xmlEscape($row['lname']) . "</lname>
            <address>" . xmlEscape($row['street']) . "</address>
            <city>" . xmlEscape($row['city']) . "</city>
            <state>" . xmlEscape($row['state']) . "</state>
            <zip>" . xmlEscape($row['zip']) . "</zip>
            <work_phone>" . xmlEscape($row['phonew1']) . "</work_phone>
            <facility_extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['fid'])) . "</facility_extension>
            <facility_sha_extension>" . xmlEscape("c2ee9ee9-ae31-4628-a919-fec1cbb58686") . "</facility_sha_extension>
            <facility_name>" . xmlEscape($row['name']) . "</facility_name>
            <facility_address>" . xmlEscape($row['fstreet']) . "</facility_address>
            <facility_city>" . xmlEscape($row['fcity']) . "</facility_city>
            <facility_state>" . xmlEscape($row['fstate']) . "</facility_state>
            <facility_country>" . xmlEscape($row['country_code']) . "</facility_country>
            <facility_zip>" . xmlEscape($row['fzip']) . "</facility_zip>
            <facility_phone>" . xmlEscape($row['fphone']) . "</facility_phone>
            <procedure_date>" . xmlEscape(preg_replace('/-/', '', substr($row['proc_date'], 0, 10))) . "</procedure_date>
            </procedure>";
        }
        $procedure .= '</procedures>';
        return $procedure;
    }

    /**
     * @param $pid
     * @return string
     */
    public function getResults($pid, $encounter)
    {
        $wherCon = '';
        $sqlBindArray = [];
        if (!empty($this->encounterFilterList)) {
            $wherCon .= " po.encounter_id IN (" . implode(",", array_map('intval', $this->encounterFilterList)) . ") AND ";
        } elseif ($this->searchFiltered) {
            // if we are filtering our results, if there is no connected procedures to an encounter that fits within our
            // date range then we want to return an empty procedures list
            return "<results></results>";
        }

        $results = '';
        $query = "SELECT prs.result AS result_value, prs.units, prs.range, prs.result_text as order_title, prs.result_code, prs.procedure_result_id,
        prs.result_text as result_desc, prs.procedure_result_id AS test_code, poc.procedure_code, poc.procedure_name, poc.diagnoses, po.date_ordered, prs.date AS result_time, prs.abnormal AS abnormal_flag,po.order_status AS order_status
        , provider_id AS provenance_updated_by, prs.date AS result_date, pr.date_report AS report_date
        FROM procedure_order AS po
        JOIN procedure_order_code as poc on poc.procedure_order_id = po.procedure_order_id
        JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id
        JOIN procedure_result AS prs ON prs.procedure_report_id = pr.procedure_report_id
        WHERE $wherCon po.patient_id = ? AND prs.result NOT IN ('DNR','TNP')";
        array_push($sqlBindArray, $pid);

        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, $sqlBindArray);

        $results_list = array();
        foreach ($res as $row) {
            if (empty($row['result_code']) && empty($row['abnormal_flag'])) {
                continue;
            }
            // make sure we have our provenance information
            $results_list[$row['test_code']]['provenance_updated_by'] = $row['provenance_updated_by'];
            $results_list[$row['test_code']]['date_modified'] = $row['result_date'] ?? $row['report_date'] ?? $row['date_ordered'];

            $results_list[$row['test_code']]['test_code'] = $row['test_code'];
            $results_list[$row['test_code']]['order_title'] = $row['order_title'];
            $results_list[$row['test_code']]['order_status'] = $row['order_status'];
            $results_list[$row['test_code']]['date_ordered'] = substr(str_replace("-", '', $row['date_ordered']), 0, 8);
            $results_list[$row['test_code']]['date_ordered_table'] = $row['date_ordered'];
            $results_list[$row['test_code']]['procedure_code'] = $row['procedure_code'];
            $results_list[$row['test_code']]['procedure_name'] = $row['procedure_name'];
            $results_list[$row['test_code']]['subtest'][$row['procedure_result_id']]['result_code'] = ($row['result_code'] ? $row['result_code'] : 0);
            $results_list[$row['test_code']]['subtest'][$row['procedure_result_id']]['result_desc'] = $row['result_desc'];
            $results_list[$row['test_code']]['subtest'][$row['procedure_result_id']]['units'] = $row['units'];
            $results_list[$row['test_code']]['subtest'][$row['procedure_result_id']]['range'] = $row['range'];
            $results_list[$row['test_code']]['subtest'][$row['procedure_result_id']]['result_value'] = $row['result_value'];
            $results_list[$row['test_code']]['subtest'][$row['procedure_result_id']]['result_time'] = substr(preg_replace('/-/', '', $row['result_time']), 0, 8);
            $results_list[$row['test_code']]['subtest'][$row['procedure_result_id']]['abnormal_flag'] = $row['abnormal_flag'];
        }

        $results = '<results>';
        foreach ($results_list as $row) {
            $order_status = $order_status_table = '';
            if ($row['order_status'] == 'complete') {
                $order_status = 'completed';
                $order_status_table = 'completed';
            } elseif ($row['order_status'] == 'pending') {
                $order_status = 'active';
                $order_status_table = 'pending';
            } else {
                $order_status = 'completed';
                $order_status_table = '';
            }
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['date_modified']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, $encounter);

            $results .= '<result>' . $provenanceXml . '
        <extension>' . xmlEscape(base64_encode($_SESSION['site_id'] . $row['test_code'])) . '</extension>
        <root>' . xmlEscape("7d5a02b0-67a4-11db-bd13-0800200c9a66") . '</root>
        <date_ordered>' . xmlEscape($row['date_ordered']) . '</date_ordered>
        <date_ordered_table>' . xmlEscape($row['date_ordered_table']) . '</date_ordered_table>
        <title>' . xmlEscape($row['order_title']) . '</title>
        <test_code>' . xmlEscape($row['procedure_code']) . '</test_code>
        <test_name>' . xmlEscape($row['procedure_name']) . '</test_name>
        <order_status_table>' . xmlEscape($order_status_table) . '</order_status_table>
        <order_status>' . xmlEscape($order_status) . '</order_status>';
            foreach ($row['subtest'] as $row_1) {
                $units = $row_1['units'] ?: '';
                $highlow = preg_split("/[\s,-\--]+/", $row_1['range']);
                $results .= '
            <subtest>
            <extension>' . xmlEscape(base64_encode($_SESSION['site_id'] . $row_1['result_code'])) . '</extension>
            <root>' . xmlEscape("7d5a02b0-67a4-11db-bd13-0800200c9a66") . '</root>
            <range>' . xmlEscape($row_1['range']) . '</range>
            <low>' . xmlEscape(trim($highlow[0])) . '</low>
            <high>' . xmlEscape(trim($highlow[1] ?? '')) . '</high>
            <unit>' . xmlEscape($units) . '</unit>
            <result_code>' . xmlEscape($row_1['result_code']) . '</result_code>
            <result_desc>' . xmlEscape($row_1['result_desc']) . '</result_desc>
            <result_value>' . xmlEscape(($row_1['result_value'] ? $row_1['result_value'] : 0)) . '</result_value>
            <result_time>' . xmlEscape($row_1['result_time']) . '</result_time>
            <abnormal_flag>' . xmlEscape($row_1['abnormal_flag']) . '</abnormal_flag>
            </subtest>';
            }

            $results .= '
        </result>';
        }

        $results .= '</results>';
        return $results;
    }

    /*
    #**************************************************#
    #                ENCOUNTER HISTORY                 #
    #**************************************************#
    */
    /**
     * @param $pid
     * @return string
     */
    public function getEncounterHistory($pid)
    {
        $wherCon = '';
        $sqlBindArray = [];
        if (!empty($this->encounterFilterList)) {
            $wherCon .= " fe.encounter IN (" . implode(",", array_map('intval', $this->encounterFilterList)) . ") AND ";
        } elseif ($this->searchFiltered) {
            // if we are filtering our results, if there is no connected procedures to an encounter that fits within our
            // date range then we want to return an empty procedures list
            return "<encounter_list></encounter_list>";
        }

        $results = "";
        $query = "SELECT fe.date, fe.encounter,fe.reason, fe.encounter_type_code, fe.encounter_type_description,
        f.id as fid, f.name, f.phone, f.street as fstreet, f.city as fcity, f.state as fstate, f.postal_code as fzip, f.country_code, f.phone as fphone, f.facility_npi as fnpi,
        f.facility_code as foid, u.fname, u.mname, u.lname, u.npi, u.street, u.city, u.state, u.zip, u.phonew1, cat.pc_catname, lo.title AS physician_type, lo.codes AS physician_type_code
        FROM form_encounter AS fe
        LEFT JOIN facility AS f ON f.id=fe.facility_id
        LEFT JOIN users AS u ON u.id=fe.provider_id
        LEFT JOIN openemr_postcalendar_categories AS cat ON cat.pc_catid=fe.pc_catid
        LEFT JOIN list_options AS lo ON lo.list_id = 'physician_type' AND lo.option_id = u.physician_type
        WHERE $wherCon fe.pid = ? ORDER BY fe.date";
        array_push($sqlBindArray, $pid);
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, $sqlBindArray);

        $primary_diagnosis = '';
        $results = "<encounter_list>";
        foreach ($res as $row) {
            $tmp = explode(":", $row['physician_type_code']);
            $physician_code_type = str_replace('-', ' ', $tmp[0]);
            $row['physician_type_code'] = $tmp[1] ?? '';
            $date_zone = !empty($row['date']) ? date("Y-m-d H:i:sO", strtotime(($row['date']))) : '';
            $date_zone_end = !empty($date_zone) ? date("Y-m-d H:i:sO", strtotime('+30 minutes', strtotime($date_zone))) : '';
            $encounter_reason = '';
            if (empty($row['reason'])) {
                $row['reason'] = xlt('Reason not given');
            }
            $encounter_reason = "<encounter_reason>" . xmlEscape($row['reason']) . "</encounter_reason>";

            $codes = "";
            $query_procedures = "SELECT c.code, c.code_text FROM billing AS b
                JOIN code_types AS ct ON ct.ct_key = ?
                JOIN codes AS c ON c.code = b.code AND c.code_type = ct.ct_id
                WHERE b.pid = ? AND b.code_type = ? AND activity = 1 AND b.encounter = ?";
            $appTable_procedures = new ApplicationTable();
            $res_procedures = $appTable_procedures->zQuery($query_procedures, array('CPT4', $pid, 'CPT4', $row['encounter']));
            $issue_q = "SELECT ll.diagnosis AS encounter_diagnosis, ll.diagnosis as raw_diagnosis,
                ll.title, ll.begdate, ll.enddate, ie.list_id From issue_encounter AS ie
                Left JOIN lists AS ll ON ll.id = ie.list_id Where ie.encounter = ?";
            $res_issues = $appTable_procedures->zQuery($issue_q, array($row['encounter']));
            foreach ($res_procedures as $row_procedures) {
                $codes .= "
                <procedures>
                <code>" . xmlEscape($row_procedures['code']) . "</code>
                <code_type>" . xmlEscape("CPT4") . "</code_type>
                <text>" . xmlEscape($row_procedures['code_text']) . "</text>
                </procedures>";
            }
            $encounter_ext = base64_encode($_SESSION['site_id'] . $row['encounter']);
            $encounter_root = $this->formatUid($_SESSION['site_id'] . $row['encounter']);
            $problem = '';
            $primary_diagnosis = '';
            $issue_codes = '';
            if (count($res_issues ?? []) > 0) {
                $i = 0;
                foreach ($res_issues as $issue) {
                    $i++;
                    $tmp = explode(":", $issue['raw_diagnosis']);
                    $code_type = str_replace('-', ' ', $tmp[0]);

                    $encounter_activity = '';
                    if ($issue['enddate'] !== '') {
                        $encounter_activity = 'Completed';
                    } else {
                        $encounter_activity = 'Active';
                    }
                    $issue_ext = base64_encode($_SESSION['site_id'] . $issue['list_id']);
                    $issue_codes .= "
                    <problem>
                    <extension>" . $issue_ext . "</extension>
                    <date>" . xmlEscape($issue['begdate']) . "</date>
                    <code>" . xmlEscape($tmp[1]) . "</code>
                    <code_type>" . xmlEscape($code_type) . "</code_type>
                    <text>" . xmlEscape(Listener::z_xlt($issue['title'])) . "</text>
                    <status>" . xmlEscape($encounter_activity) . "</status>
                    </problem>";
                    // diagnosis for care plan forms etc.
                    if ($i === 1) {
                        $encounter_diagnosis = "
                        <encounter_diagnosis>
                        <extension>" . $issue_ext . "</extension>
                        <code>" . xmlEscape($tmp[1]) . "</code>
                        <code_type>" . xmlEscape($code_type) . "</code_type>
                        <text>" . xmlEscape(Listener::z_xlt($issue['title'])) . "</text>
                        <status>" . xmlEscape($encounter_activity) . "</status>
                        </encounter_diagnosis>";
                        if (empty($primary_diagnosis) && !empty($code_type)) {
                            $primary_diagnosis = "
                        <primary_diagnosis>
                        <root>" . xmlEscape($encounter_root) . "</root>
                        <extension>" . xmlEscape($encounter_ext) . "</extension>
                        <encounter_date>" . xmlEscape($date_zone) . "</encounter_date>
                        <encounter_end_date>" . xmlEscape($date_zone_end) . "</encounter_end_date>
                        <code>" . xmlEscape($tmp[1] ?? '') . "</code>
                        <code_type>" . xmlEscape($code_type ?? '') . "</code_type>
                        <text>" . xmlEscape(Listener::z_xlt($issue['title'] ?? '')) . "</text>
                        <status>" . xmlEscape($encounter_activity ?? '') . "</status>
                        </primary_diagnosis>";
                        }
                    }
                }
            } else {
                $encounter_diagnosis = "
                <encounter_diagnosis>
                <extension></extension>
                <code></code>
                <code_type></code_type>
                <text></text>
                <status></status>
                </encounter_diagnosis>";
                $issue_codes = "
                <problem>
                <extension></extension>
                <code></code>
                <code_type></code_type>
                <text></text>
                <status></status>
                </problem>";
            }
            if (empty($primary_diagnosis) && !empty($code_type)) {
                $primary_diagnosis = "
                <primary_diagnosis>
                <root>" . xmlEscape($encounter_root) . "</root>
                <extension>" . xmlEscape($encounter_ext) . "</extension>
                <encounter_date>" . xmlEscape($date_zone) . "</encounter_date>
                <encounter_end_date>" . xmlEscape($date_zone_end) . "</encounter_end_date>
                <code>" . xmlEscape($tmp[1] ?? '') . "</code>
                <code_type>" . xmlEscape($code_type ?? '') . "</code_type>
                <text>" . xmlEscape(Listener::z_xlt($issue['title'] ?? '')) . "</text>
                <status>" . xmlEscape($encounter_activity ?? '') . "</status>
                </primary_diagnosis>";
            }
            $location_details = ($row['name'] !== '') ? (',' . $row['fstreet'] . ',' . $row['fcity'] . ',' . $row['fstate'] . ' ' . $row['fzip']) : '';
            $tmp_enc = explode(":", $row['encounter_type_code'] ?? '');
            $enc_code_type = str_replace('-', ' ', $tmp_enc[0] ?? '');
            $enc_code = $tmp_enc[1] ?? '';
            $results .= "
        <encounter>
        <extension>" . xmlEscape($encounter_ext) . "</extension>
        <sha_extension>" . xmlEscape($encounter_root) . "</sha_extension>
        <encounter_id>" . xmlEscape($row['encounter']) . "</encounter_id>
        <code>" . xmlEscape($enc_code) . "</code>
        <code_type>" . xmlEscape($enc_code_type) . "</code_type>
        <code_description>" . xmlEscape($row['encounter_type_description']) . "</code_description>
        <visit_category>" . xmlEscape($row['pc_catname']) . "</visit_category>
        <performer>" . xmlEscape($row['fname'] . " " . $row['mname'] . " " . $row['lname']) . "</performer>
        <physician_type_code>" . xmlEscape($row['physician_type_code']) . "</physician_type_code>
        <physician_type>" . xmlEscape($row['physician_type']) . "</physician_type>
        <physician_code_type>" . xmlEscape($physician_code_type) . "</physician_code_type>
        <npi>" . xmlEscape($row['npi']) . "</npi>
        <fname>" . xmlEscape($row['fname']) . "</fname>
        <mname>" . xmlEscape($row['mname']) . "</mname>
        <lname>" . xmlEscape($row['lname']) . "</lname>
        <street>" . xmlEscape($row['street']) . "</street>
        <city>" . xmlEscape($row['city']) . "</city>
        <state>" . xmlEscape($row['state']) . "</state>
        <zip>" . xmlEscape($row['zip']) . "</zip>
        <work_phone>" . xmlEscape($row['phonew1']) . "</work_phone>
        <location>" . xmlEscape($row['name']) . "</location>
        <location_details>" . xmlEscape($location_details) . "</location_details>
        <date>" . xmlEscape($date_zone) . "</date>
        <date_formatted>" . xmlEscape(str_replace("-", '', substr($row['date'], 0, 10))) . "</date_formatted>
        <facility_extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['fid'])) . "</facility_extension>
        <facility_sha_extension>" . xmlEscape($this->formatUid($_SESSION['site_id'] . $row['fid'])) . "</facility_sha_extension>
        <facility_npi>" . xmlEscape($row['fnpi']) . "</facility_npi>
        <facility_oid>" . xmlEscape($row['foid']) . "</facility_oid>
        <facility_name>" . xmlEscape($row['name']) . "</facility_name>
        <facility_address>" . xmlEscape($row['fstreet']) . "</facility_address>
        <facility_city>" . xmlEscape($row['fcity']) . "</facility_city>
        <facility_state>" . xmlEscape($row['fstate']) . "</facility_state>
        <facility_country>" . xmlEscape($row['country_code']) . "</facility_country>
        <facility_zip>" . xmlEscape($row['fzip']) . "</facility_zip>
        <facility_phone>" . xmlEscape($row['fphone']) . "</facility_phone>
        <encounter_procedures>$codes</encounter_procedures>
        <encounter_problems>$issue_codes</encounter_problems>
        $encounter_diagnosis
        $encounter_reason
        </encounter>";
        }
        if (empty($primary_diagnosis)) {
            $primary_diagnosis = "
                <primary_diagnosis>
                <root></root>
                <extension></extension>
                <encounter_date></encounter_date>
                <encounter_end_date></encounter_end_date>
                <code></code>
                <code_type></code_type>
                <text></text>
                <status></status>
                </primary_diagnosis>";
        }
        $results .= "</encounter_list>" . $primary_diagnosis;
        return $results;
    }

    /*
    #**************************************************#
    #                  PROGRESS NOTES                  #
    #**************************************************#
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getProgressNotes($pid, $encounter)
    {
        $progress_notes = '';
        $formTables_details = $this->fetchFields('progress_note', 'assessment_plan', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $progress_notes .= "<progressNotes>";
        if (!empty($result)) {
            foreach ($result as $row) {
                foreach ($row as $key => $value) {
                    $progress_notes .= "<item>" . xmlEscape($value) . "</item>";
                }
            }
        }

        $progress_notes .= "</progressNotes>";

        return $progress_notes;
    }

    /*
    #**************************************************#
    #                DISCHARGE SUMMARY                 #
    #**************************************************#
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getHospitalCourse($pid, $encounter)
    {
        $hospital_course = '';
        $formTables_details = $this->fetchFields('discharge_summary', 'hospital_course', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $hospital_course .= "<hospitalCourse><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $hospital_course .= xmlEscape(implode(' ', $row));
            }
        }
        $hospital_course .= "</item></hospitalCourse>";

        return $hospital_course;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getDischargeDiagnosis($pid, $encounter)
    {
        $discharge_diagnosis = '';
        $formTables_details = $this->fetchFields('discharge_summary', 'hospital_discharge_diagnosis', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $discharge_diagnosis .= "<dischargediagnosis><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $discharge_diagnosis .= xmlEscape(implode(' ', $row));
            }
        }
        $discharge_diagnosis .= "</item></dischargediagnosis>";

        return $discharge_diagnosis;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getDischargeMedications($pid, $encounter)
    {
        $discharge_medications = '';
        $formTables_details = $this->fetchFields('discharge_summary', 'hospital_discharge_medications', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $discharge_medications .= "<dischargemedication><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $discharge_medications .= xmlEscape(implode(' ', $row));
            }
        }

        $discharge_medications .= "</item></dischargemedication>";

        return $discharge_medications;
    }

    /*
    #***********************************************#
    #               PROCEDURE NOTES                 #
    #***********************************************#
    Sub section of PROCEDURE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $complications  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getComplications($pid, $encounter)
    {
        $complications = '';
        $formTables_details = $this->fetchFields('procedure_note', 'complications', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $complications .= "<complications>";
        $complications .= "<age>" . xmlEscape($this->getAge($pid)) . "</age><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $complications .= xmlEscape(implode(' ', $row));
            }
        }

        $complications .= "</item></complications>";

        return $complications;
    }

    /*
    Sub section of PROCEDURE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $procedure_diag  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getPostProcedureDiag($pid, $encounter)
    {
        $procedure_diag = '';
        $formTables_details = $this->fetchFields('procedure_note', 'postprocedure_diagnosis', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_diag .= '<procedure_diagnosis>';
        $procedure_diag .= "<age>" . xmlEscape($this->getAge($pid)) . "</age><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $procedure_diag .= xmlEscape(implode(' ', $row));
            }
        }

        $procedure_diag .= '</item></procedure_diagnosis>';

        return $procedure_diag;
    }

    /*
    Sub section of PROCEDURE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $procedure_description  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getProcedureDescription($pid, $encounter)
    {
        $procedure_description = '';
        $formTables_details = $this->fetchFields('procedure_note', 'procedure_description', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_description .= "<procedure_description><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $procedure_description .= xmlEscape(implode(' ', $row));
            }
        }

        $procedure_description .= "</item></procedure_description>";

        return $procedure_description;
    }

    /*
    Sub section of PROCEDURE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $procedure_indications  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getProcedureIndications($pid, $encounter)
    {
        $procedure_indications = '';
        $formTables_details = $this->fetchFields('procedure_note', 'procedure_indications', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_indications .= "<procedure_indications><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $procedure_indications .= xmlEscape(implode(' ', $row));
            }
        }

        $procedure_indications .= "</item></procedure_indications>";

        return $procedure_indications;
    }

    /*
    #***********************************************#
    #                OPERATIVE NOTES                #
    #***********************************************#
    Sub section of OPERATIVE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $anesthesia  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getAnesthesia($pid, $encounter)
    {
        $anesthesia = '';
        $formTables_details = $this->fetchFields('operative_note', 'anesthesia', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $anesthesia .= "<anesthesia><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $anesthesia .= xmlEscape(implode(' ', $row));
            }
        }

        $anesthesia .= "</item></anesthesia>";
        return $anesthesia;
    }

    /*
    Sub section of OPERATIVE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $post_operative_diag  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getPostoperativeDiag($pid, $encounter)
    {
        $post_operative_diag = '';
        $formTables_details = $this->fetchFields('operative_note', 'post_operative_diagnosis', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $post_operative_diag .= "<post_operative_diag><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $post_operative_diag .= xmlEscape(implode(' ', $row));
            }
        }

        $post_operative_diag .= "</item></post_operative_diag>";
        return $post_operative_diag;
    }

    /*
    Sub section of OPERATIVE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $pre_operative_diag  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getPreOperativeDiag($pid, $encounter)
    {
        $pre_operative_diag = '';
        $formTables_details = $this->fetchFields('operative_note', 'pre_operative_diagnosis', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $pre_operative_diag .= "<pre_operative_diag><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $pre_operative_diag .= xmlEscape(implode(' ', $row));
            }
        }

        $pre_operative_diag .= "</item></pre_operative_diag>";
        return $pre_operative_diag;
    }

    /*
    Sub section of OPERATIVE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $pre_operative_diag  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getEstimatedBloodLoss($pid, $encounter)
    {
        $estimated_blood_loss = '';
        $formTables_details = $this->fetchFields('operative_note', 'procedure_estimated_blood_loss', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $estimated_blood_loss .= "<blood_loss><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $estimated_blood_loss .= xmlEscape(implode(' ', $row));
            }
        }

        $estimated_blood_loss .= "</item></blood_loss>";
        return $estimated_blood_loss;
    }

    /*
    Sub section of OPERATIVE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $pre_operative_diag  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getProcedureFindings($pid, $encounter)
    {
        $procedure_findings = '';
        $formTables_details = $this->fetchFields('operative_note', 'procedure_findings', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_findings .= "<procedure_findings><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $procedure_findings .= xmlEscape(implode(' ', $row));
            }
        }

        $procedure_findings .= "</item><age>" . xmlEscape($this->getAge($pid)) . "</age></procedure_findings>";
        return $procedure_findings;
    }

    /*
    Sub section of OPERATIVE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $pre_operative_diag  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getProcedureSpecimensTaken($pid, $encounter)
    {
        $procedure_specimens = '';
        $formTables_details = $this->fetchFields('operative_note', 'procedure_specimens_taken', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_specimens .= "<procedure_specimens><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $procedure_specimens .= xmlEscape(implode(' ', $row));
            }
        }

        $procedure_specimens .= "</item></procedure_specimens>";
        return $procedure_specimens;
    }

    /*
    #***********************************************#
    #             CONSULTATION NOTES                #
    #***********************************************#
    Sub section of CONSULTATION NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $hp  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getHP($pid, $encounter)
    {
        $hp = '';
        $formTables_details = $this->fetchFields('consultation_note', 'history_of_present_illness', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $hp .= "<hp><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $hp .= xmlEscape(implode(' ', $row));
            }
        }

        $hp .= "</item></hp>";
        return $hp;
    }

    /*
    Sub section of CONSULTATION NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $physical_exam  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getPhysicalExam($pid, $encounter)
    {
        $physical_exam = '';
        $formTables_details = $this->fetchFields('consultation_note', 'physical_exam', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $physical_exam .= "<physical_exam><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $physical_exam .= xmlEscape(implode(' ', $row));
            }
        }

        $physical_exam .= "</item></physical_exam>";
        return $physical_exam;
    }

    /*
    #********************************************************#
    #                HISTORY AND PHYSICAL NOTES              #
    #********************************************************#
    Sub section of HISTORY AND PHYSICAL NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $chief_complaint  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getChiefComplaint($pid, $encounter)
    {
        $chief_complaint = '';
        $formTables_details = $this->fetchFields('history_physical_note', 'chief_complaint', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $chief_complaint .= "<chief_complaint><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $chief_complaint .= xmlEscape(implode(' ', $row));
            }
        }

        $chief_complaint .= "</item></chief_complaint>";
        return $chief_complaint;
    }

    /*
    Sub section of HISTORY AND PHYSICAL NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $general_status  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getGeneralStatus($pid, $encounter)
    {
        $general_status = '';
        $formTables_details = $this->fetchFields('history_physical_note', 'general_status', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $general_status .= "<general_status><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $general_status .= xmlEscape(implode(' ', $row));
            }
        }

        $general_status .= "</item></general_status>";
        return $general_status;
    }

    /*
    Sub section of HISTORY AND PHYSICAL NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $history_past_illness  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getHistoryOfPastIllness($pid, $encounter)
    {
        $history_past_illness = '';
        $formTables_details = $this->fetchFields('history_physical_note', 'hpi_past_med', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $history_past_illness .= "<history_past_illness><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $history_past_illness .= xmlEscape(implode(' ', $row));
            }
        }

        $history_past_illness .= "</item></history_past_illness>";
        return $history_past_illness;
    }

    /*
    Sub section of HISTORY AND PHYSICAL NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $review_of_systems  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getReviewOfSystems($pid, $encounter)
    {
        $review_of_systems = '';
        $formTables_details = $this->fetchFields('history_physical_note', 'review_of_systems', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $review_of_systems .= "<review_of_systems><item>";
        if (!empty($result)) {
            foreach ($result as $row) {
                $review_of_systems .= xmlEscape(implode(' ', $row));
            }
        }

        $review_of_systems .= "</item></review_of_systems>";
        return $review_of_systems;
    }

    /*
    Sub section of HISTORY AND PHYSICAL NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $vitals  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getVitals($pid)
    {
        $wherCon = '';
        $first_encounter = null;
        if (!empty($this->encounterFilterList)) {
            $wherCon .= " AND fe.encounter IN (" . implode(",", array_map('intval', $this->encounterFilterList)) . ") ";
            $first_encounter = reset($this->encounterFilterList);
        } elseif ($this->searchFiltered) {
            // if we are filtering our results, if there is no connected procedures to an encounter that fits within our
            // date range then we want to return an empty procedures list
            return "<vitals_list></vitals_list>";
        }


        $vitals = '';
        $query = "SELECT DATE(fe.date) AS date, fe.encounter, fv.id, fv.*
                ,u.id AS provenance_updated_by, f.date AS modifydate  FROM forms AS f
                JOIN form_encounter AS fe ON fe.encounter = f.encounter AND fe.pid = f.pid
                JOIN form_vitals AS fv ON fv.id = f.form_id
                LEFT JOIN users as u on u.username = fv.user
                WHERE f.pid = ? AND f.formdir = 'vitals' AND f.deleted=0 $wherCon
                ORDER BY fe.date DESC";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));


        $vitals .= "<vitals_list>";
        foreach ($res as $row) {
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['modifydate']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, $first_encounter);
            $convWeightValue = number_format($row['weight'] * 0.45359237, 2);
            $convHeightValue = number_format(round($row['height'] * 2.54, 1), 2);
            $convTempValue = number_format((round($row['temperature'] - 32) * (5 / 9)), 1);
            if ($GLOBALS['units_of_measurement'] == 2 || $GLOBALS['units_of_measurement'] == 4) {
                $weight_value = $convWeightValue;
                $weight_unit = 'kg';
                $height_value = $convHeightValue;
                $height_unit = 'cm';
                $temp_value = $convTempValue;
                $temp_unit = 'Cel';
            } else {
                // these value sets have to come from urn:oid:2.16.840.1.113883.1.11.12839 which is codes here: http://unitsofmeasure.org/
                // nice website with these values are https://build.fhir.org/ig/HL7/UTG/ValueSet-v3-UnitsOfMeasureCaseSensitive.html
                $temp = US_weight($row['weight'], 1);
                $tempArr = explode(" ", $temp);
                $weight_value = (float)$tempArr[0];
                $weight_unit = '[lb_av]'; // pounds US, British
                $height_value = (float)$row['height'];
                $height_unit = '[in_i]'; // inches international
                $temp_value = (float)$row['temperature'];
                $temp_unit = '[degF]'; // degrees fahrenheit
            }

            $vitals .= "<vitals>" . $provenanceXml . "
            <extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['encounter'])) . "</extension>
            <sha_extension>" . xmlEscape("c6f88321-67ad-11db-bd13-0800200c9a66") . "</sha_extension>
            <date>" . xmlEscape(date('Y-m-d', strtotime($row['date'])) ?: '') . "</date>
            <effectivetime>" . xmlEscape(date('Y-m-d H:i:s', strtotime($row['date']))) . "</effectivetime>
            <temperature>" . xmlEscape($temp_value ?: '') . "</temperature>
            <unit_temperature>" . xmlEscape($temp_unit ?: '') . "</unit_temperature>
            <extension_temperature>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'temperature')) . "</extension_temperature>
            <bpd>" . xmlEscape(($row['bpd'] ?: '')) . "</bpd>
            <extension_bpd>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'bpd')) . "</extension_bpd>
            <bps>" . xmlEscape(($row['bps'] ?: '')) . "</bps>
            <extension_bps>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'bps')) . "</extension_bps>
            <head_circ>" . xmlEscape(((float)$row['head_circ'] ?: '')) . "</head_circ>
            <extension_head_circ>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'head_circ')) . "</extension_head_circ>
            <pulse>" . xmlEscape(((float)$row['pulse'] ?: '')) . "</pulse>
            <extension_pulse>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'pulse')) . "</extension_pulse>
            <height>" . xmlEscape($height_value ?: '') . "</height>
            <extension_height>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'height')) . "</extension_height>
            <unit_height>" . xmlEscape($height_unit ?: '') . "</unit_height>
            <oxygen_saturation>" . xmlEscape(((float)$row['oxygen_saturation'] ?: '')) . "</oxygen_saturation>
            <extension_oxygen_saturation>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'oxygen_saturation')) . "</extension_oxygen_saturation>
            <breath>" . xmlEscape(((float)$row['respiration'] ?: '')) . "</breath>
            <extension_breath>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'breath')) . "</extension_breath>
            <weight>" . xmlEscape($weight_value ?: '') . "</weight>
            <extension_weight>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'weight')) . "</extension_weight>
            <unit_weight>" . xmlEscape($weight_unit ?: '') . "</unit_weight>
            <BMI>" . xmlEscape(((float)$row['BMI'] ?: '')) . "</BMI>
            <extension_BMI>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'BMI')) . "</extension_BMI>
            <BMI_status>" . xmlEscape(($row['BMI_status'] ?: '')) . "</BMI_status>
            <extension_oxygen_flow_rate>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'oxygen_flow_rate')) . "</extension_oxygen_flow_rate>
            <oxygen_flow_rate>" . xmlEscape(((float)$row['oxygen_flow_rate'] ?: '')) . "</oxygen_flow_rate>
            <extension_ped_weight_height>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'ped_weight_height')) . "</extension_ped_weight_height>
            <ped_weight_height>" . xmlEscape(((float)$row['ped_weight_height'] ?: '')) . "</ped_weight_height>
            <extension_ped_bmi>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'ped_bmi')) . "</extension_ped_bmi>
            <ped_bmi>" . xmlEscape(((float)$row['ped_bmi'] ?: '')) . "</ped_bmi>
            <extension_ped_head_circ>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'ped_head_circ')) . "</extension_ped_head_circ>
            <ped_head_circ>" . xmlEscape(((float)$row['ped_head_circ'] ?: '')) . "</ped_head_circ>
            <extension_inhaled_oxygen_concentration>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['id'] . 'inhaled_oxygen_concentration')) . "</extension_inhaled_oxygen_concentration>
            <inhaled_oxygen_concentration>" . xmlEscape(((float)$row['inhaled_oxygen_concentration'] ?: '')) . "</inhaled_oxygen_concentration>
            </vitals>";
        }

        $vitals .= "</vitals_list>";
        return $vitals;
    }

    /*
    Sub section of HISTORY AND PHYSICAL NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $social_history  XML which contains the details collected from the patient.
    */
    /**
     * @param $pid
     * @return string
     */
    public function getSocialHistory($pid)
    {
        $social_history = '';
        $arr = array(
            'alcohol' => '160573003',
            'drug' => '363908000',
            'employment' => '364703007',
            'exercise' => '256235009',
            'other_social_history' => '228272008',
            'diet' => '364393001',
            'smoking' => '229819007',
            'toxic_exposure' => '425400000'
        );
        $arr_status = array(
            'currenttobacco' => 'Current',
            'quittobacco' => 'Quit',
            'nevertobacco' => 'Never',
            'currentalcohol' => 'Current',
            'quitalcohol' => 'Quit',
            'neveralcohol' => 'Never'
        );

        $snomeds_status = array(
            'currenttobacco' => 'completed',
            'quittobacco' => 'completed',
            'nevertobacco' => 'completed',
            'not_applicabletobacco' => 'completed'
        );

        $snomeds = array(
            '1' => '449868002',
            '2' => '428041000124106',
            '3' => '8517006',
            '4' => '266919005',
            '5' => '77176002'
        );

        $alcohol_status = array(
            'currentalcohol' => 'completed',
            'quitalcohol' => 'completed',
            'neveralcohol' => 'completed'
        );

        $alcohol_status_codes = array(
            'currentalcohol' => '11',
            'quitalcohol' => '22',
            'neveralcohol' => '33'
        );

        $query = "SELECT id, tobacco, alcohol, exercise_patterns, recreational_drugs,date,created_by AS provenance_updated_by
                    FROM history_data WHERE pid=? ORDER BY id DESC LIMIT 1";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));

        $social_history .= "<social_history>";
        foreach ($res as $row) {
            $tobacco = explode('|', $row['tobacco']);
            $status_code = (new CarecoordinationTable())->getListCodes($tobacco[3] ?? '', 'smoking_status');
            $status_code = str_replace("SNOMED-CT:", "", $status_code);
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['date']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, null);
            $social_history .= "<history_element>" . $provenanceXml . "
                                  <extension>" . xmlEscape(base64_encode('smoking' . $_SESSION['site_id'] . $row['id'])) . "</extension>
                                  <sha_extension>" . xmlEscape("9b56c25d-9104-45ee-9fa4-e0f3afaa01c1") . "</sha_extension>
                                  <element>" . xmlEscape('Smoking') . "</element>
                                  <description>" . xmlEscape((new CarecoordinationTable())->getListTitle($tobacco[3] ?? '', 'smoking_status')) . "</description>
                                  <status_code>" . xmlEscape(($status_code ? $status_code : '')) . "</status_code>
                                  <status>" . xmlEscape((($snomeds_status[$tobacco[1] ?? ''] ?? '') ? $snomeds_status[$tobacco[1]] : "")) . "</status>
                                  <date>" . (($tobacco[2] ?? '') ? xmlEscape($this->date_format($tobacco[2])) : '') . "</date>
                                  <date_formatted>" . (($tobacco[2] ?? '') ? xmlEscape(preg_replace('/-/', '', $tobacco[2])) : '') . "</date_formatted>
                                  <code>" . xmlEscape(($arr['smoking'] ? $arr['smoking'] : '')) . "</code>
                            </history_element>";
            $alcohol = explode('|', $row['alcohol']);
            $social_history .= "<history_element>" . $provenanceXml . "
                                  <extension>" . xmlEscape(base64_encode('alcohol' . $_SESSION['site_id'] . $row['id'])) . "</extension>
                                  <sha_extension>" . xmlEscape("37f76c51-6411-4e1d-8a37-957fd49d2cef") . "</sha_extension>
                                  <element>" . xmlEscape('Alcohol') . "</element>
                                  <description>" . xmlEscape($alcohol[0] ?? '') . "</description>
                                  <status_code>" . xmlEscape((($alcohol_status_codes[$alcohol[1] ?? ''] ?? '') ? $alcohol_status_codes[$alcohol[1]] : '')) . "</status_code>
                                  <status>" . xmlEscape((($alcohol_status[$alcohol[1] ?? ''] ?? '') ? $alcohol_status[$alcohol[1]] : 'completed')) . "</status>
                                  <date>" . (($alcohol[2] ?? '') ? xmlEscape($this->date_format($alcohol[2])) : '') . "</date>
                                  <date_formatted>" . (($alcohol[2] ?? '') ? xmlEscape(preg_replace('/-/', '', $alcohol[2])) : '') . "</date_formatted>
                                  <code>" . xmlEscape($arr['alcohol']) . "</code>
                            </history_element>";
        }

        $social_history .= "</social_history>";
        return $social_history;
    }

    /**
     * Generates an unstructured component template for each patient document to be exported.
     * The resulting template is structured for direct placement into the unstructured
     * document by the ccda service.
     * BTW HL7 spec doesn't allow but one component per unstructured document so
     * this will break spec. However, we'll keep it our little secret as this is off by default.
     * User can turn on during exports for OpenEMR to OpenEMR transfer of patients.
     *
     * @param $pid
     * @return string
     */
    public function getDocumentsForExport($pid): string
    {
        $c = 0;
        $file_templates = "<patient_files>"; // This is known by service and should not be changed!
        $query = "SELECT c.id, c.name as cat_name, d.id AS document_id, d.id, d.type, d.mimetype, d.url, d.hash, d.docdate, d.name as file_name
                FROM `categories` AS c, documents AS d, `categories_to_documents` AS c2d
                WHERE c.id = c2d.category_id AND c2d.document_id = d.id AND d.foreign_id = ?";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($pid));
        foreach ($result as $row_folders) {
            if ((stripos($row_folders['file_name'], 'unstructured') !== false) || $row_folders['cat_name'] == 'CCDA') {
                continue;
            }
            $doc = Documents::getDocument($row_folders['document_id']);
            // I think gzcompress() is best option here, however if disagree and change then
            // remember to change the compression attribute to notify importers how to handle.
            $doc_compressed = gzcompress($doc, 9);
            $doc_b64 = base64_encode($doc_compressed);
            $mime = xmlEscape($row_folders['mimetype']);
            $cat = xmlEscape($row_folders['cat_name']);
            $name = xmlEscape($row_folders['file_name']);
            $hash = xmlEscape($row_folders['hash']);
            // I may put limits on these in near future depending on how well-behaved import is.
            $doc_len = strlen($doc);
            $doc_len_compressed = strlen($doc_compressed);
            $b64_len = strlen($doc_b64);
            // a component for each file. compression='ZL' is ZLIB RFC 1950 DF is Deflate RFC 1951
            $file_templates .= "
<component>
  <nonXMLBody>
    <text category='$cat' name='$name' hash='$hash' mediaType='$mime' representation='B64' compression='ZL'>$doc_b64</text>
  </nonXMLBody>
</component>";
            $c++;
        }
        $file_templates .= "</patient_files>";

        if ($c === 0) {
            // Empty null flavored document will not be generated by service.
            return '';
        }
        // Pass back template.
        return $file_templates;
    }

    /*
    #********************************************************#
    #                  UNSTRUCTURED DOCUMENTS                #
    #********************************************************#
    */
    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getUnstructuredDocuments($pid, $encounter)
    {
        $image = '';
        $formTables_details = $this->fetchFields('unstructured_document', 'unstructured_doc', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $image .= "<document>";
        if (!empty($result)) {
            foreach ($result as $row) {
                foreach ($row as $key => $value) {
                    $image .= "<item>";
                    $image .= "<type>" . xmlEscape($row[$key][1]) . "</type>";
                    $image .= "<content>" . xmlEscape($row[$key][0]) . "</content>";
                    $image .= "</item>";
                }
            }
        }

        $image .= "</document>";
        return $image;
    }

    /**
     * @param $field_name
     * @return null
     */
    public function getCarecoordinationModuleSettingValue($field_name)
    {
        $query = "SELECT field_value FROM modules AS mo "
        . " JOIN module_configuration AS conf ON mo.mod_id=conf.module_id "
        . " WHERE mo.mod_directory='Carecoordination' AND conf.field_name=?";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($field_name));
        foreach ($res as $result) {
            return $result['field_value'];
        }
        return null;
    }

    /**
     * @param $field_name
     * @return array|null
     */
    public function getCarecoordinationProvenanceForField($field_name)
    {
        $query = "SELECT updated_by AS provenance_updated_by, date_modified FROM modules AS mo "
            . " JOIN module_configuration AS conf ON mo.mod_id=conf.module_id "
            . " WHERE mo.mod_directory='Carecoordination' AND conf.field_name=?";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($field_name));
        $provenanceRecord = null;
        foreach ($res as $row) {
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['date_modified']
            ];
        }
        return $provenanceRecord;
    }

    /**
     * @param $field_name
     * @return void
     */
    public function getDetails($field_name)
    {
        if ($field_name == 'hie_custodian_id') {
            $query = "SELECT f.name AS organization, f.street, f.city, f.state, f.postal_code AS zip, f.phone as phonew1, f.uuid, f.oid AS facility_oid, f.facility_npi
        FROM facility AS f
        JOIN modules AS mo ON mo.mod_directory='Carecoordination'
        JOIN module_configuration AS conf ON conf.field_value=f.id AND mo.mod_id=conf.module_id
        WHERE conf.field_name=?";
        } elseif (is_string($field_name)) {
            $query = "SELECT u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.city, u.state, u.zip, CONCAT_WS(' ','',u.phonew1) AS phonew1, u.organization, u.specialty, conf.field_name, mo.mod_name, lo.title as  physician_type, SUBSTRING(lo.codes, LENGTH('SNOMED-CT:')+1, LENGTH(lo.codes)) as  physician_type_code, u.uuid
            ,facility.facility_npi, facility.facility_taxonomy, lous.title as taxonomy_desc, facility.uuid AS facility_uuid, facility.oid AS facility_oid, facility.name AS facility_name
            ,provider_roles.title AS provider_role_title, u.taxonomy AS provider_role_code
        FROM users AS u
        LEFT JOIN list_options AS lo ON lo.list_id = 'physician_type' AND lo.option_id = u.physician_type
        LEFT JOIN facility ON u.facility_id = facility.id
        LEFT JOIN list_options AS lous ON lous.list_id = 'us-core-provider-specialty' AND lous.option_id = facility.facility_taxonomy
        LEFT JOIN list_options AS provider_roles ON provider_roles.list_id = 'us-core-provider-role' AND provider_roles.option_id = u.taxonomy
        JOIN modules AS mo ON mo.mod_directory='Carecoordination'
        JOIN module_configuration AS conf ON conf.field_value=u.id AND mo.mod_id=conf.module_id
        WHERE conf.field_name=?";
        } elseif (is_int($field_name)) {
            $query = "SELECT u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.city, u.state, u.zip, CONCAT_WS(' ','',u.phonew1) AS phonew1, u.organization, u.specialty, lo.title as  physician_type, SUBSTRING(lo.codes, LENGTH('SNOMED-CT:')+1, LENGTH(lo.codes)) as  physician_type_code, u.uuid
        ,facility.facility_npi, facility.facility_taxonomy, lous.title as taxonomy_desc, facility.uuid AS facility_uuid, facility.oid AS facility_oid, facility.name AS facility_name
        ,provider_roles.title AS provider_role_title, u.taxonomy AS provider_role_code
        FROM users AS u
        LEFT JOIN facility ON u.facility_id = facility.id
        LEFT JOIN list_options AS lous ON lous.list_id = 'us-core-provider-specialty' AND lous.option_id = facility.facility_taxonomy
        LEFT JOIN list_options AS lo ON lo.list_id = 'physician_type' AND lo.option_id = u.physician_type
        LEFT JOIN list_options AS provider_roles ON provider_roles.list_id = 'us-core-provider-role' AND provider_roles.option_id = u.taxonomy
        WHERE u.id=?";
        }

        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($field_name));
        foreach ($res as $result) {
            if (!empty($result['phonew1'])) {
                $result['phonew1'] = trim($result['phonew1']);
            }
            if (empty($result['facility_oid'])) {
                // TODO: set the oid to an NPI number as our default if we don't have one.
                $result['facility_oid'] = "2.16.840.1.113883.4.6";
            }
            return $result;
        }
    }

    /*
    Get the Age of a patient
    * @param    int     $pid    Patient Internal Identifier.

    * return    int     $age    Age of a patient will be returned
    */
    /**
     * @param $pid
     * @param $date
     * @return int
     */
    public function getAge($pid, $date = null)
    {
        if ($date != '') {
            $date = $date;
        } else {
            $date = date('Y-m-d H:i:s');
        }

        $age = 0;
        $query = "select ROUND(DATEDIFF('$date',DOB)/365.25) AS age from patient_data where pid= ? ";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));

        foreach ($res as $row) {
            $age = $row['age'];
        }

        return $age;
    }

    /**
     * @return array
     */
    public function getRepresentedOrganization()
    {
        $query = "select * from facility where primary_business_entity = ? Limit 1";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array(1));

        $records = array();
        foreach ($res as $row) {
            $records = $row;
        }

        return $records;
    }

    /*Get the list of items mapped to a particular CCDA section

    * @param        $ccda_component     CCDA component
    * @param        $ccda_section       CCDA section of the above component
    * @param        $user_id            1
    * @return       $ret                Array containing the list of items mapped in a particular CCDA section.
    */
    /**
     * @param $ccda_component
     * @param $ccda_section
     * @param $user_id
     * @return array
     */
    public function fetchFields($ccda_component, $ccda_section, $user_id)
    {
        $form_type = $table_name = $field_names = '';
        $query = "select * from ccda_table_mapping
            left join ccda_field_mapping as ccf on ccf.table_id = ccda_table_mapping.id
            where ccda_component = ? and ccda_component_section = ? and user_id = ? and deleted = 0";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($ccda_component, $ccda_section, $user_id));
        $field_names_type3 = '';
        $ret = array();
        $field_names_type1 = '';
        $field_names_type2 = '';
        foreach ($res as $row) {
            $form_type = $row['form_type'];
            $table_name = $row['form_table'];
            $form_dir = $row['form_dir'];
            if ($form_type == 1) {
                if ($field_names_type1) {
                    $field_names_type1 .= ',';
                }

                $field_names_type1 .= $row['ccda_field'];
                $ret[$row['ccda_component_section'] . "_" . $form_dir] = array($form_type, $table_name, $form_dir, $field_names_type1);
            } elseif ($form_type == 2) {
                if ($field_names_type2) {
                    $field_names_type2 .= ',';
                }

                $field_names_type2 .= $row['ccda_field'];
                $ret[$row['ccda_component_section'] . "_" . $form_dir] = array($form_type, $table_name, $form_dir, $field_names_type2);
            } elseif ($form_type == 3) {
                if ($field_names_type3) {
                    $field_names_type3 .= ',';
                }

                $field_names_type3 .= $row['ccda_field'];
                $ret[$row['ccda_component_section'] . "_" . $form_dir] = array($form_type, $table_name, $form_dir, $field_names_type3);
            }
        }

        return $ret;
    }

    /*Fetch the form values

    * @param        $pid
    * @param        $encounter
    * @param        $formTables
    * @return       $res            Array of forms values of a single section
    */
    /**
     * @param $pid
     * @param $encounter
     * @param $formTables
     * @return array|string
     */
    public function fetchFormValues($pid, $encounter, $formTables)
    {
        if (empty($encounter)) {
            return "";
        }
        $res = array();
        $count_folder = 0;
        foreach ($formTables as $formTables_details) {
            /***************Fetching the form id for the patient***************/
            $query = "select form_id,encounter from forms where pid = ? and formdir = ? AND deleted=0";
            $appTable = new ApplicationTable();
            $form_ids = $appTable->zQuery($query, array($pid, $formTables_details[2]));
            /***************Fetching the form id for the patient***************/

            if ($formTables_details[0] == 1) {//Fetching the values from an HTML form
                if (!$formTables_details[1]) {//Fetching the complete form
                    foreach ($form_ids as $row) {//Fetching the values of each forms
                        foreach ($row as $key => $value) {
                            ob_start();
                            if (file_exists($GLOBALS['fileroot'] . '/interface/forms/' . $formTables_details[2] . '/report.php')) {
                                include_once($GLOBALS['fileroot'] . '/interface/forms/' . $formTables_details[2] . '/report.php');
                                call_user_func($formTables_details[2] . "_report", $pid, $encounter, 2, $value);
                            }

                            $res[0][$value] = ob_get_clean();
                        }
                    }
                } else {//Fetching a single field from the table
                    $primary_key = '';
                    $query = "SHOW INDEX FROM ? WHERE Key_name='PRIMARY'";
                    $appTable = new ApplicationTable();
                    $res_primary = $appTable->zQuery($query, array($formTables_details[1]));
                    foreach ($res_primary as $row_primary) {
                        $primary_key = $row_primary['Column_name'];
                    }

                    unset($res_primary);

                    $query = "select " . $formTables_details[3] . " from " . $formTables_details[1] . "
                    join forms as f on f.pid=? AND f.encounter=? AND f.form_id=" . $formTables_details[1] . "." . $primary_key . " AND f.formdir=?
                    where 1 = 1 ";
                    $appTable = new ApplicationTable();
                    $result = $appTable->zQuery($query, array($pid, $encounter, $formTables_details[2]));

                    foreach ($result as $row) {
                        foreach ($row as $key => $value) {
                            $res[0][$key] .= trim($value);
                        }
                    }
                }
            } elseif ($formTables_details[0] == 2) {//Fetching the values from an LBF form
                if (!$formTables_details[1]) {//Fetching the complete LBF
                    foreach ($form_ids as $row) {
                        foreach ($row as $key => $value) {
                            //This section will be used to fetch complete LBF. This has to be completed. We are working on this.
                        }
                    }
                } elseif (!$formTables_details[3]) {//Fetching the complete group from an LBF
                    foreach ($form_ids as $row) {//Fetching the values of each encounters
                        foreach ($row as $key => $value) {
                            ob_start();
                            ?>
                            <table>
                                <?php
                                display_layout_rows_group_new($formTables_details[2], '', '', $pid, $value, array($formTables_details[1]), '');
                                ?>
                            </table>
                            <?php
                            $res[0][$value] = ob_get_clean();
                        }
                    }
                } else {
                    $formid_list = "";
                    foreach ($form_ids as $row) {//Fetching the values of each forms
                        foreach ($row as $key => $value) {
                            if ($formid_list) {
                                $formid_list .= ',';
                            }

                            $formid_list .= $value;
                        }
                    }

                    $formid_list = $formid_list ? $formid_list : "''";
                    $lbf = "lbf_data";
                    $filename = "{$GLOBALS['srcdir']}/" . $formTables_details[2] . "/" . $formTables_details[2] . "_db.php";
                    if (file_exists($filename)) {
                        include_once($filename);
                    }

                    $field_ids = explode(',', $formTables_details[3]);
                    $fields_str = '';
                    foreach ($field_ids as $key => $value) {
                        if ($fields_str != '') {
                            $fields_str .= ",";
                        }

                        $fields_str .= "'$value'";
                    }

                    $query = "select * from " . $lbf . "
                    join forms as f on f.pid = ? AND f.form_id = " . $lbf . ".form_id AND f.formdir = ? AND " . $lbf . ".field_id IN (" . $fields_str . ")
                    where deleted = 0";
                    $appTable = new ApplicationTable();
                    $result = $appTable->zQuery($query, array($pid, $formTables_details[2]));

                    foreach ($result as $row) {
                        preg_match('/\.$/', trim($row['field_value']), $matches);
                        if (count($matches) == 0) {
                            $row['field_value'] .= ". ";
                        }

                        $res[0][$row['field_id']] .= $row['field_value'];
                    }
                }
            } elseif ($formTables_details[0] == 3) {//Fetching documents from mapped folders
                $query = "SELECT c.id, c.name, d.id AS document_id, d.type, d.mimetype, d.url, d.docdate
                FROM categories AS c, documents AS d, categories_to_documents AS c2d
                WHERE c.id = ? AND c.id = c2d.category_id AND c2d.document_id = d.id AND d.foreign_id = ?";

                $appTable = new ApplicationTable();
                $result = $appTable->zQuery($query, array($formTables_details[2], $pid));

                foreach ($result as $row_folders) {
                    $r = \Documents\Plugin\Documents::getDocument($row_folders['document_id']);
                    $res[0][$count_folder][0] = base64_encode($r);
                    $res[0][$count_folder][1] = $row_folders['mimetype'];
                    $res[0][$count_folder][2] = $row_folders['url'];
                    $count_folder++;
                }
            }
        }

        return $res;
    }

    /*
    * Retrive the saved settings of the module from database
    *
    * @param    string      $module_directory       module directory name
    * @param    string      $field_name             field name as in the module_settings table
    */
    /**
     * @param $module_directory
     * @param $field_name
     * @return void
     */
    public function getSettings($module_directory, $field_name)
    {
        $query = "SELECT mo_conf.field_value FROM modules AS mo
        LEFT JOIN module_configuration AS mo_conf ON mo_conf.module_id = mo.mod_id
        WHERE mo.mod_directory = ? AND mo_conf.field_name = ?";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($module_directory, $field_name));
        foreach ($result as $row) {
            return $row['field_value'];
        }
    }

    /*
    * Get the encounters in a particular date
    *
    * @param    Date    $date           Date format yyyy-mm-dd
    * $return   Array   $date_list      List of encounter in the given date.
    */
    /**
     * @param $date
     * @return array
     */
    public function getEncounterDate($date)
    {
        $date_list = array();
        $query = "select pid, encounter from form_encounter where date between ? and ?";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($date, $date));

        $count = 0;
        foreach ($result as $row) {
            $date_list[$count]['pid'] = $row['pid'];
            $date_list[$count]['encounter'] = $row['encounter'];
            $count++;
        }

        return $date_list;
    }

    /*
    * Sign Off an encounter
    *
    * @param    integer     $pid
    * @param    integer     $encounter
    * @return   array       $forms          List of locked forms
    */
    /**
     * @param $pid
     * @param $encounter
     * @return array
     */
    public function signOff($pid, $encounter)
    {
        /*Saving Demographics to locked data*/
        $query_patient_data = "SELECT * FROM patient_data WHERE pid = ?";
        $appTable = new ApplicationTable();
        $result_patient_data = $appTable->zQuery($query_patient_data, array($pid));
        foreach ($result_patient_data as $row_patient_data) {
        }

        $query_dem = "SELECT field_id FROM layout_options WHERE form_id = ?";
        $appTable = new ApplicationTable();
        $result_dem = $appTable->zQuery($query_dem, array('DEM'));

        foreach ($result_dem as $row_dem) {
            $query_insert_patient_data = "INSERT INTO combination_form_locked_data SET pid = ?, encounter = ?, form_dir = ?, field_name = ?, field_value = ?";
            $appTable = new ApplicationTable();
            $result_dem = $appTable->zQuery($query_insert_patient_data, array($pid, $encounter, 'DEM', $row_dem['field_id'], $row_patient_data[$row_dem['field_id']]));
        }

        /*************************************/

        $query_saved_forms = "SELECT formid FROM combined_encountersaved_forms WHERE pid = ? AND encounter = ?";
        $appTable = new ApplicationTable();
        $result_saved_forms = $appTable->zQuery($query_saved_forms, array($pid, $encounter));
        $count = 0;
        foreach ($result_saved_forms as $row_saved_forms) {
            $form_dir = '';
            $form_type = 0;
            $form_id = 0;
            $temp = explode('***', $row_saved_forms['formid']);
            if ($temp[1] == 1) { //Fetch HTML form id from the Combination form template
                $form_type = 0;
                $form_dir = $temp[0];
            } else { //Fetch LBF form from the Combination form template
                $temp_1 = explode('*', $temp[1]);
                if ($temp_1[1] == 1) { //Complete LBF in Combination form
                    $form_type = 1;
                    $form_dir = $temp[0];
                } elseif ($temp_1[1] == 2) { //Particular section from LBF in Combination form
                    $temp_2 = explode('|', $temp[0]);
                    $form_type = 1;
                    $form_dir = $temp_2[0];
                }
            }

            /*Fetch form id from the concerned tables*/
            if ($form_dir == 'HIS') { //Fetching History form id
                $query_form_id = "SELECT MAX(id) AS form_id FROM history_data WHERE pid = ?";
                $appTable = new ApplicationTable();
                $result_form_id = $appTable->zQuery($query_form_id, array($pid));
            } else { //Fetching normal form id
                $query_form_id = "select form_id from forms where pid = ? and encounter = ? and formdir = ?";
                $appTable = new ApplicationTable();
                $result_form_id = $appTable->zQuery($query_form_id, array($pid, $encounter, $form_dir));
            }

            foreach ($result_form_id as $row_form_id) {
                $form_id = $row_form_id['form_id'];
            }

            /****************************************/
            $forms[$count]['formdir'] = $form_dir;
            $forms[$count]['formtype'] = $form_type;
            $forms[$count]['formid'] = $form_id;
            $this->lockedthisform($pid, $encounter, $form_dir, $form_type, $form_id);
            $count++;
        }

        return $forms;
    }

    /*
    * Lock a component in combination form
    *
    * @param    integer     $pid
    * @param    integer     $encounter
    * @param    integer     $formdir        Form directory
    * @param    integer     $formtype       Form type, 0 => HTML, 1 => LBF
    * @param    integer     $formid         Saved form id from forms table
    *
    * @return   None
    */
    /**
     * @param $pid
     * @param $encounter
     * @param $formdir
     * @param $formtype
     * @param $formid
     * @return void
     */
    public function lockedthisform($pid, $encounter, $formdir, $formtype, $formid)
    {
        $query = "select count(*) as count from combination_form where pid = ? and encounter = ? and form_dir = ? and form_type = ? and form_id = ?";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($pid, $encounter, $formdir, $formtype, $formid));
        foreach ($result as $count) {
        }

        if ($count['count'] == 0) {
            $query_insert = "INSERT INTO combination_form SET pid = ?, encounter = ?, form_dir = ?, form_type = ?, form_id = ?";
            $appTable = new ApplicationTable();
            $result = $appTable->zQuery($query_insert, array($pid, $encounter, $formdir, $formtype, $formid));
        }
    }

    /*
    * Return the list of CCDA components
    *
    * @param    $type
    * @return   Array       $components
    */
    /**
     * @param $type
     * @return array
     */
    public function getCCDAComponents($type)
    {
        $components = array();
        $query = "select * from ccda_components where ccda_type = ?";
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($type));

        foreach ($result as $row) {
            $components[$row['ccda_components_field']] = $row['ccda_components_name'];
        }

        return $components;
    }

    /*
    * Store the status of the CCDA sent to HIE
    *
    * @param    integer     $pid
    * @param    integer     $encounter
    * @param    integer     $content
    * @param    integer     $time
    * @param    integer     $status
    * @return   None
    */
    /**
     * @param $pid
     * @param $encounter
     * @param $content
     * @param $time
     * @param $status
     * @param $user_id
     * @param $document_type
     * @param $view
     * @param $transfer
     * @param $emr_transfer
     * @return GeneratedCcdaResult
     * @throws \Exception
     */
    public function logCCDA($pid, $encounter, $content, $time, $status, $user_id, $document_type, $view = 0, $transfer = 0, $emr_transfer = 0)
    {
        $content = base64_decode($content);
        $document = new \Document();
        $document_type = $document_type ?? '';

        // we need to populate the category id based upon the document_type
        // TOC -> CCDA folder
        // CCD -> TOC
        //
        $categoryId = QueryUtils::fetchSingleValue(
            'Select `id` FROM categories WHERE name=?',
            'id',
            [self::CCDA_DOCUMENT_FOLDER]
        );

        if ($categoryId === false) {
            throw new RuntimeException("document category id does not exist in system");
        }

        // we want to grab the patient name here so we can provide a human readable document name
        $binaryUuid = (new UuidRegistry(['table_name' => 'ccda']))->createUuid();
        $patientService = new PatientService();
        $patient = $patientService->findByPid($pid);
        if (!empty($patient)) {
            // should always be populated...
            // we are only supporting xml for now
            $file_name = "CCDA_" . $patient['lname'] . '_' . $patient['fname'];
            if (!empty($document_type)) {
                $file_name .= '_' . $document_type;
            }
            $file_name .= '_' . date("Y-m-d") . ".xml";
        } else {
            $file_name = UuidRegistry::uuidToString($binaryUuid) . ".xml";
        }

        $mimeType = "text/xml";

        try {
            \sqlBeginTrans();

            // set the foreign key so we can track documents connected to a specific export
            $result = $document->createDocument(
                $pid,
                $categoryId,
                $file_name,
                $mimeType,
                $content
            );
            if (!empty($result)) {
                throw new \RuntimeException("Failed to save document for ccda. Message: " . $result);
            }

            $file_path = $document->get_url();
            $docid = $document->get_couch_docid();
            $revid = $document->get_couch_revid();
            $hash = $document->get_hash();
            $encrypted = $document->is_encrypted();
            $referralId = $this->getMostRecentPatientReferral($pid);

            $query = "insert into ccda (`uuid`, `pid`, `encounter`, `ccda_data`, `time`, `status`, `user_id`, `couch_docid`, `couch_revid`, `hash`, `view`, `transfer`, `emr_transfer`, `encrypted`, `transaction_id`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $appTable = new ApplicationTable();
            $result = $appTable->zQuery($query, array($binaryUuid, $pid, $encounter, $file_path, $time, $status, $user_id, $docid, $revid, $hash, $view, $transfer, $emr_transfer, $encrypted, $referralId));

            // now let's go ahead and log our amc actions for this behavior
            if (!empty($emr_transfer)) {
                $this->logAmc($pid, $referralId);
            }
            $moduleInsertId = $result->getGeneratedValue();

            // if we have an id, then let's update our document with the foreign key reference
            $document->set_foreign_reference_id($moduleInsertId);
            $document->set_foreign_reference_table('ccda');
            // if we have encounter information we are going to populate it.
            if (!empty($encounter)) {
                $document->set_encounter_check(0);
                $document->set_encounter_id($encounter);
            }
            $document->persist(); // save the updated references here.
            \sqlCommitTrans();
        } catch (\Exception $exception) {
            \sqlRollbackTrans();
            // TODO: @adunsulag do we need to clean up the file if we fail to commit the transaction here?
            throw $exception;
        }
        return new GeneratedCcdaResult($moduleInsertId, UuidRegistry::uuidToString($binaryUuid), $file_name, $content);
    }

    /**
     * Retrieves the most recent patient referral found in the transactions table or null if none is found.
     *
     * @param $pid
     * @return int|null
     */
    private function getMostRecentPatientReferral($pid)
    {
        $appTable = new ApplicationTable();
        // this segment of code is attempting to connect a CCDA to a Referral form (stored in the transactions)
        // table so we can track for Automated Measure Calculation (AMC) purposes.  This assumes that a referral
        // form has been created before the CCDA was sent (otherwise the transaction id is 0)

        // this query is only true if the referral was inserted as part of the ccda generation process.  This is code migrated from EncountermanagerTable
        $refs = $appTable->zQuery("select t.id as trans_id from transactions t where t.pid = ? and t.date = NOW() AND t.title = 'LBTref'", array($pid));
        if ($refs->count() == 0) {
            // the choose the most recent transaction to link this up...  This could create problems in the
            // future if multiple referrals are created BEFORE sending the CCDA.
            // TODO: is there a way to fix it so we can choose a referral (works for single ccda generation, more problematic for multiple patient select).
            $trans = $appTable->zQuery("select id from transactions where pid = ? and title = 'LBTref' order by id desc limit 1", array($pid));
            $trans_cur = $trans->current();
            $trans_id = $trans_cur['id'] ?? null;
        } else {
            foreach ($refs as $r) {
                $trans_id = $r['trans_id'];
            }
        }
        return $trans_id;
    }

    /**
     * Marks a ccda as having all of the requisite data to be counted for the send summary of care amc rule
     *
     * @param $pid        number The patient identifier
     * @param $referralId number The id of the referral stored in the transactions table
     */
    private function logAmc($pid, $referralId)
    {
        if (empty($referralId)) {
            // user is sending a CCDA w/o any kind of connecting referral... we will log the error and continue
            (new SystemLogger())->errorLogCaller("Failed to log amc information due to missing referral id.  User is sending CCDA w/o any connecting referral record", ['pid' => $pid]);
            return;
        }

        $amc_num_result = $this->amc_num_result;
        // either has the issue in the CCDA
        if ($amc_num_result['problems'] > 0 && $amc_num_result['medications'] > 0 && $amc_num_result['allergies'] > 0) {
            amcAdd('send_sum_valid_ccda', true, $pid, 'transactions', $referralId);
        }
    }

    /**
     * @param $logID
     * @return mixed
     */
    public function getCcdaLogDetails($logID = 0)
    {
        $query_ccda_log = "SELECT pid, encounter, ccda_data, time, status, user_id, couch_docid, couch_revid, view, transfer,emr_transfer FROM ccda WHERE id = ?";
        $appTable = new ApplicationTable();
        $res_ccda_log = $appTable->zQuery($query_ccda_log, array($logID));
        return $res_ccda_log->current();
    }

    /*
    * Convert date from database format to required format
    *
    * @param    String      $date       Date from database (format: YYYY-MM-DD)
    * @param    String      $format     Required date format
    *
    * @return   String      $formatted_date New formatted date
    */
    /**
     * @param $date
     * @param $format
     * @return string|void
     */
    public function date_format($date, $format = null)
    {
        if (!$date) {
            return;
        }

        $format = $format ?: 'm/d/y';
        $temp = explode(' ', $date); //split using space and consider the first portion, incase of date with time
        $date = $temp[0];
        $date = str_replace('/', '-', $date);
        $arr = explode('-', $date);

        if ($format == 'm/d/y') {
            $formatted_date = $arr[1] . "/" . $arr[2] . "/" . $arr[0];
        }

        $formatted_date = ($temp[1] ?? '') ? $formatted_date . " " . $temp[1] : $formatted_date; //append the time, if exists, with the new formatted date
        return $formatted_date;
    }

    /*
    * Generate CODE for medication, allergies etc.. if the code is not present by default.
    * The code is generated from the text that we give for medications or allergies.
    *
    * The text is encrypted using SHA1() and the string is parsed. Alternate letters from the SHA1 string is fetched
    * and the result is again parsed. We again take the alternate letters from the string. This is done twice to reduce
    * duplicate codes beign generated from this function.
    *
    * @param    String      Code text
    *
    * @return   String      Code
    */
    /**
     * @param $code_text
     * @return mixed|string
     */
    public function generate_code($code_text)
    {
        $rx = sqlQuery("Select drug_code From drugs Where name = ?", array("$code_text"));
        if (!empty($rx)) {
            return $rx['drug_code'];
        }
        $encrypted = sha1($code_text);
        $code = '';
        for ($i = 0, $iMax = strlen($encrypted); $i <= $iMax;) {
            $code .= $encrypted[$i];
            $i = $i + 2;
        }

        $encrypted = $code;
        $code = '';
        for ($i = 0, $iMax = strlen($encrypted); $i <= $iMax;) {
            $code .= $encrypted[$i];
            $i = $i + 2;
        }

        $code = strtoupper(substr($code, 0, 6));
        return $code;
    }

    /**
     * @param $pid
     * @return mixed|null
     */
    public function getProviderId($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT providerID FROM patient_data WHERE `pid`  = ?";
        $result = $appTable->zQuery($query, array($pid));
        $row = $result->current();
        return $row['providerID'] ?? null;
    }

    /**
     * @param $pid
     * @return mixed|null
     */
    public function getPatientProviderStatus($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT provider_since_date, care_team_status FROM patient_data WHERE `pid`  = ?";
        $result = $appTable->zQuery($query, array($pid));
        $row = $result->current();
        return $row ?? null;
    }

    /**
     * @param $uid
     * @return void
     */
    public function getUserDetails($uid)
    {
        $query = "SELECT u.title,npi,fname,mname,lname,street,city,state,zip,CONCAT_WS(' ','',phonew1) AS phonew1, lo.title as  physician_type, facility As organization, taxonomy, lous.title as taxonomy_desc, specialty, SUBSTRING(lo.codes, LENGTH('SNOMED-CT:')+1, LENGTH(lo.codes)) as physician_type_code FROM users as u
        LEFT JOIN list_options AS lo ON lo.list_id = 'physician_type' AND lo.option_id = u.physician_type
        LEFT JOIN list_options AS lous ON lous.list_id = 'us-core-provider-specialty' AND lous.option_id = u.taxonomy
        WHERE `id` = ?";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($uid));
        foreach ($res as $result) {
            if (!empty($result['phonew1'])) {
                // not sure why we are concat_ws the phone but we need to trim off any excess white space to fix
                // our phone formatting issues on the node side.
                $result['phonew1'] = trim($result['phonew1']);
            }
            return $result;
        }
    }

    /**
     * Checks to see if the snomed codes are installed and we can then query against them.
     */
    private function is_snomed_codes_installed(ApplicationTable $appTable)
    {
        $codes_installed = false;
        // this throws an exception... which is sad
        // TODO: is there a better way to know if the snomed codes are installed instead of using this method?
        // we set $error=false or else it will display on the screen, which seems counterintuitive... it also suppresses the exception
        $result = $appTable->zQuery("Describe `sct_descriptions`", $params = '', $log = true, $error = false);
        if ($result !== false) { // will return false if there is an error
            $codes_installed = true;
        }

        return $codes_installed;
    }

    /**
     * get details from care plan form
     * @param $pid Patient Internal Identifier.
     * @return string $planofcare  XML which contains the details collected from the patient.
     */
    public function getPlanOfCare($pid, $encounter)
    {
        $wherCon = '';
        $appTable = new ApplicationTable();
        $sqlBindArray = ['Plan_of_Care_Type', $pid, 'care_plan', 0];

        if (!empty($this->encounterFilterList)) {
            $wherCon = " AND f.encounter IN (" . implode(",", array_map("intval", $this->encounterFilterList)) . ")";
        } elseif ($this->searchFiltered) {
            // there are no encounters to filter on in the form and we are filtering the data...
            return "<planofcare></planofcare><goals></goals><health_concerns></health_concerns>";
        }

        UuidRegistry::createMissingUuidsForTables(['lists']);
        // TODO: implement referrals.  Our transactions table does not keep a code value which is required to report for ccda.
        //  We used to grab the referrals but since we have no codes for the referral we ended up just skipping all the data
        //  We removed the care plan transaction information here as it wasn't being used here or with serveccda.  When we
        //  support codes in the transaction table we can add that back in.
        $query = "SELECT 'care_plan' AS source,fcp.encounter,fcp.code,fcp.codetext,fcp.description,fcp.date,l.`notes` AS moodCode,fcp.care_plan_type AS care_plan_type,fcp.note_related_to as note_issues
            , u.id AS provenance_updated_by, f.date AS modifydate, f.form_id
            FROM forms AS f
            LEFT JOIN form_care_plan AS fcp ON fcp.id = f.form_id
            LEFT JOIN codes AS c ON c.code = fcp.code
            LEFT JOIN code_types AS ct ON c.`code_type` = ct.ct_id
            LEFT JOIN users as u on u.username = fcp.user
            LEFT JOIN `list_options` l ON l.`option_id` = fcp.`care_plan_type` AND l.`list_id`=?
            WHERE f.pid = ? AND f.formdir = ? AND f.deleted = ? $wherCon Order By fcp.encounter DESC";
        $res = $appTable->zQuery($query, $sqlBindArray);
        $status = 'Pending';
        $status_entry = 'active';
        $planofcare = '<planofcare>';
        $goals = '<goals>';
        $concerns = '<health_concerns>';
        foreach ($res as $row) {
            // we are handling the dates differently here than the other filtered data types because the transaction
            // table stores the refer_date as a textual string and we can't convert it in a cross-database fashion right
            // now to do our date comparisons like we do all of the other fields.
            if ($this->searchFiltered) {
                $rowDate = strtotime($row['date']);
                // if we can't format the date and we are filtering then we exclude it,
                if (
                    $rowDate === false
                    // we have a from date so we filter by it
                    || (isset($this->searchFromDate) && $rowDate < $this->searchFromDate)
                    // we have a to date so we filter by it
                    || (isset($this->searchToDate) && $rowDate > $this->searchToDate)
                ) {
                    continue;
                }
            }
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['modifydate']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, $encounter);
            $row['description'] = preg_replace("/\{\|([^\]]*)\|}/", '', $row['description']);
            $tmp = explode(":", $row['code']);
            $code_type = $tmp[0];
            $code = $tmp[1] ?? '';
            if ($row['care_plan_type'] === 'health_concern') {
                $issue_uuid = "<issues>\n";
                if (!empty($row['note_issues'])) {
                    $issues = json_decode($row['note_issues'], true);
                    foreach ($issues as $issue) {
                        $q = "Select uuid from lists Where id = ?";
                        $uuid = sqlQuery($q, array($issue))['uuid'];
                        if (empty($uuid)) {
                            continue;
                        }
                        $uuid_problem = UuidRegistry::uuidToString($uuid);
                        $issue_uuid .= "<issue_uuid>" . xmlEscape($uuid_problem) . "</issue_uuid>\n";
                    }
                }
                $concerns .= "<concern>" . $provenanceXml .
                    $issue_uuid . "</issues>" .
                    "<encounter>" . xmlEscape($row['encounter']) . "</encounter>
                <extension>" . xmlEscape(base64_encode($_SESSION['site_id'] . $row['encounter'])) . "</extension>
                <sha_extension>" . xmlEscape($this->formatUid($row['form_id'] . $row['description'])) . "</sha_extension>
                <text>" . xmlEscape($row['date'] . " " . $row['description']) . '</text>
                <code>' . xmlEscape($code) . '</code>
                <code_type>' . xmlEscape($code_type) . '</code_type>
                <code_text>' . xmlEscape($row['codetext']) . '</code_text>
                <date>' . xmlEscape($row['date']) . '</date>
                <date_formatted>' . xmlEscape(str_replace("-", '', $row['date'])) . '</date_formatted>
                </concern>';
            }
            if ($row['care_plan_type'] === 'goal') {
                $goals .= '<item>' . $provenanceXml . '
                <extension>' . xmlEscape(base64_encode($_SESSION['site_id'] . $row['encounter'])) . '</extension>
                <sha_extension>' . xmlEscape($this->formatUid($row['form_id'] . $row['description'])) . '</sha_extension>
                <care_plan_type>' . xmlEscape($row['care_plan_type']) . '</care_plan_type>
                <encounter>' . xmlEscape($row['encounter']) . '</encounter>
                <code>' . xmlEscape($code) . '</code>
                <code_text>' . xmlEscape($row['codetext']) . '</code_text>
                <description>' . xmlEscape($row['description']) . '</description>
                <date>' . xmlEscape($row['date']) . '</date>
                <date_formatted>' . xmlEscape(str_replace("-", '', $row['date'])) . '</date_formatted>
                <status>' . xmlEscape($status) . '</status>
                <status_entry>' . xmlEscape($status_entry) . '</status_entry>
                <code_type>' . xmlEscape($code_type) . '</code_type>
                <moodCode>' . xmlEscape($row['moodCode']) . '</moodCode>
                </item>';
            } elseif ($row['care_plan_type'] !== 'health_concern') {
                $planofcare .= '<item>' . $provenanceXml . '
                <extension>' . xmlEscape(base64_encode($_SESSION['site_id'] . $row['encounter'])) . '</extension>
                <sha_extension>' . xmlEscape($this->formatUid($row['form_id'] . $row['description'])) . '</sha_extension>
                <care_plan_type>' . xmlEscape($row['care_plan_type']) . '</care_plan_type>
                <encounter>' . xmlEscape($row['encounter']) . '</encounter>
                <code>' . xmlEscape($code) . '</code>
                <code_text>' . xmlEscape($row['codetext']) . '</code_text>
                <description>' . xmlEscape($row['description']) . '</description>
                <date>' . xmlEscape($row['date']) . '</date>
                <date_formatted>' . xmlEscape(str_replace("-", '', $row['date'])) . '</date_formatted>
                <status>' . xmlEscape($status) . '</status>
                <status_entry>' . xmlEscape($status_entry) . '</status_entry>
                <code_type>' . xmlEscape($code_type) . '</code_type>
                <moodCode>' . xmlEscape($row['moodCode']) . '</moodCode>
                </item>';
            }
        }

        $planofcare .= '</planofcare>';
        $goals .= '</goals>';
        $concerns .= '</health_concerns>';
        return $planofcare . $goals . $concerns;
    }

    /*
   * get details from functional and cognitive status form
   * @param    int     $pid           Patient Internal Identifier.
   * @param    int     $encounter     Current selected encounter.

   * return    string  $functional_cognitive  XML which contains the details collected from the patient.
   */
    /**
     * @param $pid
     * @return string
     */
    public function getFunctionalCognitiveStatus($pid)
    {
        $wherCon = '';
        $sqlBindArray = [];

        if (!empty($this->encounterFilterList)) {
            $wherCon .= " f.encounter IN (" . implode(",", array_map('intval', $this->encounterFilterList)) . ") AND ";
        } elseif ($this->searchFiltered) {
            // if we are filtering our results, if there is no connected procedures to an encounter that fits within our
            // date range then we want to return an empty procedures list
            return "<functional_status></functional_status><mental_status></mental_status>";
        }

        $functional_status = '<functional_status>';
        $cognitive_status = '<mental_status>';
        $query = "SELECT ffcs.* FROM forms AS f
                LEFT JOIN form_functional_cognitive_status AS ffcs ON ffcs.id = f.form_id
                WHERE $wherCon f.pid = ? AND f.formdir = ? AND f.deleted = ?";
        array_push($sqlBindArray, $pid, 'functional_cognitive_status', 0);
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, $sqlBindArray);

        foreach ($res as $row) {
            // $row['activity'] designates functional or cognitive status
            if ($row['activity'] == 1) {
                $cognitive_status .= '<item>
    <cognitive>1</cognitive>
    <code>' . xmlEscape(($row['code'] ?: '')) . '</code>
    <code_text>' . xmlEscape(($row['codetext'] ?: '')) . '</code_text>
    <description>' . xmlEscape($row['date'] . ' ' . $row['description'] . " | ") . '</description>
    <date>' . xmlEscape($row['date']) . '</date>
    <date_formatted>' . xmlEscape(str_replace("-", '', $row['date'])) . '</date_formatted>
    <status>' . xmlEscape('completed') . '</status>
    <age>' . xmlEscape($this->getAge($pid)) . '</age>
    </item>';
            } else {
                $functional_status .= '<item>
    <cognitive>0</cognitive>
    <code>' . xmlEscape(($row['code'] ?: '')) . '</code>
    <code_text>' . xmlEscape(($row['codetext'] ?: '')) . '</code_text>
    <description>' . xmlEscape($row['date'] . ' ' . $row['description'] . " | ") . '</description>
    <date>' . xmlEscape($row['date']) . '</date>
    <date_formatted>' . xmlEscape(str_replace("-", '', $row['date'])) . '</date_formatted>
    <status>' . xmlEscape('completed') . '</status>
    <age>' . xmlEscape($this->getAge($pid)) . '</age>
    </item>';
            }
        }
        $functional_status .= '</functional_status>';
        $cognitive_status .= '</mental_status>';
        return $functional_status . $cognitive_status;
    }

    /**
     * @param $pid
     * @param $encounter
     * @return string
     */
    public function getClinicalNotes($pid, $encounter)
    {
        $wherCon = '';
        $sqlBindArray = [];
        if ($this->searchFiltered) {
            if (empty($this->encounterFilterList)) {
                return "<clinical_notes></clinical_notes>";
            } else {
                $wherCon .= " f.encounter IN (" . implode(",", array_map('intval', $this->encounterFilterList)) . ") AND ";
            }
        } elseif ($encounter) {
            $wherCon = " f.encounter = ? AND ";
            $sqlBindArray[] = $encounter;
        }

        $clinical_notes = '';
        $query = "SELECT fnote.*, u.*, fac.*,u.id AS provenance_updated_by, f.date AS modifydate, fac.oid AS facility_oid FROM forms AS f
                LEFT JOIN `form_clinical_notes` AS fnote ON fnote.`form_id` = f.`form_id`
                LEFT JOIN users as u on u.username = fnote.user
                LEFT JOIN facility as fac on fac.id = u.facility_id
                WHERE $wherCon f.`pid` = ? AND f.`formdir` = ? AND f.`deleted` = ? Order By fnote.`encounter`, fnote.`date`, fnote.`clinical_notes_type` DESC";
        array_push($sqlBindArray, $pid, 'clinical_notes', 0);
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, $sqlBindArray);

        $clinical_notes .= '<clinical_notes>';
        foreach ($res as $row) {
            if (empty($row['clinical_notes_type'])) {
                continue;
            }
            $provenanceRecord = [
                'author_id' => $row['provenance_updated_by']
                ,'time' => $row['modifydate']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, $encounter);
            $tmp = explode(":", $row['code']);
            $code_type = $tmp[0];
            $code = $tmp[1];
            $clt = xmlEscape($row['clinical_notes_type']);
            $clinical_notes .= "<$clt>" . $provenanceXml .
                '<clinical_notes_type>' . $clt . '</clinical_notes_type>
            <encounter>' . xmlEscape($row['encounter']) . '</encounter>
            <author_title>' . xmlEscape($row['title']) . '</author_title>
            <author_first>' . xmlEscape($row['fname']) . '</author_first>
            <author_last>' . xmlEscape($row['lname']) . '</author_last>
            <author_npi>' . xmlEscape($row['npi']) . '</author_npi>
            <facility_name>' . xmlEscape($row['name']) . '</facility_name>
            <facility_npi>' . xmlEscape($row['facility_npi']) . '</facility_npi>
            <facility_oid>' . xmlEscape($row['facility_oid']) . '</facility_oid>
            <code>' . xmlEscape($code) . '</code>
            <code_text>' . xmlEscape($row['codetext']) . '</code_text>
            <description>' . xmlEscape($row['description']) . '</description>
            <date>' . xmlEscape($row['date']) . '</date>
            <date_formatted>' . xmlEscape(str_replace("-", '', $row['date'])) . '</date_formatted>
            <code_type>' . xmlEscape($code_type) . "</code_type>
            </$clt>";
        }

        $clinical_notes .= '</clinical_notes>';
        return $clinical_notes;
    }

    /**
     * @param $pid
     * @return mixed
     */
    public function getCareTeamProviderId($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT care_team_provider FROM patient_data WHERE `pid`  = ?";
        $result = $appTable->zQuery($query, array($pid));
        $row = $result->current();
        return $row['care_team_provider'];
    }

    /**
     * @param $pid
     * @return string
     */
    public function getClinicalInstructions($pid)
    {
        $wherCon = '';
        $sqlBindArray = [];
        if (!empty($this->encounterFilterList)) {
            $wherCon .= " f.encounter IN (" . implode(",", array_map('intval', $this->encounterFilterList)) . ") AND ";
        } elseif ($this->searchFiltered) {
            // if we are filtering our results, if there is no connected procedures to an encounter that fits within our
            // date range then we want to return an empty procedures list
            return "<clinical_instruction></clinical_instruction>";
        }

        $query = "SELECT fci.* FROM forms AS f
                LEFT JOIN form_clinical_instructions AS fci ON fci.id = f.form_id
                WHERE $wherCon f.pid = ? AND f.formdir = ? AND f.deleted = ?";
        array_push($sqlBindArray, $pid, 'clinical_instructions', 0);
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, $sqlBindArray);
        $clinical_instructions = '<clinical_instruction>';
        foreach ($res as $row) {
            $clinical_instructions .= '<item>' . xmlEscape($row['instruction']) . '</item>';
        }

        $clinical_instructions .= '</clinical_instruction>';
        return $clinical_instructions;
    }

    /**
     * @param $pid
     * @return array
     */
    private function getReferralRecords($pid)
    {
        $wherCon = '';
        $sqlBindArray = [$pid];
        $wherCon .= "ORDER BY date DESC";

        $appTable = new ApplicationTable();
        $query = "SELECT ref_body.field_value AS body, ref_to.field_value AS refer_to
                    , ref_from.field_value AS refer_from, ref_billing_facility_id.field_value AS billing_facility_id
                    , t.date AS creation_date, ref_date.field_value AS refer_date
                    , u.id AS provenance_updated_by
                    FROM transactions t
                        JOIN lbt_data ref_body ON ref_body.form_id=t.id AND ref_body.field_id = 'body'
                        JOIN lbt_data ref_to ON ref_to.form_id=t.id AND ref_to.field_id = 'refer_to'
                        JOIN lbt_data ref_date ON ref_date.form_id=t.id AND ref_date.field_id = 'refer_date'
                        JOIN lbt_data ref_from ON ref_from.form_id=t.id AND ref_from.field_id = 'refer_from'
                        JOIN lbt_data ref_billing_facility_id ON ref_billing_facility_id.form_id=t.id 
                        LEFT JOIN users u ON t.user = u.username
                            AND ref_billing_facility_id.field_id = 'billing_facility_id'
                    WHERE pid = ? $wherCon";

        $result = $appTable->zQuery($query, $sqlBindArray);
        $records = [];
        foreach ($result as $row) {
            // because of the way transactions store dates as string and we don't have a cross data base compliant way of
            // converting fields to dates we have to sort the dates in the application layer.
            if ($this->searchFiltered) {
                $rowDate = strtotime($row['refer_date']);
                // if we can't format the date and we are filtering then we exclude it,
                if (
                    $rowDate === false
                    // we have a from date so we filter by it
                    || (isset($this->searchFromDate) && $rowDate < $this->searchFromDate)
                    // we have a to date so we filter by it
                    || (isset($this->searchToDate) && $rowDate > $this->searchToDate)
                ) {
                    continue;
                }
            }
            $records[] = $row;
        }
        return $records;
    }

    /**
     * @param $pid
     * @return string
     */
    public function getReferrals($pid)
    {
        $referrals = '';
        $result = $this->getReferralRecords($pid);
        $referralsXML = '<referral_reason>';
        if (!empty($result[0])) {
            $referral = $result[0];
            $referralsXML .= '<text>' . xmlEscape($referral['body']) . '</text>
                           <date>' . xmlEscape($referral['refer_date']) . '</date>';
            $provenanceRecord = [
                'author_id' => $referral['provenance_updated_by']
                ,'time' => $referral['creation_date']
            ];
            $provenanceXml = $this->getAuthorXmlForRecord($provenanceRecord, $pid, null);
            $referralsXML .= $provenanceXml;
        }

        $referralsXML .= '</referral_reason>';
        return $referralsXML;
    }

    /**
     * @param $pid
     * @return string
     */
    public function getLatestEncounter($pid)
    {
        $encounter = '';
        $appTable = new ApplicationTable();
        $query = "SELECT encounter FROM form_encounter  WHERE pid = ? ORDER BY id DESC LIMIT 1";
        $result = $appTable->zQuery($query, array($pid));
        foreach ($result as $row) {
            $encounter = $row['encounter'];
        }

        return $encounter;
    }

    /**
     * @param $str
     * @return string
     */
    public function formatUid($str)
    {
        $sha = sha1($str);
        return substr(preg_replace('/^.{8}|.{4}/', '\0-', $sha, 4), 0, 36);
    }

    /**
     * @param $pid
     * @param $encounter
     * @return array
     */
    private function getEncounterListForDateRange($pid, $encounter)
    {
        $encounter = '';
        $appTable = new ApplicationTable();
        $boundParams = [$pid];
        $query = "SELECT encounter FROM form_encounter  WHERE pid = ? ";
        if (!empty($encounter)) {
            $query .= " AND encounter = ? ";
            $boundParams[] = $encounter;
        }
        $searchClause = $this->getDateQueryClauseForColumn('date');
        if (!empty($searchClause)) {
            $query .= "AND " . $searchClause->getFragment();
            $boundParams = array_merge($boundParams, $searchClause->getBoundValues());
        }

        $query .= " ORDER BY id DESC";
        $result = $appTable->zQuery($query, $boundParams);
        $encounters = [];
        foreach ($result as $row) {
            $encounters[] = intval($row['encounter']);
        }

        return $encounters;
    }
}

?>
