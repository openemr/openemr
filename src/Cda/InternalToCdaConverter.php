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
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.1.1', '2014-06-09');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.1.1');

        $code = $this->createElement('code');
        $code->setAttribute('code', '10160-0');
        $code->setAttribute('displayName', 'History of medication use');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

        $section->appendChild($this->createElement('title', 'History of medication use'));

        $medications = $this->xpath('/CCDA/medications/medication');
        $this->appendMedicationsNarrative($section, $medications);

        $index = 1;
        foreach ($medications as $med) {
            $this->appendMedicationEntry($section, $med, $index);
            $index++;
        }

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    /**
     * @param \DOMNodeList<\DOMElement> $medications
     */
    private function appendMedicationsNarrative(DOMElement $section, \DOMNodeList $medications): void
    {
        $text = $this->createElement('text');
        $table = $this->createElement('table');
        $table->setAttribute('width', '100%');
        $table->setAttribute('border', '1');

        $thead = $this->createElement('thead');
        $headerRow = $this->createElement('tr');
        $headerRow->appendChild($this->createElement('th', 'Medication Class'));
        $headerRow->appendChild($this->createElement('th', '# fills'));
        $headerRow->appendChild($this->createElement('th', 'Last fill date'));
        $thead->appendChild($headerRow);
        $table->appendChild($thead);

        $index = 1;
        foreach ($medications as $med) {
            $tbody = $this->createElement('tbody');
            $row = $this->createElement('tr');

            $drugCell = $this->createElement('td', $this->xpathValue('drug', $med));
            $drugCell->setAttribute('ID', 'medinfo' . $index);
            $row->appendChild($drugCell);

            $row->appendChild($this->createElement('td', '0'));
            $row->appendChild($this->createElement('td', date('Y-m-d')));

            $tbody->appendChild($row);
            $table->appendChild($tbody);
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
        $author = $this->createElement('author');
        $author->setAttribute('typeCode', 'AUT');

        $this->appendTemplateId($author, '2.16.840.1.113883.10.20.22.4.119');

        $time = $this->xpathValue('author/time', $med);
        $timeEl = $this->createElement('time');
        $timeEl->setAttribute('value', $this->formatTimestamp($time));
        $author->appendChild($timeEl);

        $assignedAuthor = $this->createElement('assignedAuthor');

        $authorId = $this->xpathValue('author/id', $med);
        $authorNpi = $this->xpathValue('author/npi', $med);
        $id = $this->createElement('id');
        $id->setAttribute('root', $authorId !== '' ? $authorId : '2.16.840.1.113883.4.6');
        $id->setAttribute('extension', $authorNpi !== '' ? $authorNpi : 'NI');
        $assignedAuthor->appendChild($id);

        $code = $this->createElement('code');
        $code->setAttribute('code', $this->xpathValue('author/physician_type_code', $med));
        $code->setAttribute('displayName', $this->xpathValue('author/physician_type', $med));
        $code->setAttribute('codeSystem', $this->xpathValue('author/physician_type_system', $med));
        $code->setAttribute('codeSystemName', $this->xpathValue('author/physician_type_system_name', $med));
        $assignedAuthor->appendChild($code);

        $assignedPerson = $this->createElement('assignedPerson');
        $name = $this->createElement('name');
        $lname = $this->xpathValue('author/lname', $med);
        $fname = $this->xpathValue('author/fname', $med);
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
        $facilityOid = $this->xpathValue('author/facility_oid', $med);
        $facilityNpi = $this->xpathValue('author/facility_npi', $med);
        $orgId->setAttribute('root', $facilityOid !== '' ? $facilityOid : '2.16.840.1.113883.4.6');
        $orgId->setAttribute('extension', $facilityNpi !== '' ? $facilityNpi : 'NI');
        $repOrg->appendChild($orgId);
        $facilityName = $this->xpathValue('author/facility_name', $med);
        $repOrg->appendChild($this->createElement('name', $facilityName !== '' ? $facilityName : null));
        $assignedAuthor->appendChild($repOrg);

        $author->appendChild($assignedAuthor);
        $subAdmin->appendChild($author);
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
        // TODO: Implement
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
        // TODO: Implement
    }

    private function renderPayersSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderMedicalEquipmentSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderFunctionalStatusSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderMentalStatusSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderPlanOfCareSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderGoalsSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderHealthConcernsSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
    }

    private function renderAssessmentSection(DOMElement $structuredBody): void
    {
        // TODO: Implement
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
}
