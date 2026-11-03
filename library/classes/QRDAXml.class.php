<?php

/**
 *
 * This program implements the XML Writer to generate QRDA Category I (or) III 2014 XML.
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    https://www.open-emr.org
 */


class QRDAXml extends XmlWriterOemr
{
    public $unique_id;

    public function __construct(public $nqf_code = '', $indent = '  ')
    {
        parent::__construct($indent);
    }


    public function open_clinicaldocument()
    {
        $this->push('ClinicalDocument', ['xmlns' => 'urn:hl7-org:v3', 'xmlns:voc' => 'urn:hl7-org:v3/voc', 'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation' => 'urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/','xmlns:sdtc' => 'urn:hl7-org:sdtc']);
    }

    public function close_clinicaldocument()
    {
        $this->pop();
    }

    public function self_realmcode()
    {
        $this->emptyelement('realmCode', ['code' => 'US']);
    }

    public function self_typeid()
    {
        $this->emptyelement('typeId', ['root' => '2.16.840.1.113883.1.3', 'extension' => 'POCD_HD000040']);
    }

    public function self_templateid($id)
    {
        $this->emptyelement('templateId', ['root' => $id]);
    }

    public function self_id()
    {
        $this->emptyelement('id', ['root' => $this->unique_id]);
    }

    public function self_code()
    {
        $this->emptyelement('code', [ 'code' => '55184-6', 'codeSystem' => '2.16.840.1.113883.6.1', 'codeSystemName' => 'LOINC', 'displayName' => 'Quality Reporting Document Architecture Calculated Summary Report']);
    }

    public function add_title($value)
    {
        $this->element('title', $value);
    }

    public function self_efftime($value)
    {
        $this->emptyelement('effectiveTime', ['value' => $value]);
    }

    public function self_confidentcode()
    {
        $this->emptyelement('confidentialityCode', ['codeSystem' => '2.16.840.1.113883.5.25', 'code' => 'N', 'codeSystemName' => 'HL7Confidentiality']);
    }

    public function self_lang()
    {
        $this->emptyelement('languageCode', ['code' => 'en']);
    }

    public function self_setid($id)
    {
        $this->emptyelement('setId', ['root' => $id]);
    }

    public function self_version()
    {
        $this->emptyelement('versionNumber', ['value' => 1]);
    }


    public function self_setpatientRoleid()
    {
        $this->emptyelement('id', ['nullFlavor' => 'NA']);
    }

    public function add_patientRole()
    {
        $this->push('patientRole');

        $this->emptyelement('id', ['nullFlavor' => 'NA']);

        $this->pop();
    }

    public function open_recordTarget()
    {
        $this->push('recordTarget');
    }

    public function close_recordTarget()
    {
        $this->pop();
    }

    public function open_author()
    {
        $this->push('author');
    }

    public function close_author()
    {
        $this->pop();
    }

    public function self_authorTime($value)
    {
        $this->emptyelement('time', ['value' => $value]);
    }

    public function open_assignAuthor()
    {
        $this->push('assignedAuthor');
    }

    public function close_assignAuthor()
    {
        $this->pop();
    }

    public function self_customId($id)
    {
        $this->emptyelement('id', ['root' => $id]);
    }


    public function add_authReprestOrginisation($facilArr)
    {
        $this->push('representedOrganization');
        $this->self_customTag('id', ['root' => '2.16.840.1.113883.19.5', 'extension' => '223344']);
        $this->element('name', $facilArr['name']);
        if (!empty($facilArr['phone'])) {
            $this->self_customTag('telecom', ['value' => $facilArr['phone'], 'use' => 'WP']);
        } else {
            $this->self_customTag('telecom', ["nullFlavor" => "UNK"]);
        }

        $this->add_facilAddress($facilArr);
        $this->pop();
    }

    public function open_custodian()
    {
        $this->push('custodian');
    }

    public function close_custodian()
    {
        $this->pop();
    }

    public function open_assgnCustodian()
    {
        $this->push('assignedCustodian');
    }

    public function close_assgnCustodian()
    {
        $this->pop();
    }

    public function self_reprsntCustId()
    {
        $this->emptyelement('id', ['root' => '2.16.840.1.113883.19.5']);
    }

    public function add_represtCustodianOrginisation($facilArr)
    {
        $this->push('representedCustodianOrganization');
        $this->self_reprsntCustId();
        $this->element('name', $facilArr['name']);
        if (!empty($facilArr['phone'])) {
            $this->self_customTag('telecom', ['value' => $facilArr['phone'], 'use' => 'WP']);
        } else {
            $this->self_customTag('telecom', ["nullFlavor" => "UNK"]);
        }

        $this->add_facilAddress($facilArr);
        $this->pop();
    }

    public function open_infoRecipient()
    {
        $this->push('informationRecipient');
    }

    public function close_infoRecipient()
    {
        $this->pop();
    }

    public function self_intendedId()
    {
        $this->emptyelement('id', ['root' => '2.16.840.1.113883.3.249.7', 'extension' => 'CPC']);
    }

    public function add_indententRecipient()
    {
        $this->push('intendedRecipient');
        $this->self_intendedId();
        $this->pop();
    }

    public function open_legalAuthenticator()
    {
        $this->push('legalAuthenticator');
    }

    public function close_legalAuthenticator()
    {
        $this->pop();
    }

    public function self_legalSignCode()
    {
        $this->emptyelement('signatureCode', ['code' => 'S']);
    }


    public function open_assignedEntity()
    {
        $this->push('assignedEntity');
    }

    public function close_assignedEntity()
    {
        $this->pop();
    }

    public function self_represntOrgId()
    {
        $this->emptyelement('id', ['root' => '2.16.840.1.113883.19.5', 'extension' => '223344']);
    }

    public function add_represntOrgName($name)
    {
        $this->push('representedOrganization');
        $this->self_represntOrgId();
        if ($name) {
            $this->element('name', $name);
        } else {
            $this->emptyelement('name');
        }

        $this->pop();
    }

    public function open_participant_data($code_type)
    {
        $this->push('participant', ['typeCode' => $code_type]);
    }

    public function close_participant_data()
    {
        $this->pop();
    }

    public function open_assocEntityData($class_code)
    {
        $this->push('associatedEntity', ['classCode' => $class_code]);
    }

    public function close_assocEntityData()
    {
        $this->pop();
    }

    public function self_participantCodeDevice()
    {
        $this->emptyelement('code', ['code' => '129465004', 'displayName' => 'medical record, device', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED-CT']);
    }

    public function self_participantCodeLocation()
    {
        $this->emptyelement('code', ['code' => '394730007', 'displayName' => 'healthcare related organization', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED-CT']);
    }

    public function self_particpantIdInfo($arr)
    {
        $this->emptyelement('id', $arr);
    }

    public function add_facilAddress($addrArr)
    {

        $this->push('addr', ["use" => "WP"]);
        if ($addrArr['street'] != "") {
            $this->element('streetAddressLine', $addrArr['street']);
        } else {
            $this->emptyelement('streetAddressLine', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['city'] != "") {
            $this->element('city', $addrArr['city']);
        } else {
            $this->emptyelement('city', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['state'] != "") {
            $this->element('state', $addrArr['state']);
        } else {
            $this->emptyelement('state', ["nullFlavor" => "UNK"]);
        }


        if ($addrArr['postal_code'] != "") {
            $this->element('postalCode', $addrArr['postal_code']);
        } else {
            $this->emptyelement('postalCode', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['country_code'] != "") {
            $this->element('country', $addrArr['country_code']);
        } else {
            $this->emptyelement('country', ["nullFlavor" => "UNK"]);
        }

        $this->pop();
    }

    public function open_mainComponent()
    {
        $this->push('component');
    }

    public function close_mainComponent()
    {
        $this->pop();
    }

    public function open_structuredBody()
    {
        $this->push('structuredBody');
    }

    public function close_structuredBody()
    {
        $this->pop();
    }

    public function open_loopComponent()
    {
        $this->push('component');
    }

    public function close_loopComponent()
    {
        $this->pop();
    }

    public function open_section()
    {
        $this->push('section');
    }

    public function close_section()
    {
        $this->pop();
    }

    public function self_codeCustom($arr)
    {
        $this->emptyelement('code', $arr);
    }

    public function open_text()
    {
        $this->push('text');
    }

    public function close_text()
    {
        $this->pop();
    }

    public function open_list()
    {
        $this->push('list');
    }

    public function close_list()
    {
        $this->pop();
    }

    public function add_item($value)
    {
        $this->element('item', $value);
    }

    public function open_entry($code_type = '')
    {
        if ($code_type != "") {
            $this->push('entry', ['typeCode' => $code_type]);
        } else {
            $this->push('entry');
        }
    }

    public function close_entry()
    {
        $this->pop();
    }

    public function open_act($arr)
    {
        $this->push('act', $arr);
    }

    public function close_act()
    {
        $this->pop();
    }

    public function add_entryEffectTime($arr)
    {
        $this->push('effectiveTime');
        $this->emptyelement('low', ['value' => $arr['low']]);
        if (isset($arr['high'])) {
            $this->emptyelement('high', ['value' => $arr['high']]);
        }

        $this->pop();
    }


    public function open_customTag($ele, $arr = [])
    {
        if (count($arr) > 0) {
            $this->push($ele, $arr);
        } else {
            $this->push($ele);
        }
    }

    public function close_customTag()
    {
        $this->pop();
    }

    public function add_trElementsTitles()
    {
        $this->element('th', 'eMeasure Title');
        $this->element('th', 'Version neutral identifier');
        $this->element('th', 'Version specific identifier');
    }

    public function add_trElementsValues($arr = [])
    {
        $this->element('td', $arr[0]);
        $this->element('td', $arr[1]);
        $this->element('td', $arr[2]);
    }

    public function innerContent($arr = [])
    {
        $this->xml .= '<content styleCode="Bold">' . $arr['name'] . '</content>:' . trim((string) $arr['value']);
    }

    public function self_customTag($tag, $arr)
    {
        $this->emptyelement($tag, $arr);
    }

    public function textDispContent($content)
    {
        $this->xml .= '<text>' . $content . '</text>';
    }

    public function add_providerName($nameArr)
    {
        $this->push('name');
        $this->element('given', $nameArr['fname']);
        $this->element('family', $nameArr['lname']);
        $this->pop();
    }

    public function add_facilName($facilName)
    {
        $this->element('name', $facilName);
    }

    public function add_patientAddress($addrArr)
    {
        $this->push('addr', ['use' => 'WP']);
        if ($addrArr['street'] != "") {
            $this->element('streetAddressLine', $addrArr['street']);
        } else {
            $this->emptyelement('streetAddressLine', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['city'] != "") {
            $this->element('city', $addrArr['city']);
        } else {
            $this->emptyelement('city', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['state'] != "") {
            $this->element('state', $addrArr['state']);
        } else {
            $this->emptyelement('state', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['postal_code'] != "") {
            $this->element('postalCode', $addrArr['postal_code']);
        } else {
            $this->emptyelement('postalCode', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['country_code'] != "") {
            $this->element('country', $addrArr['country_code']);
        } else {
            $this->emptyelement('country', ["nullFlavor" => "UNK"]);
        }

        $this->pop();
    }


    public function add_patName($nameArr)
    {
        $this->push('name');
        $this->element('given', $nameArr['fname']);
        $this->element('family', $nameArr['lname']);
        $this->pop();
    }

    public function add_userAddress($addrArr)
    {

        $this->push('addr', ['use' => 'WP']);
        if ($addrArr['street'] != "") {
            $this->element('streetAddressLine', $addrArr['street']);
        } else {
            $this->emptyelement('streetAddressLine', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['city'] != "") {
            $this->element('city', $addrArr['city']);
        } else {
            $this->emptyelement('city', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['state'] != "") {
            $this->element('state', $addrArr['state']);
        } else {
            $this->emptyelement('state', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['postal_code'] != "") {
            $this->element('postalCode', $addrArr['postal_code']);
        } else {
            $this->emptyelement('postalCode', ["nullFlavor" => "UNK"]);
        }

        if ($addrArr['country_code'] != "") {
            $this->element('country', $addrArr['country_code']);
        } else {
            $this->emptyelement('country', ["nullFlavor" => "UNK"]);
        }

        $this->pop();
    }

    public function add_entryTime($arr)
    {
        $this->push('time');
        $this->emptyelement('low', ['value' => $arr['low']]);
        if (isset($arr['high'])) {
            $this->emptyelement('high', ['value' => $arr['high']]);
        }

        $this->pop();
    }

    public function add_entryEffectTimeQRDA($arr)
    {
        $this->push('effectiveTime');
        $this->emptyelement('low', ['value' => $arr['low']]);
        if ($arr['high'] != "") {
            $this->emptyelement('high', ['value' => $arr['high']]);
        } else {
            $this->emptyelement('high', ['nullFlavor' => 'NI']);
        }

        $this->pop();
    }

    public function add_entryEffectTimeQRDAMed($arr)
    {
        $arrPass = ['xsi:type' => 'IVL_TS'];
        $this->push('effectiveTime', $arrPass);
        $this->emptyelement('low', ['value' => $arr['low']]);
        if ($arr['high'] != "") {
            $this->emptyelement('high', ['value' => $arr['high']]);
        } else {
            $this->emptyelement('high', ['nullFlavor' => 'NI']);
        }

        $this->pop();
    }
}
