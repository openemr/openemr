<?php

/**
 * interface/modules/zend_modules/module/Carecoordination/src/Carecoordination/Model/EncounterccdadispatchTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Riju K P <rijukp@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

use Application\Model\ApplicationTable;
use Carecoordination\Model\CarecoordinationTable;
use CouchDB;
use Laminas\Db\Adapter\Driver\Pdo\Result;
use Laminas\Db\TableGateway\AbstractTableGateway;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Uuid\UuidRegistry;

require_once(dirname(__FILE__) . "/../../../../../../../../custom/code_types.inc.php");
require_once(dirname(__FILE__) . "/../../../../../../../forms/vitals/report.php");

class EncounterccdadispatchTable extends AbstractTableGateway
{
    public function __construct()
    {
    }

    /*Fetch Patient data from EMR

    * @param    $pid
    * @param    $encounter
    * @return   $patient_data   Patient Data in XML format
    */
    public function getPatientdata($pid, $encounter)
    {
        $query = "select patient_data.*, l1.notes AS race_code, l1.title as race_title, l2.notes AS ethnicity_code, l2.title as ethnicity_title, l3.title as religion, l3.notes as religion_code, l4.notes as language_code, l4.title as language_title
                        from patient_data
                        left join list_options as l1 on l1.list_id=? AND l1.option_id=race
                        left join list_options as l2 on l2.list_id=? AND l2.option_id=ethnicity
			left join list_options AS l3 ON l3.list_id=? AND l3.option_id=religion
			left join list_options AS l4 ON l4.list_id=? AND l4.option_id=language
                        where pid=?";
        $appTable = new ApplicationTable();
        $row = $appTable->zQuery($query, array('race', 'ethnicity', 'religious_affiliation', 'language', $pid));

        foreach ($row as $result) {
            $patient_data = "<patient>
                <id>" . htmlspecialchars($result['pid'], ENT_QUOTES) . "</id>
                <encounter>" . htmlspecialchars($encounter, ENT_QUOTES) . "</encounter>
		<prefix>" . htmlspecialchars($result['title'], ENT_QUOTES) . "</prefix>
                <fname>" . htmlspecialchars($result['fname'], ENT_QUOTES) . "</fname>
                <mname>" . htmlspecialchars($result['mname'], ENT_QUOTES) . "</mname>
                <lname>" . htmlspecialchars($result['lname'], ENT_QUOTES) . "</lname>
                <street>" . htmlspecialchars($result['street'], ENT_QUOTES) . "</street>
                <city>" . htmlspecialchars($result['city'], ENT_QUOTES) . "</city>
                <state>" . htmlspecialchars($result['state'], ENT_QUOTES) . "</state>
                <postalCode>" . htmlspecialchars($result['postal_code'], ENT_QUOTES) . "</postalCode>
                <country>" . htmlspecialchars($result['country_code'], ENT_QUOTES) . "</country>
                <ssn>" . htmlspecialchars($result['ss'] ? $result['ss'] : 0, ENT_QUOTES) . "</ssn>
                <dob>" . htmlspecialchars(str_replace('-', '', $result['DOB']), ENT_QUOTES) . "</dob>
                <gender>" . htmlspecialchars($result['sex'], ENT_QUOTES) . "</gender>
                <gender_code>" . htmlspecialchars(strtoupper(substr($result['sex'], 0, 1)), ENT_QUOTES) . "</gender_code>
                <status>" . htmlspecialchars($result['status'] ? $result['status'] : 'NULL', ENT_QUOTES) . "</status>
                <status_code>" . htmlspecialchars($result['status'] ? strtoupper(substr($result['status'], 0, 1)) : 0, ENT_QUOTES) . "</status_code>
                <phone_home>" . htmlspecialchars(($result['phone_home'] ? $result['phone_home'] : 0), ENT_QUOTES) . "</phone_home>
                <religion>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($result['religion'] ? $result['religion'] : 'NULL'), ENT_QUOTES) . "</religion>
                <religion_code>" . htmlspecialchars($result['religion_code'] ? $result['religion_code'] : 0, ENT_QUOTES) . "</religion_code>
                <race>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($result['race_title']), ENT_QUOTES) . "</race>
				<race_code>" . htmlspecialchars($result['race_code'], ENT_QUOTES) . "</race_code>
                <ethnicity>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($result['ethnicity_title']), ENT_QUOTES) . "</ethnicity>
				<ethnicity_code>" . htmlspecialchars($result['ethnicity_code'], ENT_QUOTES) . "</ethnicity_code>
		<language>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($result['language_title']), ENT_QUOTES) . "</language>
		<language_code>" . htmlspecialchars($result['language_code'], ENT_QUOTES) . "</language_code>
            </patient>
		<guardian>
			<fname>" . htmlspecialchars($result[''], ENT_QUOTES) . "</fname>
			<lname>" . htmlspecialchars($result[''], ENT_QUOTES) . "</lname>
			<code>" . htmlspecialchars($result[''], ENT_QUOTES) . "</code>
			<relation>" . htmlspecialchars($result['guardianrelationship'], ENT_QUOTES) . "</relation>
			<display_name>" . htmlspecialchars($result['guardiansname'], ENT_QUOTES) . "</display_name>
			<street>" . htmlspecialchars($result['guardianaddress'], ENT_QUOTES) . "</street>
			<city>" . htmlspecialchars($result['guardiancity'], ENT_QUOTES) . "</city>
			<state>" . htmlspecialchars($result['guardianstate'], ENT_QUOTES) . "</state>
			<postalCode>" . htmlspecialchars($result['guardianpostalcode'], ENT_QUOTES) . "</postalCode>
			<country>" . htmlspecialchars($result['guardiancountry'], ENT_QUOTES) . "</country>
			<telecom>" . htmlspecialchars($result['guardianphone'], ENT_QUOTES) . "</telecom>
		</guardian>";
        }

        return $patient_data;
    }

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
                    <facility_id>" . $result['id'] . "</facility_id>
                    <facility_npi>" . htmlspecialchars($result['facility_npi'], ENT_QUOTES) . "</facility_npi>
                    <facility_oid>" . htmlspecialchars($result['facility_code'], ENT_QUOTES) . "</facility_oid>
                    <facility_name>" . htmlspecialchars($result['name'], ENT_QUOTES) . "</facility_name>
                    <facility_phone>" . htmlspecialchars(($result['phone'] ? $result['phone'] : 0), ENT_QUOTES) . "</facility_phone>
                    <facility_fax>" . htmlspecialchars($result['fax'], ENT_QUOTES) . "</facility_fax>
                    <facility_street>" . htmlspecialchars($result['street'], ENT_QUOTES) . "</facility_street>
                    <facility_city>" . htmlspecialchars($result['city'], ENT_QUOTES) . "</facility_city>
                    <facility_state>" . htmlspecialchars($result['state'], ENT_QUOTES) . "</facility_state>
                    <facility_postal_code>" . htmlspecialchars($result['postal_code'], ENT_QUOTES) . "</facility_postal_code>
                    <facility_country_code>" . htmlspecialchars($result['country_code'], ENT_QUOTES) . "</facility_country_code>
                </encounter_provider>
            ";
        }

        return $provider_details;
    }

    public function getAuthor($pid, $encounter)
    {
        $author = '';
        $details = $this->getDetails('hie_author_id');

        $author = "
        <author>
            <streetAddressLine>" . htmlspecialchars($details['street'], ENT_QUOTES) . "</streetAddressLine>
            <city>" . htmlspecialchars($details['city'], ENT_QUOTES) . "</city>
            <state>" . htmlspecialchars($details['state'], ENT_QUOTES) . "</state>
            <postalCode>" . htmlspecialchars($details['zip'], ENT_QUOTES) . "</postalCode>
            <country>" . htmlspecialchars($details[''], ENT_QUOTES) . "</country>
            <telecom>" . htmlspecialchars(($details['phonew1'] ? $details['phonew1'] : 0), ENT_QUOTES) . "</telecom>
            <fname>" . htmlspecialchars($details['fname'], ENT_QUOTES) . "</fname>
            <lname>" . htmlspecialchars($details['lname'], ENT_QUOTES) . "</lname>
            <npi>" . htmlspecialchars($details['npi'], ENT_QUOTES) . "</npi>
        </author>";

        return $author;
    }

    public function getDataEnterer($pid, $encounter)
    {
        $data_enterer = '';
        $details = $this->getDetails('hie_data_enterer_id');

        $data_enterer = "
        <data_enterer>
            <streetAddressLine>" . htmlspecialchars($details['street'], ENT_QUOTES) . "</streetAddressLine>
            <city>" . htmlspecialchars($details['city'], ENT_QUOTES) . "</city>
            <state>" . htmlspecialchars($details['state'], ENT_QUOTES) . "</state>
            <postalCode>" . htmlspecialchars($details['zip'], ENT_QUOTES) . "</postalCode>
            <country>" . htmlspecialchars($details[''], ENT_QUOTES) . "</country>
            <telecom>" . htmlspecialchars(($details['phonew1'] ? $details['phonew1'] : 0), ENT_QUOTES) . "</telecom>
            <fname>" . htmlspecialchars($details['fname'], ENT_QUOTES) . "</fname>
            <lname>" . htmlspecialchars($details['lname'], ENT_QUOTES) . "</lname>
        </data_enterer>";

        return $data_enterer;
    }

    public function getInformant($pid, $encounter)
    {
        $informant = '';
        $details = $this->getDetails('hie_informant_id');
        $personal_informant = $this->getDetails('hie_personal_informant_id');

        $informant = "<informer>
            <streetAddressLine>" . htmlspecialchars($details['street'], ENT_QUOTES) . "</streetAddressLine>
            <city>" . htmlspecialchars($details['city'], ENT_QUOTES) . "</city>
            <state>" . htmlspecialchars($details['state'], ENT_QUOTES) . "</state>
            <postalCode>" . htmlspecialchars($details['zip'], ENT_QUOTES) . "</postalCode>
            <country>" . htmlspecialchars($details[''], ENT_QUOTES) . "</country>
            <telecom>" . htmlspecialchars(($details['phonew1'] ? $details['phonew1'] : 0), ENT_QUOTES) . "</telecom>
            <fname>" . htmlspecialchars($details['fname'], ENT_QUOTES) . "</fname>
            <lname>" . htmlspecialchars($details['lname'], ENT_QUOTES) . "</lname>
            <personal_informant>" . htmlspecialchars($this->getSettings('Carecoordination', 'hie_personal_informant_id'), ENT_QUOTES) . "</personal_informant>
        </informer>";

        return $informant;
    }

    public function getCustodian($pid, $encounter)
    {
        $custodian = '';
        $details = $this->getDetails('hie_custodian_id');

        $custodian = "<custodian>
            <streetAddressLine>" . htmlspecialchars($details['street'], ENT_QUOTES) . "</streetAddressLine>
            <city>" . htmlspecialchars($details['city'], ENT_QUOTES) . "</city>
            <state>" . htmlspecialchars($details['state'], ENT_QUOTES) . "</state>
            <postalCode>" . htmlspecialchars($details['zip'], ENT_QUOTES) . "</postalCode>
            <country>" . htmlspecialchars($details[''], ENT_QUOTES) . "</country>
            <telecom>" . htmlspecialchars(($details['phonew1'] ? $details['phonew1'] : 0), ENT_QUOTES) . "</telecom>
            <name>" . htmlspecialchars($details['organization'], ENT_QUOTES) . "</name>
            <organization>" . htmlspecialchars($details['organization'], ENT_QUOTES) . "</organization>
        </custodian>";

        return $custodian;
    }

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
            $query = "select fname, lname, organization, street, city, state, zip, phonew1 from users where email = ?";
            $field_name[] = $params;
        } elseif ($recipients == 'patient') {
            $query = "select fname, lname from patient_data WHERE pid = ?";
            $field_name[] = $params;
        } else {
            if (!$params) {
                $params = $_SESSION['authUserID'];
            }

            $query = "select fname, lname, organization, street, city, state, zip, phonew1 from users where id = ?";
            $field_name[] = $params;
        }

        if ($recipients != 'hie') {
            $res = $appTable->zQuery($query, $field_name);
            $result = $res->current();
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
        <fname>" . htmlspecialchars($details['fname'], ENT_QUOTES) . "</fname>
        <lname>" . htmlspecialchars($details['lname'], ENT_QUOTES) . "</lname>
        <organization>" . htmlspecialchars($details['organization'], ENT_QUOTES) . "</organization>
	    <street>" . htmlspecialchars($details['street'], ENT_QUOTES) . "</street>
	    <city>" . htmlspecialchars($details['city'], ENT_QUOTES) . "</city>
	    <state>" . htmlspecialchars($details['state'], ENT_QUOTES) . "</state>
	    <zip>" . htmlspecialchars($details['zip'], ENT_QUOTES) . "</zip>
	    <phonew1>" . htmlspecialchars($details['phonew1'], ENT_QUOTES) . "</phonew1>
        </information_recipient>";

        return $information_recipient;
    }

    public function getLegalAuthenticator($pid, $encounter)
    {
        $legal_authenticator = '';
        $details = $this->getDetails('hie_legal_authenticator_id');

        $legal_authenticator = "<legal_authenticator>
            <streetAddressLine>" . htmlspecialchars($details['street'], ENT_QUOTES) . "</streetAddressLine>
            <city>" . htmlspecialchars($details['city'], ENT_QUOTES) . "</city>
            <state>" . htmlspecialchars($details['state'], ENT_QUOTES) . "</state>
            <postalCode>" . htmlspecialchars($details['zip'], ENT_QUOTES) . "</postalCode>
            <country>" . htmlspecialchars($details[''], ENT_QUOTES) . "</country>
            <telecom>" . htmlspecialchars(($details['phonew1'] ? $details['phonew1'] : 0), ENT_QUOTES) . "</telecom>
            <fname>" . htmlspecialchars($details['fname'], ENT_QUOTES) . "</fname>
            <lname>" . htmlspecialchars($details['lname'], ENT_QUOTES) . "</lname>
        </legal_authenticator>";

        return $legal_authenticator;
    }

    public function getAuthenticator($pid, $encounter)
    {
        $authenticator = '';
        $details = $this->getDetails('hie_authenticator_id');

        $authenticator = "<authenticator>
            <streetAddressLine>" . htmlspecialchars($details['street'], ENT_QUOTES) . "</streetAddressLine>
            <city>" . htmlspecialchars($details['city'], ENT_QUOTES) . "</city>
            <state>" . htmlspecialchars($details['state'], ENT_QUOTES) . "</state>
            <postalCode>" . htmlspecialchars($details['zip'], ENT_QUOTES) . "</postalCode>
            <country>" . htmlspecialchars($details[''], ENT_QUOTES) . "</country>
            <telecom>" . htmlspecialchars(($details['phonew1'] ? $details['phonew1'] : 0), ENT_QUOTES) . "</telecom>
            <fname>" . htmlspecialchars($details['fname'], ENT_QUOTES) . "</fname>
            <lname>" . htmlspecialchars($details['lname'], ENT_QUOTES) . "</lname>
        </authenticator>";

        return $authenticator;
    }

    public function getPrimaryCareProvider($pid, $encounter)
    {
        $primary_care_provider = '';

        $getprovider = $this->getProviderId($pid);
        if ($getprovider != 0 && $getprovider != '') {
            $details = $this->getUserDetails($getprovider);
        }

        $get_care_team_provider = $this->getCareTeamProviderId($pid);
        if ($get_care_team_provider != 0 && $get_care_team_provider != '') {
            $details2 = $this->getUserDetails($get_care_team_provider);
        }

        if (($getprovider == 0 || $getprovider == '') && ($get_care_team_provider == 0 || $get_care_team_provider == '')) {
            $details = $this->getDetails('hie_primary_care_provider_id');
        }

        $primary_care_provider = "
        <primary_care_provider>
          <provider>
            <prefix>" . htmlspecialchars($details['title'], ENT_QUOTES) . "</prefix>
            <fname>" . htmlspecialchars($details['fname'], ENT_QUOTES) . "</fname>
            <lname>" . htmlspecialchars($details['lname'], ENT_QUOTES) . "</lname>
            <speciality>" . htmlspecialchars($details['specialty'], ENT_QUOTES) . "</speciality>
            <organization>" . htmlspecialchars($details['organization'], ENT_QUOTES) . "</organization>
            <telecom>" . htmlspecialchars(($details['phonew1'] ? $details['phonew1'] : 0), ENT_QUOTES) . "</telecom>
            <addr>" . htmlspecialchars($details[''], ENT_QUOTES) . "</addr>
            <npi>" . htmlspecialchars($details['npi'], ENT_QUOTES) . "</npi>
            <physician_type>" . htmlspecialchars($details['physician_type'], ENT_QUOTES) . "</physician_type>
            <physician_type_code>" . htmlspecialchars($details['physician_type_code'], ENT_QUOTES) . "</physician_type_code>
          </provider>
          <provider>
            <prefix>" . htmlspecialchars($details2['title'], ENT_QUOTES) . "</prefix>
            <fname>" . htmlspecialchars($details2['fname'], ENT_QUOTES) . "</fname>
            <lname>" . htmlspecialchars($details2['lname'], ENT_QUOTES) . "</lname>
            <speciality>" . htmlspecialchars($details2['specialty'], ENT_QUOTES) . "</speciality>
            <organization>" . htmlspecialchars($details2['organization'], ENT_QUOTES) . "</organization>
            <telecom>" . htmlspecialchars(($details2['phonew1'] ? $details['phonew1'] : 0), ENT_QUOTES) . "</telecom>
            <addr>" . htmlspecialchars($details2[''], ENT_QUOTES) . "</addr>
            <npi>" . htmlspecialchars($details['npi'], ENT_QUOTES) . "</npi>
            <physician_type>" . htmlspecialchars($details2['physician_type'], ENT_QUOTES) . "</physician_type>
            <physician_type_code>" . htmlspecialchars($details2['physician_type_code'], ENT_QUOTES) . "</physician_type_code>
          </provider>
        </primary_care_provider>
        ";
        return $primary_care_provider;
    }

    /*
    #******************************************************#
    #                  CONTINUITY OF CARE                  #
    #******************************************************#
    */
    public function getAllergies($pid, $encounter)
    {
        $allergies = '';
        $query = "SELECT l.id, l.title, l.begdate, l.enddate, lo.title AS observation,
            SUBSTRING(lo.codes, LOCATE(':',lo.codes)+1, LENGTH(lo.codes)) AS observation_code,
						SUBSTRING(l.`diagnosis`,1,LOCATE(':',l.diagnosis)-1) AS code_type_real,
						l.reaction, l.diagnosis, l.diagnosis AS code
						FROM lists AS l
						LEFT JOIN list_options AS lo ON lo.list_id = ? AND lo.option_id = l.severity_al
						WHERE l.type = ? AND l.pid = ?";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array('severity_ccda', 'allergy', $pid));

        $allergies = "<allergies>";
        foreach ($res as $row) {
            $split_codes = explode(';', $row['code']);
            foreach ($split_codes as $key => $single_code) {
                $code = $code_text = $code_rx = $code_text_rx = $code_snomed = $code_text_snomed = $reaction_text = $reaction_code = '';
                $get_code_details = explode(':', $single_code);

                if ($get_code_details[0] == 'RXNORM') {
                    $code_rx = $get_code_details[1];
                    $code_text_rx = lookup_code_descriptions($single_code);
                } elseif ($get_code_details[0] == 'SNOMED') {
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
                }

                $allergies .= "<allergy>
							<id>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . $single_code), ENT_QUOTES) . "</id>
							<sha_id>" . htmlspecialchars("36e3e930-7b14-11db-9fe1-0800200c9a66", ENT_QUOTES) . "</sha_id>
							<title>" . htmlspecialchars($row['title'], ENT_QUOTES) . ($single_code ? " [" . htmlspecialchars($single_code, ENT_QUOTES) . "]" : '') . "</title>
							<diagnosis_code>" . htmlspecialchars(($code ? $code : 0), ENT_QUOTES) . "</diagnosis_code>
							<diagnosis>" . htmlspecialchars(($code_text ? \Application\Listener\Listener::z_xlt($code_text) : 'NULL'), ENT_QUOTES) . "</diagnosis>
							<rxnorm_code>" . htmlspecialchars(($code_rx ? $code_rx : 0), ENT_QUOTES) . "</rxnorm_code>
							<rxnorm_code_text>" . htmlspecialchars(($code_text_rx ? \Application\Listener\Listener::z_xlt($code_text_rx) : 'NULL'), ENT_QUOTES) . "</rxnorm_code_text>
							<snomed_code>" . htmlspecialchars(($code_snomed ? $code_snomed : 0), ENT_QUOTES) . "</snomed_code>
							<snomed_code_text>" . htmlspecialchars(($code_text_snomed ? \Application\Listener\Listener::z_xlt($code_text_snomed) : 'NULL'), ENT_QUOTES) . "</snomed_code_text>
							<status_table>" . ($status_table ? $status_table : 'NULL') . "</status_table>
							<status>" . ($active ? $active : 'NULL') . "</status>
							<allergy_status>" . ($allergy_status ? $allergy_status : 'NULL') . "</allergy_status>
							<status_code>" . ($status_code ? $status_code : 0) . "</status_code>
							<outcome>" . htmlspecialchars(($row['observation'] ? \Application\Listener\Listener::z_xlt($row['observation']) : 'NULL'), ENT_QUOTES) . "</outcome>
							<outcome_code>" . htmlspecialchars(($row['observation_code'] ? $row['observation_code'] : 0), ENT_QUOTES) . "</outcome_code>
							<startdate>" . htmlspecialchars($row['begdate'] ? preg_replace('/-/', '', $row['begdate']) : "00000000", ENT_QUOTES) . "</startdate>
							<enddate>" . htmlspecialchars($row['enddate'] ? preg_replace('/-/', '', $row['enddate']) : "00000000", ENT_QUOTES) . "</enddate>
							<reaction_text>" . htmlspecialchars($reaction_text ? \Application\Listener\Listener::z_xlt($reaction_text) : 'NULL', ENT_QUOTES) . "</reaction_text>
							<reaction_code>" . htmlspecialchars($reaction_code ? $reaction_code : 0, ENT_QUOTES) . "</reaction_code>
							<RxNormCode>" . htmlspecialchars($code_rx, ENT_QUOTES) . "</RxNormCode>
							<RxNormCode_text>" . htmlspecialchars(!empty($code_text_rx) ? $code_text_rx : $row['title'], ENT_QUOTES) . "</RxNormCode_text>
						</allergy>";
            }
        }

        $allergies .= "</allergies>";
        return $allergies;
    }

    public function getMedications($pid, $encounter)
    {
        $medications = '';
        $query = "select l.id, l.date_added, l.drug, l.dosage, l.quantity, l.size, l.substitute, l.drug_info_erx, l.active, SUBSTRING(l3.codes, LOCATE(':',l3.codes)+1, LENGTH(l3.codes)) AS route_code,
                       l.rxnorm_drugcode, l1.title as unit, l1.codes as unit_code,l2.title as form,SUBSTRING(l2.codes, LOCATE(':',l2.codes)+1, LENGTH(l2.codes)) AS form_code, l3.title as route, l4.title as `interval`,
                       u.title, u.fname, u.lname, u.mname, u.npi, u.street, u.streetb, u.city, u.state, u.zip, u.phonew1, l.note
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

            $unit = $str = $active = '';

            if ($row['size'] > 0) {
                $unit = $row['size'] . " " . \Application\Listener\Listener::z_xlt($row['unit']) . " ";
            }

            $str = $unit . " " . \Application\Listener\Listener::z_xlt($row['route']) . " " . $row['dosage'] . " " . \Application\Listener\Listener::z_xlt($row['form'] . " " . $row['interval']);

            if ($row['active'] > 0) {
                $active = 'active';
            } else {
                $active = 'completed';
            }

            if ($row['date_added']) {
                $start_date = str_replace('-', '', $row['date_added']);
                $start_date_formatted = \Application\Model\ApplicationTable::fixDate($row['date_added'], $GLOBALS['date_display_format'], 'yyyy-mm-dd');
                ;
            }

            $medications .= "<medication>
    <id>" . htmlspecialchars($row['id'], ENT_QUOTES) . "</id>
    <extension>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id']), ENT_QUOTES) . "</extension>
    <sha_extension>" . htmlspecialchars("cdbd33f0-6cde-11db-9fe1-0800200c9a66", ENT_QUOTES) . "</sha_extension>
    <performer_name>" . htmlspecialchars($row['fname'] . " " . $row['mname'] . " " . $row['lname'], ENT_QUOTES) . "</performer_name>
    <fname>" . htmlspecialchars($row['fname'], ENT_QUOTES) . "</fname>
    <mname>" . htmlspecialchars($row['mname'], ENT_QUOTES) . "</mname>
    <lname>" . htmlspecialchars($row['lname'], ENT_QUOTES) . "</lname>
    <title>" . htmlspecialchars($row['title'], ENT_QUOTES) . "</title>
    <npi>" . htmlspecialchars($row['npi'], ENT_QUOTES) . "</npi>
    <address>" . htmlspecialchars($row['street'], ENT_QUOTES) . "</address>
    <city>" . htmlspecialchars($row['city'], ENT_QUOTES) . "</city>
    <state>" . htmlspecialchars($row['state'], ENT_QUOTES) . "</state>
    <zip>" . htmlspecialchars($row['zip'], ENT_QUOTES) . "</zip>
    <work_phone>" . htmlspecialchars($row['phonew1'], ENT_QUOTES) . "</work_phone>
    <drug>" . htmlspecialchars($row['drug'], ENT_QUOTES) . "</drug>
    <direction>" . htmlspecialchars($str, ENT_QUOTES) . "</direction>
    <dosage>" . htmlspecialchars($row['dosage'], ENT_QUOTES) . "</dosage>
    <size>" . htmlspecialchars(($row['size'] ? $row['size'] : 0), ENT_QUOTES) . "</size>
    <unit>" . htmlspecialchars(($row['unit'] ? preg_replace('/\s*/', '', \Application\Listener\Listener::z_xlt($row['unit'])) : 'Unit'), ENT_QUOTES) . "</unit>
    <unit_code>" . htmlspecialchars(($row['unit_code'] ? $row['unit_code'] : 0), ENT_QUOTES) . "</unit_code>
    <form>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($row['form']), ENT_QUOTES) . "</form>
    <form_code>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($row['form_code']), ENT_QUOTES) . "</form_code>
    <route_code>" . htmlspecialchars($row['route_code'], ENT_QUOTES) . "</route_code>
    <route>" . htmlspecialchars($row['route'], ENT_QUOTES) . "</route>
    <interval>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($row['interval']), ENT_QUOTES) . "</interval>
    <start_date>" . htmlspecialchars($start_date, ENT_QUOTES) . "</start_date>
    <start_date_formatted>" . htmlspecialchars($row['date_added'], ENT_QUOTES) . "</start_date_formatted>
    <end_date>" . htmlspecialchars('00000000', ENT_QUOTES) . "</end_date>
    <status>" . $active . "</status>
    <indications>" . htmlspecialchars(($row['pres_erx_diagnosis_name'] ? $row['pres_erx_diagnosis_name'] : 'NULL'), ENT_QUOTES) . "</indications>
    <indications_code>" . htmlspecialchars(($row['pres_erx_diagnosis'] ? $row['pres_erx_diagnosis'] : 0), ENT_QUOTES) . "</indications_code>
    <instructions>" . htmlspecialchars($row['note'], ENT_QUOTES) . "</instructions>
    <rxnorm>" . htmlspecialchars($row['rxnorm_drugcode'], ENT_QUOTES) . "</rxnorm>
    <provider_id></provider_id>
    <provider_name></provider_name>
    </medication>";
        }

        $medications .= "</medications>";
        return $medications;
    }

    public function getProblemList($pid, $encounter)
    {
        $problem_lists = '';
        $query = "select l.*, lo.title as observation, lo.codes as observation_code, l.diagnosis AS code
											from lists AS l
											left join list_options as lo on lo.option_id = l.outcome AND lo.list_id = ?
											where l.type = ? and l.pid = ? AND l.outcome <> ? AND l.id NOT IN(SELECT list_id FROM issue_encounter WHERE pid = ?)";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array('outcome', 'medical_problem', $pid, 1, $pid));

        $problem_lists .= '<problem_lists>';
        foreach ($res as $row) {
            $split_codes = explode(';', $row['code']);
            foreach ($split_codes as $key => $single_code) {
                $get_code_details = explode(':', $single_code);

                $code = $get_code_details[1];
                $code_text = lookup_code_descriptions($single_code);

                $age = $this->getAge($pid, $row['begdate']);
                $start_date = str_replace('-', '', $row['begdate']);
                $end_date = str_replace('-', '', $row['enddate']);

                $status = $status_table = '';
                $start_date = $start_date ? $start_date : '0';
                $end_date = $end_date ? $end_date : '0';

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
                $observation_code = $observation_code[1];

                $problem_lists .= "<problem>
						<extension>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id']), ENT_QUOTES) . "</extension>
						<sha_extension>" . htmlspecialchars("ec8a6ff8-ed4b-4f7e-82c3-e98e58b45de7", ENT_QUOTES) . "</sha_extension>
						<title>" . htmlspecialchars($row['title'], ENT_QUOTES) . ($single_code ? " [" . htmlspecialchars($single_code, ENT_QUOTES) . "]" : '') . "</title>
						<code>" . ($code ? $code : 0) . "</code>
						<code_text>" . htmlspecialchars(($code_text ? $code_text : 'NULL'), ENT_QUOTES) . "</code_text>
						<age>" . $age . "</age>
						<start_date_table>" . $row['begdate'] . "</start_date_table>
						<start_date>" . $start_date . "</start_date>
						<end_date>" . $end_date . "</end_date>
						<status>" . $status . "</status>
						<status_table>" . $status_table . "</status_table>
						<status_code>" . $status_code . "</status_code>
						<observation>" . htmlspecialchars(($observation ? \Application\Listener\Listener::z_xlt($observation) : 'NULL'), ENT_QUOTES) . "</observation>
						<observation_code>" . htmlspecialchars(($observation_code ? $observation_code : 0), ENT_QUOTES) . "</observation_code>
						<diagnosis>" . htmlspecialchars($code ? $code : 0) . "</diagnosis>
					</problem>";
            }
        }

        $problem_lists .= '</problem_lists>';
        return $problem_lists;
    }

    public function getImmunization($pid, $encounter)
    {
        $immunizations = '';
        $query = "SELECT im.*, cd.code_text, DATE(administered_date) AS administered_date,
		    DATE_FORMAT(administered_date,'%Y%m%d') AS administered_formatted, lo.title as route_of_administration,
		    u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.streetb, u.city, u.state, u.zip, u.phonew1,
		    f.name, f.phone, SUBSTRING(lo.codes, LOCATE(':',lo.codes)+1, LENGTH(lo.codes)) AS route_code
		    FROM immunizations AS im
		    LEFT JOIN codes AS cd ON cd.code = im.cvx_code
		    JOIN code_types AS ctype ON ctype.ct_key = 'CVX' AND ctype.ct_id=cd.code_type
		    LEFT JOIN list_options AS lo ON lo.list_id = 'drug_route' AND lo.option_id = im.route
		    LEFT JOIN users AS u ON u.id = im.administered_by_id
		    LEFT JOIN facility AS f ON f.id = u.facility_id
		    WHERE im.patient_id=?";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));

        $immunizations .= '<immunizations>';
        foreach ($res as $row) {
            $immunizations .= "
	    <immunization>
		<extension>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id']), ENT_QUOTES) . "</extension>
		<sha_extension>" . htmlspecialchars("e6f1ba43-c0ed-4b9b-9f12-f435d8ad8f92", ENT_QUOTES) . "</sha_extension>
		<id>" . htmlspecialchars($row['id'], ENT_QUOTES) . "</id>
		<cvx_code>" . htmlspecialchars($row['cvx_code'], ENT_QUOTES) . "</cvx_code>
		<code_text>" . htmlspecialchars($row['code_text'], ENT_QUOTES) . "</code_text>
		<reaction>" . htmlspecialchars($row['reaction'], ENT_QUOTES) . "</reaction>
		<npi>" . htmlspecialchars($row['npi'], ENT_QUOTES) . "</npi>
		<administered_by>" . htmlspecialchars($row['administered_by'], ENT_QUOTES) . "</administered_by>
		<fname>" . htmlspecialchars($row['fname'], ENT_QUOTES) . "</fname>
		<mname>" . htmlspecialchars($row['mname'], ENT_QUOTES) . "</mname>
		<lname>" . htmlspecialchars($row['lname'], ENT_QUOTES) . "</lname>
		<title>" . htmlspecialchars($row['title'], ENT_QUOTES) . "</title>
		<address>" . htmlspecialchars($row['street'], ENT_QUOTES) . "</address>
		<city>" . htmlspecialchars($row['city'], ENT_QUOTES) . "</city>
		<state>" . htmlspecialchars($row['state'], ENT_QUOTES) . "</state>
		<zip>" . htmlspecialchars($row['zip'], ENT_QUOTES) . "</zip>
		<work_phone>" . htmlspecialchars($row['phonew1'], ENT_QUOTES) . "</work_phone>
		<administered_on>" . htmlspecialchars($row['administered_date'], ENT_QUOTES) . "</administered_on>
		<administered_formatted>" . htmlspecialchars($row['administered_formatted'], ENT_QUOTES) . "</administered_formatted>
		<note>" . htmlspecialchars($row['note'], ENT_QUOTES) . "</note>
		<route_of_administration>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($row['route_of_administration']), ENT_QUOTES) . "</route_of_administration>
		<route_code>" . htmlspecialchars($row['route_code'], ENT_QUOTES) . "</route_code>
		<status>completed</status>
		<facility_name>" . htmlspecialchars($row['name'], ENT_QUOTES) . "</facility_name>
		<facility_phone>" . htmlspecialchars($row['phone'], ENT_QUOTES) . "</facility_phone>
	    </immunization>";
        }

        $immunizations .= '</immunizations>';

        return $immunizations;
    }

    public function getProcedures($pid, $encounter)
    {
        $wherCon = '';
        if ($encounter) {
            $wherCon = "AND b.encounter = $encounter";
        }

        $procedure = '';
        $query = "select b.id, b.date as proc_date, b.code_text, b.code, fe.date,
	u.fname, u.lname, u.mname, u.npi, u.street, u.city, u.state, u.zip,
	f.id as fid, f.name, f.phone, f.street as fstreet, f.city as fcity, f.state as fstate, f.postal_code as fzip, f.country_code, f.phone as fphone
	from billing as b
        LEFT join code_types as ct on ct.ct_key
        LEFT join codes as c on c.code = b.code AND c.code_type = ct.ct_id
        LEFT join form_encounter as fe on fe.pid = b.pid AND fe.encounter = b.encounter
	LEFT JOIN users AS u ON u.id = b.provider_id
	LEFT JOIN facility AS f ON f.id = fe.facility_id
        where b.pid = ? and b.activity = ? $wherCon";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid, 1));

        $procedure = '<procedures>';
        foreach ($res as $row) {
            $procedure .= "<procedure>
		    <extension>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id']), ENT_QUOTES) . "</extension>
		    <sha_extension>" . htmlspecialchars("d68b7e32-7810-4f5b-9cc2-acd54b0fd85d", ENT_QUOTES) . "</sha_extension>
                    <description>" . htmlspecialchars($row['code_text'], ENT_QUOTES) . "</description>
		    <code>" . htmlspecialchars($row['code'], ENT_QUOTES) . "</code>
                    <date>" . htmlspecialchars(substr($row['date'], 0, 10), ENT_QUOTES) . "</date>
		    <npi>" . htmlspecialchars($row['npi'], ENT_QUOTES) . "</npi>
		    <fname>" . htmlspecialchars($row['fname'], ENT_QUOTES) . "</fname>
		    <mname>" . htmlspecialchars($row['mname'], ENT_QUOTES) . "</mname>
		    <lname>" . htmlspecialchars($row['lname'], ENT_QUOTES) . "</lname>
		    <address>" . htmlspecialchars($row['street'], ENT_QUOTES) . "</address>
		    <city>" . htmlspecialchars($row['city'], ENT_QUOTES) . "</city>
		    <state>" . htmlspecialchars($row['state'], ENT_QUOTES) . "</state>
		    <zip>" . htmlspecialchars($row['zip'], ENT_QUOTES) . "</zip>
		    <work_phone>" . htmlspecialchars($row['phonew1'], ENT_QUOTES) . "</work_phone>
		    <facility_extension>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['fid']), ENT_QUOTES) . "</facility_extension>
		    <facility_sha_extension>" . htmlspecialchars("c2ee9ee9-ae31-4628-a919-fec1cbb58686", ENT_QUOTES) . "</facility_sha_extension>
		    <facility_name>" . htmlspecialchars($row['name'], ENT_QUOTES) . "</facility_name>
		    <facility_address>" . htmlspecialchars($row['fstreet'], ENT_QUOTES) . "</facility_address>
		    <facility_city>" . htmlspecialchars($row['fcity'], ENT_QUOTES) . "</facility_city>
		    <facility_state>" . htmlspecialchars($row['fstate'], ENT_QUOTES) . "</facility_state>
		    <facility_country>" . htmlspecialchars($row['country_code'], ENT_QUOTES) . "</facility_country>
		    <facility_zip>" . htmlspecialchars($row['fzip'], ENT_QUOTES) . "</facility_zip>
		    <facility_phone>" . htmlspecialchars($row['fphone'], ENT_QUOTES) . "</facility_phone>
		    <procedure_date>" . htmlspecialchars(preg_replace('/-/', '', substr($row['proc_date'], 0, 10)), ENT_QUOTES) . "</procedure_date>
                </procedure>";
        }

        $procedure .= '</procedures>';
        return $procedure;
    }

    public function getResults($pid, $encounter)
    {
        $wherCon = '';
        if ($encounter) {
            $wherCon = "AND po.encounter_id = $encounter";
        }

        $results = '';
        $query = "SELECT prs.result AS result_value, prs.units, prs.range, prs.result_text as order_title, prs.result_code, prs.procedure_result_id,
	    prs.result_text as result_desc, prs.procedure_result_id AS test_code, poc.procedure_code, poc.procedure_name, poc.diagnoses, po.date_ordered, prs.date AS result_time, prs.abnormal AS abnormal_flag,po.order_status AS order_status
	    FROM procedure_order AS po
	    JOIN procedure_order_code as poc on poc.procedure_order_id = po.procedure_order_id
	    JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id
	    JOIN procedure_result AS prs ON prs.procedure_report_id = pr.procedure_report_id
        WHERE po.patient_id = ? AND prs.result NOT IN ('DNR','TNP') $wherCon";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));

        $results_list = array();
        foreach ($res as $row) {
            $results_list[$row['test_code']]['test_code'] = $row['test_code'];
            $results_list[$row['test_code']]['order_title'] = $row['order_title'];
            $results_list[$row['test_code']]['order_status'] = $row['order_status'];
            $results_list[$row['test_code']]['date_ordered'] = substr(preg_replace('/-/', '', $row['date_ordered']), 0, 8);
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

            $results .= '<result>
		<extension>' . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['test_code']), ENT_QUOTES) . '</extension>
		<root>' . htmlspecialchars("7d5a02b0-67a4-11db-bd13-0800200c9a66", ENT_QUOTES) . '</root>
		<date_ordered>' . htmlspecialchars($row['date_ordered'], ENT_QUOTES) . '</date_ordered>
		<date_ordered_table>' . htmlspecialchars($row['date_ordered_table'], ENT_QUOTES) . '</date_ordered_table>
        <title>' . htmlspecialchars($row['order_title'], ENT_QUOTES) . '</title>
		<test_code>' . htmlspecialchars($row['procedure_code'], ENT_QUOTES) . '</test_code>
		<test_name>' . htmlspecialchars($row['procedure_name'], ENT_QUOTES) . '</test_name>
        <order_status_table>' . htmlspecialchars($order_status_table, ENT_QUOTES) . '</order_status_table>
        <order_status>' . htmlspecialchars($order_status, ENT_QUOTES) . '</order_status>';
            foreach ($row['subtest'] as $row_1) {
                $units = $row_1['units'] ? $row_1['units'] : 'Unit';
                $results .= '
		    <subtest>
			<extension>' . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['result_code']), ENT_QUOTES) . '</extension>
			<root>' . htmlspecialchars("7d5a02b0-67a4-11db-bd13-0800200c9a66", ENT_QUOTES) . '</root>
			<range>' . htmlspecialchars($row_1['range'], ENT_QUOTES) . '</range>
			<unit>' . htmlspecialchars($units, ENT_QUOTES) . '</unit>
			<result_code>' . htmlspecialchars($row_1['result_code'], ENT_QUOTES) . '</result_code>
			<result_desc>' . htmlspecialchars($row_1['result_desc'], ENT_QUOTES) . '</result_desc>
			<result_value>' . htmlspecialchars(($row_1['result_value'] ? $row_1['result_value'] : 0), ENT_QUOTES) . '</result_value>
			<result_time>' . htmlspecialchars($row_1['result_time'], ENT_QUOTES) . '</result_time>
			<abnormal_flag>' . htmlspecialchars($row_1['abnormal_flag'], ENT_QUOTES) . '</abnormal_flag>
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
    public function getEncounterHistory($pid, $encounter)
    {
        $wherCon = '';
        if ($encounter) {
            $wherCon = "AND fe.encounter = $encounter";
        }

        $results = "";
        $query = "SELECT fe.date, fe.encounter,fe.reason,
	    f.id as fid, f.name, f.phone, f.street as fstreet, f.city as fcity, f.state as fstate, f.postal_code as fzip, f.country_code, f.phone as fphone, f.facility_npi as fnpi,
	    f.facility_code as foid, u.fname, u.mname, u.lname, u.npi, u.street, u.city, u.state, u.zip, u.phonew1, cat.pc_catname, lo.title, lo.codes AS physician_type_code,
	    SUBSTRING(ll.diagnosis, LENGTH('SNOMED-CT:')+1, LENGTH(ll.diagnosis)) AS encounter_diagnosis, ll.title, ll.begdate, ll.enddate
	    FROM form_encounter AS fe
	    LEFT JOIN facility AS f ON f.id=fe.facility_id
	    LEFT JOIN users AS u ON u.id=fe.provider_id
	    LEFT JOIN openemr_postcalendar_categories AS cat ON cat.pc_catid=fe.pc_catid
	    LEFT JOIN list_options AS lo ON lo.list_id = ? AND lo.option_id = u.physician_type
	    LEFT JOIN issue_encounter AS ie ON ie.encounter=fe.encounter AND ie.pid=fe.pid
	    LEFT JOIN lists AS ll ON ll.id=ie.list_id AND ll.pid=fe.pid
	    WHERE fe.pid = ? $wherCon ORDER BY fe.date";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array('physician_type', $pid));

        $results = "<encounter_list>";
        foreach ($res as $row) {
            $encounter_reason = '';
            if ($row['reason'] != '') {
                $encounter_reason = "<encounter_reason>" . htmlspecialchars($this->date_format(substr($row['date'], 0, 10)) . " - " . $row['reason'], ENT_QUOTES) . "</encounter_reason>";
            }

            $codes = "";
            $query_procedures = "SELECT c.code, c.code_text FROM billing AS b
			    JOIN code_types AS ct ON ct.ct_key = ?
			    JOIN codes AS c ON c.code = b.code AND c.code_type = ct.ct_id
			    WHERE b.pid = ? AND b.code_type = ? AND activity = 1 AND b.encounter = ?";
            $appTable_procedures = new ApplicationTable();
            $res_procedures = $appTable_procedures->zQuery($query_procedures, array('CPT4', $pid, 'CPT4', $row['encounter']));
            foreach ($res_procedures as $row_procedures) {
                $codes .= "
		<procedures>
		    <code>" . htmlspecialchars($row_procedures['code'], ENT_QUOTES) . "</code>
		    <text>" . htmlspecialchars($row_procedures['code_text'], ENT_QUOTES) . "</text>
		</procedures>
		";
            }

            if ($row['encounter_diagnosis']) {
                $encounter_activity = '';
                if ($row['enddate'] != '') {
                    $encounter_activity = 'Completed';
                } else {
                    $encounter_activity = 'Active';
                }

                $codes .= "
		<procedures>
		    <code>" . htmlspecialchars($row['encounter_diagnosis'], ENT_QUOTES) . "</code>
		    <text>" . htmlspecialchars(\Application\Listener\Listener::z_xlt($row['title']), ENT_QUOTES) . "</text>
		    <status>" . htmlspecialchars($encounter_activity, ENT_QUOTES) . "</status>
		</procedures>
		";
            }

            $location_details = ($row['name'] != '') ? (',' . $row['fstreet'] . ',' . $row['fcity'] . ',' . $row['fstate'] . ' ' . $row['fzip']) : '';
            $results .= "
	    <encounter>
		<extension>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['encounter']), ENT_QUOTES) . "</extension>
		<sha_extension>" . htmlspecialchars(sha1($_SESSION['site_id'] . $row['encounter']), ENT_QUOTES) . "</sha_extension>
		<encounter_id>" . htmlspecialchars($row['encounter'], ENT_QUOTES) . "</encounter_id>
		<visit_category>" . htmlspecialchars($row['pc_catname'], ENT_QUOTES) . "</visit_category>
		<performer>" . htmlspecialchars($row['fname'] . " " . $row['mname'] . " " . $row['lname'], ENT_QUOTES) . "</performer>
		<physician_type_code>" . htmlspecialchars($row['physician_type_code'], ENT_QUOTES) . "</physician_type_code>
		<physician_type>" . htmlspecialchars($row['title'], ENT_QUOTES) . "</physician_type>
		<npi>" . htmlspecialchars($row['npi'], ENT_QUOTES) . "</npi>
		<fname>" . htmlspecialchars($row['fname'], ENT_QUOTES) . "</fname>
		<mname>" . htmlspecialchars($row['mname'], ENT_QUOTES) . "</mname>
		<lname>" . htmlspecialchars($row['lname'], ENT_QUOTES) . "</lname>
		<street>" . htmlspecialchars($row['street'], ENT_QUOTES) . "</street>
		<city>" . htmlspecialchars($row['city'], ENT_QUOTES) . "</city>
		<state>" . htmlspecialchars($row['state'], ENT_QUOTES) . "</state>
		<zip>" . htmlspecialchars($row['zip'], ENT_QUOTES) . "</zip>
		<work_phone>" . htmlspecialchars($row['phonew1'], ENT_QUOTES) . "</work_phone>
		<location>" . htmlspecialchars($row['name'], ENT_QUOTES) . "</location>
        <location_details>" . htmlspecialchars($location_details, ENT_QUOTES) . "</location_details>
		<date>" . htmlspecialchars($this->date_format(substr($row['date'], 0, 10)), ENT_QUOTES) . "</date>
		<date_formatted>" . htmlspecialchars(preg_replace('/-/', '', substr($row['date'], 0, 10)), ENT_QUOTES) . "</date_formatted>
		<facility_extension>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['fid']), ENT_QUOTES) . "</facility_extension>
		<facility_sha_extension>" . htmlspecialchars(sha1($_SESSION['site_id'] . $row['fid']), ENT_QUOTES) . "</facility_sha_extension>
		<facility_npi>" . htmlspecialchars($row['fnpi'], ENT_QUOTES) . "</facility_npi>
		<facility_oid>" . htmlspecialchars($row['foid'], ENT_QUOTES) . "</facility_oid>
		<facility_name>" . htmlspecialchars($row['name'], ENT_QUOTES) . "</facility_name>
		<facility_address>" . htmlspecialchars($row['fstreet'], ENT_QUOTES) . "</facility_address>
		<facility_city>" . htmlspecialchars($row['fcity'], ENT_QUOTES) . "</facility_city>
		<facility_state>" . htmlspecialchars($row['fstate'], ENT_QUOTES) . "</facility_state>
		<facility_country>" . htmlspecialchars($row['country_code'], ENT_QUOTES) . "</facility_country>
		<facility_zip>" . htmlspecialchars($row['fzip'], ENT_QUOTES) . "</facility_zip>
		<facility_phone>" . htmlspecialchars($row['fphone'], ENT_QUOTES) . "</facility_phone>
		<encounter_procedures>$codes</encounter_procedures>
                $encounter_reason
	    </encounter>";
        }

        $results .= "</encounter_list>";
        return $results;
    }

    /*
    #**************************************************#
    #                  PROGRESS NOTES                  #
    #**************************************************#
    */
    public function getProgressNotes($pid, $encounter)
    {
        $progress_notes = '';
        $formTables_details = $this->fetchFields('progress_note', 'assessment_plan', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $progress_notes .= "<progressNotes>";
        foreach ($result as $row) {
            foreach ($row as $key => $value) {
                $progress_notes .= "<item>" . htmlspecialchars($value, ENT_QUOTES) . "</item>";
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
    public function getHospitalCourse($pid, $encounter)
    {
        $hospital_course = '';
        $formTables_details = $this->fetchFields('discharge_summary', 'hospital_course', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $hospital_course .= "<hospitalCourse><item>";
        foreach ($result as $row) {
            $hospital_course .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
        }

        $hospital_course .= "</item></hospitalCourse>";

        return $hospital_course;
    }

    public function getDischargeDiagnosis($pid, $encounter)
    {
        $discharge_diagnosis = '';
        $formTables_details = $this->fetchFields('discharge_summary', 'hospital_discharge_diagnosis', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $discharge_diagnosis .= "<dischargediagnosis><item>";
        foreach ($result as $row) {
            $discharge_diagnosis .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
        }

        $discharge_diagnosis .= "</item></dischargediagnosis>";

        return $discharge_diagnosis;
    }

    public function getDischargeMedications($pid, $encounter)
    {
        $discharge_medications = '';
        $formTables_details = $this->fetchFields('discharge_summary', 'hospital_discharge_medications', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $discharge_medications .= "<dischargemedication><item>";
        foreach ($result as $row) {
            $discharge_medications .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getComplications($pid, $encounter)
    {
        $complications = '';
        $formTables_details = $this->fetchFields('procedure_note', 'complications', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $complications .= "<complications>";
        $complications .= "<age>" . $this->getAge($pid) . "</age><item>";
        foreach ($result as $row) {
            $complications .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getPostProcedureDiag($pid, $encounter)
    {
        $procedure_diag = '';
        $formTables_details = $this->fetchFields('procedure_note', 'postprocedure_diagnosis', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_diag .= '<procedure_diagnosis>';
        $procedure_diag .= "<age>" . $this->getAge($pid) . "</age><item>";
        foreach ($result as $row) {
            $procedure_diag .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getProcedureDescription($pid, $encounter)
    {
        $procedure_description = '';
        $formTables_details = $this->fetchFields('procedure_note', 'procedure_description', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_description .= "<procedure_description><item>";
        foreach ($result as $row) {
            $procedure_description .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getProcedureIndications($pid, $encounter)
    {
        $procedure_indications = '';
        $formTables_details = $this->fetchFields('procedure_note', 'procedure_indications', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_indications .= "<procedure_indications><item>";
        foreach ($result as $row) {
            $procedure_indications .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getAnesthesia($pid, $encounter)
    {
        $anesthesia = '';
        $formTables_details = $this->fetchFields('operative_note', 'anesthesia', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $anesthesia .= "<anesthesia><item>";
        foreach ($result as $row) {
            $anesthesia .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getPostoperativeDiag($pid, $encounter)
    {
        $post_operative_diag = '';
        $formTables_details = $this->fetchFields('operative_note', 'post_operative_diagnosis', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $post_operative_diag .= "<post_operative_diag><item>";
        foreach ($result as $row) {
            $post_operative_diag .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getPreOperativeDiag($pid, $encounter)
    {
        $pre_operative_diag = '';
        $formTables_details = $this->fetchFields('operative_note', 'pre_operative_diagnosis', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $pre_operative_diag .= "<pre_operative_diag><item>";
        foreach ($result as $row) {
            $pre_operative_diag .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getEstimatedBloodLoss($pid, $encounter)
    {
        $estimated_blood_loss = '';
        $formTables_details = $this->fetchFields('operative_note', 'procedure_estimated_blood_loss', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $estimated_blood_loss .= "<blood_loss><item>";
        foreach ($result as $row) {
            $estimated_blood_loss .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getProcedureFindings($pid, $encounter)
    {
        $procedure_findings = '';
        $formTables_details = $this->fetchFields('operative_note', 'procedure_findings', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_findings .= "<procedure_findings><item>";
        foreach ($result as $row) {
            $procedure_findings .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
        }

        $procedure_findings .= "</item><age>" . $this->getAge($pid) . "</age></procedure_findings>";
        return $procedure_findings;
    }

    /*
    Sub section of OPERATIVE NOTES in CCDA.
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $pre_operative_diag  XML which contains the details collected from the patient.
    */
    public function getProcedureSpecimensTaken($pid, $encounter)
    {
        $procedure_specimens = '';
        $formTables_details = $this->fetchFields('operative_note', 'procedure_specimens_taken', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $procedure_specimens .= "<procedure_specimens><item>";
        foreach ($result as $row) {
            $procedure_specimens .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getHP($pid, $encounter)
    {
        $hp = '';
        $formTables_details = $this->fetchFields('consultation_note', 'history_of_present_illness', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $hp .= "<hp><item>";
        foreach ($result as $row) {
            $hp .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getPhysicalExam($pid, $encounter)
    {
        $physical_exam = '';
        $formTables_details = $this->fetchFields('consultation_note', 'physical_exam', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $physical_exam .= "<physical_exam><item>";
        foreach ($result as $row) {
            $physical_exam .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getChiefComplaint($pid, $encounter)
    {
        $chief_complaint = '';
        $formTables_details = $this->fetchFields('history_physical_note', 'chief_complaint', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $chief_complaint .= "<chief_complaint><item>";
        foreach ($result as $row) {
            $chief_complaint .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getGeneralStatus($pid, $encounter)
    {
        $general_status = '';
        $formTables_details = $this->fetchFields('history_physical_note', 'general_status', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $general_status .= "<general_status><item>";
        foreach ($result as $row) {
            $general_status .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getHistoryOfPastIllness($pid, $encounter)
    {
        $history_past_illness = '';
        $formTables_details = $this->fetchFields('history_physical_note', 'hpi_past_med', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $history_past_illness .= "<history_past_illness><item>";
        foreach ($result as $row) {
            $history_past_illness .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getReviewOfSystems($pid, $encounter)
    {
        $review_of_systems = '';
        $formTables_details = $this->fetchFields('history_physical_note', 'review_of_systems', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $review_of_systems .= "<review_of_systems><item>";
        foreach ($result as $row) {
            $review_of_systems .= htmlspecialchars(implode(' ', $row), ENT_QUOTES);
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
    public function getVitals($pid, $encounter)
    {
        $wherCon = '';
        if ($encounter) {
            $wherCon = "AND fe.encounter = $encounter";
        }

        $vitals = '';
        $query = "SELECT DATE(fe.date) AS date, fv.id, temperature, bpd, bps, head_circ, pulse, height, oxygen_saturation, weight, BMI FROM forms AS f
                JOIN form_encounter AS fe ON fe.encounter = f.encounter AND fe.pid = f.pid
                JOIN form_vitals AS fv ON fv.id = f.form_id
                WHERE f.pid = ? AND f.formdir = 'vitals' AND f.deleted=0 $wherCon
                ORDER BY fe.date DESC";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));


        $vitals .= "<vitals_list>";
        foreach ($res as $row) {
            $convWeightValue = number_format($row['weight'] * 0.45359237, 2);
            $convHeightValue = round(number_format($row['height'] * 2.54, 2), 1);
            if ($GLOBALS['units_of_measurement'] == 2 || $GLOBALS['units_of_measurement'] == 4) {
                $weight_value = $convWeightValue;
                $weight_unit = 'kg';
                $height_value = $convHeightValue;
                $height_unit = 'cm';
            } else {
                $temp = US_weight($row['weight'], 1);
                $tempArr = explode(" ", $temp);
                $weight_value = $tempArr[0];
                $weight_unit = 'lb';
                $height_value = $row['height'];
                $height_unit = 'in';
            }

            $vitals .= "<vitals>
		    <extension>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id']), ENT_QUOTES) . "</extension>
		    <sha_extension>" . htmlspecialchars("c6f88321-67ad-11db-bd13-0800200c9a66", ENT_QUOTES) . "</sha_extension>
                    <date>" . htmlspecialchars($this->date_format($row['date']), ENT_QUOTES) . "</date>
                    <effectivetime>" . htmlspecialchars(preg_replace('/-/', '', $row['date']), ENT_QUOTES) . "000000</effectivetime>
                    <temperature>" . htmlspecialchars($row['temperature'], ENT_QUOTES) . "</temperature>
		    <extension_temperature>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'temperature'), ENT_QUOTES) . "</extension_temperature>
                    <bpd>" . htmlspecialchars(($row['bpd'] ? $row['bpd'] : 0), ENT_QUOTES) . "</bpd>
		    <extension_bpd>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'bpd'), ENT_QUOTES) . "</extension_bpd>
                    <bps>" . htmlspecialchars(($row['bps'] ? $row['bps'] : 0), ENT_QUOTES) . "</bps>
		    <extension_bps>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'bps'), ENT_QUOTES) . "</extension_bps>
                    <head_circ>" . htmlspecialchars(($row['head_circ'] ? $row['head_circ'] : 0), ENT_QUOTES) . "</head_circ>
		    <extension_head_circ>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'head_circ'), ENT_QUOTES) . "</extension_head_circ>
                    <pulse>" . htmlspecialchars(($row['pulse'] ? $row['pulse'] : 0), ENT_QUOTES) . "</pulse>
		    <extension_pulse>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'pulse'), ENT_QUOTES) . "</extension_pulse>
                    <height>" . htmlspecialchars($height_value, ENT_QUOTES) . "</height>
		    <extension_height>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'height'), ENT_QUOTES) . "</extension_height>
          <unit_height>" . htmlspecialchars($height_unit, ENT_QUOTES) . "</unit_height>
                    <oxygen_saturation>" . htmlspecialchars(($row['oxygen_saturation'] ? $row['oxygen_saturation'] : 0), ENT_QUOTES) . "</oxygen_saturation>
		    <extension_oxygen_saturation>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'oxygen_saturation'), ENT_QUOTES) . "</extension_oxygen_saturation>
                    <breath>" . htmlspecialchars(($row['respiration'] ? $row['respiration'] : 0), ENT_QUOTES) . "</breath>
		    <extension_breath>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'breath'), ENT_QUOTES) . "</extension_breath>
                    <weight>" . htmlspecialchars($weight_value, ENT_QUOTES) . "</weight>
		    <extension_weight>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'weight'), ENT_QUOTES) . "</extension_weight>
          <unit_weight>" . htmlspecialchars($weight_unit, ENT_QUOTES) . "</unit_weight>
                    <BMI>" . htmlspecialchars(($row['BMI'] ? $row['BMI'] : 0), ENT_QUOTES) . "</BMI>
		    <extension_BMI>" . htmlspecialchars(base64_encode($_SESSION['site_id'] . $row['id'] . 'BMI'), ENT_QUOTES) . "</extension_BMI>
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
    public function getSocialHistory($pid, $encounter)
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

        $query = "SELECT id, tobacco, alcohol, exercise_patterns, recreational_drugs FROM history_data WHERE pid=? ORDER BY id DESC LIMIT 1";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));

        $social_history .= "<social_history>";
        foreach ($res as $row) {
            $tobacco = explode('|', $row['tobacco']);
            $status_code = (new CarecoordinationTable())->getListCodes($tobacco[3], 'smoking_status');
            $status_code = str_replace("SNOMED-CT:", "", $status_code);
            $social_history .= "<history_element>
                                  <extension>" . htmlspecialchars(base64_encode('smoking' . $_SESSION['site_id'] . $row['id']), ENT_QUOTES) . "</extension>
                                  <sha_extension>" . htmlspecialchars("9b56c25d-9104-45ee-9fa4-e0f3afaa01c1", ENT_QUOTES) . "</sha_extension>
                                  <element>" . htmlspecialchars('Smoking', ENT_QUOTES) . "</element>
                                  <description>" . htmlspecialchars((new CarecoordinationTable())->getListTitle($tobacco[3], 'smoking_status'), ENT_QUOTES) . "</description>
                                  <status_code>" . htmlspecialchars(($status_code ? $status_code : 0), ENT_QUOTES) . "</status_code>
                                  <status>" . htmlspecialchars(($snomeds_status[$tobacco[1]] ? $snomeds_status[$tobacco[1]] : 'NULL'), ENT_QUOTES) . "</status>
                                  <date>" . ($tobacco[2] ? htmlspecialchars($this->date_format($tobacco[2]), ENT_QUOTES) : 0) . "</date>
                                  <date_formatted>" . ($tobacco[2] ? htmlspecialchars(preg_replace('/-/', '', $tobacco[2]), ENT_QUOTES) : 0) . "</date_formatted>
                                  <code>" . htmlspecialchars(($arr['smoking'] ? $arr['smoking'] : 0), ENT_QUOTES) . "</code>
                            </history_element>";
            $alcohol = explode('|', $row['alcohol']);
            $social_history .= "<history_element>
                                  <extension>" . htmlspecialchars(base64_encode('alcohol' . $_SESSION['site_id'] . $row['id']), ENT_QUOTES) . "</extension>
                                  <sha_extension>" . htmlspecialchars("37f76c51-6411-4e1d-8a37-957fd49d2cef", ENT_QUOTES) . "</sha_extension>
                                  <element>" . htmlspecialchars('Alcohol', ENT_QUOTES) . "</element>
                                  <description>" . htmlspecialchars($alcohol[0], ENT_QUOTES) . "</description>
                                  <status_code>" . htmlspecialchars(($alcohol_status_codes[$alcohol[1]] ? $alcohol_status_codes[$alcohol[1]] : 0), ENT_QUOTES) . "</status_code>
                                  <status>" . htmlspecialchars(($alcohol_status[$alcohol[1]] ? $alcohol_status[$alcohol[1]] : 'completed'), ENT_QUOTES) . "</status>
                                  <date>" . ($alcohol[2] ? htmlspecialchars($this->date_format($alcohol[2]), ENT_QUOTES) : 0) . "</date>
                                  <date_formatted>" . ($alcohol[2] ? htmlspecialchars(preg_replace('/-/', '', $alcohol[2]), ENT_QUOTES) : 0) . "</date_formatted>
                                  <code>" . htmlspecialchars($arr['alcohol'], ENT_QUOTES) . "</code>
                            </history_element>";
        }

        $social_history .= "</social_history>";
        return $social_history;
    }

    /*
    #********************************************************#
    #                  UNSTRUCTURED DOCUMENTS                #
    #********************************************************#
    */
    public function getUnstructuredDocuments($pid, $encounter)
    {
        $image = '';
        $formTables_details = $this->fetchFields('unstructured_document', 'unstructured_doc', 1);
        $result = $this->fetchFormValues($pid, $encounter, $formTables_details);

        $image .= "<document>";
        foreach ($result as $row) {
            foreach ($row as $key => $value) {
                $image .= "<item>";
                $image .= "<type>" . $row[$key][1] . "</type>";
                $image .= "<content>" . $row[$key][0] . "</content>";
                $image .= "</item>";
            }
        }

        $image .= "</document>";
        return $image;
    }

    public function getDetails($field_name)
    {
        if ($field_name == 'hie_custodian_id') {
            $query = "SELECT f.name AS organization, f.street, f.city, f.state, f.postal_code AS zip, f.phone AS phonew1
			FROM facility AS f
			JOIN modules AS mo ON mo.mod_directory='Carecoordination'
			JOIN module_configuration AS conf ON conf.field_value=f.id AND mo.mod_id=conf.module_id
			WHERE conf.field_name=?";
        } else {
            $query = "SELECT u.title, u.fname, u.mname, u.lname, u.npi, u.street, u.city, u.state, u.zip, CONCAT_WS(' ','',u.phonew1) AS phonew1, u.organization, u.specialty, conf.field_name, mo.mod_name, lo.title as  physician_type, SUBSTRING(lo.codes, LENGTH('SNOMED-CT:')+1, LENGTH(lo.codes)) as  physician_type_code
            FROM users AS u
	    LEFT JOIN list_options AS lo ON lo.list_id = 'physician_type' AND lo.option_id = u.physician_type
            JOIN modules AS mo ON mo.mod_directory='Carecoordination'
            JOIN module_configuration AS conf ON conf.field_value=u.id AND mo.mod_id=conf.module_id
            WHERE conf.field_name=?";
        }

        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($field_name));
        foreach ($res as $result) {
            return $result;
        }
    }

    /*
    Get the Age of a patient
    * @param    int     $pid    Patient Internal Identifier.

    * return    int     $age    Age of a patient will be returned
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

    public function getRepresentedOrganization()
    {
        $query = "select * from facility where primary_business_entity = 1";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid));

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
    public function fetchFields($ccda_component, $ccda_section, $user_id)
    {
        $form_type = $table_name = $field_names = '';
        $query = "select * from ccda_table_mapping
            left join ccda_field_mapping as ccf on ccf.table_id = ccda_table_mapping.id
            where ccda_component = ? and ccda_component_section = ? and user_id = ? and deleted = 0";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($ccda_component, $ccda_section, $user_id));

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
    public function fetchFormValues($pid, $encounter, $formTables)
    {
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
    public function logCCDA($pid, $encounter, $content, $time, $status, $user_id, $view = 0, $transfer = 0, $emr_transfer = 0)
    {
        $content = base64_decode($content);
        $file_path = '';
        $docid = '';
        $revid = '';
        if ($GLOBALS['document_storage_method'] == 1) {
            $couch = new CouchDB();
            $docid = $couch->createDocId('ccda');
            $binaryUuid = UuidRegistry::uuidToBytes($docid);
            if ($GLOBALS['couchdb_encryption']) {
                $encrypted = 1;
                $cryptoGen = new CryptoGen();
                $resp = $couch->save_doc(['_id' => $docid, 'data' => $cryptoGen->encryptStandard($content, null, 'database')]);
            } else {
                $encrypted = 0;
                $resp = $couch->save_doc(['_id' => $docid, 'data' => base64_encode($content)]);
            }
            $docid = $resp->id;
            $revid = $resp->rev;
        } else {
            $binaryUuid = (new UuidRegistry(['table_name' => 'ccda']))->createUuid();
            $file_name = UuidRegistry::uuidToString($binaryUuid);
            $file_path = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $pid . '/CCDA';
            if (!is_dir($file_path)) {
                mkdir($file_path, 0777, true);
            }

            $fccda = fopen($file_path . "/" . $file_name, "w");
            if ($GLOBALS['drive_encryption']) {
                $encrypted = 1;
                $cryptoGen = new CryptoGen();
                fwrite($fccda, $cryptoGen->encryptStandard($content, null, 'database'));
            } else {
                $encrypted = 0;
                fwrite($fccda, $content);
            }
            fclose($fccda);
            $file_path = $file_path . "/" . $file_name;
        }

        $query = "insert into ccda (`uuid`, `pid`, `encounter`, `ccda_data`, `time`, `status`, `user_id`, `couch_docid`, `couch_revid`, `hash`, `view`, `transfer`, `emr_transfer`, `encrypted`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $hash = hash('sha3-512', $content);
        $appTable = new ApplicationTable();
        $result = $appTable->zQuery($query, array($binaryUuid, $pid, $encounter, $file_path, $time, $status, $user_id, $docid, $revid, $hash, $view, $transfer, $emr_transfer, $encrypted));
        return $moduleInsertId = $result->getGeneratedValue();
    }

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
    public function date_format($date, $format = null)
    {
        if (!$date) {
            return;
        }

        $format = $format ? $format : 'm/d/y';
        $temp = explode(' ', $date); //split using space and consider the first portion, incase of date with time
        $date = $temp[0];
        $date = str_replace('/', '-', $date);
        $arr = explode('-', $date);

        if ($format == 'm/d/y') {
            $formatted_date = $arr[1] . "/" . $arr[2] . "/" . $arr[0];
        }

        $formatted_date = $temp[1] ? $formatted_date . " " . $temp[1] : $formatted_date; //append the time, if exists, with the new formatted date
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
    public function generate_code($code_text)
    {
        $encrypted = sha1($code_text);
        $code = '';
        for ($i = 0; $i <= strlen($encrypted);) {
            $code .= $encrypted[$i];
            $i = $i + 2;
        }

        $encrypted = $code;
        $code = '';
        for ($i = 0; $i <= strlen($encrypted);) {
            $code .= $encrypted[$i];
            $i = $i + 2;
        }

        $code = strtoupper(substr($code, 0, 6));
        return $code;
    }

    public function getProviderId($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT providerID FROM patient_data WHERE `pid`  = ?";
        $result = $appTable->zQuery($query, array($pid));
        $row = $result->current();
        return $row['providerID'];
    }

    public function getUserDetails($uid)
    {
        $query = "SELECT u.title,npi,fname,mname,lname,street,city,state,zip,CONCAT_WS(' ','',phonew1) AS phonew1, lo.title as  physician_type,
                       organization, specialty, SUBSTRING(lo.codes, LENGTH('SNOMED-CT:')+1, LENGTH(lo.codes)) as  physician_type_code FROM users as u
		       LEFT JOIN list_options AS lo ON lo.list_id = 'physician_type' AND lo.option_id = u.physician_type
		       WHERE `id` = ?";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($uid));
        foreach ($res as $result) {
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
        // we set $error=false or else it will display on the screen, which seems counterintuitive... it also supresses the exception
        $result = $appTable->zQuery("Describe `sct_descriptions`", $params = '', $log = true, $error = false);
        if ($result !== false) { // will return false if there is an error
            $codes_installed = true;
        }


        return $codes_installed;
    }

    /*
    * get details from care plan form
    * @param    int     $pid           Patient Internal Identifier.
    * @param    int     $encounter     Current selected encounter.

    * return    string  $planofcare  XML which contains the details collected from the patient.
    */
    public function getPlanOfCare($pid, $encounter)
    {
        $wherCon = '';
        $appTable = new ApplicationTable();
        if ($encounter) {
            $query = "SELECT form_id FROM forms  WHERE pid = ? AND formdir = ? AND deleted = 0 ORDER BY date DESC LIMIT 1";
            $result = $appTable->zQuery($query, array($pid, 'care_plan'));
            foreach ($result as $row) {
                $form_id = $row['form_id'];
            }

            if ($form_id) {
                $wherCon = "AND f.form_id = $form_id";
            }
        }

        // some installations of OpenEMR do not have the SNOMED codes installed.  Rather than failing on a left join because
        // the table does not exist we will include the SNOMED code pieces only if we have the sct_descriptions table installed.
        // TODO: is there a better way to find out if the SNOMED tables have been installed through a global setting instead of describing the tables?
        $fcp_code_type = 'ct.`ct_key` AS fcp_code_type';
        $sct_descriptions_join = '';
        $care_plan_query_data = ['Plan_of_Care_Type', $pid, 'care_plan', 0, $pid];
        if ($this->is_snomed_codes_installed($appTable)) {
            $fcp_code_type = "IF(sct_descriptions.ConceptId,'SNOMED-CT',ct.`ct_key`) AS fcp_code_type";
            $sct_descriptions_join = ' LEFT JOIN sct_descriptions ON sct_descriptions.ConceptId = fcp.`code`
            AND sct_descriptions.DescriptionStatus = ? AND sct_descriptions.DescriptionType = ?
            LEFT JOIN sct_concepts ON sct_descriptions.ConceptId = sct_concepts.ConceptId ';
            $care_plan_query_data = array_merge([0, 1], $care_plan_query_data);
        }

        $query = "SELECT 'care_plan' AS source,fcp.code,fcp.codetext,fcp.description,fcp.date," . $fcp_code_type . " , l.`notes` AS moodCode
                 FROM forms AS f
                LEFT JOIN form_care_plan AS fcp ON fcp.id = f.form_id
                 LEFT JOIN codes AS c ON c.code = fcp.code
                 LEFT JOIN code_types AS ct ON c.`code_type` = ct.ct_id
                " . $sct_descriptions_join . "
                 LEFT JOIN `list_options` l ON l.`option_id` = fcp.`care_plan_type` AND l.`list_id`=?
                 WHERE f.pid = ? AND f.formdir = ? AND f.deleted = ? $wherCon
                 UNION
                 SELECT 'referal' AS source,0 AS CODE,'NULL' AS codetext,CONCAT_WS(', ',l1.field_value,CONCAT_WS(' ',u.fname,u.lname),CONCAT('Tel:',u.phonew1),u.street,u.city,CONCAT_WS(' ',u.state,u.zip),CONCAT('Schedule Date: ',l2.field_value)) AS description,l2.field_value AS DATE,'' AS fcp_code_type,'' moodCode
                 FROM transactions AS t
                 LEFT JOIN lbt_data AS l1 ON l1.form_id=t.id AND l1.field_id = 'body'
                 LEFT JOIN lbt_data AS l2 ON l2.form_id=t.id AND l2.field_id = 'refer_date'
                 LEFT JOIN lbt_data AS l3 ON l3.form_id=t.id AND l3.field_id = 'refer_to'
                 LEFT JOIN users AS u ON u.id = l3.field_value
                 WHERE t.pid = ?";
        $res = $appTable->zQuery($query, $care_plan_query_data);
        $status = 'Pending';
        $status_entry = 'active';
        $planofcare .= '<planofcare>';
        foreach ($res as $row) {
            //$date_formatted = \Application\Model\ApplicationTable::fixDate($row['date'],$GLOBALS['date_display_format'],'yyyy-mm-dd');
            $code_type = '';
            if ($row['fcp_code_type'] == 'SNOMED-CT') {
                $code_type = '2.16.840.1.113883.6.96';
            } elseif ($row['fcp_code_type'] == 'CPT4') {
                $code_type = '2.16.840.1.113883.6.12';
            } elseif ($row['fcp_code_type'] == 'LOINC') {
                $code_type = '2.16.840.1.113883.6.1';
            }

            $planofcare .= '<item>
        <code>' . htmlspecialchars($row['code'], ENT_QUOTES) . '</code>
        <code_text>' . htmlspecialchars($row['codetext'], ENT_QUOTES) . '</code_text>
        <description>' . htmlspecialchars($row['description'], ENT_QUOTES) . '</description>
        <date>' . htmlspecialchars($row['date'], ENT_QUOTES) . '</date>
        <date_formatted>' . htmlspecialchars(preg_replace('/-/', '', $row['date']), ENT_QUOTES) . '</date_formatted>
        <status>' . htmlspecialchars($status, ENT_QUOTES) . '</status>
        <status_entry>' . htmlspecialchars($status_entry, ENT_QUOTES) . '</status_entry>
        <code_type>' . htmlspecialchars($code_type, ENT_QUOTES) . '</code_type>
        <moodCode>' . htmlspecialchars($row['moodCode'], ENT_QUOTES) . '</moodCode>
        </item>';
        }

        $planofcare .= '</planofcare>';
        return $planofcare;
    }

    /*
   * get details from functional and cognitive status form
   * @param    int     $pid           Patient Internal Identifier.
   * @param    int     $encounter     Current selected encounter.

   * return    string  $functional_cognitive  XML which contains the details collected from the patient.
   */
    public function getFunctionalCognitiveStatus($pid, $encounter)
    {
        $wherCon = '';
        if ($encounter) {
            $wherCon = "AND f.encounter = $encounter";
        }

        $functional_cognitive = '';
        $query = "SELECT ffcs.* FROM forms AS f
                LEFT JOIN form_functional_cognitive_status AS ffcs ON ffcs.id = f.form_id
                WHERE f.pid = ? AND f.formdir = ? AND f.deleted = ? $wherCon";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid, 'functional_cognitive_status', 0));

        $functional_cognitive .= '<functional_cognitive_status>';
        foreach ($res as $row) {
            $status = $status_entry = '';
            if ($row['activity'] == 1) {
                $status = 'Active';
                $status_code = '55561003';
                $status_entry = 'completed';
            } else {
                $status = 'Inactive';
                $status_code = '73425007';
                $status_entry = 'completed';
            }

            $functional_cognitive .= '<item>
        <code>' . htmlspecialchars(($row['code'] ? $row['code'] : 0), ENT_QUOTES) . '</code>
        <code_text>' . htmlspecialchars(($row['codetext'] ? $row['codetext'] : 'NULL'), ENT_QUOTES) . '</code_text>
        <description>' . htmlspecialchars($row['description'], ENT_QUOTES) . '</description>
        <date>' . htmlspecialchars($row['date'], ENT_QUOTES) . '</date>
        <date_formatted>' . htmlspecialchars(preg_replace('/-/', '', $row['date']), ENT_QUOTES) . '</date_formatted>
        <status>' . $status . '</status>
        <status_code>' . $status_code . '</status_code>
        <status_entry>' . $status_entry . '</status_entry>
        <age>' . $this->getAge($pid) . '</age>
        </item>';
        }

        $functional_cognitive .= '</functional_cognitive_status>';
        return $functional_cognitive;
    }

    public function getCareTeamProviderId($pid)
    {
        $appTable = new ApplicationTable();
        $query = "SELECT care_team_provider FROM patient_data WHERE `pid`  = ?";
        $result = $appTable->zQuery($query, array($pid));
        $row = $result->current();
        return $row['care_team_provider'];
    }

    public function getClinicalInstructions($pid, $encounter)
    {
        $wherCon = '';
        if ($encounter) {
            $wherCon = "AND f.encounter = $encounter";
        }

        $query = "SELECT fci.* FROM forms AS f
                LEFT JOIN form_clinical_instructions AS fci ON fci.id = f.form_id
                WHERE f.pid = ? AND f.formdir = ? AND f.deleted = ? $wherCon";
        $appTable = new ApplicationTable();
        $res = $appTable->zQuery($query, array($pid, 'clinical_instructions', 0));
        $clinical_instructions = '<clinical_instruction>';
        foreach ($res as $row) {
            $clinical_instructions .= '<item>' . htmlspecialchars($row['instruction']) . '</item>';
        }

        $clinical_instructions .= '</clinical_instruction>';
        return $clinical_instructions;
    }

    public function getRefferals($pid, $encounter)
    {
        $wherCon = '';
        if ($encounter) {
            $wherCon = "ORDER BY date DESC LIMIT 1";
        }

        $appTable = new ApplicationTable();
        $referrals = '';
        $query = "SELECT field_value FROM transactions JOIN lbt_data ON form_id=id AND field_id = 'body' WHERE pid = ? $wherCon";
        $result = $appTable->zQuery($query, array($pid));
        $referrals = '<referral_reason>';
        foreach ($result as $row) {
            $referrals .= '<text>' . htmlspecialchars($row['field_value']) . '</text>';
        }

        $referrals .= '</referral_reason>';
        return $referrals;
    }

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
}

?>
