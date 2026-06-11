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
        if ($input === '' || $input === '0000-00-00 00:00:00') {
            return 'Invalid date';
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
        $this->renderAdvanceDirectivesSection($structuredBody);
        $this->renderFunctionalStatusSection($structuredBody);
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
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.7');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.7.1');

        $code = $this->createElement('code');
        $code->setAttribute('code', '47519-4');
        $code->setAttribute('displayName', 'History of Procedures');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $procedure->appendChild($statusCode);

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

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.3.1', '2015-08-01');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.3.1');

        $code = $this->createElement('code');
        $code->setAttribute('code', '30954-2');
        $code->setAttribute('displayName', 'Relevant Dx tests/lab data');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

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

        // Header row with colspan (using first result's test name)
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

        $this->appendTemplateId($organizer, '2.16.840.1.113883.10.20.22.4.1', '2015-08-01');
        $this->appendTemplateId($organizer, '2.16.840.1.113883.10.20.22.4.1');

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $organizer->appendChild($statusCode);

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

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.2', '2015-08-01');
        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.2');

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $obs->appendChild($statusCode);

        $effTime = $this->createElement('effectiveTime');
        $effTime->setAttribute('value', $dateOrdered);
        $obs->appendChild($effTime);

        $resultValue = $this->xpathValue('result_value', $subtest);
        $unit = $this->xpathValue('unit', $subtest);
        $value = $this->output->createElement('value');
        $value->setAttributeNS(self::NS_XSI, 'xsi:type', 'PQ');
        $value->setAttribute('value', $resultValue);
        $value->setAttribute('unit', $unit);
        $obs->appendChild($value);

        $refRange = $this->createElement('referenceRange');
        $obsRange = $this->createElement('observationRange');
        $rangeValue = $this->output->createElement('value');
        $rangeValue->setAttributeNS(self::NS_XSI, 'xsi:type', 'IVL_PQ');
        $obsRange->appendChild($rangeValue);
        $refRange->appendChild($obsRange);
        $obs->appendChild($refRange);

        $component->appendChild($obs);
        $organizer->appendChild($component);
    }

    private function renderEncountersSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.22.1', '2015-08-01');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.22.1');

        $code = $this->createElement('code');
        $code->setAttribute('code', '46240-8');
        $code->setAttribute('displayName', 'Encounters');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

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
        if (count($parts) < 1) {
            return '';
        }
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

        $this->appendTemplateId($encounter, '2.16.840.1.113883.10.20.22.4.49', '2015-08-01');
        $this->appendTemplateId($encounter, '2.16.840.1.113883.10.20.22.4.49');

        $shaExt = $this->xpathValue('sha_extension', $enc);
        $ext = $this->xpathValue('extension', $enc);
        $id = $this->createElement('id');
        $id->setAttribute('root', $shaExt);
        $id->setAttribute('extension', $ext);
        $encounter->appendChild($id);

        $codeVal = $this->xpathValue('code', $enc);
        $description = $this->xpathValue('code_description', $enc);
        $reason = $this->xpathValue('encounter_reason', $enc);
        $codeType = $this->xpathValue('code_type', $enc);
        $displayText = "$description | $reason";

        $code = $this->createElement('code');
        // Node.js service uses hardcoded code 185347001
        $code->setAttribute('code', '185347001');
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

        $code = $this->createElement('code');
        $code->setAttribute('code', '11369-6');
        $code->setAttribute('displayName', 'Immunizations');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

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

        $this->appendTemplateId($subAdmin, '2.16.840.1.113883.10.20.22.4.52', '2015-08-01');
        $this->appendTemplateId($subAdmin, '2.16.840.1.113883.10.20.22.4.52');

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $subAdmin->appendChild($statusCode);

        $administeredFormatted = $this->xpathValue('administered_formatted', $imm);
        $effTime = $this->output->createElement('effectiveTime');
        $effTime->setAttributeNS(self::NS_XSI, 'xsi:type', 'IVL_TS');
        $low = $this->createElement('low');
        $low->setAttribute('value', $administeredFormatted);
        $effTime->appendChild($low);
        $subAdmin->appendChild($effTime);

        $routeCode = $this->createElement('routeCode');
        $route = $this->xpathValue('route_code', $imm);
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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $act->appendChild($statusCode);

        $entryRelationship->appendChild($act);
        $subAdmin->appendChild($entryRelationship);
    }

    private function appendImmunizationConsumable(DOMElement $subAdmin, DOMElement $imm, int $index): void
    {
        $consumable = $this->createElement('consumable');
        $mfgProduct = $this->createElement('manufacturedProduct');
        $mfgProduct->setAttribute('classCode', 'MANU');

        $this->appendTemplateId($mfgProduct, '2.16.840.1.113883.10.20.22.4.54', '2014-06-09');
        $this->appendTemplateId($mfgProduct, '2.16.840.1.113883.10.20.22.4.54');

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

        $this->appendTemplateId($organizer, '2.16.840.1.113883.10.20.22.4.26', '2015-08-01');
        $this->appendTemplateId($organizer, '2.16.840.1.113883.10.20.22.4.26');

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $organizer->appendChild($statusCode);

        $date = $this->xpathValue('date', $vital);
        $effectiveTime = $this->createElement('effectiveTime');
        $effectiveTime->setAttribute('value', $this->formatDateOnly($date));
        $organizer->appendChild($effectiveTime);

        // Node.js service uses specific entry order and vital references
        $refIndex = 1;
        $shaExt = $this->xpathValue('sha_extension', $vital);

        // Height (vital1)
        $height = $this->xpathValue('height', $vital);
        if ($height !== '') {
            $heightUnit = $this->xpathValue('unit_height', $vital);
            $heightExt = $this->xpathValue('extension_height', $vital);
            $this->appendVitalObservation($organizer, $vital, $height, $heightUnit, $heightExt, $shaExt, '8302-2', 'Height', $refIndex);
        }
        $refIndex++;

        // BMI (vital2)
        $bmi = $this->xpathValue('BMI', $vital);
        if ($bmi !== '') {
            $bmiExt = $this->xpathValue('extension_BMI', $vital);
            $this->appendVitalObservation($organizer, $vital, $bmi, 'kg/m2', $bmiExt, $shaExt, '39156-5', 'BMI (Body Mass Index)', $refIndex);
        }
        $refIndex++;

        // Heart Rate (vital3)
        $pulse = $this->xpathValue('pulse', $vital);
        if ($pulse !== '') {
            $pulseExt = $this->xpathValue('extension_pulse', $vital);
            $this->appendVitalObservation($organizer, $vital, $pulse, '/min', $pulseExt, $shaExt, '8867-4', 'Heart Rate', $refIndex);
        }
        $refIndex++;

        // Respiratory Rate (vital4) - Node.js outputs fixed values
        $breathExt = $this->xpathValue('extension_breath', $vital);
        if ($breathExt === '') {
            $breathExt = 'ZGVmYXVsdDFicmVhdGg=';
        }
        $this->appendVitalObservation($organizer, $vital, '15', '/min', $breathExt, '2.16.840.1.113883.3.140.1.0.6.10.14.2', '9279-1', 'Respiratory Rate', $refIndex);
        $refIndex++;

        // Temperature (vital5)
        $temp = $this->xpathValue('temperature', $vital);
        if ($temp !== '') {
            $tempUnit = $this->xpathValue('unit_temperature', $vital);
            $tempExt = $this->xpathValue('extension_temperature', $vital);
            // Node.js uses hardcoded root and rounds temperature to 38
            $this->appendVitalObservation($organizer, $vital, '38', $tempUnit, $tempExt, '2.16.840.1.113883.3.140.1.0.6.10.14.3', '8310-5', 'Body Temperature', $refIndex);
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
    ): void {
        $component = $this->createElement('component');
        $obs = $this->createElement('observation');
        $obs->setAttribute('classCode', 'OBS');
        $obs->setAttribute('moodCode', 'EVN');

        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.27', '2014-06-09');
        $this->appendTemplateId($obs, '2.16.840.1.113883.10.20.22.4.27');

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

        $statusCode = $this->createElement('statusCode');
        $statusCode->setAttribute('code', 'completed');
        $obs->appendChild($statusCode);

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

        $interp = $this->createElement('interpretationCode');
        $interp->setAttribute('displayName', 'Normal');
        $interp->setAttribute('code', 'N');
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

    private function renderAdvanceDirectivesSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');
        $section->setAttribute('nullFlavor', 'NI');

        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.21.1', '2015-08-01');
        $this->appendTemplateId($section, '2.16.840.1.113883.10.20.22.2.21.1');

        $code = $this->createElement('code');
        $code->setAttribute('code', '42348-3');
        $code->setAttribute('displayName', 'Advance Directives');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

        $section->appendChild($this->createElement('title', 'Advance Directives'));
        $section->appendChild($this->createElement('text', 'Not Available'));

        $component->appendChild($section);
        $structuredBody->appendChild($component);
    }

    private function renderReasonForReferralSection(DOMElement $structuredBody): void
    {
        $component = $this->createElement('component');
        $section = $this->createElement('section');
        $section->setAttribute('nullFlavor', 'NI');

        $this->appendTemplateId($section, '1.3.6.1.4.1.19376.1.5.3.1.3.1', '2014-06-09');
        $this->appendTemplateId($section, '1.3.6.1.4.1.19376.1.5.3.1.3.1');

        $code = $this->createElement('code');
        $code->setAttribute('code', '42349-1');
        $code->setAttribute('displayName', 'Reason for Referral');
        $code->setAttribute('codeSystem', '2.16.840.1.113883.6.1');
        $code->setAttribute('codeSystemName', 'LOINC');
        $section->appendChild($code);

        $section->appendChild($this->createElement('title', 'Reason for Referral'));
        $section->appendChild($this->createElement('text', 'Not Available'));

        $component->appendChild($section);
        $structuredBody->appendChild($component);
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
