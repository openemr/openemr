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
        $this->input->loadXML($internalXml);
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

        $this->appendTemplateId($root, '2.16.840.1.113883.10.20.22.1.1', '2023-05-01');
        $this->appendTemplateId($root, '2.16.840.1.113883.10.20.22.1.1');
        $this->appendTemplateId($root, '2.16.840.1.113883.10.20.22.1.1', '2015-08-01');
        $this->appendTemplateId($root, '2.16.840.1.113883.10.20.22.1.1');
        $this->appendTemplateId($root, $docOid, '2015-08-01');
        $this->appendTemplateId($root, $docOid);

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
        $id = $this->createElement('id');
        $id->setAttribute('root', '2.16.840.1.113883.4.6');
        $id->setAttribute('extension', $npi);
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
        if ($fname === '' && $lname === '') {
            return;
        }

        $infoRecipient = $this->createElement('informationRecipient');
        $intendedRecipient = $this->createElement('intendedRecipient');

        $recipient = $this->createElement('informationRecipient');
        $name = $this->createElement('name');
        if ($lname !== '') {
            $name->appendChild($this->createElement('family', $lname));
        }
        if ($fname !== '') {
            $name->appendChild($this->createElement('given', $fname));
        }
        $recipient->appendChild($name);
        $intendedRecipient->appendChild($recipient);

        $org = $this->xpathValue('/CCDA/information_recipient/organization');
        if ($org !== '') {
            $receivedOrg = $this->createElement('receivedOrganization');
            $receivedOrg->appendChild($this->createElement('name', $org));
            $intendedRecipient->appendChild($receivedOrg);
        }

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

        $npi = $this->xpathValue('/CCDA/author/npi');
        $id = $this->createElement('id');
        $id->setAttribute('root', '2.16.840.1.113883.4.6');
        $id->setAttribute('extension', $npi);
        $assignedEntity->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', $this->xpathValue('/CCDA/author/physician_type_code'));
        $code->setAttribute('displayName', $this->xpathValue('/CCDA/author/physician_type'));
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.101');
        $code->setAttribute('codeSystemName', 'NUCC Health Care Provider Taxonomy');
        $originalText = $this->createElement('originalText', 'Care Team Member');
        $code->appendChild($originalText);
        $assignedEntity->appendChild($code);

        $addr = $this->createElement('addr');
        $country = $this->xpathValue('/CCDA/author/country');
        $addr->appendChild($this->createElement('country', $country !== '' ? $country : 'US'));
        $assignedEntity->appendChild($addr);

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
        if ($input === '') {
            return '';
        }
        $input = str_replace([' ', '-', ':'], '', $input);
        if (strlen($input) >= 14 && str_contains($input, '+')) {
            $parts = explode('+', $input);
            return substr($parts[0], 0, 12) . '+' . $parts[1];
        }
        if (strlen($input) >= 12) {
            return substr($input, 0, 12) . '+0000';
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
        $this->renderEncountersSection($structuredBody);
        $this->renderImmunizationsSection($structuredBody);
        $this->renderVitalSignsSection($structuredBody);
        $this->renderSocialHistorySection($structuredBody);
        $this->renderPayersSection($structuredBody);
        $this->renderMedicalEquipmentSection($structuredBody);
        $this->renderFunctionalStatusSection($structuredBody);
        $this->renderMentalStatusSection($structuredBody);
        $this->renderPlanOfCareSection($structuredBody);
        $this->renderGoalsSection($structuredBody);
        $this->renderHealthConcernsSection($structuredBody);
        $this->renderAssessmentSection($structuredBody);

        $component->appendChild($structuredBody);
        $root->appendChild($component);
    }

    private function renderCareTeamSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $careTeam = $this->xpath('/CCDA/care_team/team');
        if ($careTeam->length === 0) {
            $section->setAttribute('nullFlavor', 'NI');
        }

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.500', '2019-07-01');

        $code = $this->createElement('code');
        $code->setAttribute('code', '85847-2');
        $code->setAttribute('displayName', 'Patient Care Teams');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

        $section->appendChild($this->createElement('title', 'Patient Care Teams'));

        if ($careTeam->length === 0) {
            $section->appendChild($this->createElement('text', 'A Care Team is not assigned.'));
        } else {
            // TODO: Implement care team entries
            $section->appendChild($this->createElement('text', 'Care Team information'));
        }

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    private function renderAllergiesSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.6.1', '2015-08-01');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.6.1');

        $code = $this->createElement('code');
        $code->setAttribute('code', '48765-2');
        $code->setAttribute('displayName', 'Allergies, adverse reactions, alerts');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

        $section->appendChild($this->createElement('title', 'Allergies, adverse reactions, alerts'));

        $allergies = $this->xpath('/CCDA/allergies/allergy');
        if ($allergies->length === 0) {
            $section->appendChild($this->createElement('text', 'No known Allergies and Intolerances'));
            $this->appendNoKnownAllergiesEntry($section);
        } else {
            // TODO: Implement allergy entries with data
        }

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    private function appendNoKnownAllergiesEntry(DOMElement $section): void
    {
        $entry = $this->createElement('entry');
        $entry->setAttribute('typeCode', 'DRIV');

        $act = $this->createElement('act');
        $act->setAttribute('classCode', 'ACT');
        $act->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.30', '2015-08-01');
        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.30');

        $id = $this->createElement('id');
        $id->setAttribute('nullFlavor', 'UNK');
        $act->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', 'CONC');
        $code->setAttribute('displayName', 'Concerns');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.5.6');
        $act->appendChild($code);

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'active');
        $act->appendChild($statusCode);

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

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.7', '2014-06-09');
        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.7');

        $obsId = $this->createElement('id');
        $obsId->setAttribute('nullFlavor', 'UNK');
        $obs->appendChild($obsId);

        $obsCode = $this->createElement('code');
        $obsCode->setAttribute('code', 'ASSERTION');
        $obsCode->setAttribute('displayName', 'Assertion');
        $obsCode->setAttribute('codeSystem', '2.16.840.1.113883.5.4');
        $obsCode->setAttribute('codeSystemName', 'ActCode');
        $obs->appendChild($obsCode);

        $obsStatus = $this->createElement('statusCode');
        $obsStatus->setAttribute('code', 'completed');
        $obs->appendChild($obsStatus);

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
        $subAdmin->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($subAdmin, '2.16.840.1.113883.10.20.22.4.16', '2014-06-09');
        $this->appendTemplateId($subAdmin, '2.16.840.1.113883.10.20.22.4.16');

        $shaExt = $this->xpathValue('sha_extension', $med);
        $ext = $this->xpathValue('extension', $med);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $subAdmin->appendChild($id);

        $direction = $this->xpathValue('direction', $med);
        $subAdmin->appendChild($this->createElement('text', $direction));

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $subAdmin->appendChild($statusCode);

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
        $effTime2 = $this->output->createElement('effectiveTime');
        $effTime2->setAttributeNS(self::NS_XSI, 'xsi:type', 'PIVL_TS');
        $effTime2->setAttribute('institutionSpecified', 'true');
        $effTime2->setAttribute('operator', 'A');
        $period = $this->createElement('period');
        $period->setAttribute('value', $dosage !== '' ? (string) (int) floatval($dosage) : '1');
        $effTime2->appendChild($period);
        $subAdmin->appendChild($effTime2);

        $routeCode = $this->createElement('routeCode');
        $route = $this->xpathValue('route_code', $med);
        if ($route !== '') {
            $routeCode->setAttribute('code', $route);
            $routeCode->setAttribute('codeSystem', '2.16.840.1.113883.3.26.1.1');
        } else {
            $routeCode->setAttribute('nullFlavor', 'UNK');
        }
        $subAdmin->appendChild($routeCode);

        $subAdmin->appendChild($this->createElement('doseQuantity'));

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

        $this->appendTemplateId($mfgProduct, '2.16.840.1.113883.10.20.22.4.23', '2014-06-09');
        $this->appendTemplateId($mfgProduct, '2.16.840.1.113883.10.20.22.4.23');

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

        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.3', '2015-08-01');
        $this->appendTemplateId($act, '2.16.840.1.113883.10.20.22.4.3');

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $act->appendChild($statusCode);

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

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.4', '2015-08-01');
        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.4');

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $obs->appendChild($statusCode);

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

        $code = $this->createElement('code');
        $code->setAttribute('code', '33999-4');
        $code->setAttribute('displayName', 'Status');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $statusObs->appendChild($code);

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $statusObs->appendChild($statusCode);

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

        $code = $this->createElement('code');
        $code->setAttribute('code', '11323-3');
        $code->setAttribute('displayName', 'Health status');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $healthObs->appendChild($code);

        $text = $this->createElement('text');
        $ref = $this->createElement('reference');
        $ref->setAttribute('value', '#healthStatus' . $index);
        $text->appendChild($ref);
        $healthObs->appendChild($text);

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $healthObs->appendChild($statusCode);

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $ageObs->appendChild($statusCode);

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
        // TODO: Implement
    }

    private function renderResultsSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderEncountersSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderImmunizationsSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderVitalSignsSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderSocialHistorySection(DOMElement $structuredBody): void
    {
        $socialHistory = $this->xpath('/CCDA/history_physical/social_history/*');
        if ($socialHistory->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.17', '2015-08-01');
            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.17');

            $code = $this->createElement('code');
            $code->setAttribute('code', '29762-2');
            $code->setAttribute('displayName', 'Social History');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Social History'));
            $section->appendChild($this->createElement('text', 'Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement social history with data
        }
    }

    private function renderPayersSection(DOMElement $structuredBody): void
    {
        $payers = $this->xpath('/CCDA/payers/payer');
        if ($payers->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.18');
            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.18', '2015-08-01');

            $code = $this->createElement('code');
            $code->setAttribute('code', '48768-6');
            $code->setAttribute('displayName', 'Payers');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Payers'));
            $section->appendChild($this->createElement('text', 'Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement payers with data
        }
    }

    private function renderMedicalEquipmentSection(DOMElement $structuredBody): void
    {
        $devices = $this->xpath('/CCDA/medical_devices/device');
        if ($devices->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.23', '2014-06-09');
            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.23');

            $code = $this->createElement('code');
            $code->setAttribute('code', '46264-8');
            $code->setAttribute('displayName', 'Medical Equipment');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Medical Equipment'));
            $section->appendChild($this->createElement('text', 'Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement medical equipment with data
        }
    }

    private function renderFunctionalStatusSection(DOMElement $structuredBody): void
    {
        $functionalStatus = $this->xpath('/CCDA/functional_status/functional_status');
        if ($functionalStatus->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.14', '2014-06-09');
            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.14');

            $code = $this->createElement('code');
            $code->setAttribute('code', '47420-5');
            $code->setAttribute('displayName', 'Functional Status');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Functional Status'));
            $section->appendChild($this->createElement('text', 'Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement functional status with data
        }
    }

    private function renderMentalStatusSection(DOMElement $structuredBody): void
    {
        $mentalStatus = $this->xpath('/CCDA/mental_status/status');
        if ($mentalStatus->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.56', '2015-08-01');

            $code = $this->createElement('code');
            $code->setAttribute('code', '10190-7');
            $code->setAttribute('displayName', 'Mental Status');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Mental Status'));
            $section->appendChild($this->createElement('text', 'Mental Status Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement mental status with data
        }
    }

    private function renderPlanOfCareSection(DOMElement $structuredBody): void
    {
        $planOfCare = $this->xpath('/CCDA/planofcare/item');
        if ($planOfCare->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.10', '2014-06-09');
            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.10');

            $code = $this->createElement('code');
            $code->setAttribute('code', '18776-5');
            $code->setAttribute('displayName', 'Treatment Plan');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Treatment Plan'));
            $section->appendChild($this->createElement('text', 'Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement plan of care with data
        }
    }

    private function renderGoalsSection(DOMElement $structuredBody): void
    {
        $goals = $this->xpath('/CCDA/goals/goal');
        if ($goals->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.60');
            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.60', '2015-08-01');

            $code = $this->createElement('code');
            $code->setAttribute('code', '61146-7');
            $code->setAttribute('displayName', 'Goals');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Goals'));
            $section->appendChild($this->createElement('text', 'Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement goals with data
        }
    }

    private function renderHealthConcernsSection(DOMElement $structuredBody): void
    {
        $concerns = $this->xpath('/CCDA/health_concerns/concern');
        if ($concerns->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.58', '2015-08-01');
            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.58');

            $code = $this->createElement('code');
            $code->setAttribute('code', '75310-3');
            $code->setAttribute('displayName', 'Health Concerns Document');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Health Concerns Document'));
            $section->appendChild($this->createElement('text', 'Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement health concerns with data
        }
    }

    private function renderAssessmentSection(DOMElement $structuredBody): void
    {
        $assessments = $this->xpath('/CCDA/clinical_notes/evaluation_note');
        if ($assessments->length === 0) {
            $component = $this->createElement('component');
            $section = $this->createElement('section');
            $section->setAttribute('nullFlavor', 'NI');

            $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.8');

            $code = $this->createElement('code');
            $code->setAttribute('code', '51848-0');
            $code->setAttribute('displayName', 'Assessments');
            $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
            $code->setAttribute('codeSystemName', 'LOINC');
            $section->appendChild($code);

            $section->appendChild($this->createElement('title', 'Assessments'));
            $section->appendChild($this->createElement('text', 'Not Available'));

            $this->appendSection($structuredBody, $component, $section);
        } else {
            // TODO: Implement assessments with data
        }
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

    private function appendId(DOMElement $parent, string $root, string $extension = ''): void
    {
        $el = $this->createElement('id');
        $el->setAttribute('root', $root);
        if ($extension !== '') {
            $el->setAttribute('extension', $extension);
        }
        $parent->appendChild($el);
    }

    private function appendCode(
        DOMElement $parent,
        string $elementName,
        string $code,
        string $codeSystem,
        string $displayName = '',
        string $codeSystemName = '',
    ): void {
        $el = $this->createElement($elementName);
        $el->setAttribute('code', $code);
        $el->setAttribute('codeSystem', $codeSystem);
        if ($displayName !== '') {
            $el->setAttribute('displayName', $displayName);
        }
        if ($codeSystemName !== '') {
            $el->setAttribute('codeSystemName', $codeSystemName);
        }
        $parent->appendChild($el);
    }

    private function formatDate(string $input): string
    {
        $input = trim($input);
        if ($input === '' || $input === '0000-00-00') {
            return '';
        }
        // TODO: Port date formatting logic from Node
        return $input;
    }

    private function cleanCode(string $code): string
    {
        $code = trim($code);
        if ($code === '') {
            return 'null_flavor';
        }
        return (string) preg_replace('/[.#]/', '', $code);
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

        $code = $this->createElement('code');
        $code->setAttribute('code', $loincCode);
        $code->setAttribute('displayName', $title);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

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
     * Creates an empty section with nullFlavor="NI"
     */
    private function createEmptySection(
        DOMElement $structuredBody,
        string $templateId,
        ?string $templateExtension,
        string $loincCode,
        string $title,
        string $emptyText = 'Not Available',
    ): void {
        $component = $this->createElement('component');
        $section = $this->createElement('section');
        $section->setAttribute('nullFlavor', 'NI');

        if ($templateExtension !== null) {
            $this->appendTemplateId($section, $templateId, $templateExtension);
            $this->appendTemplateId($section, $templateId);
        } else {
            $this->appendTemplateId($section, $templateId);
        }

        $code = $this->createElement('code');
        $code->setAttribute('code', $loincCode);
        $code->setAttribute('displayName', $title);
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

        $section->appendChild($this->createElement('title', $title));
        $section->appendChild($this->createElement('text', $emptyText));

        $this->appendSection($structuredBody, $component, $section);
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
}
