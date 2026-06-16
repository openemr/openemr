<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cda;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Ramsey\Uuid\Uuid;

class InternalToCdaConverter
{
    private const NS_CDA = 'urn:hl7-org:v3';
    private const NS_SDTC = 'urn:hl7-org:sdtc';
    private const NS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';

    private DOMDocument $output;
    private DOMDocument $input;
    private DOMXPath $inputXpath;

    public function convert(string $internalXml): string
    {
        $this->input = new DOMDocument();
        $result = $this->input->loadXML($internalXml, LIBXML_NONET);
        if ($result === false) {
            throw new \InvalidArgumentException('Failed to parse input XML');
        }
        $this->inputXpath = new DOMXPath($this->input);

        $this->output = new DOMDocument('1.0', 'UTF-8');
        $this->output->formatOutput = true;

        $xsl = $this->output->createProcessingInstruction(
            'xml-stylesheet',
            'type="text/xsl" href="CDA.xsl"'
        );
        $this->output->appendChild($xsl);

        $root = $this->createRootElement();
        $this->output->appendChild($root);

        $this->renderHeader($root);
        $this->renderBody($root);

        $xml = $this->output->saveXML();
        if ($xml === false) {
            throw new \RuntimeException('Failed to serialize XML');
        }
        return $xml;
    }

    private function createRootElement(): DOMElement
    {
        $root = $this->output->createElementNS(self::NS_CDA, 'ClinicalDocument');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', self::NS_XSI);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:voc', 'urn:hl7-org:v3/voc');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sdtc', self::NS_SDTC);
        return $root;
    }

    private function renderHeader(DOMElement $root): void
    {
        $this->renderDocumentMetadata($root);
        $this->renderRecordTarget($root);
        $this->renderAuthor($root);
        $this->renderCustodian($root);
        $this->renderInformationRecipient($root);
        $this->renderParticipants($root);
        $this->renderDocumentationOf($root);
    }

    private function renderDocumentMetadata(DOMElement $root): void
    {
        $docType = $this->xpathValue('/CCDA/doc_type');

        $docCode = '34133-9';
        $docName = 'Summarization of Episode Note';
        $docOid = '2.16.840.1.113883.10.20.22.1.2';

        if ($docType === 'referral') {
            $docCode = '57133-1';
            $docName = 'Referral Note';
            $docOid = '2.16.840.1.113883.10.20.22.1.14';
        } elseif ($docType === 'unstructured') {
            $docCode = '34133-9';
            $docName = 'Patient Documents';
            $docOid = '2.16.840.1.113883.10.20.22.1.10';
        }

        $realmCode = $this->createElement('realmCode');
        $realmCode->setAttribute('code', 'US');
        $root->appendChild($realmCode);

        $typeId = $this->createElement('typeId');
        $typeId->setAttribute('root', '2.16.840.1.113883.1.3');
        $typeId->setAttribute('extension', 'POCD_HD000040');
        $root->appendChild($typeId);

        $this->appendVersionedTemplateId($root, '2.16.840.1.113883.10.20.22.1.1', '2023-05-01');
        $this->appendVersionedTemplateId($root, '2.16.840.1.113883.10.20.22.1.1', '2015-08-01');
        $this->appendVersionedTemplateId($root, $docOid, '2015-08-01');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid === '') {
            $facilityOid = '2.16.840.1.113883.19.5.99999.1';
        }

        $this->appendId($root, $facilityOid, 'OE-DOC-0001');

        $code = $this->createElement('code');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $code->setAttribute('code', $docCode);
        $code->setAttribute('displayName', $docName);
        $root->appendChild($code);

        $root->appendChild($this->createElement('title', $docName));

        $createdTime = $this->xpathValue('/CCDA/created_time_timezone');
        $effectiveTime = $this->createElement('effectiveTime');
        $effectiveTime->setAttribute('value', $this->formatTimestamp($createdTime));
        $root->appendChild($effectiveTime);

        $confidentiality = $this->createElement('confidentialityCode');
        $confidentiality->setAttribute('displayName', 'Normal');
        $confidentiality->setAttribute('code', 'N');
        $confidentiality->setAttribute('codeSystem', '2.16.840.1.113883.5.25');
        $confidentiality->setAttribute('codeSystemName', 'Confidentiality Code');
        $root->appendChild($confidentiality);

        $languageCode = $this->createElement('languageCode');
        $languageCode->setAttribute('code', 'en-US');
        $root->appendChild($languageCode);

        $setId = $this->createElement('setId');
        $setId->setAttribute('root', $facilityOid);
        $setId->setAttribute('extension', 'sOE-DOC-0001');
        $root->appendChild($setId);

        $versionNumber = $this->createElement('versionNumber');
        $versionNumber->setAttribute('value', '1');
        $root->appendChild($versionNumber);
    }

    private function renderRecordTarget(DOMElement $root): void
    {
        $recordTarget = $this->createElement('recordTarget');
        $patientRole = $this->createElement('patientRole');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid === '') {
            $facilityOid = '2.16.840.1.113883.19.5.99999.1';
        }

        $patientUuid = $this->xpathValue('/CCDA/patient/uuid');
        $this->appendId($patientRole, $facilityOid, $patientUuid);

        $this->appendPatientAddress($patientRole);
        $this->appendPatientTelecoms($patientRole);
        $this->appendPatientDemographics($patientRole);
        $this->appendProviderOrganization($patientRole);

        $recordTarget->appendChild($patientRole);
        $root->appendChild($recordTarget);
    }

    private function appendPatientAddress(DOMElement $patientRole): void
    {
        $addr = $this->createElement('addr');
        $use = $this->xpathValue('/CCDA/patient/use');
        if ($use !== '') {
            $addr->setAttribute('use', $use);
        }

        $street = $this->xpathValue('/CCDA/patient/street[1]');
        if ($street !== '') {
            $addr->appendChild($this->createElement('streetAddressLine', $street));
        }

        $city = $this->xpathValue('/CCDA/patient/city');
        if ($city !== '') {
            $addr->appendChild($this->createElement('city', $city));
        }

        $state = $this->xpathValue('/CCDA/patient/state');
        if ($state !== '') {
            $addr->appendChild($this->createElement('state', $state));
        }

        $postalCode = $this->xpathValue('/CCDA/patient/postalCode');
        if ($postalCode !== '') {
            $addr->appendChild($this->createElement('postalCode', $postalCode));
        }

        $country = $this->xpathValue('/CCDA/patient/country');
        $addr->appendChild($this->createElement('country', $country !== '' ? $country : 'US'));

        $useablePeriod = $this->output->createElement('useablePeriod');
        $useablePeriod->setAttributeNS(self::NS_XSI, 'xsi:type', 'IVL_TS');
        $low = $this->createElement('low');
        $low->setAttribute('value', date('Ymd'));
        $useablePeriod->appendChild($low);
        $addr->appendChild($useablePeriod);

        $patientRole->appendChild($addr);
    }

    private function appendPatientTelecoms(DOMElement $patientRole): void
    {
        $phoneHome = $this->xpathValue('/CCDA/patient/phone_home');
        if ($phoneHome !== '') {
            $telecom = $this->createElement('telecom');
            $telecom->setAttribute('value', 'tel:' . $phoneHome);
            $telecom->setAttribute('use', 'HP');
            $patientRole->appendChild($telecom);
        }

        $phoneMobile = $this->xpathValue('/CCDA/patient/phone_mobile');
        if ($phoneMobile !== '') {
            $telecom = $this->createElement('telecom');
            $telecom->setAttribute('value', 'tel:' . $phoneMobile);
            $telecom->setAttribute('use', 'MC');
            $patientRole->appendChild($telecom);
        }

        $phoneWork = $this->xpathValue('/CCDA/patient/phone_work');
        if ($phoneWork !== '') {
            $telecom = $this->createElement('telecom');
            $telecom->setAttribute('value', 'tel:' . $phoneWork);
            $telecom->setAttribute('use', 'WP');
            $patientRole->appendChild($telecom);
        }

        $phoneEmergency = $this->xpathValue('/CCDA/patient/phone_emergency');
        if ($phoneEmergency !== '') {
            $telecom = $this->createElement('telecom');
            $telecom->setAttribute('value', 'tel:' . $phoneEmergency);
            $telecom->setAttribute('use', 'EC');
            $patientRole->appendChild($telecom);
        }

        $email = $this->xpathValue('/CCDA/patient/email');
        if ($email !== '') {
            $telecom = $this->createElement('telecom');
            $telecom->setAttribute('value', 'mailto:' . $email);
            $patientRole->appendChild($telecom);
        }
    }

    private function appendPatientDemographics(DOMElement $patientRole): void
    {
        $patient = $this->createElement('patient');

        $name = $this->createElement('name');
        $name->setAttribute('use', 'L');
        $lname = $this->xpathValue('/CCDA/patient/lname');
        $fname = $this->xpathValue('/CCDA/patient/fname');
        if ($lname !== '') {
            $name->appendChild($this->createElement('family', $lname));
        }
        if ($fname !== '') {
            $name->appendChild($this->createElement('given', $fname));
        }
        $patient->appendChild($name);

        $genderCode = $this->xpathValue('/CCDA/patient/gender_code');
        $gender = $this->xpathValue('/CCDA/patient/gender');
        $adminGender = $this->createElement('administrativeGenderCode');
        $adminGender->setAttribute('code', $genderCode);
        $adminGender->setAttribute('codeSystem', '2.16.840.1.113883.5.1');
        $adminGender->setAttribute('codeSystemName', 'HL7 AdministrativeGender');
        $adminGender->setAttribute('displayName', strtoupper($gender));
        $patient->appendChild($adminGender);

        $dob = $this->xpathValue('/CCDA/patient/dob');
        $birthTime = $this->createElement('birthTime');
        $birthTime->setAttribute('value', $dob);
        $patient->appendChild($birthTime);

        $maritalStatus = $this->xpathValue('/CCDA/patient/status');
        $maritalStatusCode = $this->xpathValue('/CCDA/patient/status_code');
        if ($maritalStatus !== '' || $maritalStatusCode !== '') {
            $maritalEl = $this->createElement('maritalStatusCode');
            $maritalEl->setAttribute('code', $maritalStatusCode !== '' ? substr($maritalStatusCode, 0, 1) : '');
            $maritalEl->setAttribute('displayName', strtoupper($maritalStatus));
            $maritalEl->setAttribute('codeSystem', '2.16.840.1.113883.5.2');
            $maritalEl->setAttribute('codeSystemName', 'HL7 Marital Status');
            $patient->appendChild($maritalEl);
        }

        $raceCode = $this->xpathValue('/CCDA/patient/race_code');
        $race = $this->xpathValue('/CCDA/patient/race');
        $raceEl = $this->createElement('raceCode');
        $raceEl->setAttribute('displayName', $race);
        $raceEl->setAttribute('code', $raceCode);
        $raceEl->setAttribute('codeSystem', '2.16.840.1.113883.6.238');
        $raceEl->setAttribute('codeSystemName', 'Race and Ethnicity - CDC');
        $patient->appendChild($raceEl);

        $sdtcRace = $this->output->createElementNS(self::NS_SDTC, 'sdtc:raceCode');
        $raceGroupCode = $this->xpathValue('/CCDA/patient/race_group_code');
        if ($raceGroupCode !== '') {
            $sdtcRace->setAttribute('code', $raceGroupCode);
            $sdtcRace->setAttribute('codeSystem', '2.16.840.1.113883.6.238');
        } else {
            $sdtcRace->setAttribute('nullFlavor', 'UNK');
        }
        $patient->appendChild($sdtcRace);

        $ethnicityCode = $this->xpathValue('/CCDA/patient/ethnicity_code');
        $ethnicity = $this->xpathValue('/CCDA/patient/ethnicity');
        $ethnicEl = $this->createElement('ethnicGroupCode');
        $ethnicEl->setAttribute('displayName', $ethnicity);
        $ethnicEl->setAttribute('code', $ethnicityCode);
        $ethnicEl->setAttribute('codeSystem', '2.16.840.1.113883.6.238');
        $ethnicEl->setAttribute('codeSystemName', 'Race and Ethnicity - CDC');
        $patient->appendChild($ethnicEl);

        $this->appendLanguageCommunication($patient);

        $patientRole->appendChild($patient);
    }

    private function appendLanguageCommunication(DOMElement $patient): void
    {
        $langCode = $this->xpathValue('/CCDA/patient/language_code');
        if ($langCode === '') {
            return;
        }

        $langComm = $this->createElement('languageCommunication');

        $code = $this->createElement('languageCode');
        $code->setAttribute('code', $langCode . '-US');
        $langComm->appendChild($code);

        $modeCode = $this->createElement('modeCode');
        $modeCode->setAttribute('displayName', 'Expressed spoken');
        $modeCode->setAttribute('code', 'ESP');
        $modeCode->setAttribute('codeSystem', '2.16.840.1.113883.5.60');
        $modeCode->setAttribute('codeSystemName', 'LanguageAbilityMode');
        $langComm->appendChild($modeCode);

        $proficiency = $this->createElement('proficiencyLevelCode');
        $proficiency->setAttribute('code', 'G');
        $proficiency->setAttribute('displayName', 'Good');
        $proficiency->setAttribute('codeSystem', '2.16.840.1.113883.5.61');
        $proficiency->setAttribute('codeSystemName', 'LanguageAbilityProficiency');
        $langComm->appendChild($proficiency);

        $pref = $this->createElement('preferenceInd');
        $pref->setAttribute('value', 'true');
        $langComm->appendChild($pref);

        $patient->appendChild($langComm);
    }

    private function appendProviderOrganization(DOMElement $patientRole): void
    {
        $provOrg = $this->createElement('providerOrganization');

        $npi = $this->xpathValue('/CCDA/encounter_provider/facility_npi');
        $id = $this->createElement('id');
        $id->setAttribute('root', '2.16.840.1.113883.4.6');
        $id->setAttribute('extension', $npi);
        $provOrg->appendChild($id);

        $name = $this->xpathValue('/CCDA/encounter_provider/facility_name');
        $provOrg->appendChild($this->createElement('name', $name !== '' ? $name : null));

        $phone = $this->xpathValue('/CCDA/encounter_provider/facility_phone');
        $telecom = $this->createElement('telecom');
        $telecom->setAttribute('use', 'WP');
        $telecom->setAttribute('value', $phone);
        $provOrg->appendChild($telecom);

        $addr = $this->createElement('addr');
        $addr->setAttribute('use', 'WP');
        $country = $this->xpathValue('/CCDA/encounter_provider/facility_country_code');
        $addr->appendChild($this->createElement('country', $country !== '' ? $country : 'US'));
        $addr->appendChild($this->createElement('state', $this->xpathValue('/CCDA/encounter_provider/facility_state') ?: null));
        $addr->appendChild($this->createElement('city', $this->xpathValue('/CCDA/encounter_provider/facility_city') ?: null));
        $addr->appendChild($this->createElement('postalCode', $this->xpathValue('/CCDA/encounter_provider/facility_postal_code') ?: null));
        $addr->appendChild($this->createElement('streetAddressLine', $this->xpathValue('/CCDA/encounter_provider/facility_street') ?: null));
        $provOrg->appendChild($addr);

        $patientRole->appendChild($provOrg);
    }

    private function renderAuthor(DOMElement $root): void
    {
        $author = $this->createElement('author');

        $createdTime = $this->xpathValue('/CCDA/created_time_timezone');
        $time = $this->createElement('time');
        $time->setAttribute('value', $this->formatTimestamp($createdTime));
        $author->appendChild($time);

        $assignedAuthor = $this->createElement('assignedAuthor');

        $npi = $this->xpathValue('/CCDA/author/npi');
        $authorUuid = $this->xpathValue('/CCDA/author/id');
        $id = $this->createElement('id');
        if ($npi !== '') {
            $id->setAttribute('root', '2.16.840.1.113883.4.6');
            $id->setAttribute('extension', $npi);
        } else {
            $id->setAttribute('root', $authorUuid);
            $id->setAttribute('extension', 'NI');
        }
        $assignedAuthor->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', $this->xpathValue('/CCDA/author/physician_type_code'));
        $code->setAttribute('displayName', $this->xpathValue('/CCDA/author/physician_type'));
        $code->setAttribute('codeSystem', $this->xpathValue('/CCDA/author/physician_type_system'));
        $code->setAttribute('codeSystemName', $this->xpathValue('/CCDA/author/physician_type_system_name'));
        $assignedAuthor->appendChild($code);

        $addr = $this->createElement('addr');
        $addr->setAttribute('use', 'WP');
        $country = $this->xpathValue('/CCDA/author/country');
        $addr->appendChild($this->createElement('country', $country !== '' ? $country : 'US'));
        $addr->appendChild($this->createElement('state', $this->xpathValue('/CCDA/author/state') ?: null));
        $addr->appendChild($this->createElement('city', $this->xpathValue('/CCDA/author/city') ?: null));
        $addr->appendChild($this->createElement('postalCode', $this->xpathValue('/CCDA/author/postalCode') ?: null));
        $addr->appendChild($this->createElement('streetAddressLine', $this->xpathValue('/CCDA/author/streetAddressLine') ?: null));
        $assignedAuthor->appendChild($addr);

        $assignedPerson = $this->createElement('assignedPerson');
        $name = $this->createElement('name');
        $lname = $this->xpathValue('/CCDA/author/lname');
        $fname = $this->xpathValue('/CCDA/author/fname');
        if ($lname !== '') {
            $name->appendChild($this->createElement('family', $lname));
        }
        if ($fname !== '') {
            $name->appendChild($this->createElement('given', $fname));
        }
        $assignedPerson->appendChild($name);
        $assignedAuthor->appendChild($assignedPerson);

        $this->appendRepresentedOrganization($assignedAuthor);

        $author->appendChild($assignedAuthor);
        $root->appendChild($author);
    }

    private function appendRepresentedOrganization(DOMElement $assignedAuthor): void
    {
        $repOrg = $this->createElement('representedOrganization');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid === '') {
            $facilityOid = '2.16.840.1.113883.19.5.99999.1';
        }

        $id = $this->createElement('id');
        $id->setAttribute('root', $facilityOid);
        $repOrg->appendChild($id);

        $name = $this->xpathValue('/CCDA/encounter_provider/facility_name');
        $repOrg->appendChild($this->createElement('name', $name !== '' ? $name : null));

        $phone = $this->xpathValue('/CCDA/encounter_provider/facility_phone');
        if ($phone !== '') {
            $telecom = $this->createElement('telecom');
            $telecom->setAttribute('value', 'tel:' . $phone);
            $telecom->setAttribute('use', 'WP');
            $repOrg->appendChild($telecom);
        }

        $addr = $this->createElement('addr');
        $addr->setAttribute('use', 'WP');
        $country = $this->xpathValue('/CCDA/encounter_provider/facility_country_code');
        $addr->appendChild($this->createElement('country', $country !== '' ? $country : 'US'));
        $addr->appendChild($this->createElement('state', $this->xpathValue('/CCDA/encounter_provider/facility_state') ?: null));
        $addr->appendChild($this->createElement('city', $this->xpathValue('/CCDA/encounter_provider/facility_city') ?: null));
        $addr->appendChild($this->createElement('postalCode', $this->xpathValue('/CCDA/encounter_provider/facility_postal_code') ?: null));
        $addr->appendChild($this->createElement('streetAddressLine', $this->xpathValue('/CCDA/encounter_provider/facility_street') ?: null));
        $repOrg->appendChild($addr);

        $assignedAuthor->appendChild($repOrg);
    }

    private function renderCustodian(DOMElement $root): void
    {
        $custodian = $this->createElement('custodian');
        $assignedCustodian = $this->createElement('assignedCustodian');
        $repCustOrg = $this->createElement('representedCustodianOrganization');

        $npi = $this->xpathValue('/CCDA/encounter_provider/facility_npi');
        $id = $this->createElement('id');
        $id->setAttribute('root', '2.16.840.1.113883.4.6');
        $id->setAttribute('extension', $npi);
        $repCustOrg->appendChild($id);

        $name = $this->xpathValue('/CCDA/custodian/name');
        $repCustOrg->appendChild($this->createElement('name', $name));

        $phone = $this->xpathValue('/CCDA/custodian/telecom');
        $telecom = $this->createElement('telecom');
        $telecom->setAttribute('value', 'tel:' . $phone);
        $telecom->setAttribute('use', 'WP');
        $repCustOrg->appendChild($telecom);

        $addr = $this->createElement('addr');
        $country = $this->xpathValue('/CCDA/custodian/country');
        $addr->appendChild($this->createElement('country', $country !== '' ? $country : 'US'));
        $addr->appendChild($this->createElement('state', $this->xpathValue('/CCDA/custodian/state') ?: null));
        $addr->appendChild($this->createElement('city', $this->xpathValue('/CCDA/custodian/city') ?: null));
        $addr->appendChild($this->createElement('postalCode', $this->xpathValue('/CCDA/custodian/postalCode') ?: null));
        $addr->appendChild($this->createElement('streetAddressLine', $this->xpathValue('/CCDA/custodian/streetAddressLine') ?: null));
        $repCustOrg->appendChild($addr);

        $assignedCustodian->appendChild($repCustOrg);
        $custodian->appendChild($assignedCustodian);
        $root->appendChild($custodian);
    }

    private function renderInformationRecipient(DOMElement $root): void
    {
        $fname = $this->xpathValue('/CCDA/information_recipient/fname');
        $lname = $this->xpathValue('/CCDA/information_recipient/lname');
        $org = $this->xpathValue('/CCDA/information_recipient/organization');

        $infoRecipient = $this->createElement('informationRecipient');
        $intendedRecipient = $this->createElement('intendedRecipient');

        $recipient = $this->createElement('informationRecipient');
        $name = $this->createElement('name');
        $name->appendChild($this->createElement('family', $lname !== '' ? $lname : null));
        if ($fname !== '') {
            $name->appendChild($this->createElement('given', $fname));
        }
        $recipient->appendChild($name);
        $intendedRecipient->appendChild($recipient);

        $receivedOrg = $this->createElement('receivedOrganization');
        $receivedOrg->appendChild($this->createElement('name', $org !== '' ? $org : 'org'));
        $intendedRecipient->appendChild($receivedOrg);

        $infoRecipient->appendChild($intendedRecipient);
        $root->appendChild($infoRecipient);
    }

    private function renderParticipants(DOMElement $root): void
    {
        $participants = $this->xpath('/CCDA/document_participants/participant');
        foreach ($participants as $participantEl) {
            $this->renderParticipant($root, $participantEl);
        }
    }

    private function renderParticipant(DOMElement $root, DOMElement $participantEl): void
    {
        $type = $this->xpathValue('type', $participantEl);
        if ($type === '') {
            return;
        }

        $participant = $this->createElement('participant');
        $participant->setAttribute('typeCode', $type);

        $this->appendTemplateId($participant, '2.16.840.1.113883.10.20.22.5.8', '2023-05-01');

        $dateTime = $this->xpathValue('date_time', $participantEl);
        $time = $this->createElement('time');
        $time->setAttribute('value', $this->formatTimestamp($dateTime));
        $participant->appendChild($time);

        $associatedEntity = $this->createElement('associatedEntity');
        $associatedEntity->setAttribute('classCode', 'ASSIGNED');

        $orgId = $this->xpathValue('organization_id', $participantEl);
        $orgNpi = $this->xpathValue('organization_npi', $participantEl);
        $id = $this->createElement('id');
        $id->setAttribute('root', $orgId);
        $id->setAttribute('extension', $orgNpi !== '' ? $orgNpi : 'NI');
        $associatedEntity->appendChild($id);

        $code = $this->createElement('code');
        $taxonomy = $this->xpathValue('organization_taxonomy', $participantEl);
        if ($taxonomy !== '') {
            $code->setAttribute('code', $taxonomy);
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.101');
            $taxDesc = $this->xpathValue('organization_taxonomy_desc', $participantEl);
            if ($taxDesc !== '') {
                $code->setAttribute('displayName', $taxDesc);
            }
        } else {
            $code->setAttribute('nullFlavor', 'UNK');
        }
        $associatedEntity->appendChild($code);

        $addrUse = $this->xpathValue('address_use', $participantEl);
        $addr = $this->createElement('addr');
        if ($addrUse !== '') {
            $addr->setAttribute('use', $addrUse);
        }
        $country = $this->xpathValue('country', $participantEl);
        $addr->appendChild($this->createElement('country', $country !== '' ? $country : 'US'));
        $associatedEntity->appendChild($addr);

        $fname = $this->xpathValue('fname', $participantEl);
        $lname = $this->xpathValue('lname', $participantEl);
        if ($fname !== '' || $lname !== '') {
            $assocPerson = $this->createElement('associatedPerson');
            $assocPerson->setAttribute('classCode', 'PSN');
            $assocPerson->setAttribute('determinerCode', 'INSTANCE');
            $name = $this->createElement('name');
            if ($lname !== '') {
                $name->appendChild($this->createElement('family', $lname));
            }
            if ($fname !== '') {
                $name->appendChild($this->createElement('given', $fname));
            }
            $assocPerson->appendChild($name);
            $associatedEntity->appendChild($assocPerson);
        }

        $participant->appendChild($associatedEntity);
        $root->appendChild($participant);
    }

    private function renderDocumentationOf(DOMElement $root): void
    {
        $documentationOf = $this->createElement('documentationOf');
        $documentationOf->setAttribute('typeCode', 'DOC');

        $serviceEvent = $this->createElement('serviceEvent');
        $serviceEvent->setAttribute('classCode', 'PCPR');

        $code = $this->createElement('code');
        $code->setAttribute('nullFlavor', 'UNK');
        $serviceEvent->appendChild($code);

        $effectiveTime = $this->createElement('effectiveTime');
        $timeStart = $this->xpathValue('/CCDA/time_start');
        $timeEnd = $this->xpathValue('/CCDA/time_end');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatTimestamp($timeStart));
        $effectiveTime->appendChild($low);
        $high = $this->createElement('high');
        $high->setAttribute('value', $this->formatTimestamp($timeEnd));
        $effectiveTime->appendChild($high);
        $serviceEvent->appendChild($effectiveTime);

        $this->renderPerformer($serviceEvent);

        $documentationOf->appendChild($serviceEvent);
        $root->appendChild($documentationOf);
    }

    private function renderPerformer(DOMElement $serviceEvent): void
    {
        $performer = $this->createElement('performer');
        $performer->setAttribute('typeCode', 'PRF');

        $assignedEntity = $this->createElement('assignedEntity');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid === '') {
            $facilityOid = '2.16.840.1.113883.19.5.99999.1';
        }

        $providerNpi = $this->xpathValue('/CCDA/primary_care_provider/provider/npi');
        $providerTableId = $this->xpathValue('/CCDA/primary_care_provider/provider/table_id');
        $id = $this->createElement('id');
        if ($providerNpi !== '') {
            $id->setAttribute('root', '2.16.840.1.113883.4.6');
            $id->setAttribute('extension', $providerNpi);
        } else {
            $id->setAttribute('root', $facilityOid);
            $id->setAttribute('extension', $providerTableId !== '' ? $providerTableId : 'NI');
        }
        $assignedEntity->appendChild($id);

        $taxonomy = $this->xpathValue('/CCDA/primary_care_provider/provider/taxonomy');
        $taxonomyDesc = $this->xpathValue('/CCDA/primary_care_provider/provider/taxonomy_description');
        $code = $this->createElement('code');
        $code->setAttribute('code', $taxonomy !== '' ? $taxonomy : $this->xpathValue('/CCDA/author/physician_type_code'));
        $code->setAttribute('displayName', $taxonomyDesc !== '' ? $taxonomyDesc : $this->xpathValue('/CCDA/author/physician_type'));
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.101');
        $code->setAttribute('codeSystemName', 'NUCC Health Care Provider Taxonomy');
        $originalText = $this->createElement('originalText', 'Care Team Member');
        $code->appendChild($originalText);
        $assignedEntity->appendChild($code);

        $addr = $this->createElement('addr');
        $street = $this->xpathValue('/CCDA/encounter_provider/facility_street');
        if ($street !== '') {
            $addr->appendChild($this->createElement('streetAddressLine', $street));
        }
        $city = $this->xpathValue('/CCDA/encounter_provider/facility_city');
        if ($city !== '') {
            $addr->appendChild($this->createElement('city', $city));
        }
        $state = $this->xpathValue('/CCDA/encounter_provider/facility_state');
        if ($state !== '') {
            $addr->appendChild($this->createElement('state', $state));
        }
        $postalCode = $this->xpathValue('/CCDA/encounter_provider/facility_postal_code');
        if ($postalCode !== '') {
            $addr->appendChild($this->createElement('postalCode', $postalCode));
        }
        $country = $this->xpathValue('/CCDA/encounter_provider/facility_country_code');
        $addr->appendChild($this->createElement('country', $country !== '' ? $country : 'US'));
        $assignedEntity->appendChild($addr);

        $phone = $this->xpathValue('/CCDA/encounter_provider/facility_phone');
        if ($phone !== '') {
            $telecom = $this->createElement('telecom');
            $telecom->setAttribute('value', 'tel:' . $phone);
            $assignedEntity->appendChild($telecom);
        }

        $assignedPerson = $this->createElement('assignedPerson');
        $name = $this->createElement('name');
        $lname = $this->xpathValue('/CCDA/author/lname');
        $fname = $this->xpathValue('/CCDA/author/fname');
        if ($lname !== '') {
            $name->appendChild($this->createElement('family', $lname));
        }
        if ($fname !== '') {
            $name->appendChild($this->createElement('given', $fname));
        }
        $assignedPerson->appendChild($name);
        $assignedEntity->appendChild($assignedPerson);

        $performer->appendChild($assignedEntity);
        $serviceEvent->appendChild($performer);
    }

    private function formatTimestamp(string $input): string
    {
        $input = trim($input);
        if ($input === '' || $input === '0000-00-00 00:00:00') {
            return 'Invalid date';
        }

        // Extract timezone offset before stripping characters (handles both + and -)
        $offset = '+0000';
        if (preg_match('/([+-]\d{4})$/', $input, $matches) === 1) {
            $offset = $matches[1];
            $input = substr($input, 0, -5);
        }

        // Strip formatting characters from date/time portion
        $input = str_replace([' ', '-', ':'], '', $input);

        if (strlen($input) >= 12) {
            return substr($input, 0, 12) . $offset;
        }
        return $input;
    }

    private function renderBody(DOMElement $root): void
    {
        $component = $this->createElement('component');
        $structuredBody = $this->createElement('structuredBody');

        $this->renderCareTeamSection($structuredBody);
        $this->renderAllergiesSection($structuredBody);
        $this->renderMedicationsSection($structuredBody);
        $this->renderProblemsSection($structuredBody);
        $this->renderProceduresSection($structuredBody);
        $this->renderResultsSection($structuredBody);
        $this->renderAdvanceDirectivesSection($structuredBody);
        $this->renderFunctionalStatusSection($structuredBody);
        $this->renderClinicalNoteSections($structuredBody);
        $this->renderEncountersSection($structuredBody);
        $this->renderImmunizationsSection($structuredBody);
        $this->renderPayersSection($structuredBody);
        $this->renderAssessmentSection($structuredBody);
        $this->renderPlanOfCareSection($structuredBody);
        $this->renderGoalsSection($structuredBody);
        $this->renderHealthConcernsSection($structuredBody);
        $this->renderReasonForReferralSection($structuredBody);
        $this->renderMentalStatusSection($structuredBody);
        $this->renderSocialHistorySection($structuredBody);
        $this->renderVitalSignsSection($structuredBody);
        $this->renderMedicalEquipmentSection($structuredBody);

        $component->appendChild($structuredBody);
        $root->appendChild($component);
    }

    private function renderCareTeamSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $providers = $this->xpath('/CCDA/care_team/provider');
        $isActive = $this->xpathValue('/CCDA/care_team/is_active');
        $hasActiveTeam = $isActive === 'active' && $providers->length > 0;

        if (!$hasActiveTeam) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.500', '2019-07-01');

        $section->appendChild($this->createLoincCode('85847-2', 'Patient Care Teams'));
        $section->appendChild($this->createElement('title', 'Patient Care Teams'));

        if (!$hasActiveTeam) {
            $section->appendChild($this->createElement('text', 'A Care Team is not assigned.'));
        } else {
            $this->appendCareTeamNarrative($section, $providers);
            $this->appendCareTeamEntry($section, $providers);
        }

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $providers
     */
    private function appendCareTeamNarrative(DOMElement $section, \DOMNodeList $providers): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Name', 'Role', 'Phone']);

        $index = 1;
        foreach ($providers as $provider) {
            $fname = $this->xpathValue('fname', $provider);
            $lname = $this->xpathValue('lname', $provider);
            $name = trim($fname . ' ' . $lname);
            $role = $this->xpathValue('role_display', $provider);
            $phone = $this->xpathValue('telecom', $provider);

            $this->appendTableRow($table, [$name, $role, $phone], 'teamMember' . $index);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $providers
     */
    private function appendCareTeamEntry(DOMElement $section, \DOMNodeList $providers): void
    {
        $entry = $this->createElement('entry');

        $organizer = $this->createElement('organizer');
        $organizer->setAttribute('classCode', 'CLUSTER');
        $organizer->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($organizer, '2.16.840.1.113883.10.20.22.4.500', '2019-07-01');

        // uniqueId
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $id = $this->createElement('id');
            $id->setAttribute('root', $facilityOid);
            $organizer->appendChild($id);
        }

        $organizer->appendChild($this->createLoincCode('86744-0', 'Care Team Information'));

        $this->appendStatusCode($organizer, ActStatus::Active);

        // effectiveTime from first provider's since date
        $providerSince = '';
        foreach ($providers as $provider) {
            $since = $this->xpathValue('provider_since', $provider);
            if ($since !== '') {
                $providerSince = $since;
                break;
            }
        }
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $providerSince !== '' ? $this->formatDateTime($providerSince) : '');
        $effectiveTime->appendChild($low);
        $organizer->appendChild($effectiveTime);

        // Author
        $authorEl = $this->xpath('/CCDA/care_team/author')->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($organizer, $authorEl);
        }

        // Provider components
        $index = 1;
        foreach ($providers as $provider) {
            $this->appendCareTeamProviderComponent($organizer, $provider, $index);
            $index++;
        }

        $entry->appendChild($organizer);
        $section->appendChild($entry);
    }

    private function appendCareTeamProviderComponent(DOMElement $organizer, DOMElement $provider, int $index): void
    {
        $component = $this->createElement('component');

        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'PCPR');
        $act->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.500.1', '2019-07-01');

        // uniqueId
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $id = $this->createElement('id');
            $id->setAttribute('root', $facilityOid);
            $act->appendChild($id);
        }

        $act->appendChild($this->createLoincCode('85847-2', 'Patient Care team information'));

        $status = $this->xpathValue('status', $provider);
        $this->appendStatusCode($act, ActStatus::tryFrom($status) ?? ActStatus::Active);

        $since = $this->xpathValue('provider_since', $provider);
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $since !== '' ? $this->formatDateTime($since) : '');
        $effectiveTime->appendChild($low);
        $act->appendChild($effectiveTime);

        // Performer
        $performer = $this->createElement('performer');
        $performer->setAttribute('typeCode', 'PRF');

        // Function code
        $roleCode = $this->xpathValue('role_code', $provider);
        $roleDisplay = $this->xpathValue('role_display', $provider);
        if ($roleCode !== '' || $roleDisplay !== '') {
            $functionCode = $this->output->createElement('functionCode');
            $functionCode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', 'urn:hl7-org:sdtc');
            if ($roleCode !== '') {
                $functionCode->setAttribute('code', $this->cleanCode($roleCode));
            }
            if ($roleDisplay !== '') {
                $functionCode->setAttribute('displayName', $roleDisplay);
            }
            $functionCode->setAttribute('codeSystem', '2.16.840.1.113883.6.101');
            $functionCode->setAttribute('codeSystemName', 'SNOMED CT');

            $origText = $this->createElement('originalText');
            $ref = $this->createElement('reference');
            $ref->setAttribute('value', '#teamMember' . $index);
            $origText->appendChild($ref);
            $functionCode->appendChild($origText);

            $performer->appendChild($functionCode);
        }

        // Assigned entity
        $assignedEntity = $this->createElement('assignedEntity');

        $npi = $this->xpathValue('npi', $provider);
        $tableId = $this->xpathValue('table_id', $provider);
        $idEl = $this->createElement('id');
        $idEl->setAttribute('root', $npi !== '' ? '2.16.840.1.113883.4.6' : ($facilityOid !== '' ? $facilityOid : 'NI'));
        $idEl->setAttribute('extension', $npi !== '' ? $npi : $tableId);
        $assignedEntity->appendChild($idEl);

        // Address
        $addr = $this->createElement('addr');
        $street = $this->xpathValue('street', $provider);
        $city = $this->xpathValue('city', $provider);
        $state = $this->xpathValue('state', $provider);
        $zip = $this->xpathValue('zip', $provider);
        $addr->appendChild($this->createElement('streetAddressLine', $street !== '' ? $street : null));
        $addr->appendChild($this->createElement('city', $city !== '' ? $city : null));
        $addr->appendChild($this->createElement('state', $state !== '' ? $state : null));
        $addr->appendChild($this->createElement('postalCode', $zip !== '' ? $zip : null));
        $addr->appendChild($this->createElement('country', 'US'));
        $assignedEntity->appendChild($addr);

        // Telecom
        $phone = $this->xpathValue('telecom', $provider);
        $telecom = $this->createElement('telecom');
        $telecom->setAttribute('use', 'WP');
        $telecom->setAttribute('value', $phone !== '' ? 'tel:' . $phone : '');
        $assignedEntity->appendChild($telecom);

        // Assigned person
        $assignedPerson = $this->createElement('assignedPerson');
        $name = $this->createElement('name');
        $fname = $this->xpathValue('fname', $provider);
        $lname = $this->xpathValue('lname', $provider);
        $name->appendChild($this->createElement('given', $fname !== '' ? $fname : null));
        $name->appendChild($this->createElement('family', $lname !== '' ? $lname : null));
        $assignedPerson->appendChild($name);
        $assignedEntity->appendChild($assignedPerson);

        $performer->appendChild($assignedEntity);
        $act->appendChild($performer);

        $component->appendChild($act);
        $organizer->appendChild($component);
    }

    private function formatDateTime(string $input): string
    {
        $input = trim($input);
        if ($input === '' || $input === '0000-00-00') {
            return '';
        }
        $input = str_replace(['-', ' ', ':'], '', $input);
        return $input;
    }

    private function renderAllergiesSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.6.1', '2015-08-01');

        $section->appendChild($this->createLoincCode('48765-2', 'Allergies, adverse reactions, alerts'));
        $section->appendChild($this->createElement('title', 'Allergies, adverse reactions, alerts'));

        $allergies = $this->xpath('/CCDA/allergies/allergy');
        if ($allergies->length === 0) {
            $section->appendChild($this->createElement('text', 'No known Allergies and Intolerances'));
            $this->appendNoKnownAllergiesEntry($section);
        } else {
            $this->appendAllergiesNarrative($section, $allergies);
            $index = 1;
            foreach ($allergies as $allergy) {
                $this->appendAllergyEntry($section, $allergy, $index);
                $index++;
            }
        }

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $allergies
     */
    private function appendAllergiesNarrative(DOMElement $section, \DOMNodeList $allergies): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Substance', 'Reaction', 'Severity', 'Status']);

        $index = 1;
        foreach ($allergies as $allergy) {
            $title = $this->xpathValue('title', $allergy);
            $reaction = $this->xpathValue('reaction_text', $allergy);
            $outcome = $this->xpathValue('outcome', $allergy);
            $status = $this->xpathValue('status_table', $allergy);

            $tbody = $this->createElement('tbody');
            $row = $this->createElement('tr');

            $cell1 = $this->createElement('td', $title);
            $cell1->setAttribute('ID', 'allergy' . $index);
            $row->appendChild($cell1);

            $cell2 = $this->createElement('td', $reaction !== '' ? $reaction : 'No Data Available');
            $cell2->setAttribute('ID', 'reaction' . $index);
            $row->appendChild($cell2);

            $row->appendChild($this->createElement('td', $outcome !== '' ? $outcome : 'No Data Available'));
            $row->appendChild($this->createElement('td', $status !== '' ? $status : 'No Data Available'));

            $tbody->appendChild($row);
            $table->appendChild($tbody);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendAllergyEntry(DOMElement $section, DOMElement $allergy, int $index): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'ACT');
        $act->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($act, '2.16.840.1.113883.10.20.22.4.30', '2015-08-01');

        $shaId = $this->xpathValue('sha_id', $allergy);
        $id = $this->xpathValue('id', $allergy);
        $idEl = $this->createElement('id');
        $idEl->setAttribute('root', $shaId !== '' ? $shaId : 'NI');
        $idEl->setAttribute('extension', $id);
        $act->appendChild($idEl);

        $code = $this->createElement('code');
        $code->setAttribute('code', 'CONC');
        $code->setAttribute('displayName', 'Concerns');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.6');
        $act->appendChild($code);

        $this->appendStatusCode($act, ActStatus::Active);

        $startDate = $this->xpathValue('startdate', $allergy);
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($startDate));
        $effectiveTime->appendChild($low);
        $act->appendChild($effectiveTime);

        $authorEl = $this->xpath('author', $allergy)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($act, $authorEl);
        }

        $this->appendAllergyObservation($act, $allergy, $index);

        $entry->appendChild($act);
        $section->appendChild($entry);
    }

    private function appendAllergyObservation(DOMElement $act, DOMElement $allergy, int $index): void
    {
        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'SUBJ');
        $entryRel->setAttribute('inversionInd', 'true');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.7', '2014-06-09');

        $shaExt = $this->xpathValue('sha_extension', $allergy);
        $id = $this->xpathValue('id', $allergy);
        $obsId = $this->createElement('id');
        $obsId->setAttribute('root', $shaExt !== '' ? $shaExt : '2a620155-9d11-439e-92b3-5d9815ff4ee8');
        $obsId->setAttribute('extension', $id !== '' ? $id . '1' : '');
        $obs->appendChild($obsId);

        $obsCode = $this->createElement('code');
        $obsCode->setAttribute('code', 'ASSERTION');
        $obsCode->setAttribute('displayName', 'Assertion');
        $obsCode->setAttribute('codeSystem', '2.16.840.1.113883.5.4');
        $obsCode->setAttribute('codeSystemName', 'ActCode');
        $obs->appendChild($obsCode);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $startDate = $this->xpathValue('startdate', $allergy);
        $obsEffTime = $this->createElement('effectiveTime');
        $obsLow = $this->createElement('low');
        $obsLow->setAttribute('value', $this->formatDateOnly($startDate));
        $obsEffTime->appendChild($obsLow);
        $obs->appendChild($obsEffTime);

        // Intolerance value
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', '420134006');
        $value->setAttribute('displayName', 'Propensity to adverse reactions to drug');
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', 'SNOMED CT');
        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#reaction' . $index);
        $origText->appendChild($ref);
        $value->appendChild($origText);
        $obs->appendChild($value);

        $authorEl = $this->xpath('author', $allergy)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        // Allergen participant
        $this->appendAllergenParticipant($obs, $allergy, $index);

        // Status observation
        $this->appendAllergyStatusObservation($obs, $allergy);

        // Reaction observation
        $this->appendAllergyReactionObservation($obs, $allergy);

        // Severity observation
        $this->appendAllergySeverityObservation($obs, $allergy);

        $entryRel->appendChild($obs);
        $act->appendChild($entryRel);
    }

    private function appendAllergenParticipant(DOMElement $obs, DOMElement $allergy, int $index): void
    {
        $participant = $this->createElement('participant');
        $participant->setAttribute('typeCode', 'CSM');

        $partRole = $this->createElement('participantRole');
        $partRole->setAttribute('classCode', 'MANU');

        $playingEntity = $this->createElement('playingEntity');
        $playingEntity->setAttribute('classCode', 'MMAT');

        $title = $this->xpathValue('title', $allergy);
        $rxnormCode = $this->xpathValue('rxnorm_code', $allergy);
        $rxnormText = $this->xpathValue('rxnorm_code_text', $allergy);
        $snomedCode = $this->xpathValue('snomed_code', $allergy);
        $snomedText = $this->xpathValue('snomed_code_text', $allergy);

        $code = $this->createElement('code');
        if ($rxnormText !== '') {
            $code->setAttribute('code', $this->cleanCode($rxnormCode));
            $code->setAttribute('displayName', $title);
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.88');
            $code->setAttribute('codeSystemName', 'RXNORM');
        } elseif ($snomedText !== '') {
            $code->setAttribute('code', $this->cleanCode($snomedCode));
            $code->setAttribute('displayName', $title);
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
            $code->setAttribute('codeSystemName', 'SNOMED CT');
        } else {
            $code->setAttribute('nullFlavor', 'UNK');
        }

        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#allergy' . $index);
        $origText->appendChild($ref);
        $code->appendChild($origText);

        $playingEntity->appendChild($code);
        $partRole->appendChild($playingEntity);
        $participant->appendChild($partRole);
        $obs->appendChild($participant);
    }

    private function appendAllergyStatusObservation(DOMElement $obs, DOMElement $allergy): void
    {
        $statusTable = $this->xpathValue('status_table', $allergy);
        $statusCode = $this->xpathValue('status_code', $allergy);
        if ($statusTable === '' && $statusCode === '') {
            return;
        }

        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'SUBJ');
        $entryRel->setAttribute('inversionInd', 'true');

        $statusObs = $this->createElement('observation');
        $statusObs->setAttribute('classCode', 'OBS');
        $statusObs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($statusObs, '2.16.840.1.113883.10.20.22.4.28');

        $statusObs->appendChild($this->createLoincCode('33999-4', 'Status'));
        $this->appendStatusCode($statusObs, ActStatus::Completed);

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CE');
        $value->setAttribute('code', $this->cleanCode($statusCode));
        $value->setAttribute('displayName', $statusTable);
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', 'SNOMED CT');
        $statusObs->appendChild($value);

        $entryRel->appendChild($statusObs);
        $obs->appendChild($entryRel);
    }

    private function appendAllergyReactionObservation(DOMElement $obs, DOMElement $allergy): void
    {
        $reactionText = $this->xpathValue('reaction_text', $allergy);
        $reactionCode = $this->xpathValue('reaction_code', $allergy);
        if ($reactionText === '' && $reactionCode === '') {
            return;
        }

        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'MFST');
        $entryRel->setAttribute('inversionInd', 'true');

        $reactionObs = $this->createElement('observation');
        $reactionObs->setAttribute('classCode', 'OBS');
        $reactionObs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($reactionObs, '2.16.840.1.113883.10.20.22.4.9', '2014-06-09');

        $id = $this->createElement('id');
        $id->setAttribute('root', '4adc1020-7b14-11db-9fe1-0800200c9a64');
        $reactionObs->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', 'ASSERTION');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.4');
        $reactionObs->appendChild($code);

        $this->appendStatusCode($reactionObs, ActStatus::Completed);

        $startDate = $this->xpathValue('startdate', $allergy);
        $endDate = $this->xpathValue('enddate', $allergy);
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($startDate));
        $effectiveTime->appendChild($low);
        if ($endDate !== '') {
            $high = $this->createElement('high');
            $high->setAttribute('value', $this->formatDateOnly($endDate));
            $effectiveTime->appendChild($high);
        }
        $reactionObs->appendChild($effectiveTime);

        $codeType = $this->xpathValue('reaction_code_type', $allergy);
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $this->cleanCode($reactionCode));
        $value->setAttribute('displayName', $reactionText);
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', $codeType !== '' ? $codeType : 'SNOMED CT');
        $reactionObs->appendChild($value);

        $entryRel->appendChild($reactionObs);
        $obs->appendChild($entryRel);
    }

    private function appendAllergySeverityObservation(DOMElement $obs, DOMElement $allergy): void
    {
        $outcome = $this->xpathValue('outcome', $allergy);
        $outcomeCode = $this->xpathValue('outcome_code', $allergy);
        if ($outcome === '' && $outcomeCode === '') {
            return;
        }

        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'SUBJ');
        $entryRel->setAttribute('inversionInd', 'true');

        $sevObs = $this->createElement('observation');
        $sevObs->setAttribute('classCode', 'OBS');
        $sevObs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($sevObs, '2.16.840.1.113883.10.20.22.4.8', '2014-06-09');

        $code = $this->createElement('code');
        $code->setAttribute('code', 'SEV');
        $code->setAttribute('displayName', 'Severity');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.4');
        $code->setAttribute('codeSystemName', 'ActCode');
        $sevObs->appendChild($code);

        $this->appendStatusCode($sevObs, ActStatus::Completed);

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $this->cleanCode($outcomeCode));
        $value->setAttribute('displayName', $outcome);
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', 'SNOMED CT');
        $sevObs->appendChild($value);

        $entryRel->appendChild($sevObs);
        $obs->appendChild($entryRel);
    }

    private function appendNoKnownAllergiesEntry(DOMElement $section): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'ACT');
        $act->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($act, '2.16.840.1.113883.10.20.22.4.30', '2015-08-01');

        $id = $this->createElement('id');
        $id->setAttribute('nullFlavor', 'UNK');
        $act->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', 'CONC');
        $code->setAttribute('displayName', 'Concerns');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.6');
        $act->appendChild($code);

        $this->appendStatusCode($act, ActStatus::Active);

        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', date('Ymd'));
        $effectiveTime->appendChild($low);
        $act->appendChild($effectiveTime);

        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'SUBJ');
        $entryRel->setAttribute('inversionInd', 'true');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');
        $obs->setAttribute('negationInd', 'true');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.7', '2014-06-09');

        $obsId = $this->createElement('id');
        $obsId->setAttribute('nullFlavor', 'UNK');
        $obs->appendChild($obsId);

        $obsCode = $this->createElement('code');
        $obsCode->setAttribute('code', 'ASSERTION');
        $obsCode->setAttribute('displayName', 'Assertion');
        $obsCode->setAttribute('codeSystem', '2.16.840.1.113883.5.4');
        $obsCode->setAttribute('codeSystemName', 'ActCode');
        $obs->appendChild($obsCode);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $obsEffTime = $this->createElement('effectiveTime');
        $obsLow = $this->createElement('low');
        $obsLow->setAttribute('value', date('Ymd'));
        $obsEffTime->appendChild($obsLow);
        $obs->appendChild($obsEffTime);

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', '419199007');
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', 'SNOMED-CT');
        $value->setAttribute('displayName', 'Allergy to substance (disorder)');
        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#reaction1');
        $origText->appendChild($ref);
        $value->appendChild($origText);
        $obs->appendChild($value);

        $participant = $this->createElement('participant');
        $participant->setAttribute('typeCode', 'CSM');
        $partRole = $this->createElement('participantRole');
        $partRole->setAttribute('classCode', 'MANU');
        $playingEntity = $this->createElement('playingEntity');
        $playingEntity->setAttribute('classCode', 'MMAT');
        $peCode = $this->createElement('code');
        $peCode->setAttribute('nullFlavor', 'NA');
        $playingEntity->appendChild($peCode);
        $partRole->appendChild($playingEntity);
        $participant->appendChild($partRole);
        $obs->appendChild($participant);

        $entryRel->appendChild($obs);
        $act->appendChild($entryRel);
        $entry->appendChild($act);
        $section->appendChild($entry);
    }

    private function renderMedicationsSection(DOMElement $structuredBody): void
    {
        [$component, $section] = $this->createSection(
            '2.16.840.1.113883.10.20.22.2.1.1',
            '2014-06-09',
            '10160-0',
            'History of medication use',
        );

        $medications = $this->xpath('/CCDA/medications/medication');
        $this->appendMedicationsNarrative($section, $medications);

        $index = 1;
        foreach ($medications as $med) {
            $this->appendMedicationEntry($section, $med, $index);
            $index++;
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $medications
     */
    private function appendMedicationsNarrative(DOMElement $section, \DOMNodeList $medications): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Medication Class', '# fills', 'Last fill date']);

        $index = 1;
        foreach ($medications as $med) {
            $this->appendTableRow(
                $table,
                [$this->xpathValue('drug', $med), '0', date('Y-m-d')],
                'medinfo' . $index,
            );
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendMedicationEntry(DOMElement $section, DOMElement $med, int $index): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $subAdmin = $this->createElement('substanceAdministration');
        $subAdmin->setAttribute('classCode', 'SBADM');
        // moodCode should probably be INT for active/prescribed, EVN for completed,
        // but Node.js hardcodes status to 'Completed' (serveccda.js:287) so we match that.
        $subAdmin->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($subAdmin, '2.16.840.1.113883.10.20.22.4.16', '2014-06-09');

        $shaExt = $this->xpathValue('sha_extension', $med);
        $ext = $this->xpathValue('extension', $med);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $subAdmin->appendChild($id);

        $direction = $this->xpathValue('direction', $med);
        $subAdmin->appendChild($this->createElement('text', $direction));

        $this->appendStatusCode($subAdmin, ActStatus::Completed);

        $startDate = $this->xpathValue('start_date', $med);
        $endDate = $this->xpathValue('end_date', $med);
        $effTime1 = $this->output->createElement('effectiveTime');
        $effTime1->setAttributeNS(self::NS_XSI, 'xsi:type', 'IVL_TS');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($startDate));
        $effTime1->appendChild($low);
        $high = $this->createElement('high');
        $high->setAttribute('value', $this->formatDateOnly($endDate));
        $effTime1->appendChild($high);
        $subAdmin->appendChild($effTime1);

        $dosage = $this->xpathValue('dosage', $med);
        $interval = $this->xpathValue('interval', $med);
        $effTime2 = $this->output->createElement('effectiveTime');
        $effTime2->setAttributeNS(self::NS_XSI, 'xsi:type', 'PIVL_TS');
        $effTime2->setAttribute('institutionSpecified', 'true');
        $effTime2->setAttribute('operator', 'A');
        $period = $this->createElement('period');
        $period->setAttribute('value', $dosage !== '' ? (string) (int) floatval($dosage) : '1');
        if ($interval !== '') {
            $period->setAttribute('unit', $interval);
        }
        $effTime2->appendChild($period);
        $subAdmin->appendChild($effTime2);

        $routeCode = $this->createElement('routeCode');
        $route = $this->mapRouteCode($this->xpathValue('route_code', $med));
        if ($route !== '') {
            $routeCode->setAttribute('code', $route);
            $routeCode->setAttribute('codeSystem', '2.16.840.1.113883.3.26.1.1');
        } else {
            $routeCode->setAttribute('nullFlavor', 'UNK');
        }
        $subAdmin->appendChild($routeCode);

        $size = $this->xpathValue('size', $med);
        $unit = $this->xpathValue('unit', $med);
        $doseQuantity = $this->createElement('doseQuantity');
        $sizeFloat = floatval($size);
        if ($sizeFloat > 0) {
            $doseQuantity->setAttribute('value', (string) $sizeFloat);
        }
        if ($unit !== '') {
            $doseQuantity->setAttribute('unit', $unit);
        }
        $subAdmin->appendChild($doseQuantity);

        $this->appendMedicationConsumable($subAdmin, $med, $index);
        $this->appendMedicationAuthor($subAdmin, $med);

        $entry->appendChild($subAdmin);
        $section->appendChild($entry);
    }

    private function appendMedicationConsumable(DOMElement $subAdmin, DOMElement $med, int $index): void
    {
        $consumable = $this->createElement('consumable');
        $mfgProduct = $this->createElement('manufacturedProduct');
        $mfgProduct->setAttribute('classCode', 'MANU');

        $this->appendVersionedTemplateId($mfgProduct, '2.16.840.1.113883.10.20.22.4.23', '2014-06-09');

        $shaExt = $this->xpathValue('sha_extension', $med);
        $ext = $this->xpathValue('extension', $med);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext . '1');
        $mfgProduct->appendChild($id);

        $mfgMaterial = $this->createElement('manufacturedMaterial');
        $drug = $this->xpathValue('drug', $med);
        $rxnorm = $this->xpathValue('rxnorm', $med);
        $code = $this->createElement('code');
        $code->setAttribute('code', $this->cleanCode($rxnorm));
        $code->setAttribute('displayName', $drug);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.88');
        $code->setAttribute('codeSystemName', 'RXNORM');
        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#medinfo' . $index);
        $origText->appendChild($ref);
        $code->appendChild($origText);
        $mfgMaterial->appendChild($code);

        $mfgProduct->appendChild($mfgMaterial);
        $consumable->appendChild($mfgProduct);
        $subAdmin->appendChild($consumable);
    }

    private function appendMedicationAuthor(DOMElement $subAdmin, DOMElement $med): void
    {
        $authorEl = $this->xpath('author', $med)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($subAdmin, $authorEl);
        }
    }

    private function formatDateOnly(string $input): string
    {
        $input = trim($input);
        if ($input === '' || $input === '0000-00-00') {
            return date('Ymd');
        }
        $input = str_replace(['-', ' ', ':'], '', $input);
        return substr($input, 0, 8);
    }

    private function renderProblemsSection(DOMElement $structuredBody): void
    {
        [$component, $section] = $this->createSection(
            '2.16.840.1.113883.10.20.22.2.5.1',
            '2015-08-01',
            '11450-4',
            'Problem List',
        );

        $problems = $this->xpath('/CCDA/problem_lists/problem');
        $this->appendProblemsNarrative($section, $problems);

        $index = 1;
        foreach ($problems as $problem) {
            $this->appendProblemEntry($section, $problem, $index);
            $index++;
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $problems
     */
    private function appendProblemsNarrative(DOMElement $section, \DOMNodeList $problems): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Concern', 'Last Observation', 'Reported']);

        $index = 1;
        foreach ($problems as $problem) {
            $title = $this->xpathValue('title', $problem);
            $startDate = $this->xpathValue('start_date_table', $problem);
            $dateOnly = $startDate !== '' ? substr(str_replace(['-', ' ', ':'], '', $startDate), 0, 10) : '';
            $formattedDate = $dateOnly !== '' ? substr($dateOnly, 0, 4) . '-' . substr($dateOnly, 4, 2) . '-' . substr($dateOnly, 6, 2) : '';

            $tbody = $this->createElement('tbody');
            $row = $this->createElement('tr');

            $cell1 = $this->createElement('td', $title);
            $cell1->setAttribute('ID', 'problem' . $index);
            $row->appendChild($cell1);

            $cell2 = $this->createElement('td', 'No Data Available');
            $cell2->setAttribute('ID', 'healthStatus' . $index);
            $row->appendChild($cell2);

            $row->appendChild($this->createElement('td', $formattedDate));

            $tbody->appendChild($row);
            $table->appendChild($tbody);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendProblemEntry(DOMElement $section, DOMElement $problem, int $index): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'ACT');
        $act->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($act, '2.16.840.1.113883.10.20.22.4.3', '2015-08-01');

        $shaExt = $this->xpathValue('sha_extension', $problem);
        $ext = $this->xpathValue('extension', $problem);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $act->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', 'CONC');
        $code->setAttribute('displayName', 'Concern');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.6');
        $code->setAttribute('codeSystemName', 'HL7ActClass');
        $act->appendChild($code);

        $this->appendStatusCode($act, ActStatus::Completed);

        $startDate = $this->xpathValue('start_date', $problem);
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($startDate));
        $effectiveTime->appendChild($low);
        $act->appendChild($effectiveTime);

        $authorEl = $this->xpath('author', $problem)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($act, $authorEl);
        }

        $this->appendProblemObservation($act, $problem, $index);

        $entry->appendChild($act);
        $section->appendChild($entry);
    }

    private function appendProblemObservation(DOMElement $act, DOMElement $problem, int $index): void
    {
        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'SUBJ');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.4', '2015-08-01');

        $shaExt = $this->xpathValue('sha_extension', $problem);
        $ext = $this->xpathValue('extension', $problem);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $obs->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', '64572001');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('displayName', 'Condition');
        $translation = $this->createElement('translation');
        $translation->setAttribute('code', '75323-6');
        $translation->setAttribute('displayName', 'Condition');
        $translation->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $translation->setAttribute('codeSystemName', 'LOINC');
        $code->appendChild($translation);
        $obs->appendChild($code);

        $text = $this->createElement('text');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#problem' . $index);
        $text->appendChild($ref);
        $obs->appendChild($text);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $startDate = $this->xpathValue('start_date', $problem);
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($startDate));
        $effectiveTime->appendChild($low);
        $obs->appendChild($effectiveTime);

        $title = $this->xpathValue('title', $problem);
        $problemCode = $this->xpathValue('code', $problem);
        $codeType = $this->xpathValue('code_type', $problem);
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $problemCode);
        $value->setAttribute('displayName', $title);
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', $codeType);
        $obs->appendChild($value);

        $authorEl = $this->xpath('author', $problem)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        $this->appendProblemStatus($obs, $problem);
        $this->appendAgeAtOnset($obs, $problem);
        $this->appendHealthStatus($obs, $index);

        $entryRel->appendChild($obs);
        $act->appendChild($entryRel);
    }

    private function appendProblemStatus(DOMElement $obs, DOMElement $problem): void
    {
        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'REFR');

        $statusObs = $this->createElement('observation');
        $statusObs->setAttribute('classCode', 'OBS');
        $statusObs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($statusObs, '2.16.840.1.113883.10.20.22.4.6');

        $shaExt = $this->xpathValue('sha_extension', $problem);
        $ext = $this->xpathValue('extension', $problem);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $statusObs->appendChild($id);

        $statusObs->appendChild($this->createLoincCode('33999-4', 'Status'));
        $this->appendStatusCode($statusObs, ActStatus::Completed);

        $startDate = $this->xpathValue('start_date', $problem);
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($startDate));
        $effectiveTime->appendChild($low);
        $statusObs->appendChild($effectiveTime);

        $statusTable = $this->xpathValue('status_table', $problem);
        $statusCode = $this->xpathValue('status_code', $problem);
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        if ($statusTable === 'Resolved') {
            $value->setAttribute('code', '413322009');
            $value->setAttribute('displayName', 'Resolved');
        } elseif ($statusTable === 'Inactive') {
            $value->setAttribute('code', '73425007');
            $value->setAttribute('displayName', 'Inactive');
        } else {
            $value->setAttribute('code', $statusCode !== '' ? $statusCode : '55561003');
            $value->setAttribute('displayName', $statusTable !== '' ? $statusTable : 'Active');
        }
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', 'SNOMED CT');
        $statusObs->appendChild($value);

        $entryRel->appendChild($statusObs);
        $obs->appendChild($entryRel);
    }

    private function appendHealthStatus(DOMElement $obs, int $index): void
    {
        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'REFR');

        $healthObs = $this->createElement('observation');
        $healthObs->setAttribute('classCode', 'OBS');
        $healthObs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($healthObs, '2.16.840.1.113883.10.20.22.4.5');

        $healthObs->appendChild($this->createLoincCode('11323-3', 'Health status'));

        $text = $this->createElement('text');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#healthStatus' . $index);
        $text->appendChild($ref);
        $healthObs->appendChild($text);

        $this->appendStatusCode($healthObs, ActStatus::Completed);

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', '81323004');
        $value->setAttribute('displayName', '');
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', 'SNOMED CT');
        $healthObs->appendChild($value);

        $entryRel->appendChild($healthObs);
        $obs->appendChild($entryRel);
    }

    private function appendAgeAtOnset(DOMElement $obs, DOMElement $problem): void
    {
        $age = $this->xpathValue('age', $problem);
        if ($age === '') {
            return;
        }

        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'SUBJ');
        $entryRel->setAttribute('inversionInd', 'true');

        $ageObs = $this->createElement('observation');
        $ageObs->setAttribute('classCode', 'OBS');
        $ageObs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($ageObs, '2.16.840.1.113883.10.20.22.4.31');

        $code = $this->createElement('code');
        $code->setAttribute('code', '445518008');
        $code->setAttribute('displayName', 'Age At Onset');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', 'SNOMED-CT');
        $ageObs->appendChild($code);

        $this->appendStatusCode($ageObs, ActStatus::Completed);

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'PQ');
        $value->setAttribute('value', $age);
        $value->setAttribute('unit', 'a');
        $ageObs->appendChild($value);

        $entryRel->appendChild($ageObs);
        $obs->appendChild($entryRel);
    }

    private function renderProceduresSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.7');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.7.1');

        $section->appendChild($this->createLoincCode('47519-4', 'History of Procedures'));
        $section->appendChild($this->createElement('title', 'History of Procedures'));

        $procedures = $this->xpath('/CCDA/procedures/procedure');
        $this->appendProceduresNarrative($section, $procedures);

        $index = 1;
        foreach ($procedures as $proc) {
            $this->appendProcedureEntry($section, $proc, $index);
            $index++;
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $procedures
     */
    private function appendProceduresNarrative(DOMElement $section, \DOMNodeList $procedures): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Service', 'Procedure code', 'Service date', 'Servicing provider', 'Phone#']);

        $index = 1;
        foreach ($procedures as $proc) {
            $description = $this->xpathValue('description', $proc);
            $code = $this->xpathValue('code', $proc);
            $date = $this->xpathValue('date', $proc);
            $fname = $this->xpathValue('fname', $proc);
            $lname = $this->xpathValue('lname', $proc);
            $provider = ($fname !== '' || $lname !== '') ? trim("$fname $lname") : 'No Data Available';

            $tbody = $this->createElement('tbody');
            $row = $this->createElement('tr');

            $cell1 = $this->createElement('td', $description);
            $cell1->setAttribute('ID', 'procedure' . $index);
            $row->appendChild($cell1);

            $row->appendChild($this->createElement('td', $code));
            $row->appendChild($this->createElement('td', $date));
            $row->appendChild($this->createElement('td', $provider));
            $row->appendChild($this->createElement('td', 'No Data Available'));

            $tbody->appendChild($row);
            $table->appendChild($tbody);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendProcedureEntry(DOMElement $section, DOMElement $proc, int $index): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $procedure = $this->createElement('procedure');
        $procedure->setAttribute('classCode', 'PROC');
        $procedure->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($procedure, '2.16.840.1.113883.10.20.22.4.14');

        $shaExt = $this->xpathValue('sha_extension', $proc);
        $ext = $this->xpathValue('extension', $proc);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $procedure->appendChild($id);

        $codeVal = $this->xpathValue('code', $proc);
        $description = $this->xpathValue('description', $proc);
        $codeType = $this->xpathValue('code_type', $proc);
        $code = $this->createElement('code');
        $code->setAttribute('code', $codeVal);
        $code->setAttribute('displayName', $description);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', $codeType);
        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#procedure' . $index);
        $origText->appendChild($ref);
        $code->appendChild($origText);
        $procedure->appendChild($code);

        $this->appendStatusCode($procedure, ActStatus::Completed);

        $date = $this->xpathValue('date', $proc);
        $dateFormatted = str_replace('-', '', $date);
        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('value', $dateFormatted);
        $procedure->appendChild($effTime);

        $authorEl = $this->xpath('author', $proc)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($procedure, $authorEl);
        }

        $entry->appendChild($procedure);
        $section->appendChild($entry);
    }

    private function renderResultsSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.3.1', '2015-08-01');

        $section->appendChild($this->createLoincCode('30954-2', 'Relevant Dx tests/lab data'));
        $section->appendChild($this->createElement('title', 'Relevant Dx tests/lab data'));

        $results = $this->xpath('/CCDA/results/result');
        $this->appendResultsNarrative($section, $results);
        $this->appendResultsEntry($section, $results);

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $results
     */
    private function appendResultsNarrative(DOMElement $section, \DOMNodeList $results): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Test/Result', 'Value', 'Units', 'Reference Range', 'Interpretation', 'Date']);

        $tbody = $this->createElement('tbody');

        // Single header row using first result's test name (matches Node.js behavior)
        $firstResult = $results->item(0);
        if ($firstResult instanceof DOMElement) {
            $testName = $this->xpathValue('test_name', $firstResult);
            $headerRow = $this->createElement('tr');
            $headerCell = $this->createElement('td', $testName);
            $headerCell->setAttribute('colspan', '7');
            $headerRow->appendChild($headerCell);
            $tbody->appendChild($headerRow);
        }

        $index = 1;
        foreach ($results as $result) {
            $dateOrdered = $this->xpathValue('date_ordered_table', $result);
            $dateDisplay = substr($dateOrdered, 0, 10); // Format: 2018-06-16

            $subtests = $this->xpath('subtest', $result);
            foreach ($subtests as $subtest) {
                $desc = $this->xpathValue('result_desc', $subtest);
                $value = $this->xpathValue('result_value', $subtest);
                $unit = $this->xpathValue('unit', $subtest);

                $row = $this->createElement('tr');

                $row->appendChild($this->createElement('td', $desc));

                $valueCell = $this->createElement('td', $value);
                $valueCell->setAttribute('ID', 'result' . $index);
                $row->appendChild($valueCell);

                $row->appendChild($this->createElement('td', $unit));
                $row->appendChild($this->createElement('td', 'No Data Available'));
                $row->appendChild($this->createElement('td', 'No Data Available'));
                $row->appendChild($this->createElement('td', $dateDisplay));

                $tbody->appendChild($row);
                $index++;
            }
        }

        $table->appendChild($tbody);
        $text->appendChild($table);
        $section->appendChild($text);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $results
     */
    private function appendResultsEntry(DOMElement $section, \DOMNodeList $results): void
    {
        $firstResult = $results->item(0);
        if (!$firstResult instanceof DOMElement) {
            return;
        }

        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $organizer = $this->createElement('organizer');
        $organizer->setAttribute('classCode', 'BATTERY');
        $organizer->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($organizer, '2.16.840.1.113883.10.20.22.4.1', '2015-08-01');

        // uniqueId element (comes before regular id per Node.js blue-button-generate)
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $organizer->appendChild($uniqueId);
        }

        $root = $this->xpathValue('root', $firstResult);
        $ext = $this->xpathValue('extension', $firstResult);
        $id = $this->createElement('id');
        $id->setAttribute('root', $root);
        $id->setAttribute('extension', $ext);
        $organizer->appendChild($id);

        $testCode = $this->xpathValue('test_code', $firstResult);
        $testName = $this->xpathValue('test_name', $firstResult);
        $code = $this->createElement('code');
        $code->setAttribute('code', $testCode);
        $code->setAttribute('displayName', $testName);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $organizer->appendChild($code);

        $this->appendStatusCode($organizer, ActStatus::Completed);

        $authorEl = $this->xpath('author', $firstResult)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($organizer, $authorEl);
        }

        // Add all subtests from all results as components
        $index = 1;
        foreach ($results as $result) {
            $subtests = $this->xpath('subtest', $result);
            $dateOrdered = $this->xpathValue('date_ordered', $result);
            foreach ($subtests as $subtest) {
                $this->appendResultComponent($organizer, $subtest, $dateOrdered, $index);
                $index++;
            }
        }

        $entry->appendChild($organizer);
        $section->appendChild($entry);
    }

    private function appendResultComponent(DOMElement $organizer, DOMElement $subtest, string $dateOrdered, int $index): void
    {
        $component = $this->createElement('component');
        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.2', '2015-08-01');

        $root = $this->xpathValue('root', $subtest);
        $ext = $this->xpathValue('extension', $subtest);
        $id = $this->createElement('id');
        $id->setAttribute('root', $root);
        $id->setAttribute('extension', $ext);
        $obs->appendChild($id);

        $code = $this->xpathValue('result_code', $subtest);
        $desc = $this->xpathValue('result_desc', $subtest);
        $codeEl = $this->createElement('code');
        $codeEl->setAttribute('code', $code);
        $codeEl->setAttribute('displayName', $desc);
        $codeEl->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $codeEl->setAttribute('codeSystemName', 'LOINC');
        $obs->appendChild($codeEl);

        $text = $this->createElement('text');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#result' . $index);
        $text->appendChild($ref);
        $obs->appendChild($text);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('value', $dateOrdered);
        $obs->appendChild($effTime);

        $resultValue = $this->xpathValue('result_value', $subtest);
        $unit = $this->xpathValue('unit', $subtest);
        $range = $this->xpathValue('range', $subtest);

        // Determine value type: PQ for numeric with unit, ST for string, CO for "NEGATIVE"
        $valueType = 'PQ';
        if (!is_numeric($resultValue) || $unit === '') {
            $valueType = 'ST';
        }
        if (strtoupper($resultValue) === 'NEGATIVE' || strtoupper($range) === 'NEGATIVE') {
            $valueType = 'CO';
        }

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', $valueType);
        if ($valueType === 'PQ') {
            $value->setAttribute('value', $resultValue);
            $value->setAttribute('unit', $unit);
        } elseif ($valueType === 'CO') {
            $value->setAttribute('code', '260385009');
            $value->setAttribute('codeSystemName', 'SNOMED-CT');
            $value->setAttribute('displayName', 'Negative');
            $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        } else {
            $value->nodeValue = $resultValue;
        }
        $obs->appendChild($value);

        // Interpretation code based on abnormal_flag
        $abnormalFlag = strtoupper($this->xpathValue('abnormal_flag', $subtest));
        if ($abnormalFlag !== '') {
            $interp = $this->createElement('interpretationCode');
            if ($abnormalFlag === 'NO') {
                $interp->setAttribute('code', 'N');
                $interp->setAttribute('displayName', 'Normal');
            } elseif ($abnormalFlag === 'YES') {
                $interp->setAttribute('code', 'A');
                $interp->setAttribute('displayName', 'Abnormal');
            }
            $interp->setAttribute('codeSystem', '2.16.840.1.113883.5.83');
            $obs->appendChild($interp);
        }

        // Reference range
        $low = $this->xpathValue('low', $subtest);
        $high = $this->xpathValue('high', $subtest);
        $refRange = $this->createElement('referenceRange');
        $obsRange = $this->createElement('observationRange');

        if ($low !== '' || $high !== '') {
            $rangeValue = $this->output->createElement('value');
            $rangeValue->setAttributeNS(self::NS_XSI, 'xsi:type', 'IVL_PQ');
            if ($low !== '') {
                $lowEl = $this->createElement('low');
                $lowEl->setAttribute('value', $low);
                if ($unit !== '') {
                    $lowEl->setAttribute('unit', $unit);
                }
                $rangeValue->appendChild($lowEl);
            }
            if ($high !== '') {
                $highEl = $this->createElement('high');
                $highEl->setAttribute('value', $high);
                if ($unit !== '') {
                    $highEl->setAttribute('unit', $unit);
                }
                $rangeValue->appendChild($highEl);
            }
            $obsRange->appendChild($rangeValue);
        } elseif ($range !== '') {
            $rangeText = $this->createElement('text', $range);
            $obsRange->appendChild($rangeText);
        } else {
            $rangeValue = $this->output->createElement('value');
            $rangeValue->setAttributeNS(self::NS_XSI, 'xsi:type', 'IVL_PQ');
            $obsRange->appendChild($rangeValue);
        }
        $refRange->appendChild($obsRange);
        $obs->appendChild($refRange);

        $component->appendChild($obs);
        $organizer->appendChild($component);
    }

    private function renderEncountersSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.22.1', '2015-08-01');

        $section->appendChild($this->createLoincCode('46240-8', 'Encounters'));
        $section->appendChild($this->createElement('title', 'Encounters'));

        $encounters = $this->xpath('/CCDA/encounter_list/encounter');
        $this->appendEncountersNarrative($section, $encounters);

        $index = 1;
        foreach ($encounters as $encounter) {
            $this->appendEncounterEntry($section, $encounter, $index);
            $index++;
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $encounters
     */
    private function appendEncountersNarrative(DOMElement $section, \DOMNodeList $encounters): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Type', 'Facility', 'Date of Service', 'Diagnosis/Complaint']);

        $index = 1;
        foreach ($encounters as $enc) {
            $description = $this->xpathValue('code_description', $enc);
            $reason = $this->xpathValue('encounter_reason', $enc);
            $displayText = "$description | $reason";
            $date = $this->xpathValue('date', $enc);
            // Format date as MM/DD/YYYY
            $dateFormatted = $this->formatEncounterDate($date);

            $tbody = $this->createElement('tbody');
            $row = $this->createElement('tr');

            $cell1 = $this->createElement('td', $displayText);
            $cell1->setAttribute('ID', 'Encounter' . $index);
            $row->appendChild($cell1);

            $row->appendChild($this->createElement('td', 'No Data Available'));
            $row->appendChild($this->createElement('td', $dateFormatted));
            $row->appendChild($this->createElement('td', 'No Data Available'));

            $tbody->appendChild($row);
            $table->appendChild($tbody);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function formatEncounterDate(string $date): string
    {
        // Input: "2001-06-06 21:30:39+0000"
        // Output: "06/06/2001"
        $parts = explode(' ', $date);
        $dateParts = explode('-', $parts[0]);
        if (count($dateParts) !== 3) {
            return '';
        }
        return sprintf('%s/%s/%s', $dateParts[1], $dateParts[2], $dateParts[0]);
    }

    private function appendEncounterEntry(DOMElement $section, DOMElement $enc, int $index): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $encounter = $this->createElement('encounter');
        $encounter->setAttribute('classCode', 'ENC');
        $encounter->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($encounter, '2.16.840.1.113883.10.20.22.4.49', '2015-08-01');

        $shaExt = $this->xpathValue('sha_extension', $enc);
        $ext = $this->xpathValue('extension', $enc);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $encounter->appendChild($id);

        $procedureCode = $this->xpathValue('encounter_procedures/procedures/code', $enc);
        $description = $this->xpathValue('code_description', $enc);
        $reason = $this->xpathValue('encounter_reason', $enc);
        $codeType = $this->xpathValue('encounter_procedures/procedures/code_type', $enc);
        $displayText = "$description | $reason";

        $code = $this->createElement('code');
        // Node.js uses encounter_procedures.procedures.code with fallback to 185347001
        $code->setAttribute('code', $procedureCode !== '' ? $procedureCode : '185347001');
        $code->setAttribute('displayName', $displayText);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', $codeType !== '' ? $codeType : 'SNOMED CT');

        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#Encounter' . $index);
        $origText->appendChild($ref);
        $code->appendChild($origText);

        $translation = $this->createElement('translation');
        $translation->setAttribute('code', 'AMB');
        $translation->setAttribute('displayName', 'Ambulatory');
        $translation->setAttribute('codeSystem', '2.16.840.1.113883.5.4');
        $translation->setAttribute('codeSystemName', 'ActCode');
        $code->appendChild($translation);

        $encounter->appendChild($code);

        $date = $this->xpathValue('date', $enc);
        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('value', $this->formatTimestamp($date));
        $encounter->appendChild($effTime);

        $this->appendEncounterPerformer($encounter, $enc);
        $this->appendEncounterLocation($encounter, $enc);

        $entry->appendChild($encounter);
        $section->appendChild($entry);
    }

    private function appendEncounterPerformer(DOMElement $encounter, DOMElement $enc): void
    {
        $performer = $this->createElement('performer');
        $assignedEntity = $this->createElement('assignedEntity');

        $npi = $this->xpathValue('npi', $enc);
        $id = $this->createElement('id');
        $id->setAttribute('root', '2.16.840.1.113883.4.6');
        $id->setAttribute('extension', $npi);
        $assignedEntity->appendChild($id);

        $code = $this->createElement('code');
        $physicianTypeCode = $this->xpathValue('physician_type_code', $enc);
        if ($physicianTypeCode !== '') {
            $code->setAttribute('code', $physicianTypeCode);
            $code->setAttribute('displayName', $this->xpathValue('physician_type', $enc));
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
            $code->setAttribute('codeSystemName', $this->xpathValue('physician_code_type', $enc));
        } else {
            $code->setAttribute('nullFlavor', 'UNK');
        }
        $assignedEntity->appendChild($code);

        $fname = $this->xpathValue('fname', $enc);
        $lname = $this->xpathValue('lname', $enc);
        $assignedPerson = $this->createElement('assignedPerson');
        $name = $this->createElement('name');
        if ($lname !== '') {
            $name->appendChild($this->createElement('family', $lname));
        }
        if ($fname !== '') {
            $name->appendChild($this->createElement('given', $fname));
        }
        $assignedPerson->appendChild($name);
        $assignedEntity->appendChild($assignedPerson);

        $performer->appendChild($assignedEntity);
        $encounter->appendChild($performer);
    }

    private function appendEncounterLocation(DOMElement $encounter, DOMElement $enc): void
    {
        $participant = $this->createElement('participant');
        $participant->setAttribute('typeCode', 'LOC');

        $role = $this->createElement('participantRole');
        $role->setAttribute('classCode', 'SDLOC');

        $this->appendTemplateId($role, '2.16.840.1.113883.10.20.22.4.32');

        $locationDetails = $this->xpathValue('location_details', $enc);
        $code = $this->createElement('code');
        $code->setAttribute('code', '1160-1');
        // Node.js service includes trailing space in location display
        $code->setAttribute('displayName', $locationDetails . ' ');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.259');
        $code->setAttribute('codeSystemName', 'HealthcareServiceLocation');
        $role->appendChild($code);

        $addr = $this->createElement('addr');
        $addr->appendChild($this->createElement('country', 'US'));
        $role->appendChild($addr);

        $playingEntity = $this->createElement('playingEntity');
        $playingEntity->setAttribute('classCode', 'PLC');
        $facilityName = $this->xpathValue('facility_name', $enc);
        $playingEntity->appendChild($this->createElement('name', $facilityName !== '' ? $facilityName : null));
        $role->appendChild($playingEntity);

        $participant->appendChild($role);
        $encounter->appendChild($participant);
    }

    private function renderImmunizationsSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.2');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.2.1');

        $section->appendChild($this->createLoincCode('11369-6', 'Immunizations'));
        $section->appendChild($this->createElement('title', 'Immunizations'));

        $immunizations = $this->xpath('/CCDA/immunizations/immunization');
        $this->appendImmunizationsNarrative($section, $immunizations);

        $index = 1;
        foreach ($immunizations as $imm) {
            $this->appendImmunizationEntry($section, $imm, $index);
            $index++;
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $immunizations
     */
    private function appendImmunizationsNarrative(DOMElement $section, \DOMNodeList $immunizations): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Vaccine', 'Date', 'Status']);

        $index = 1;
        foreach ($immunizations as $imm) {
            $codeText = $this->xpathValue('code_text', $imm);
            $administeredOn = $this->xpathValue('administered_on', $imm);
            $status = $this->xpathValue('status', $imm);
            // Node.js service displays "completed" as "complete"
            if ($status === 'completed') {
                $status = 'complete';
            }

            $tbody = $this->createElement('tbody');
            $row = $this->createElement('tr');

            $cell1 = $this->createElement('td', $codeText);
            $cell1->setAttribute('ID', 'immunization' . $index);
            $row->appendChild($cell1);

            $row->appendChild($this->createElement('td', $administeredOn));
            $row->appendChild($this->createElement('td', $status));

            $tbody->appendChild($row);
            $table->appendChild($tbody);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendImmunizationEntry(DOMElement $section, DOMElement $imm, int $index): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $subAdmin = $this->createElement('substanceAdministration');
        $subAdmin->setAttribute('classCode', 'SBADM');
        $subAdmin->setAttribute('moodCode', 'EVN');
        $subAdmin->setAttribute('negationInd', 'false');

        $this->appendVersionedTemplateId($subAdmin, '2.16.840.1.113883.10.20.22.4.52', '2015-08-01');

        $shaExt = $this->xpathValue('sha_extension', $imm);
        $ext = $this->xpathValue('extension', $imm);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $subAdmin->appendChild($id);

        $text = $this->createElement('text');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#immunization' . $index);
        $text->appendChild($ref);
        $subAdmin->appendChild($text);

        $this->appendStatusCode($subAdmin, ActStatus::Completed);

        $administeredFormatted = $this->xpathValue('administered_formatted', $imm);
        $effTime = $this->output->createElement('effectiveTime');
        $effTime->setAttributeNS(self::NS_XSI, 'xsi:type', 'IVL_TS');
        $low = $this->createElement('low');
        $low->setAttribute('value', $administeredFormatted);
        $effTime->appendChild($low);
        $subAdmin->appendChild($effTime);

        $routeCode = $this->createElement('routeCode');
        $route = $this->mapRouteCode($this->xpathValue('route_code', $imm));
        if ($route !== '') {
            $routeCode->setAttribute('code', $route);
            $routeCode->setAttribute('codeSystem', '2.16.840.1.113883.3.26.1.1');
        } else {
            $routeCode->setAttribute('nullFlavor', 'UNK');
        }
        $subAdmin->appendChild($routeCode);

        $this->appendImmunizationConsumable($subAdmin, $imm, $index);
        $this->appendImmunizationPerformer($subAdmin, $imm);

        $authorEl = $this->xpath('author', $imm)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($subAdmin, $authorEl);
        }

        $this->appendImmunizationEducation($subAdmin);

        $entry->appendChild($subAdmin);
        $section->appendChild($entry);
    }

    private function appendImmunizationEducation(DOMElement $subAdmin): void
    {
        $entryRelationship = $this->createElement('entryRelationship');
        $entryRelationship->setAttribute('typeCode', 'SUBJ');
        $entryRelationship->setAttribute('inversionInd', 'true');

        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'ACT');
        $act->setAttribute('moodCode', 'INT');

        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.20');

        $code = $this->createElement('code');
        $code->setAttribute('code', '171044003');
        $code->setAttribute('displayName', 'immunization education');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', 'SNOMED CT');
        $act->appendChild($code);

        $this->appendStatusCode($act, ActStatus::Completed);

        $entryRelationship->appendChild($act);
        $subAdmin->appendChild($entryRelationship);
    }

    private function appendImmunizationConsumable(DOMElement $subAdmin, DOMElement $imm, int $index): void
    {
        $consumable = $this->createElement('consumable');
        $mfgProduct = $this->createElement('manufacturedProduct');
        $mfgProduct->setAttribute('classCode', 'MANU');

        $this->appendVersionedTemplateId($mfgProduct, '2.16.840.1.113883.10.20.22.4.54', '2014-06-09');

        $id = $this->createElement('id');
        $id->setAttribute('nullFlavor', 'UNK');
        $mfgProduct->appendChild($id);

        $mfgMaterial = $this->createElement('manufacturedMaterial');
        $cvxCode = $this->xpathValue('cvx_code', $imm);
        $codeText = $this->xpathValue('code_text', $imm);
        $code = $this->createElement('code');
        $code->setAttribute('code', $cvxCode);
        $code->setAttribute('displayName', $codeText);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.12.292');
        $code->setAttribute('codeSystemName', 'CVX');
        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#imminfo' . $index);
        $origText->appendChild($ref);
        $code->appendChild($origText);
        $mfgMaterial->appendChild($code);

        $mfgProduct->appendChild($mfgMaterial);
        $consumable->appendChild($mfgProduct);
        $subAdmin->appendChild($consumable);
    }

    private function appendImmunizationPerformer(DOMElement $subAdmin, DOMElement $imm): void
    {
        $performer = $this->createElement('performer');
        $assignedEntity = $this->createElement('assignedEntity');

        $npi = $this->xpathValue('npi', $imm);
        $id = $this->createElement('id');
        $id->setAttribute('root', '2.16.840.1.113883.4.6');
        $id->setAttribute('extension', $npi);
        $assignedEntity->appendChild($id);

        $addr = $this->createElement('addr');
        $addr->appendChild($this->createElement('country', 'US'));
        $assignedEntity->appendChild($addr);

        $fname = $this->xpathValue('fname', $imm);
        $lname = $this->xpathValue('lname', $imm);
        $assignedPerson = $this->createElement('assignedPerson');
        $name = $this->createElement('name');
        if ($lname !== '') {
            $name->appendChild($this->createElement('family', $lname));
        }
        if ($fname !== '') {
            $name->appendChild($this->createElement('given', $fname));
        }
        $assignedPerson->appendChild($name);
        $assignedEntity->appendChild($assignedPerson);

        $repOrg = $this->createElement('representedOrganization');
        $facilityName = $this->xpathValue('facility_name', $imm);
        $repOrg->appendChild($this->createElement('name', $facilityName !== '' ? $facilityName : null));
        $assignedEntity->appendChild($repOrg);

        $performer->appendChild($assignedEntity);
        $subAdmin->appendChild($performer);
    }

    private function renderVitalSignsSection(DOMElement $structuredBody): void
    {
        [$component, $section] = $this->createSection(
            '2.16.840.1.113883.10.20.22.2.4.1',
            '2015-08-01',
            '8716-3',
            'Vital Signs',
        );

        $vitals = $this->xpath('/CCDA/history_physical/vitals_list/vitals');
        $this->appendVitalsNarrative($section, $vitals);

        $index = 1;
        foreach ($vitals as $vital) {
            $this->appendVitalOrganizer($section, $vital, $index);
            $index++;
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $vitals
     */
    private function appendVitalsNarrative(DOMElement $section, \DOMNodeList $vitals): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable([
            'Date',
            'Body Temperature',
            'Systolic[90-140 mmHg]',
            'Diastolic[60-90 mmHg]',
            'Heart Rate',
            'Height',
            'Weight Measured',
            'BMI (Body Mass Index)',
        ]);

        $vitalIndex = 1;
        foreach ($vitals as $vital) {
            $tbody = $this->createElement('tbody');
            $row = $this->createElement('tr');

            $date = $this->xpathValue('date', $vital);
            $row->appendChild($this->createElement('td', $date));

            // Node.js service outputs BMI in Body Temperature column
            $bmi = $this->xpathValue('BMI', $vital);
            $cell1 = $this->createElement('td', $bmi !== '' ? "$bmi kg/m2" : 'No Data Available');
            $cell1->setAttribute('ID', 'vital' . $vitalIndex++);
            $row->appendChild($cell1);

            // Systolic (bps)
            $bps = $this->xpathValue('bps', $vital);
            $cell2 = $this->createElement('td', $bps !== '' ? $bps : 'No Data Available');
            $cell2->setAttribute('ID', 'vital' . $vitalIndex++);
            $row->appendChild($cell2);

            // Diastolic (bpd)
            $bpd = $this->xpathValue('bpd', $vital);
            $cell3 = $this->createElement('td', $bpd !== '' ? $bpd : 'No Data Available');
            $cell3->setAttribute('ID', 'vital' . $vitalIndex++);
            $row->appendChild($cell3);

            // Node.js service outputs Height in Heart Rate column
            $height = $this->xpathValue('height', $vital);
            $heightUnit = $this->xpathValue('unit_height', $vital);
            $cell4 = $this->createElement('td', $height !== '' ? "$height $heightUnit" : 'No Data Available');
            $cell4->setAttribute('ID', 'vital' . $vitalIndex++);
            $row->appendChild($cell4);

            // Height column (usually empty due to above mismatch)
            $cell5 = $this->createElement('td', 'No Data Available');
            $cell5->setAttribute('ID', 'vital' . $vitalIndex++);
            $row->appendChild($cell5);

            // Weight
            $weight = $this->xpathValue('weight', $vital);
            $weightUnit = $this->xpathValue('unit_weight', $vital);
            $cell6 = $this->createElement('td', $weight !== '' ? "$weight $weightUnit" : 'No Data Available');
            $cell6->setAttribute('ID', 'vital' . $vitalIndex++);
            $row->appendChild($cell6);

            // BMI column (empty due to Node.js quirk putting it in Body Temp column)
            $cell7 = $this->createElement('td', 'No Data Available');
            $cell7->setAttribute('ID', 'vital' . $vitalIndex++);
            $row->appendChild($cell7);

            $tbody->appendChild($row);
            $table->appendChild($tbody);
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendVitalOrganizer(DOMElement $section, DOMElement $vital, int $index): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $organizer = $this->createElement('organizer');
        $organizer->setAttribute('classCode', 'CLUSTER');
        $organizer->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($organizer, '2.16.840.1.113883.10.20.22.4.26', '2015-08-01');

        $shaExt = $this->xpathValue('sha_extension', $vital);
        $ext = $this->xpathValue('extension', $vital);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $organizer->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', '46680005');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', 'SNOMED-CT');
        $code->setAttribute('displayName', 'Vital signs');
        $translation = $this->createElement('translation');
        $translation->setAttribute('code', '74728-7');
        $translation->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $translation->setAttribute('codeSystemName', 'LOINC');
        $translation->setAttribute('displayName', 'Vital signs');
        $code->appendChild($translation);
        $organizer->appendChild($code);

        $this->appendStatusCode($organizer, ActStatus::Completed);

        $date = $this->xpathValue('date', $vital);
        $effectiveTime = $this->createElement('effectiveTime');
        $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
        $organizer->appendChild($effectiveTime);

        // Node.js service uses specific entry order matching populateVital vital_list
        // Only increment refIndex when a vital is actually added
        $refIndex = 1;

        // Blood Pressure Systolic
        $bps = $this->xpathValue('bps', $vital);
        if ($bps !== '') {
            $bpsExt = $this->xpathValue('extension_bps', $vital);
            $this->appendVitalObservation($organizer, $vital, $bps, 'mm[Hg]', $bpsExt, $shaExt, '8480-6', 'Blood Pressure Systolic', $refIndex++);
        }

        // Blood Pressure Diastolic
        $bpd = $this->xpathValue('bpd', $vital);
        if ($bpd !== '') {
            $bpdExt = $this->xpathValue('extension_bpd', $vital);
            $this->appendVitalObservation($organizer, $vital, $bpd, 'mm[Hg]', $bpdExt, $shaExt, '8462-4', 'Blood Pressure Diastolic', $refIndex++);
        }

        // Average Blood Pressure
        $bpAvg = $this->xpathValue('bp_avg', $vital);
        if ($bpAvg !== '') {
            $bpAvgExt = $this->xpathValue('extension_bp_avg', $vital);
            $this->appendVitalObservation($organizer, $vital, $bpAvg, 'mm[Hg]', $bpAvgExt, $shaExt, '96607-7', 'Average Blood Pressure', $refIndex++);
        }

        // Average Systolic Blood Pressure
        $avgSystolic = $this->xpathValue('avg_systolic', $vital);
        if ($avgSystolic !== '') {
            $avgSystolicExt = $this->xpathValue('extension_avg_systolic', $vital);
            $this->appendVitalObservation($organizer, $vital, $avgSystolic, 'mm[Hg]', $avgSystolicExt, $shaExt, '96608-5', 'Average Systolic Blood Pressure', $refIndex++);
        }

        // Average Diastolic Blood Pressure
        $avgDiastolic = $this->xpathValue('avg_diastolic', $vital);
        if ($avgDiastolic !== '') {
            $avgDiastolicExt = $this->xpathValue('extension_avg_diastolic', $vital);
            $this->appendVitalObservation($organizer, $vital, $avgDiastolic, 'mm[Hg]', $avgDiastolicExt, $shaExt, '96609-3', 'Average Diastolic Blood Pressure', $refIndex++);
        }

        // Height
        $height = $this->xpathValue('height', $vital);
        if ($height !== '') {
            $heightUnit = $this->xpathValue('unit_height', $vital);
            $heightExt = $this->xpathValue('extension_height', $vital);
            $this->appendVitalObservation($organizer, $vital, $height, $heightUnit, $heightExt, $shaExt, '8302-2', 'Height', $refIndex++);
        }

        // Weight Measured
        $weight = $this->xpathValue('weight', $vital);
        if ($weight !== '') {
            $weightUnit = $this->xpathValue('unit_weight', $vital);
            $weightExt = $this->xpathValue('extension_weight', $vital);
            $this->appendVitalObservation($organizer, $vital, $weight, $weightUnit, $weightExt, $shaExt, '29463-7', 'Weight Measured', $refIndex++);
        }

        // BMI
        $bmi = $this->xpathValue('BMI', $vital);
        if ($bmi !== '') {
            $bmiExt = $this->xpathValue('extension_BMI', $vital);
            $bmiStatus = $this->xpathValue('BMI_status', $vital);
            $bmiInterp = match ($bmiStatus) {
                'Overweight' => 'High',
                'Underweight' => 'Low',
                default => 'Normal',
            };
            $this->appendVitalObservation($organizer, $vital, $bmi, 'kg/m2', $bmiExt, $shaExt, '39156-5', 'BMI (Body Mass Index)', $refIndex++, $bmiInterp);
        }

        // Heart Rate
        $pulse = $this->xpathValue('pulse', $vital);
        if ($pulse !== '') {
            $pulseExt = $this->xpathValue('extension_pulse', $vital);
            $this->appendVitalObservation($organizer, $vital, $pulse, '/min', $pulseExt, $shaExt, '8867-4', 'Heart Rate', $refIndex++);
        }

        // Respiratory Rate
        $breath = $this->xpathValue('breath', $vital);
        if ($breath !== '') {
            $breathExt = $this->xpathValue('extension_breath', $vital);
            $this->appendVitalObservation($organizer, $vital, $breath, '/min', $breathExt, '2.16.840.1.113883.3.140.1.0.6.10.14.2', '9279-1', 'Respiratory Rate', $refIndex++);
        }

        // Temperature
        $temp = $this->xpathValue('temperature', $vital);
        if ($temp !== '') {
            $tempUnit = $this->xpathValue('unit_temperature', $vital);
            $tempExt = $this->xpathValue('extension_temperature', $vital);
            $tempRounded = (string) (int) ceil((float) $temp);
            $this->appendVitalObservation($organizer, $vital, $tempRounded, $tempUnit, $tempExt, '2.16.840.1.113883.3.140.1.0.6.10.14.3', '8310-5', 'Body Temperature', $refIndex++);
        }

        // O2 Saturation
        $o2Sat = $this->xpathValue('oxygen_saturation', $vital);
        if ($o2Sat !== '') {
            $o2SatExt = $this->xpathValue('extension_oxygen_saturation', $vital);
            $this->appendVitalObservation($organizer, $vital, $o2Sat, '%', $o2SatExt, $shaExt, '59408-5', 'O2 % BldC Oximetry', $refIndex++);
        }

        // Weight for Height Percentile
        $pedWeightHeight = $this->xpathValue('ped_weight_height', $vital);
        if ($pedWeightHeight !== '') {
            $pedWeightHeightExt = $this->xpathValue('extension_ped_weight_height', $vital);
            $this->appendVitalObservation($organizer, $vital, $pedWeightHeight, '%', $pedWeightHeightExt, $shaExt, '77606-2', 'Weight for Height Percentile', $refIndex++);
        }

        // Inhaled Oxygen Concentration
        $inhaledO2 = $this->xpathValue('inhaled_oxygen_concentration', $vital);
        if ($inhaledO2 !== '') {
            $inhaledO2Ext = $this->xpathValue('extension_inhaled_oxygen_concentration', $vital);
            $this->appendVitalObservation($organizer, $vital, $inhaledO2, '%', $inhaledO2Ext, $shaExt, '3150-0', 'Inhaled Oxygen Concentration', $refIndex++);
        }

        // BMI Percentile
        $pedBmi = $this->xpathValue('ped_bmi', $vital);
        if ($pedBmi !== '') {
            $pedBmiExt = $this->xpathValue('extension_ped_bmi', $vital);
            $this->appendVitalObservation($organizer, $vital, $pedBmi, '%', $pedBmiExt, $shaExt, '59576-9', 'BMI Percentile', $refIndex++);
        }

        // Head Circumference Percentile
        $pedHeadCirc = $this->xpathValue('ped_head_circ', $vital);
        if ($pedHeadCirc !== '') {
            $pedHeadCircExt = $this->xpathValue('extension_ped_head_circ', $vital);
            $this->appendVitalObservation($organizer, $vital, $pedHeadCirc, '%', $pedHeadCircExt, $shaExt, '8289-1', 'Head Occipital-frontal Circumference Percentile', $refIndex++);
        }

        $entry->appendChild($organizer);
        $section->appendChild($entry);
    }

    private function appendVitalObservation(
        DOMElement $organizer,
        DOMElement $vital,
        string $value,
        string $unit,
        string $extension,
        string $root,
        string $loincCode,
        string $displayName,
        int $refIndex,
        string $interpretation = 'Normal',
    ): void {
        $component = $this->createElement('component');
        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.27', '2014-06-09');

        $id = $this->createElement('id');
        $id->setAttribute('root', $root);
        $id->setAttribute('extension', $extension);
        $obs->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', $loincCode);
        $code->setAttribute('displayName', $displayName);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#vital' . $refIndex);
        $origText->appendChild($ref);
        $code->appendChild($origText);
        $obs->appendChild($code);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $date = $this->xpathValue('date', $vital);
        $effectiveTime = $this->createElement('effectiveTime');
        $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
        $obs->appendChild($effectiveTime);

        $valueEl = $this->output->createElement('value');
        $valueEl->setAttributeNS(self::NS_XSI, 'xsi:type', 'PQ');
        $valueEl->setAttribute('value', $value);
        if ($unit !== '') {
            $valueEl->setAttribute('unit', $unit);
        }
        $obs->appendChild($valueEl);

        $interpCode = match ($interpretation) {
            'High' => 'H',
            'Low' => 'L',
            default => 'N',
        };
        $interp = $this->createElement('interpretationCode');
        $interp->setAttribute('displayName', $interpretation);
        $interp->setAttribute('code', $interpCode);
        $interp->setAttribute('codeSystem', '2.16.840.1.113883.5.83');
        $interp->setAttribute('codeSystemName', 'HL7 Result Interpretation');
        $obs->appendChild($interp);

        $authorEl = $this->xpath('author', $vital)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        $component->appendChild($obs);
        $organizer->appendChild($component);
    }

    private function renderSocialHistorySection(DOMElement $structuredBody): void
    {
        $socialHistory = $this->xpath('/CCDA/history_physical/social_history/history_element');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        // Check for USCDI data
        $sexObservation = $this->xpathValue('/CCDA/patient/sex_observation');
        $occupationCode = $this->xpathValue('/CCDA/patient/occupation/occupation_code');
        $tribalCode = $this->xpathValue('/CCDA/patient/tribal');
        $pregnancyCode = $this->xpathValue('/CCDA/patient/sdoh_data/pregnancy_code');
        $hungerRiskCode = $this->xpathValue('/CCDA/social_history_sdoh/hunger_vital_signs/risk_status/answer_code');
        $disabilityStatusCode = $this->xpathValue('/CCDA/patient/sdoh_data/disability_assessment/overall_status/answer_code');
        $disabilityQuestions = $this->xpath('/CCDA/patient/sdoh_data/disability_assessment/disability_questions/question');
        $hasUscdiData = $sexObservation !== '' || $occupationCode !== '' || $tribalCode !== '' || $pregnancyCode !== '' || $hungerRiskCode !== '' || $disabilityStatusCode !== '' || $disabilityQuestions->length > 0;

        if ($socialHistory->length === 0 && !$hasUscdiData) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.17', '2015-08-01');

        $section->appendChild($this->createLoincCode('29762-2', 'Social History'));
        $section->appendChild($this->createElement('title', 'Social History'));

        if ($socialHistory->length === 0 && !$hasUscdiData) {
            $section->appendChild($this->createElement('text', 'Not Available'));
        } else {
            $this->appendSocialHistoryNarrative($section, $socialHistory);
            foreach ($socialHistory as $item) {
                $this->appendSocialHistoryEntry($section, $item);
            }

            // USCDI Social History Observations
            $this->appendSexObservationEntry($section, $sexObservation);
            $this->appendSexualOrientationObservationEntry($section);
            $this->appendGenderIdentityObservationEntry($section);
            $this->appendOccupationObservationEntry($section, $occupationCode);
            $this->appendTribalAffiliationObservationEntry($section, $tribalCode);
            $this->appendPregnancyStatusObservationEntry($section, $pregnancyCode);
            $this->appendHungerVitalSignsObservationEntry($section, $hungerRiskCode);
            $this->appendDisabilityAssessmentEntry($section, $disabilityStatusCode, $disabilityQuestions);
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $items
     */
    private function appendSocialHistoryNarrative(DOMElement $section, \DOMNodeList $items): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Social History Element', 'Description', 'Date']);

        $index = 1;
        foreach ($items as $item) {
            $element = $this->xpathValue('element', $item);
            $description = $this->xpathValue('description', $item);
            $date = $this->xpathValue('date', $item);

            $this->appendTableRow($table, [$element, $description, $this->formatDateForDisplay($date)], 'social' . $index);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendSocialHistoryEntry(DOMElement $section, DOMElement $item): void
    {
        $description = strtolower($this->xpathValue('description', $item));
        $isSmokingStatus = str_contains($description, 'smoke') || str_contains($description, 'tobacco');

        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        if ($isSmokingStatus) {
            $this->appendSmokingStatusObservation($entry, $item);
        } else {
            $this->appendSocialHistoryObservation($entry, $item);
        }

        $section->appendChild($entry);
    }

    private function appendSmokingStatusObservation(DOMElement $entry, DOMElement $item): void
    {
        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.78');

        // ID
        $shaExt = $this->xpathValue('sha_extension', $item);
        $ext = $this->xpathValue('extension', $item);
        $this->appendIds($obs, $shaExt, $ext);

        // Code (LOINC for smoking status)
        $obs->appendChild($this->createLoincCode('72166-2', 'Tobacco smoking status'));

        // Status
        $this->appendStatusCode($obs, ActStatus::Completed);

        // Effective time
        $date = $this->xpathValue('date', $item);
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
            $obs->appendChild($effectiveTime);
        }

        // Value (smoking status code)
        $description = $this->xpathValue('description', $item);
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $smokingCode = $this->mapSmokingStatusCode($description);
        $value->setAttribute('code', $smokingCode['code']);
        $value->setAttribute('displayName', $smokingCode['displayName']);
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', 'SNOMED CT');
        $obs->appendChild($value);

        $entry->appendChild($obs);
    }

    private function appendSocialHistoryObservation(DOMElement $entry, DOMElement $item): void
    {
        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.38');

        // ID
        $shaExt = $this->xpathValue('sha_extension', $item);
        $ext = $this->xpathValue('extension', $item);
        $this->appendIds($obs, $shaExt, $ext);

        // Code
        $elementCode = $this->xpathValue('code', $item);
        $element = $this->xpathValue('element', $item);

        $code = $this->createElement('code');
        if ($elementCode !== '') {
            $code->setAttribute('code', $elementCode);
            $code->setAttribute('displayName', $element);
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
            $code->setAttribute('codeSystemName', 'SNOMED CT');
        } else {
            $code->setAttribute('nullFlavor', 'UNK');
        }
        $obs->appendChild($code);

        // Status
        $this->appendStatusCode($obs, ActStatus::Completed);

        // Effective time
        $date = $this->xpathValue('date', $item);
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $low = $this->createElement('low');
            $low->setAttribute('value', $this->formatDateOnly($date));
            $effectiveTime->appendChild($low);
            $obs->appendChild($effectiveTime);
        }

        // Value (description as ST)
        $description = $this->xpathValue('description', $item);
        if ($description !== '') {
            $value = $this->output->createElement('value');
            $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'ST');
            $value->appendChild($this->output->createTextNode($description));
            $obs->appendChild($value);
        }

        $entry->appendChild($obs);
    }

    /**
     * Maps smoking status description to SNOMED CT code
     * @return array{code: string, displayName: string}
     */
    private function mapSmokingStatusCode(string $description): array
    {
        $description = strtolower($description);

        return match (true) {
            str_contains($description, 'current') && str_contains($description, 'every day') => [
                'code' => '449868002',
                'displayName' => 'Current every day smoker',
            ],
            str_contains($description, 'current') && str_contains($description, 'some day') => [
                'code' => '428041000124106',
                'displayName' => 'Current some day smoker',
            ],
            str_contains($description, 'former') => [
                'code' => '8517006',
                'displayName' => 'Former smoker',
            ],
            str_contains($description, 'never') => [
                'code' => '266919005',
                'displayName' => 'Never smoker',
            ],
            str_contains($description, 'heavy') => [
                'code' => '428071000124103',
                'displayName' => 'Heavy tobacco smoker',
            ],
            str_contains($description, 'light') => [
                'code' => '428061000124105',
                'displayName' => 'Light tobacco smoker',
            ],
            str_contains($description, 'unknown if ever') => [
                'code' => '266927001',
                'displayName' => 'Unknown if ever smoked',
            ],
            str_contains($description, 'current status unknown') || str_contains($description, 'smoker') => [
                'code' => '77176002',
                'displayName' => 'Smoker, current status unknown',
            ],
            default => [
                'code' => '77176002',
                'displayName' => 'Smoker, current status unknown',
            ],
        };
    }

    private function appendSexObservationEntry(DOMElement $section, string $sexObservation): void
    {
        if ($sexObservation === '') {
            return;
        }

        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.507', '2023-06-28');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $id = $this->createElement('id');
            $id->setAttribute('root', $facilityOid);
            $obs->appendChild($id);
        }

        $obs->appendChild($this->createLoincCode('46098-0', 'Sex'));
        $this->appendStatusCode($obs, ActStatus::Completed);

        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('nullFlavor', 'NI');
        $obs->appendChild($effTime);

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');

        $sexCode = $this->mapSexObservationCode($sexObservation);
        $value->setAttribute('code', $sexCode['code']);
        $value->setAttribute('codeSystem', $sexCode['codeSystem']);
        $value->setAttribute('displayName', $sexCode['displayName']);
        $obs->appendChild($value);

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    /**
     * @return array{code: string, codeSystem: string, displayName: string}
     */
    private function mapSexObservationCode(string $sex): array
    {
        $sex = strtolower(trim($sex));

        return match ($sex) {
            'male', 'm' => [
                'code' => '248153007',
                'codeSystem' => '2.16.840.1.113883.6.96',
                'displayName' => 'Male',
            ],
            'female', 'f' => [
                'code' => '248152002',
                'codeSystem' => '2.16.840.1.113883.6.96',
                'displayName' => 'Female',
            ],
            'nonbinary' => [
                'code' => '33791000087105',
                'codeSystem' => '2.16.840.1.113883.6.96',
                'displayName' => 'Identifies as nonbinary gender (finding)',
            ],
            'asked-declined' => [
                'code' => 'asked-declined',
                'codeSystem' => '2.16.840.1.113883.4.642.4.1048',
                'displayName' => 'Asked But Declined',
            ],
            'unk', 'unknown' => [
                'code' => 'unknown',
                'codeSystem' => '2.16.840.1.113883.4.642.4.1048',
                'displayName' => 'Unknown',
            ],
            default => [
                'code' => 'unknown',
                'codeSystem' => '2.16.840.1.113883.4.642.4.1048',
                'displayName' => 'Unknown',
            ],
        };
    }

    private function appendSexualOrientationObservationEntry(DOMElement $section): void
    {
        // Sexual orientation is always included per Node.js behavior
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.38');
        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.501', '2022-06-01');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $id = $this->createElement('id');
            $id->setAttribute('root', $facilityOid);
            $obs->appendChild($id);
        }

        $obs->appendChild($this->createLoincCode('76690-7', 'Sexual Orientation'));
        $this->appendStatusCode($obs, ActStatus::Completed);

        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('nullFlavor', 'NI');
        $obs->appendChild($effTime);

        // Value with nullFlavor if no data
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('nullFlavor', 'UNK');
        $obs->appendChild($value);

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function appendGenderIdentityObservationEntry(DOMElement $section): void
    {
        // Gender identity is always included per Node.js behavior
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.38');
        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.34.3.45', '2022-06-01');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $id = $this->createElement('id');
            $id->setAttribute('root', $facilityOid);
            $obs->appendChild($id);
        }

        $obs->appendChild($this->createLoincCode('76691-5', 'Gender Identity'));
        $this->appendStatusCode($obs, ActStatus::Completed);

        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('nullFlavor', 'NI');
        $obs->appendChild($effTime);

        // Value with nullFlavor if no data
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('nullFlavor', 'ASKU');
        $obs->appendChild($value);

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function appendOccupationObservationEntry(DOMElement $section, string $occupationCode): void
    {
        if ($occupationCode === '') {
            return;
        }

        $entry = $this->createElement('entry');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.503', '2023-05-01');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $obs->appendChild($uniqueId);
        }

        $id = $this->createElement('id');
        $id->setAttribute('root', $this->generateUuid());
        $obs->appendChild($id);

        $obs->appendChild($this->createLoincCode('11341-5', 'History of occupation'));
        $this->appendStatusCode($obs, ActStatus::Completed);

        // Effective time with low/high
        $startDate = $this->xpathValue('/CCDA/patient/occupation/start_date');
        if ($startDate !== '') {
            $effTime = $this->createElement('effectiveTime');
            $low = $this->createElement('low');
            $low->setAttribute('value', $this->formatDateOnly($startDate));
            $effTime->appendChild($low);
            $obs->appendChild($effTime);
        }

        // Value
        $occupationTitle = $this->xpathValue('/CCDA/patient/occupation/occupation_title');
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $occupationCode);
        $value->setAttribute('displayName', $occupationTitle);
        $value->setAttribute('codeSystem', '2.16.840.1.114222.4.5.327');
        $value->setAttribute('codeSystemName', 'Occupational Data for Health (ODH)');
        $obs->appendChild($value);

        // Industry entryRelationship
        $industryCode = $this->xpathValue('/CCDA/patient/occupation/industry/industry_code');
        if ($industryCode !== '') {
            $this->appendOccupationIndustryObservation($obs);
        }

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function appendOccupationIndustryObservation(DOMElement $parentObs): void
    {
        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'REFR');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.504', '2023-05-01');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $obs->appendChild($uniqueId);
        }

        $id = $this->createElement('id');
        $id->setAttribute('root', $this->generateUuid());
        $obs->appendChild($id);

        $obs->appendChild($this->createLoincCode('86188-0', 'History of occupation industry'));
        $this->appendStatusCode($obs, ActStatus::Completed);

        $industryStartDate = $this->xpathValue('/CCDA/patient/occupation/industry/industry_start_date');
        if ($industryStartDate !== '') {
            $effTime = $this->createElement('effectiveTime');
            $low = $this->createElement('low');
            $low->setAttribute('value', $this->formatDateOnly($industryStartDate));
            $effTime->appendChild($low);
            $obs->appendChild($effTime);
        }

        $industryCode = $this->xpathValue('/CCDA/patient/occupation/industry/industry_code');
        $industryTitle = $this->xpathValue('/CCDA/patient/occupation/industry/industry_title');
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $industryCode);
        $value->setAttribute('displayName', $industryTitle);
        $value->setAttribute('codeSystem', '2.16.840.1.114222.4.5.327');
        $value->setAttribute('codeSystemName', 'Occupational Data for Health (ODH)');
        $obs->appendChild($value);

        $entryRel->appendChild($obs);
        $parentObs->appendChild($entryRel);
    }

    private function appendTribalAffiliationObservationEntry(DOMElement $section, string $tribalCode): void
    {
        if ($tribalCode === '') {
            return;
        }

        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.506', '2023-05-01');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $obs->appendChild($uniqueId);
        }

        $id = $this->createElement('id');
        $id->setAttribute('root', $this->generateUuid());
        $obs->appendChild($id);

        $obs->appendChild($this->createLoincCode('95370-3', 'Tribal affiliation'));
        $this->appendStatusCode($obs, ActStatus::Completed);

        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('nullFlavor', 'NI');
        $obs->appendChild($effTime);

        // Value - tribal code is the title/name, not a coded value in the current implementation
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $tribalCode);
        $value->setAttribute('codeSystem', '2.16.840.1.113883.5.140');
        $value->setAttribute('codeSystemName', 'Tribal TribalEntityUS');
        $value->setAttribute('displayName', $tribalCode);
        $obs->appendChild($value);

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function appendPregnancyStatusObservationEntry(DOMElement $section, string $pregnancyCode): void
    {
        if ($pregnancyCode === '') {
            return;
        }

        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.15.3.8', '2023-05-01');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $obs->appendChild($uniqueId);
        }

        $id = $this->createElement('id');
        $id->setAttribute('root', $this->generateUuid());
        $obs->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', 'ASSERTION');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.4');
        $obs->appendChild($code);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('nullFlavor', 'NI');
        $obs->appendChild($effTime);

        $pregnancyTitle = $this->xpathValue('/CCDA/patient/sdoh_data/pregnancy_title');
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $pregnancyCode);
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $value->setAttribute('codeSystemName', 'SNOMED-CT');
        $value->setAttribute('displayName', $pregnancyTitle);
        $obs->appendChild($value);

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function appendHungerVitalSignsObservationEntry(DOMElement $section, string $riskCode): void
    {
        if ($riskCode === '') {
            return;
        }

        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        // Outer Social History Observation wrapper
        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.38');
        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.38', '2015-08-01');
        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.38', '2022-06-01');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $obs->appendChild($uniqueId);
        }

        $id = $this->createElement('id');
        $id->setAttribute('root', $this->generateUuid());
        $obs->appendChild($id);

        // Code for Social / personal history observable
        $code = $this->createElement('code');
        $code->setAttribute('code', '160476009');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', 'SNOMED CT');
        $code->setAttribute('displayName', 'Social / personal history observable');

        $translation = $this->createElement('translation');
        $translation->setAttribute('code', '8689-2');
        $translation->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $translation->setAttribute('codeSystemName', 'LOINC');
        $translation->setAttribute('displayName', 'History of Social function');
        $code->appendChild($translation);
        $obs->appendChild($code);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $assessDate = $this->xpathValue('/CCDA/social_history_sdoh/hunger_vital_signs/assessment_date');
        if ($assessDate !== '') {
            $effTime = $this->createElement('effectiveTime');
            $effTime->setAttribute('value', $assessDate);
            $obs->appendChild($effTime);
        }

        // Nested entryRelationship for Hunger Vital Signs panel
        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'SPRT');

        $panelObs = $this->createElement('observation');
        $panelObs->setAttribute('classCode', 'OBS');
        $panelObs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($panelObs, '2.16.840.1.113883.10.20.22.4.69', '2022-06-01');

        if ($facilityOid !== '') {
            $uniqueId2 = $this->createElement('id');
            $uniqueId2->setAttribute('root', $facilityOid);
            $uniqueId2->setAttribute('extension', $this->generateUuid());
            $panelObs->appendChild($uniqueId2);
        }

        $id2 = $this->createElement('id');
        $id2->setAttribute('root', $this->generateUuid());
        $panelObs->appendChild($id2);

        $panelCode = $this->createElement('code');
        $panelCode->setAttribute('code', '88121-9');
        $panelCode->setAttribute('displayName', 'Hunger Vital Signs');
        $panelCode->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $panelCode->setAttribute('codeSystemName', 'LOINC');
        $panelObs->appendChild($panelCode);

        $derivExpr = $this->createElement('derivationExpr');
        $derivExpr->appendChild($this->output->createTextNode('Sum of hunger screening responses'));
        $panelObs->appendChild($derivExpr);

        $this->appendStatusCode($panelObs, ActStatus::Completed);

        if ($assessDate !== '') {
            $panelEffTime = $this->createElement('effectiveTime');
            $panelEffTime->setAttribute('value', $assessDate);
            $panelObs->appendChild($panelEffTime);
        }

        $score = $this->xpathValue('/CCDA/social_history_sdoh/hunger_vital_signs/score');
        $panelValue = $this->output->createElement('value');
        $panelValue->setAttributeNS(self::NS_XSI, 'xsi:type', 'INT');
        $panelValue->setAttribute('value', $score !== '' ? $score : '0');
        $panelObs->appendChild($panelValue);

        // Question 1
        $q1Code = $this->xpathValue('/CCDA/social_history_sdoh/hunger_vital_signs/question1/code');
        if ($q1Code !== '') {
            $this->appendHungerVitalSignsQuestion($panelObs, 'question1');
        }

        // Question 2
        $q2Code = $this->xpathValue('/CCDA/social_history_sdoh/hunger_vital_signs/question2/code');
        if ($q2Code !== '') {
            $this->appendHungerVitalSignsQuestion($panelObs, 'question2');
        }

        $entryRel->appendChild($panelObs);
        $obs->appendChild($entryRel);

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function appendHungerVitalSignsQuestion(DOMElement $panelObs, string $questionKey): void
    {
        $basePath = '/CCDA/social_history_sdoh/hunger_vital_signs/' . $questionKey;

        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'COMP');

        $qObs = $this->createElement('observation');
        $qObs->setAttribute('classCode', 'OBS');
        $qObs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($qObs, '2.16.840.1.113883.10.20.22.4.86');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $qObs->appendChild($uniqueId);
        }

        $id = $this->createElement('id');
        $id->setAttribute('root', $this->generateUuid());
        $qObs->appendChild($id);

        $qCode = $this->xpathValue($basePath . '/code');
        $qCodeSystem = $this->xpathValue($basePath . '/code_system');
        $qDisplay = $this->xpathValue($basePath . '/display');

        $code = $this->createElement('code');
        $code->setAttribute('code', $qCode);
        $code->setAttribute('displayName', $qDisplay);
        $code->setAttribute('codeSystem', $qCodeSystem !== '' ? $qCodeSystem : '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $qObs->appendChild($code);

        $this->appendStatusCode($qObs, ActStatus::Completed);

        $answerCode = $this->xpathValue($basePath . '/answer_code');
        $answerDisplay = $this->xpathValue($basePath . '/answer_display');

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $answerCode);
        $value->setAttribute('displayName', $answerDisplay);
        $value->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $qObs->appendChild($value);

        $entryRel->appendChild($qObs);
        $panelObs->appendChild($entryRel);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $questions
     */
    private function appendDisabilityAssessmentEntry(DOMElement $section, string $statusCode, \DOMNodeList $questions): void
    {
        if ($statusCode === '' && $questions->length === 0) {
            return;
        }

        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.38');

        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $obs->appendChild($uniqueId);
        }

        $code = $this->createElement('code');
        $code->setAttribute('code', '89571-4');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $code->setAttribute('displayName', 'Overall disability status CUBS');
        $obs->appendChild($code);

        $this->appendStatusCode($obs, ActStatus::Completed);

        if ($statusCode !== '') {
            $statusDisplay = $this->xpathValue('/CCDA/patient/sdoh_data/disability_assessment/overall_status/answer_display');
            $value = $this->output->createElement('value');
            $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
            $value->setAttribute('code', $statusCode);
            $value->setAttribute('displayName', $statusDisplay);
            $value->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $obs->appendChild($value);
        }

        // Component observations for individual disability questions
        foreach ($questions as $question) {
            $qCode = $this->xpathValue('code', $question);
            $qDisplay = $this->xpathValue('display', $question);
            $answerCode = $this->xpathValue('answer_code', $question);
            $answerDisplay = $this->xpathValue('answer_display', $question);

            if ($qCode === '' || $answerCode === '') {
                continue;
            }

            $entryRel = $this->createElement('entryRelationship');
            $entryRel->setAttribute('typeCode', 'COMP');

            $qObs = $this->createElement('observation');
            $qObs->setAttribute('classCode', 'OBS');
            $qObs->setAttribute('moodCode', 'EVN');

            $this->appendTemplateId($qObs, '2.16.840.1.113883.10.20.22.4.86');

            if ($facilityOid !== '') {
                $qUniqueId = $this->createElement('id');
                $qUniqueId->setAttribute('root', $facilityOid);
                $qUniqueId->setAttribute('extension', $this->generateUuid());
                $qObs->appendChild($qUniqueId);
            }

            $qCodeEl = $this->createElement('code');
            $qCodeEl->setAttribute('code', $qCode);
            $qCodeEl->setAttribute('displayName', $qDisplay);
            $qCodeEl->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $qCodeEl->setAttribute('codeSystemName', 'LOINC');
            $qObs->appendChild($qCodeEl);

            $this->appendStatusCode($qObs, ActStatus::Completed);

            $qValue = $this->output->createElement('value');
            $qValue->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
            $qValue->setAttribute('code', $answerCode);
            $qValue->setAttribute('displayName', $answerDisplay);
            $qValue->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $qObs->appendChild($qValue);

            $entryRel->appendChild($qObs);
            $obs->appendChild($entryRel);
        }

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function renderPayersSection(DOMElement $structuredBody): void
    {
        $payers = $this->xpath('/CCDA/payers/payer');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($payers->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.18');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.18', '2015-08-01');

        $section->appendChild($this->createLoincCode('48768-6', 'Payers'));
        $section->appendChild($this->createElement('title', 'Payers'));

        if ($payers->length === 0) {
            $section->appendChild($this->createElement('text', 'Not Available'));
        } else {
            $this->appendPayersNarrative($section, $payers);
            foreach ($payers as $payer) {
                $this->appendPayerEntry($section, $payer);
            }
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $payers
     */
    private function appendPayersNarrative(DOMElement $section, \DOMNodeList $payers): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Payer', 'Policy Type', 'Member ID']);

        $index = 1;
        foreach ($payers as $payer) {
            $orgName = $this->xpathValue('policy/insurance/performer/organization/name', $payer);
            $policyType = $this->xpathValue('policy/code/name', $payer);
            $memberId = $this->xpathValue('participant/code/name', $payer);

            $this->appendTableRow($table, [$orgName, $policyType, $memberId], 'payer' . $index);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendPayerEntry(DOMElement $section, DOMElement $payer): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        // Coverage Activity
        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'ACT');
        $act->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($act, '2.16.840.1.113883.10.20.22.4.60', '2015-08-01');

        // uniqueId
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $act->appendChild($uniqueId);
        }

        // id
        $payerId = $this->xpathValue('identifiers/identifier', $payer);
        $id = $this->createElement('id');
        $id->setAttribute('root', $payerId !== '' ? $payerId : 'NI');
        $act->appendChild($id);

        $act->appendChild($this->createLoincCode('48768-6', 'Payment sources'));

        $this->appendStatusCode($act, ActStatus::Completed);

        // Policy Activity entryRelationship
        $this->appendPolicyActivity($act, $payer);

        $entry->appendChild($act);
        $section->appendChild($entry);
    }

    private function appendPolicyActivity(DOMElement $act, DOMElement $payer): void
    {
        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'COMP');

        $policyAct = $this->createElement('act');
        $policyAct->setAttribute('classCode', 'ACT');
        $policyAct->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($policyAct, '2.16.840.1.113883.10.20.22.4.61', '2015-08-01');

        // id
        $policyId = $this->xpathValue('policy/identifiers/identifier', $payer);
        $policyExt = $this->xpathValue('policy/identifiers/extension', $payer);
        $id = $this->createElement('id');
        $id->setAttribute('root', $policyId !== '' ? $policyId : 'NI');
        $id->setAttribute('extension', $policyExt);
        $policyAct->appendChild($id);

        // Code - policy type
        $policyCode = $this->xpathValue('policy/code/code', $payer);
        $policyName = $this->xpathValue('policy/code/name', $payer);
        $code = $this->createElement('code');
        $code->setAttribute('code', $policyCode !== '' ? $policyCode : '72');
        $code->setAttribute('displayName', $policyName !== '' ? $policyName : 'Self');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.3.221.5');
        $code->setAttribute('codeSystemName', 'Source of Payment Typology');
        $policyAct->appendChild($code);

        $this->appendStatusCode($policyAct, ActStatus::Completed);

        // Performer - Insurance
        $this->appendPayerPerformer($policyAct, $payer);

        // Participant - Covered party
        $this->appendPayerParticipant($policyAct, $payer);

        $entryRel->appendChild($policyAct);
        $act->appendChild($entryRel);
    }

    private function appendPayerPerformer(DOMElement $policyAct, DOMElement $payer): void
    {
        $performer = $this->createElement('performer');
        $performer->setAttribute('typeCode', 'PRF');

        // Template ID for Payer Performer
        $this->appendTemplateId($performer, '2.16.840.1.113883.10.20.22.4.87');

        $assignedEntity = $this->createElement('assignedEntity');

        $performerId = $this->xpathValue('policy/insurance/performer/identifiers/identifier', $payer);
        $id = $this->createElement('id');
        $id->setAttribute('root', $performerId !== '' ? $performerId : 'NI');
        $assignedEntity->appendChild($id);

        // Code
        $code = $this->createElement('code');
        $code->setAttribute('code', 'PAYOR');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.110');
        $code->setAttribute('codeSystemName', 'HL7 RoleCode');
        $code->setAttribute('displayName', 'Payor');
        $assignedEntity->appendChild($code);

        // Address
        $addr = $this->createElement('addr');
        $street = $this->xpathValue('policy/insurance/performer/address/street_lines', $payer);
        $city = $this->xpathValue('policy/insurance/performer/address/city', $payer);
        $state = $this->xpathValue('policy/insurance/performer/address/state', $payer);
        $zip = $this->xpathValue('policy/insurance/performer/address/zip', $payer);
        $addr->appendChild($this->createElement('streetAddressLine', $street !== '' ? $street : null));
        $addr->appendChild($this->createElement('city', $city !== '' ? $city : null));
        $addr->appendChild($this->createElement('state', $state !== '' ? $state : null));
        $addr->appendChild($this->createElement('postalCode', $zip !== '' ? $zip : null));
        $assignedEntity->appendChild($addr);

        // Phone
        $phone = $this->xpathValue('policy/insurance/performer/phone/number', $payer);
        $telecom = $this->createElement('telecom');
        $telecom->setAttribute('value', $phone !== '' ? 'tel:' . $phone : '');
        $assignedEntity->appendChild($telecom);

        // Organization
        $orgName = $this->xpathValue('policy/insurance/performer/organization/name', $payer);
        $repOrg = $this->createElement('representedOrganization');
        $repOrg->appendChild($this->createElement('name', $orgName !== '' ? $orgName : null));
        $assignedEntity->appendChild($repOrg);

        $performer->appendChild($assignedEntity);
        $policyAct->appendChild($performer);
    }

    private function appendPayerParticipant(DOMElement $policyAct, DOMElement $payer): void
    {
        $participant = $this->createElement('participant');
        $participant->setAttribute('typeCode', 'COV');

        // Template ID for Coverage Target
        $this->appendTemplateId($participant, '2.16.840.1.113883.10.20.22.4.89');

        // Time
        $timeLow = $this->xpathValue('participant/time_low', $payer);
        $timeHigh = $this->xpathValue('participant/time_high', $payer);
        if ($timeLow !== '' || $timeHigh !== '') {
            $time = $this->createElement('time');
            if ($timeLow !== '') {
                $low = $this->createElement('low');
                $low->setAttribute('value', $this->formatDateOnly($timeLow));
                $time->appendChild($low);
            }
            if ($timeHigh !== '') {
                $high = $this->createElement('high');
                $high->setAttribute('value', $this->formatDateOnly($timeHigh));
                $time->appendChild($high);
            }
            $participant->appendChild($time);
        }

        $participantRole = $this->createElement('participantRole');
        $participantRole->setAttribute('classCode', 'PAT');

        $participantId = $this->xpathValue('participant/code/code', $payer);
        $id = $this->createElement('id');
        $id->setAttribute('root', '2.16.840.1.113883.4.3.6');
        $id->setAttribute('extension', $participantId);
        $participantRole->appendChild($id);

        // Code
        $code = $this->createElement('code');
        $code->setAttribute('code', 'SELF');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.111');
        $code->setAttribute('codeSystemName', 'HL7 RoleCode');
        $code->setAttribute('displayName', 'Self');
        $participantRole->appendChild($code);

        $participant->appendChild($participantRole);
        $policyAct->appendChild($participant);
    }

    private function renderMedicalEquipmentSection(DOMElement $structuredBody): void
    {
        $devices = $this->xpath('/CCDA/medical_devices/device');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($devices->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.23', '2014-06-09');

        $section->appendChild($this->createLoincCode('46264-8', 'Medical Equipment'));
        $section->appendChild($this->createElement('title', 'Medical Equipment'));

        if ($devices->length === 0) {
            $section->appendChild($this->createElement('text', 'Not Available'));
        } else {
            $this->appendMedicalEquipmentNarrative($section, $devices);
            $index = 1;
            foreach ($devices as $device) {
                $this->appendMedicalEquipmentEntry($section, $device, $index);
                $index++;
            }
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $devices
     */
    private function appendMedicalEquipmentNarrative(DOMElement $section, \DOMNodeList $devices): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Device', 'UDI', 'Start Date']);

        $index = 1;
        foreach ($devices as $device) {
            $codeText = $this->xpathValue('code_text', $device);
            $udi = $this->xpathValue('udi', $device);
            $startDate = $this->xpathValue('start_date', $device);

            $this->appendTableRow($table, [$codeText, $udi, $startDate], 'device' . $index);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendMedicalEquipmentEntry(DOMElement $section, DOMElement $device, int $index): void
    {
        $entry = $this->createElement('entry');

        $procedure = $this->createElement('procedure');
        $procedure->setAttribute('classCode', 'PROC');
        $procedure->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($procedure, '2.16.840.1.113883.10.20.22.4.14', '2014-06-09');

        // uniqueId
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $procedure->appendChild($uniqueId);
        }

        // id
        $shaExt = $this->xpathValue('sha_extension', $device);
        $ext = $this->xpathValue('extension', $device);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt !== '' ? $shaExt : 'NI');
        $id->setAttribute('extension', $ext);
        $procedure->appendChild($id);

        // Code
        $deviceCode = $this->xpathValue('code', $device);
        $codeText = $this->xpathValue('code_text', $device);
        $code = $this->createElement('code');
        if ($deviceCode !== '') {
            $code->setAttribute('code', $this->cleanCode($deviceCode));
        }
        if ($codeText !== '') {
            $code->setAttribute('displayName', $codeText);
        }
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', 'SNOMED CT');
        $origText = $this->createElement('originalText');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#device' . $index);
        $origText->appendChild($ref);
        $code->appendChild($origText);
        $procedure->appendChild($code);

        $this->appendStatusCode($procedure, ActStatus::Completed);

        $startDate = $this->xpathValue('start_date', $device);
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($startDate));
        $effectiveTime->appendChild($low);
        $procedure->appendChild($effectiveTime);

        // Author
        $authorEl = $this->xpath('author', $device)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($procedure, $authorEl);
        }

        // Participant (device)
        $this->appendDeviceParticipant($procedure, $device);

        $entry->appendChild($procedure);
        $section->appendChild($entry);
    }

    private function appendDeviceParticipant(DOMElement $procedure, DOMElement $device): void
    {
        $participant = $this->createElement('participant');
        $participant->setAttribute('typeCode', 'DEV');

        $participantRole = $this->createElement('participantRole');
        $participantRole->setAttribute('classCode', 'MANU');

        $this->appendTemplateId($participantRole, '2.16.840.1.113883.10.20.22.4.37');

        $udi = $this->xpathValue('udi', $device);
        $id = $this->createElement('id');
        $id->setAttribute('root', '2.16.840.1.113883.3.3719');
        $id->setAttribute('extension', $udi);
        $participantRole->appendChild($id);

        $playingDevice = $this->createElement('playingDevice');
        $deviceCode = $this->xpathValue('code', $device);
        $codeText = $this->xpathValue('code_text', $device);
        $code = $this->createElement('code');
        if ($deviceCode !== '') {
            $code->setAttribute('code', $this->cleanCode($deviceCode));
        }
        if ($codeText !== '') {
            $code->setAttribute('displayName', $codeText);
        }
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', 'SNOMED CT');
        $playingDevice->appendChild($code);
        $participantRole->appendChild($playingDevice);

        $scopingEntity = $this->createElement('scopingEntity');
        $scopingId = $this->createElement('id');
        $scopingId->setAttribute('root', '2.16.840.1.113883.3.3719');
        $scopingEntity->appendChild($scopingId);
        $participantRole->appendChild($scopingEntity);

        $participant->appendChild($participantRole);
        $procedure->appendChild($participant);
    }

    private function renderFunctionalStatusSection(DOMElement $structuredBody): void
    {
        $functionalStatus = $this->xpath('/CCDA/functional_status/item');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($functionalStatus->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.14', '2014-06-09');

        $section->appendChild($this->createLoincCode('47420-5', 'Functional Status'));
        $section->appendChild($this->createElement('title', 'Functional Status'));

        if ($functionalStatus->length === 0) {
            $section->appendChild($this->createElement('text', 'Not Available'));
        } else {
            $this->appendFunctionalStatusNarrative($section, $functionalStatus);
            foreach ($functionalStatus as $item) {
                $this->appendFunctionalStatusEntry($section, $item);
            }
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $items
     */
    private function appendFunctionalStatusNarrative(DOMElement $section, \DOMNodeList $items): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Functional Status', 'Date']);

        $index = 1;
        foreach ($items as $item) {
            $codeText = $this->xpathValue('code_text', $item);
            $date = $this->xpathValue('date', $item);
            $displayText = $codeText !== '' && $codeText !== 'NULL' ? $codeText : '';
            $this->appendTableRow($table, [$displayText, $date], 'functional_status' . $index);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendFunctionalStatusEntry(DOMElement $section, DOMElement $item): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $organizer = $this->createElement('organizer');
        $organizer->setAttribute('classCode', 'CLUSTER');
        $organizer->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($organizer, '2.16.840.1.113883.10.20.22.4.66', '2014-06-09');

        // uniqueId
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $organizer->appendChild($uniqueId);
        }

        // id
        $ext = $this->xpathValue('extension', $item);
        $id = $this->createElement('id');
        $id->setAttribute('root', '9a6d1bac-17d3-4195-89a4-1121bc809000');
        $id->setAttribute('extension', $ext);
        $organizer->appendChild($id);

        // Code - Self-Care from ICF
        $code = $this->createElement('code');
        $code->setAttribute('code', 'd5');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.254');
        $code->setAttribute('codeSystemName', 'ICF');
        $code->setAttribute('displayName', 'Self-Care');
        $organizer->appendChild($code);

        $this->appendStatusCode($organizer, ActStatus::Completed);

        // Author from global author
        $authorEl = $this->xpath('/CCDA/author')->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($organizer, $authorEl);
        }

        // Observation component
        $this->appendFunctionalStatusObservation($organizer, $item);

        $entry->appendChild($organizer);
        $section->appendChild($entry);
    }

    private function appendFunctionalStatusObservation(DOMElement $organizer, DOMElement $item): void
    {
        $component = $this->createElement('component');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.67', '2014-06-09');

        $ext = $this->xpathValue('extension', $item);
        $id = $this->createElement('id');
        $id->setAttribute('root', '9a6d1bac-17d3-4195-89a4-1121bc8090ab');
        $id->setAttribute('extension', $ext);
        $obs->appendChild($id);

        // Code
        $obs->appendChild($this->createLoincCode('54522-8', 'Functional status'));
        $this->appendStatusCode($obs, ActStatus::Completed);

        $date = $this->xpathValue('date', $item);
        $effectiveTime = $this->createElement('effectiveTime');
        $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
        $obs->appendChild($effectiveTime);

        // Value
        $itemCode = $this->xpathValue('code', $item);
        $codeText = $this->xpathValue('code_text', $item);
        $codeType = $this->xpathValue('code_type', $item);

        if ($itemCode !== '' || ($codeText !== '' && $codeText !== 'NULL')) {
            $value = $this->output->createElement('value');
            $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
            if ($itemCode !== '') {
                $value->setAttribute('code', $this->cleanCode($itemCode));
            }
            if ($codeText !== '' && $codeText !== 'NULL') {
                $value->setAttribute('displayName', $codeText);
            }
            $codeSystemName = $codeType !== '' ? $codeType : 'SNOMED CT';
            if ($codeSystemName === 'SNOMED-CT') {
                $codeSystemName = 'SNOMED CT';
            }
            $value->setAttribute('codeSystemName', $codeSystemName);
            if ($codeSystemName === 'SNOMED CT') {
                $value->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
            }
            $obs->appendChild($value);
        }

        // Author
        $authorEl = $this->xpath('/CCDA/author')->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        $component->appendChild($obs);
        $organizer->appendChild($component);
    }

    private function renderClinicalNoteSections(DOMElement $structuredBody): void
    {
        // Map of clinical_notes_type to section metadata
        $noteTypes = [
            'history_physical' => [
                'displayName' => 'History and Physical Note',
                'title' => 'History and Physical Notes',
            ],
            'progress_note' => [
                'displayName' => 'Progress Note',
                'title' => 'Progress Notes',
            ],
            'procedure_note' => [
                'displayName' => 'Procedure Note',
                'title' => 'Procedure Notes',
            ],
            'nurse_note' => [
                'displayName' => 'Nurse Notes',
                'title' => 'Nurse Notes',
            ],
            'general_note' => [
                'displayName' => 'General Note',
                'title' => 'General Notes',
            ],
            'consultation_note' => [
                'displayName' => 'Consultation Note',
                'title' => 'Consultation Notes',
            ],
            'discharge_summary' => [
                'displayName' => 'Discharge Summary',
                'title' => 'Discharge Summary',
            ],
            'laboratory_report_narrative' => [
                'displayName' => 'Laboratory Report Narrative',
                'title' => 'Laboratory Report Narrative',
            ],
            'imaging_narrative' => [
                'displayName' => 'Imaging Narrative',
                'title' => 'Imaging Narrative',
            ],
            'pathology_report_narrative' => [
                'displayName' => 'Pathology Report Narrative',
                'title' => 'Pathology Report Narrative',
            ],
        ];

        // Group notes by type
        $notesByType = $this->groupClinicalNotesByType();

        // Render each section that has notes
        foreach ($noteTypes as $type => $metadata) {
            if (array_key_exists($type, $notesByType) && count($notesByType[$type]) > 0) {
                $this->renderClinicalNoteSection(
                    $structuredBody,
                    $notesByType[$type],
                    $metadata['displayName'],
                    $metadata['title']
                );
            }
        }
    }

    /**
     * @return array<string, list<DOMElement>>
     */
    private function groupClinicalNotesByType(): array
    {
        $notesByType = [];
        $clinicalNotes = $this->xpath('/CCDA/clinical_notes/*');

        foreach ($clinicalNotes as $note) {
            $type = $this->xpathValue('clinical_notes_type', $note);
            // Skip evaluation_note as it's handled by Assessment section
            if ($type === '' || $type === 'evaluation_note') {
                continue;
            }
            if (!array_key_exists($type, $notesByType)) {
                $notesByType[$type] = [];
            }
            $notesByType[$type][] = $note;
        }

        return $notesByType;
    }

    /**
     * @param list<DOMElement> $notes
     */
    private function renderClinicalNoteSection(
        DOMElement $structuredBody,
        array $notes,
        string $displayName,
        string $title
    ): void {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.65', '2016-11-01');

        $id = $this->createElement('id');
        $id->setAttribute('root', '16C8G888-10D9-23E6-H141-0080055B0002');
        $section->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', '34117-2');
        $code->setAttribute('displayName', $displayName);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

        $section->appendChild($this->createElement('title', $title));

        // Narrative table
        $this->appendClinicalNoteNarrative($section, $notes);

        // Entries
        $refIndex = 1;
        foreach ($notes as $note) {
            $this->appendClinicalNoteEntry($section, $note, $refIndex);
            $refIndex++;
        }

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    /**
     * @param list<DOMElement> $notes
     */
    private function appendClinicalNoteNarrative(DOMElement $section, array $notes): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Summary', 'Author', 'Date']);

        $refIndex = 1;
        foreach ($notes as $note) {
            $description = $this->xpathValue('description', $note);
            $authorFname = $this->xpathValue('author/fname', $note);
            $authorLname = $this->xpathValue('author/lname', $note);
            $authorName = trim($authorFname . ' ' . $authorLname);
            $date = $this->xpathValue('date', $note);

            $this->appendTableRow($table, [
                $description,
                $authorName,
                $this->formatDateForDisplay($date),
            ], 'note' . $refIndex);
            $refIndex++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendClinicalNoteEntry(DOMElement $section, DOMElement $note, int $refIndex): void
    {
        $entry = $this->createElement('entry');

        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'ACT');
        $act->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.202', '2016-11-01');

        // Code with translation
        $code = $this->createElement('code');
        $code->setAttribute('code', '34109-9');
        $code->setAttribute('displayName', 'Note');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');

        $noteCode = $this->xpathValue('code', $note);
        $codeText = $this->xpathValue('code_text', $note);
        if ($noteCode !== '' || $codeText !== '') {
            $translation = $this->createElement('translation');
            if ($noteCode !== '') {
                $translation->setAttribute('code', $this->cleanCode($noteCode));
            }
            if ($codeText !== '') {
                $translation->setAttribute('displayName', $codeText);
            }
            $translation->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $translation->setAttribute('codeSystemName', 'LOINC');
            $code->appendChild($translation);
        }
        $act->appendChild($code);

        // Text reference
        $text = $this->createElement('text');
        $reference = $this->createElement('reference');
        $reference->setAttribute('value', '#note' . $refIndex);
        $text->appendChild($reference);
        $act->appendChild($text);

        // Status
        $this->appendStatusCode($act, ActStatus::Completed);

        // Effective time
        $date = $this->xpathValue('date', $note);
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
            $act->appendChild($effectiveTime);
        }

        // Author
        $authorEl = $this->xpath('author', $note)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($act, $authorEl);
        }

        $entry->appendChild($act);
        $section->appendChild($entry);
    }

    private function renderMentalStatusSection(DOMElement $structuredBody): void
    {
        $mentalStatus = $this->xpath('/CCDA/mental_status/item');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($mentalStatus->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.56', '2015-08-01');

        $section->appendChild($this->createLoincCode('10190-7', 'Mental Status'));
        $section->appendChild($this->createElement('title', 'Mental Status'));

        if ($mentalStatus->length === 0) {
            $section->appendChild($this->createElement('text', 'Mental Status Not Available'));
        } else {
            // Text from first item's description/note
            $firstItem = $mentalStatus->item(0);
            $note = '';
            if ($firstItem instanceof DOMElement) {
                $note = trim($this->xpathValue('description', $firstItem));
            }
            $section->appendChild($this->createElement('text', $note !== '' ? $note : 'Mental Status'));

            foreach ($mentalStatus as $item) {
                $this->appendMentalStatusEntry($section, $item);
            }
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    private function appendMentalStatusEntry(DOMElement $section, DOMElement $item): void
    {
        $entry = $this->createElement('entry');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.74', '2015-08-01');

        $id = $this->createElement('id');
        $id->setAttribute('root', '9a6d1bac-17d3-4195-89a4-1121bc809ccc');
        $obs->appendChild($id);

        // Code - Cognitive function with LOINC translation
        $code = $this->createElement('code');
        $code->setAttribute('code', '373930000');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', 'SNOMED CT');
        $code->setAttribute('displayName', 'Cognitive function');
        $translation = $this->createElement('translation');
        $translation->setAttribute('code', '75275-8');
        $translation->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $translation->setAttribute('codeSystemName', 'LOINC');
        $translation->setAttribute('displayName', 'Cognitive function');
        $code->appendChild($translation);
        $obs->appendChild($code);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $date = $this->xpathValue('date', $item);
        $effectiveTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($date));
        $effectiveTime->appendChild($low);
        $obs->appendChild($effectiveTime);

        // Value
        $itemCode = $this->xpathValue('code', $item);
        $codeText = $this->xpathValue('code_text', $item);
        $codeType = $this->xpathValue('code_type', $item);

        if ($itemCode !== '' || ($codeText !== '' && $codeText !== 'NULL')) {
            $value = $this->output->createElement('value');
            $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
            if ($itemCode !== '') {
                $value->setAttribute('code', $this->cleanCode($itemCode));
            }
            if ($codeText !== '' && $codeText !== 'NULL') {
                $value->setAttribute('displayName', $codeText);
            }
            if ($codeType !== '') {
                $value->setAttribute('codeSystemName', $codeType);
            }
            $obs->appendChild($value);
        }

        // Author
        $authorEl = $this->xpath('/CCDA/author')->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function renderPlanOfCareSection(DOMElement $structuredBody): void
    {
        $planOfCare = $this->xpath('/CCDA/planofcare/item');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($planOfCare->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.10', '2014-06-09');

        $section->appendChild($this->createLoincCode('18776-5', 'Treatment Plan'));
        $section->appendChild($this->createElement('title', 'Treatment Plan'));

        if ($planOfCare->length === 0) {
            $section->appendChild($this->createElement('text', 'Not Available'));
        } else {
            $this->appendPlanOfCareNarrative($section, $planOfCare);
            foreach ($planOfCare as $item) {
                $this->appendPlanOfCareEntry($section, $item);
            }
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $items
     */
    private function appendPlanOfCareNarrative(DOMElement $section, \DOMNodeList $items): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Plan', 'Date']);

        foreach ($items as $item) {
            $description = $this->xpathValue('description', $item);
            $date = $this->xpathValue('date', $item);
            $proposedDate = $this->xpathValue('proposed_date', $item);
            $displayDate = $proposedDate !== '' ? $proposedDate : $date;

            $this->appendTableRow($table, [$description, $this->formatDateForDisplay($displayDate)]);
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendPlanOfCareEntry(DOMElement $section, DOMElement $item): void
    {
        $carePlanType = $this->xpathValue('care_plan_type', $item);
        $codeType = $this->xpathValue('code_type', $item);

        // Normalize RXCUI to RXNORM
        if ($codeType === 'RXCUI') {
            $codeType = 'RXNORM';
        }

        // Determine entry type based on care_plan_type and code_type
        $entryType = match ($carePlanType) {
            'plan_of_care' => 'observation',
            'test_or_order' => 'observation',
            'procedure' => 'procedure',
            'planned_procedure' => 'planned_procedure',
            'appointments' => 'encounter',
            'instructions' => 'instructions',
            'referral' => '', // Excluded
            default => 'observation',
        };

        // RXNORM codes use substanceAdministration
        if ($codeType === 'RXNORM') {
            $entryType = 'substanceAdministration';
        }

        if ($entryType === '') {
            return;
        }

        // Determine moodCode for observations
        $moodCode = match ($carePlanType) {
            'test_or_order' => 'RQO',
            default => 'INT',
        };

        $entry = $this->createElement('entry');
        if ($entryType === 'observation') {
            $entry->setAttribute('typeCode', 'DRIV');
        }

        match ($entryType) {
            'observation' => $this->appendPlanOfCareObservation($entry, $item, $moodCode),
            'procedure' => $this->appendPlanOfCareProcedure($entry, $item, false),
            'planned_procedure' => $this->appendPlanOfCareProcedure($entry, $item, true),
            'encounter' => $this->appendPlanOfCareEncounter($entry, $item),
            'substanceAdministration' => $this->appendPlanOfCareSubstanceAdministration($entry, $item),
            'instructions' => $this->appendPlanOfCareInstructions($entry, $item),
        };

        $section->appendChild($entry);
    }

    private function appendPlanOfCareObservation(DOMElement $entry, DOMElement $item, string $moodCode): void
    {
        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', $moodCode);

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.44');

        // ID
        $shaExt = $this->xpathValue('sha_extension', $item);
        $ext = $this->xpathValue('extension', $item);
        $this->appendIds($obs, $shaExt, $ext);

        // Code (plan)
        $code = $this->createElement('code');
        $planCode = $this->xpathValue('code', $item);
        $planText = $this->xpathValue('code_text', $item);
        $planCodeType = $this->xpathValue('code_type', $item);
        if ($planCode !== '') {
            $code->setAttribute('code', $this->cleanCode($planCode));
        }
        if ($planText !== '') {
            $code->setAttribute('displayName', $planText);
        }
        $this->setCodeSystemAttributes($code, $planCodeType !== '' ? $planCodeType : 'SNOMED CT');
        $obs->appendChild($code);

        // Status
        $this->appendStatusCode($obs, ActStatus::Active);

        // Effective time
        $date = $this->xpathValue('proposed_date', $item);
        if ($date === '') {
            $date = $this->xpathValue('date', $item);
        }
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
            $obs->appendChild($effectiveTime);
        }

        // Value (plan name as ST)
        $description = $this->xpathValue('description', $item);
        if ($description !== '') {
            $value = $this->output->createElement('value');
            $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'ST');
            $value->appendChild($this->output->createTextNode($description));
            $obs->appendChild($value);
        }

        // Author
        $authorEl = $this->xpath('author', $item)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        $entry->appendChild($obs);
    }

    private function appendPlanOfCareProcedure(DOMElement $entry, DOMElement $item, bool $isPlanned): void
    {
        $proc = $this->createElement('procedure');
        $proc->setAttribute('classCode', 'PROC');
        $proc->setAttribute('moodCode', 'RQO');

        $this->appendTemplateId($proc, '2.16.840.1.113883.10.20.22.4.41');
        if ($isPlanned) {
            $this->appendTemplateId($proc, '2.16.840.1.113883.10.20.22.4.41', '2014-06-09');
            $this->appendTemplateId($proc, '2.16.840.1.113883.10.20.22.4.41', '2022-06-01');
        }

        // ID
        $shaExt = $this->xpathValue('sha_extension', $item);
        $ext = $this->xpathValue('extension', $item);
        $this->appendIds($proc, $shaExt, $ext);

        // Code
        $code = $this->createElement('code');
        $planCode = $this->xpathValue('code', $item);
        $planText = $this->xpathValue('code_text', $item);
        $planCodeType = $this->xpathValue('code_type', $item);
        if ($planCode !== '') {
            $code->setAttribute('code', $this->cleanCode($planCode));
        }
        if ($planText !== '') {
            $code->setAttribute('displayName', $planText);
        }
        $this->setCodeSystemAttributes($code, $planCodeType !== '' ? $planCodeType : 'SNOMED CT');
        $proc->appendChild($code);

        // Status
        $this->appendStatusCode($proc, ActStatus::Active);

        // Effective time
        $date = $this->xpathValue('proposed_date', $item);
        if ($date === '') {
            $date = $this->xpathValue('date', $item);
        }
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
            $proc->appendChild($effectiveTime);
        }

        // Author
        $authorEl = $this->xpath('author', $item)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($proc, $authorEl);
        }

        $entry->appendChild($proc);
    }

    private function appendPlanOfCareEncounter(DOMElement $entry, DOMElement $item): void
    {
        $enc = $this->createElement('encounter');
        $enc->setAttribute('classCode', 'ENC');
        $enc->setAttribute('moodCode', 'INT');

        $this->appendTemplateId($enc, '2.16.840.1.113883.10.20.22.4.40');

        // ID
        $shaExt = $this->xpathValue('sha_extension', $item);
        $ext = $this->xpathValue('extension', $item);
        $this->appendIds($enc, $shaExt, $ext);

        // Code
        $code = $this->createElement('code');
        $planCode = $this->xpathValue('code', $item);
        $planText = $this->xpathValue('code_text', $item);
        $planCodeType = $this->xpathValue('code_type', $item);
        if ($planCode !== '') {
            $code->setAttribute('code', $this->cleanCode($planCode));
        }
        if ($planText !== '') {
            $code->setAttribute('displayName', $planText);
        }
        $this->setCodeSystemAttributes($code, $planCodeType !== '' ? $planCodeType : 'SNOMED CT');
        $enc->appendChild($code);

        // Status
        $this->appendStatusCode($enc, ActStatus::Active);

        // Effective time
        $date = $this->xpathValue('proposed_date', $item);
        if ($date === '') {
            $date = $this->xpathValue('date', $item);
        }
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
            $enc->appendChild($effectiveTime);
        }

        $entry->appendChild($enc);
    }

    private function appendPlanOfCareSubstanceAdministration(DOMElement $entry, DOMElement $item): void
    {
        $subAdmin = $this->createElement('substanceAdministration');
        $subAdmin->setAttribute('classCode', 'SBADM');
        $subAdmin->setAttribute('moodCode', 'RQO');

        $this->appendVersionedTemplateId($subAdmin, '2.16.840.1.113883.10.20.22.4.42', '2014-06-09');

        // ID
        $shaExt = $this->xpathValue('sha_extension', $item);
        $ext = $this->xpathValue('extension', $item);
        $this->appendIds($subAdmin, $shaExt, $ext);

        // Text (description/name)
        $description = $this->xpathValue('description', $item);
        if ($description !== '') {
            $text = $this->createElement('text', $description);
            $subAdmin->appendChild($text);
        }

        // Status
        $this->appendStatusCode($subAdmin, ActStatus::Active);

        // Effective time
        $date = $this->xpathValue('proposed_date', $item);
        if ($date === '') {
            $date = $this->xpathValue('date', $item);
        }
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
            $subAdmin->appendChild($effectiveTime);
        }

        // Consumable with medication information
        $consumable = $this->createElement('consumable');
        $manuProd = $this->createElement('manufacturedProduct');
        $manuProd->setAttribute('classCode', 'MANU');

        $this->appendVersionedTemplateId($manuProd, '2.16.840.1.113883.10.20.22.4.23', '2014-06-09');

        $manuMaterial = $this->createElement('manufacturedMaterial');
        $code = $this->createElement('code');
        $planCode = $this->xpathValue('code', $item);
        $planText = $this->xpathValue('code_text', $item);
        if ($planCode !== '') {
            $code->setAttribute('code', $this->cleanCode($planCode));
        }
        if ($planText !== '') {
            $code->setAttribute('displayName', $planText);
        }
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.88');
        $code->setAttribute('codeSystemName', 'RXNORM');
        $manuMaterial->appendChild($code);
        $manuProd->appendChild($manuMaterial);
        $consumable->appendChild($manuProd);
        $subAdmin->appendChild($consumable);

        $entry->appendChild($subAdmin);
    }

    private function appendPlanOfCareInstructions(DOMElement $entry, DOMElement $item): void
    {
        $inst = $this->createElement('instructions');
        $inst->setAttribute('classCode', 'ACT');
        $inst->setAttribute('moodCode', 'INT');

        $this->appendTemplateId($inst, '2.16.840.1.113883.10.20.22.4.20');

        // ID
        $shaExt = $this->xpathValue('sha_extension', $item);
        $ext = $this->xpathValue('extension', $item);
        $this->appendIds($inst, $shaExt, $ext);

        // Code
        $code = $this->createElement('code');
        $planCode = $this->xpathValue('code', $item);
        $planText = $this->xpathValue('code_text', $item);
        $planCodeType = $this->xpathValue('code_type', $item);
        if ($planCode !== '') {
            $code->setAttribute('code', $this->cleanCode($planCode));
        }
        if ($planText !== '') {
            $code->setAttribute('displayName', $planText);
        }
        $this->setCodeSystemAttributes($code, $planCodeType !== '' ? $planCodeType : 'SNOMED CT');
        $inst->appendChild($code);

        // Status
        $this->appendStatusCode($inst, ActStatus::Active);

        $entry->appendChild($inst);
    }

    private function appendIds(DOMElement $parent, string $shaExt, string $ext): void
    {
        // UniqueId
        if ($shaExt !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $shaExt);
            $parent->appendChild($uniqueId);
        }

        // Extension ID
        if ($ext !== '') {
            $id = $this->createElement('id');
            $id->setAttribute('root', '9a6d1bac-17d3-4195-89a4-1121bc8090ab');
            $id->setAttribute('extension', $ext);
            $parent->appendChild($id);
        }
    }

    private function renderGoalsSection(DOMElement $structuredBody): void
    {
        $goals = $this->xpath('/CCDA/goals/goal');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($goals->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.60');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.60', '2015-08-01');

        $section->appendChild($this->createLoincCode('61146-7', 'Goals'));
        $section->appendChild($this->createElement('title', 'Goals'));

        if ($goals->length === 0) {
            $section->appendChild($this->createElement('text', 'Not Available'));
        } else {
            $this->appendGoalsNarrative($section, $goals);
            foreach ($goals as $goal) {
                $this->appendGoalEntry($section, $goal);
            }
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $goals
     */
    private function appendGoalsNarrative(DOMElement $section, \DOMNodeList $goals): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Goal', 'Date']);

        $index = 1;
        foreach ($goals as $goal) {
            $description = $this->xpathValue('description', $goal);
            $codeText = $this->xpathValue('code_text', $goal);
            $date = $this->xpathValue('date', $goal);

            $displayText = $description !== '' ? $description : ($codeText !== '' && $codeText !== 'NULL' ? $codeText : '');
            $this->appendTableRow($table, [$displayText, $date], 'goal' . $index);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendGoalEntry(DOMElement $section, DOMElement $goal): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'GOL');

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.121');
        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.121', '2022-06-01');

        // uniqueId
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $obs->appendChild($uniqueId);
        }

        // Regular id
        $shaExt = $this->xpathValue('sha_extension', $goal);
        $ext = $this->xpathValue('extension', $goal);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt !== '' ? $shaExt : 'NI');
        $id->setAttribute('extension', $ext);
        $obs->appendChild($id);

        // Code
        $goalCode = $this->xpathValue('code', $goal);
        $codeText = $this->xpathValue('code_text', $goal);
        $codeType = $this->xpathValue('code_type', $goal);
        $code = $this->createElement('code');
        if ($goalCode !== '') {
            $code->setAttribute('code', $this->cleanCode($goalCode));
            if ($codeText !== '' && $codeText !== 'NULL') {
                $code->setAttribute('displayName', $codeText);
            }
            if ($codeType !== '') {
                $code->setAttribute('codeSystemName', $codeType);
            }
        } else {
            $code->setAttribute('nullFlavor', 'UNK');
        }
        $obs->appendChild($code);

        $this->appendStatusCode($obs, ActStatus::Active);

        $date = $this->xpathValue('date', $goal);
        $effectiveTime = $this->createElement('effectiveTime');
        $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
        $obs->appendChild($effectiveTime);

        // Value - ST or CD based on sdoh_code presence
        $sdohCode = $this->xpathValue('sdoh_code', $goal);
        $value = $this->output->createElement('value');
        if ($sdohCode !== '') {
            $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
            $value->setAttribute('code', $sdohCode);
            $sdohCodeSystem = $this->xpathValue('sdoh_code_system', $goal);
            if ($sdohCodeSystem !== '') {
                $value->setAttribute('codeSystem', $sdohCodeSystem);
            }
            $sdohCodeType = $this->xpathValue('sdoh_code_type', $goal);
            if ($sdohCodeType !== '') {
                $value->setAttribute('codeSystemName', $sdohCodeType);
            }
            $sdohCodeText = $this->xpathValue('sdoh_code_text', $goal);
            if ($sdohCodeText !== '') {
                $value->setAttribute('displayName', $sdohCodeText);
            }
        } else {
            $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'ST');
            $description = $this->xpathValue('description', $goal);
            if ($description !== '') {
                $value->nodeValue = $description;
            }
        }
        $obs->appendChild($value);

        $authorEl = $this->xpath('author', $goal)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function renderHealthConcernsSection(DOMElement $structuredBody): void
    {
        $concerns = $this->xpath('/CCDA/health_concerns/concern');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($concerns->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.58', '2015-08-01');

        $section->appendChild($this->createLoincCode('75310-3', 'Health Concerns Document'));
        $section->appendChild($this->createElement('title', 'Health Concerns Document'));

        if ($concerns->length === 0) {
            $section->appendChild($this->createElement('text', 'Not Available'));
        } else {
            $this->appendHealthConcernsNarrative($section, $concerns);
            foreach ($concerns as $concern) {
                $this->appendHealthConcernEntry($section, $concern);
            }
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $concerns
     */
    private function appendHealthConcernsNarrative(DOMElement $section, \DOMNodeList $concerns): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Concern', 'Date']);

        $index = 1;
        foreach ($concerns as $concern) {
            $concernText = $this->xpathValue('text', $concern);
            $codeText = $this->xpathValue('code_text', $concern);
            $date = $this->xpathValue('date', $concern);

            $displayText = $concernText !== '' ? $concernText : $codeText;
            $this->appendTableRow($table, [$displayText, $date], 'concern' . $index);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendHealthConcernEntry(DOMElement $section, DOMElement $concern): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'ACT');
        $act->setAttribute('moodCode', 'EVN');

        // Health Concern Act template IDs
        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.132');
        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.132', '2015-08-01');
        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.132', '2022-06-01');

        // uniqueId
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $act->appendChild($uniqueId);
        }

        // Regular id
        $shaExt = $this->xpathValue('sha_extension', $concern);
        $ext = $this->xpathValue('extension', $concern);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt !== '' ? $shaExt : 'NI');
        $id->setAttribute('extension', $ext);
        $act->appendChild($id);

        $act->appendChild($this->createLoincCode('75310-3', 'Health Concern'));

        $this->appendStatusCode($act, ActStatus::Active);

        $date = $this->xpathValue('date', $concern);
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $low = $this->createElement('low');
            $low->setAttribute('value', $this->formatDateOnly($date));
            $effectiveTime->appendChild($low);
            $act->appendChild($effectiveTime);
        }

        $authorEl = $this->xpath('author', $concern)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($act, $authorEl);
        }

        // Nested Problem Observation
        $this->appendHealthConcernObservation($act, $concern);

        $entry->appendChild($act);
        $section->appendChild($entry);
    }

    private function appendHealthConcernObservation(DOMElement $act, DOMElement $concern): void
    {
        $entryRel = $this->createElement('entryRelationship');
        $entryRel->setAttribute('typeCode', 'REFR');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        // Problem Observation V3 template IDs
        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.4');
        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.4', '2015-08-01');

        // uniqueId
        $facilityOid = $this->xpathValue('/CCDA/encounter_provider/facility_oid');
        if ($facilityOid !== '') {
            $uniqueId = $this->createElement('id');
            $uniqueId->setAttribute('root', $facilityOid);
            $uniqueId->setAttribute('extension', $this->generateUuid());
            $obs->appendChild($uniqueId);
        }

        // id
        $shaExt = $this->xpathValue('sha_extension', $concern);
        $ext = $this->xpathValue('extension', $concern);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt !== '' ? $shaExt : 'NI');
        $id->setAttribute('extension', $ext . '-obs');
        $obs->appendChild($id);

        // code - Clinical finding with LOINC translation
        $code = $this->createElement('code');
        $code->setAttribute('code', '404684003');
        $code->setAttribute('displayName', 'Clinical finding (finding)');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.96');
        $code->setAttribute('codeSystemName', 'SNOMED CT');
        $translation = $this->createElement('translation');
        $translation->setAttribute('code', '75321-0');
        $translation->setAttribute('displayName', 'Clinical Finding');
        $translation->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $translation->setAttribute('codeSystemName', 'LOINC');
        $code->appendChild($translation);
        $obs->appendChild($code);

        $this->appendStatusCode($obs, ActStatus::Completed);

        $date = $this->xpathValue('date', $concern);
        if ($date !== '') {
            $effectiveTime = $this->createElement('effectiveTime');
            $low = $this->createElement('low');
            $low->setAttribute('value', $this->formatDateOnly($date));
            $effectiveTime->appendChild($low);
            $obs->appendChild($effectiveTime);
        }

        // value - CD type with concern code
        $concernCode = $this->xpathValue('code', $concern);
        $codeText = $this->xpathValue('code_text', $concern);
        $codeType = $this->xpathValue('code_type', $concern);

        if ($concernCode !== '' || $codeText !== '') {
            $value = $this->output->createElement('value');
            $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
            if ($concernCode !== '') {
                $value->setAttribute('code', $this->cleanCode($concernCode));
            }
            if ($codeText !== '') {
                $value->setAttribute('displayName', $codeText);
            }
            // Determine code system
            $codeSystem = match (strtoupper(str_replace(' ', '', $codeType))) {
                'SNOMEDCT', 'SNOMED-CT', 'SNOMED' => '2.16.840.1.113883.6.96',
                'LOINC' => '2.16.840.1.113883.6.1',
                'ICD10' => '2.16.840.1.113883.6.90',
                default => '',
            };
            if ($codeSystem !== '') {
                $value->setAttribute('codeSystem', $codeSystem);
            }
            $codeSystemName = match (strtoupper(str_replace(' ', '', $codeType))) {
                'SNOMEDCT', 'SNOMED-CT', 'SNOMED' => 'SNOMED CT',
                'ICD10' => 'ICD-10-CM',
                default => $codeType,
            };
            if ($codeSystemName !== '') {
                $value->setAttribute('codeSystemName', $codeSystemName);
            }
            $obs->appendChild($value);
        }

        $authorEl = $this->xpath('author', $concern)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        $entryRel->appendChild($obs);
        $act->appendChild($entryRel);
    }

    private function renderAdvanceDirectivesSection(DOMElement $structuredBody): void
    {
        $directives = $this->xpath('/CCDA/advance_directives/directive');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($directives->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendVersionedTemplateId($section, '2.16.840.1.113883.10.20.22.2.21.1', '2015-08-01');

        $section->appendChild($this->createLoincCode('42348-3', 'Advance Directives'));
        $section->appendChild($this->createElement('title', 'Advance Directives'));

        if ($directives->length === 0) {
            $section->appendChild($this->createElement('text', 'Not Available'));
        } else {
            $this->appendAdvanceDirectivesNarrative($section, $directives);
            foreach ($directives as $directive) {
                $this->appendAdvanceDirectiveEntry($section, $directive);
            }
        }

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $directives
     */
    private function appendAdvanceDirectivesNarrative(DOMElement $section, \DOMNodeList $directives): void
    {
        $text = $this->createElement('text');
        $table = $this->createNarrativeTable(['Directive', 'Status', 'Effective Date']);

        $index = 1;
        foreach ($directives as $directive) {
            $type = $this->xpathValue('type', $directive);
            $status = $this->xpathValue('status', $directive);
            $effectiveDate = $this->xpathValue('effective_date', $directive);

            $this->appendTableRow($table, [$type, $status, $this->formatDateForDisplay($effectiveDate)], 'directive' . $index);
            $index++;
        }

        $text->appendChild($table);
        $section->appendChild($text);
    }

    private function appendAdvanceDirectiveEntry(DOMElement $section, DOMElement $directive): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendVersionedTemplateId($obs, '2.16.840.1.113883.10.20.22.4.48', '2015-08-01');

        // ID
        $shaExt = $this->xpathValue('sha_extension', $directive);
        $ext = $this->xpathValue('extension', $directive);
        $this->appendIds($obs, $shaExt, $ext);

        // Code with translation
        $obsCode = $this->xpathValue('observation/code', $directive);
        $obsCodeSystem = $this->xpathValue('observation/code_system', $directive);
        $obsDisplay = $this->xpathValue('observation/display', $directive);

        $code = $this->createElement('code');
        if ($obsCode !== '') {
            $code->setAttribute('code', $obsCode);
            $code->setAttribute('codeSystem', $obsCodeSystem !== '' ? $obsCodeSystem : '2.16.840.1.113883.6.1');
            $codeSystemName = $obsCodeSystem === '2.16.840.1.113883.6.96' ? 'SNOMED CT' : 'LOINC';
            $code->setAttribute('codeSystemName', $codeSystemName);
            $code->setAttribute('displayName', $obsDisplay);
        } else {
            $code->setAttribute('nullFlavor', 'UNK');
        }

        // Translation
        $translation = $this->createElement('translation');
        $translation->setAttribute('code', '75320-2');
        $translation->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $translation->setAttribute('codeSystemName', 'LOINC');
        $translation->setAttribute('displayName', 'Advance directive');
        $code->appendChild($translation);
        $obs->appendChild($code);

        // Status
        $this->appendStatusCode($obs, ActStatus::Completed);

        // Effective time
        $effectiveDate = $this->xpathValue('observation/effective_date', $directive);
        if ($effectiveDate === '') {
            $effectiveDate = $this->xpathValue('effective_date', $directive);
        }
        $effTime = $this->createElement('effectiveTime');
        $low = $this->createElement('low');
        $low->setAttribute('value', $this->formatDateOnly($effectiveDate));
        $effTime->appendChild($low);
        $high = $this->createElement('high');
        $high->setAttribute('nullFlavor', 'NA');
        $effTime->appendChild($high);
        $obs->appendChild($effTime);

        // Value
        $valueCode = $this->xpathValue('observation/value_code', $directive);
        $valueCodeSystem = $this->xpathValue('observation/value_code_system', $directive);
        $valueDisplay = $this->xpathValue('observation/value_display', $directive);

        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'CD');
        $value->setAttribute('code', $valueCode !== '' ? $valueCode : '373066001');
        $value->setAttribute('codeSystem', $valueCodeSystem !== '' ? $valueCodeSystem : '2.16.840.1.113883.6.96');
        $valueCodeSystemName = $valueCodeSystem === '2.16.840.1.113883.6.1' ? 'LOINC' : 'SNOMED CT';
        $value->setAttribute('codeSystemName', $valueCodeSystemName);
        $value->setAttribute('displayName', $valueDisplay !== '' ? $valueDisplay : 'Yes (qualifier value)');
        $obs->appendChild($value);

        // Author
        $authorEl = $this->xpath('author', $directive)->item(0);
        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($obs, $authorEl);
        }

        // Participant (custodian)
        $participant = $this->createElement('participant');
        $participant->setAttribute('typeCode', 'CST');
        $participantRole = $this->createElement('participantRole');
        $participantRole->setAttribute('classCode', 'AGNT');

        $addr = $this->createElement('addr');
        $addr->setAttribute('nullFlavor', 'UNK');
        $participantRole->appendChild($addr);

        $telecom = $this->createElement('telecom');
        $telecom->setAttribute('nullFlavor', 'UNK');
        $participantRole->appendChild($telecom);

        $playingEntity = $this->createElement('playingEntity');
        $playingEntity->setAttribute('classCode', 'PSN');
        $name = $this->createElement('name');
        $name->setAttribute('nullFlavor', 'UNK');
        $playingEntity->appendChild($name);
        $participantRole->appendChild($playingEntity);

        $participant->appendChild($participantRole);
        $obs->appendChild($participant);

        // Reference to external document
        $documentRef = $this->xpathValue('document_reference', $directive);
        if ($documentRef !== '') {
            $reference = $this->createElement('reference');
            $reference->setAttribute('typeCode', 'REFR');

            $externalDoc = $this->createElement('externalDocument');
            $externalDoc->setAttribute('classCode', 'DOC');
            $externalDoc->setAttribute('moodCode', 'EVN');

            $docId = $this->createElement('id');
            $docId->setAttribute('root', $documentRef);
            $externalDoc->appendChild($docId);

            $docCode = $this->createElement('code');
            $docCode->setAttribute('nullFlavor', 'UNK');
            $externalDoc->appendChild($docCode);

            $location = $this->xpathValue('location', $directive);
            if ($location !== '') {
                $docText = $this->createElement('text');
                $docText->setAttribute('mediaType', 'text/plain');
                $docRef = $this->createElement('reference');
                $docRef->setAttribute('value', $location);
                $docText->appendChild($docRef);
                $externalDoc->appendChild($docText);
            }

            $reference->appendChild($externalDoc);
            $obs->appendChild($reference);
        }

        $entry->appendChild($obs);
        $section->appendChild($entry);
    }

    private function renderReasonForReferralSection(DOMElement $structuredBody): void
    {
        $reasonText = trim($this->xpathValue('/CCDA/referral_reason/text'));
        $authorEl = $this->xpath('/CCDA/referral_reason/author')->item(0);

        $component = $this->createElement('component');
        $section = $this->createElement('section');

        if ($reasonText === '') {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendVersionedTemplateId($section, '1.3.6.1.4.1.19376.1.5.3.1.3.1', '2014-06-09');

        $section->appendChild($this->createLoincCode('42349-1', 'Reason for Referral'));
        $section->appendChild($this->createElement('title', 'Reason for Referral'));

        if ($reasonText !== '') {
            $section->appendChild($this->createElement('text', $reasonText));
            if ($authorEl instanceof DOMElement) {
                $this->appendEntryAuthor($section, $authorEl);
            }
        } else {
            $section->appendChild($this->createElement('text', 'Not Available'));
        }

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    private function renderAssessmentSection(DOMElement $structuredBody): void
    {
        $assessments = $this->xpath('/CCDA/clinical_notes/evaluation_note');
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        // Use first assessment's description for the section text
        $description = '';
        $authorEl = null;
        if ($assessments->length > 0) {
            $firstAssessment = $assessments->item(0);
            if ($firstAssessment instanceof DOMElement) {
                $description = trim($this->xpathValue('description', $firstAssessment));
                $authorEl = $this->xpath('author', $firstAssessment)->item(0);
            }
        }

        if ($description === '') {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.8');

        $section->appendChild($this->createLoincCode('51848-0', 'Assessments'));
        $section->appendChild($this->createElement('title', 'Assessments'));

        if ($description !== '') {
            $section->appendChild($this->createElement('text', $description));
        } else {
            $section->appendChild($this->createElement('text', 'Not Available'));
        }

        if ($authorEl instanceof DOMElement) {
            $this->appendEntryAuthor($section, $authorEl);
        }

        $this->appendSection($structuredBody, $component, $section);
    }

    /**
     * @return DOMNodeList<DOMElement>
     */
    private function xpath(string $query, ?DOMElement $context = null): DOMNodeList
    {
        $result = $this->inputXpath->query($query, $context ?? $this->input->documentElement);
        if ($result === false) {
            throw new \RuntimeException("Invalid XPath query: $query");
        }
        /** @var DOMNodeList<DOMElement> */
        return $result;
    }

    private function xpathValue(string $query, ?DOMElement $context = null): string
    {
        $nodes = $this->xpath($query, $context);
        return $nodes->length > 0 ? trim((string) $nodes->item(0)?->textContent) : '';
    }

    private function createElement(string $name, ?string $text = null): DOMElement
    {
        $el = $this->output->createElement($name);
        if ($text !== null) {
            $el->textContent = $text;
        }
        return $el;
    }

    private function appendTemplateId(DOMElement $parent, string $root, ?string $extension = null): void
    {
        $el = $this->createElement('templateId');
        $el->setAttribute('root', $root);
        if ($extension !== null) {
            $el->setAttribute('extension', $extension);
        }
        $parent->appendChild($el);
    }

    private function appendVersionedTemplateId(DOMElement $parent, string $root, string $extension): void
    {
        $this->appendTemplateId($parent, $root, $extension);
        $this->appendTemplateId($parent, $root);
    }

    private function appendId(DOMElement $parent, string $root, string $extension = ''): void
    {
        $el = $this->createElement('id');
        $el->setAttribute('root', $root);
        if ($extension !== '') {
            $el->setAttribute('extension', $extension);
        }
        $parent->appendChild($el);
    }

    private function cleanCode(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return 'null_flavor';
        }
        return (string) preg_replace('/[.#]/', '', $code);
    }

    private function mapRouteCode(string $routeCode): string
    {
        $cleaned = $this->cleanCode($routeCode);
        if ($cleaned === 'null_flavor') {
            return '';
        }

        // Already a valid NCI code (C followed by digits)
        if (preg_match('/^C\d+$/', $cleaned) === 1) {
            return $cleaned;
        }

        // Common route abbreviation to NCI Thesaurus code mapping
        $routeMap = [
            'PO' => 'C38288',
            'ORAL' => 'C38288',
            'IV' => 'C38276',
            'IM' => 'C28161',
            'SC' => 'C38299',
            'SUBCUT' => 'C38299',
            'SQ' => 'C38299',
            'TOP' => 'C38304',
            'TOPICAL' => 'C38304',
            'INH' => 'C38216',
            'NASAL' => 'C38284',
            'OPTH' => 'C38287',
            'OTIC' => 'C38192',
            'RECTAL' => 'C38295',
            'VAGINAL' => 'C38313',
            'SL' => 'C38300',
            'BUCCAL' => 'C38193',
            'TD' => 'C38305',
        ];

        return $routeMap[strtoupper($cleaned)] ?? $cleaned;
    }

    /**
     * Creates a section with standard structure: component > section > templateIds > code > title
     *
     * @param string $templateId The primary template ID (will add both with and without extension)
     * @param string $templateExtension The extension for the primary template ID
     * @param string $loincCode The LOINC code for the section
     * @param string $title The section title and code displayName
     * @return array{DOMElement, DOMElement} [$component, $section]
     */
    private function createSection(
        string $templateId,
        string $templateExtension,
        string $loincCode,
        string $title,
    ): array {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendTemplateId($section, $templateId, $templateExtension);
        $this->appendTemplateId($section, $templateId);

        $section->appendChild($this->createLoincCode($loincCode, $title));
        $section->appendChild($this->createElement('title', $title));

        return [$component, $section];
    }

    /**
     * Finishes a section by appending section to component and component to structuredBody
     */
    private function appendSection(DOMElement $structuredBody, DOMElement $component, DOMElement $section): void
    {
        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    /**
     * Creates a standard entry author block from input author element
     */
    private function appendEntryAuthor(DOMElement $parent, DOMElement $sourceAuthor): void
    {
        $author = $this->createElement('author');
        $author->setAttribute('typeCode', 'AUT');

        $this->appendTemplateId($author, '2.16.840.1.113883.10.20.22.4.119');

        $time = $this->xpathValue('time', $sourceAuthor);
        $timeEl = $this->createElement('time');
        $timeEl->setAttribute('value', $this->formatTimestamp($time));
        $author->appendChild($timeEl);

        $assignedAuthor = $this->createElement('assignedAuthor');

        $authorId = $this->xpathValue('id', $sourceAuthor);
        $authorNpi = $this->xpathValue('npi', $sourceAuthor);
        $id = $this->createElement('id');
        $id->setAttribute('root', $authorNpi !== '' ? '2.16.840.1.113883.4.6' : $authorId);
        $id->setAttribute('extension', $authorNpi !== '' ? $authorNpi : 'NI');
        $assignedAuthor->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', $this->xpathValue('physician_type_code', $sourceAuthor));
        $code->setAttribute('displayName', $this->xpathValue('physician_type', $sourceAuthor));
        $code->setAttribute('codeSystem', $this->xpathValue('physician_type_system', $sourceAuthor));
        $code->setAttribute('codeSystemName', $this->xpathValue('physician_type_system_name', $sourceAuthor));
        $assignedAuthor->appendChild($code);

        $assignedPerson = $this->createElement('assignedPerson');
        $name = $this->createElement('name');
        $lname = $this->xpathValue('lname', $sourceAuthor);
        $fname = $this->xpathValue('fname', $sourceAuthor);
        if ($lname !== '') {
            $name->appendChild($this->createElement('family', $lname));
        }
        if ($fname !== '') {
            $name->appendChild($this->createElement('given', $fname));
        }
        $assignedPerson->appendChild($name);
        $assignedAuthor->appendChild($assignedPerson);

        $repOrg = $this->createElement('representedOrganization');
        $orgId = $this->createElement('id');
        $facilityOid = $this->xpathValue('facility_oid', $sourceAuthor);
        $facilityNpi = $this->xpathValue('facility_npi', $sourceAuthor);
        $orgId->setAttribute('root', $facilityOid !== '' ? $facilityOid : '2.16.840.1.113883.4.6');
        $orgId->setAttribute('extension', $facilityNpi !== '' ? $facilityNpi : 'NI');
        $repOrg->appendChild($orgId);
        $facilityName = $this->xpathValue('facility_name', $sourceAuthor);
        $repOrg->appendChild($this->createElement('name', $facilityName !== '' ? $facilityName : null));
        $assignedAuthor->appendChild($repOrg);

        $author->appendChild($assignedAuthor);
        $parent->appendChild($author);
    }

    /**
     * Creates a standard narrative table for sections
     *
     * @param array<string> $headers Column headers
     * @return DOMElement The table element (caller adds tbody rows)
     */
    private function createNarrativeTable(array $headers): DOMElement
    {
        $table = $this->createElement('table');
        $table->setAttribute('width', '100%');
        $table->setAttribute('border', '1');

        $thead = $this->createElement('thead');
        $headerRow = $this->createElement('tr');
        foreach ($headers as $header) {
            $headerRow->appendChild($this->createElement('th', $header));
        }
        $thead->appendChild($headerRow);
        $table->appendChild($thead);

        return $table;
    }

    /**
     * Adds a tbody row to a narrative table
     *
     * @param array<string> $cells Cell values
     * @param string|null $firstCellId Optional ID attribute for first cell
     */
    private function appendTableRow(DOMElement $table, array $cells, ?string $firstCellId = null): void
    {
        $tbody = $this->createElement('tbody');
        $row = $this->createElement('tr');

        $isFirst = true;
        foreach ($cells as $cellValue) {
            $cell = $this->createElement('td', $cellValue);
            if ($isFirst && $firstCellId !== null) {
                $cell->setAttribute('ID', $firstCellId);
            }
            $row->appendChild($cell);
            $isFirst = false;
        }

        $tbody->appendChild($row);
        $table->appendChild($tbody);
    }

    private function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Creates a code element with LOINC code system
     */
    private function createLoincCode(string $code, string $displayName): DOMElement
    {
        $codeEl = $this->createElement('code');
        $codeEl->setAttribute('code', $code);
        $codeEl->setAttribute('displayName', $displayName);
        $codeEl->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $codeEl->setAttribute('codeSystemName', 'LOINC');
        return $codeEl;
    }

    /**
     * Sets codeSystem and codeSystemName attributes based on code system name
     */
    private function setCodeSystemAttributes(DOMElement $element, string $codeSystemName): void
    {
        $normalizedName = strtolower(str_replace(['-', ' '], '', $codeSystemName));
        $oid = match (true) {
            str_contains($normalizedName, 'snomed') => '2.16.840.1.113883.6.96',
            str_contains($normalizedName, 'loinc') => '2.16.840.1.113883.6.1',
            str_contains($normalizedName, 'rxnorm') || str_contains($normalizedName, 'rxcui') => '2.16.840.1.113883.6.88',
            str_contains($normalizedName, 'icd10') => '2.16.840.1.113883.6.90',
            str_contains($normalizedName, 'icd9') => '2.16.840.1.113883.6.103',
            str_contains($normalizedName, 'cpt') || str_contains($normalizedName, 'cpt4') => '2.16.840.1.113883.6.12',
            str_contains($normalizedName, 'cvx') => '2.16.840.1.113883.12.292',
            default => '2.16.840.1.113883.6.96', // Default to SNOMED CT
        };

        $displayName = match (true) {
            str_contains($normalizedName, 'snomed') => 'SNOMED CT',
            str_contains($normalizedName, 'loinc') => 'LOINC',
            str_contains($normalizedName, 'rxnorm') || str_contains($normalizedName, 'rxcui') => 'RXNORM',
            str_contains($normalizedName, 'icd10') => 'ICD-10-CM',
            str_contains($normalizedName, 'icd9') => 'ICD-9-CM',
            str_contains($normalizedName, 'cpt') || str_contains($normalizedName, 'cpt4') => 'CPT4',
            str_contains($normalizedName, 'cvx') => 'CVX',
            default => 'SNOMED CT',
        };

        $element->setAttribute('codeSystem', $oid);
        $element->setAttribute('codeSystemName', $displayName);
    }

    /**
     * Format date for display in narrative tables (Y-m-d format)
     */
    private function formatDateForDisplay(string $input): string
    {
        if ($input === '') {
            return '';
        }

        $datetime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $input);
        if ($datetime === false) {
            $datetime = \DateTimeImmutable::createFromFormat('Y-m-d', $input);
        }
        if ($datetime === false) {
            return $input;
        }
        return $datetime->format('Y-m-d');
    }

    /**
     * Appends a statusCode element to the parent
     */
    private function appendStatusCode(DOMElement $parent, ActStatus $status = ActStatus::Completed): void
    {
        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', $status->value);
        $parent->appendChild($statusCode);
    }
}
