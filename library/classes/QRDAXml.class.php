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

    function __construct(public $nqf_code = '', $indent = '  ')
    {
        parent::__construct($indent);
    }


    function open_clinicaldocument()
    {
        $this->push('ClinicalDocument', ['xmlns' => 'urn:hl7-org:v3', 'xmlns:voc' => 'urn:hl7-org:v3/voc', 'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation' => 'urn:hl7-org:v3 http://xreg2.nist.gov:8080/hitspValidation/schema/','xmlns:sdtc' => 'urn:hl7-org:sdtc']);
    }

    function close_clinicaldocument()
    {
        $this->pop();
    }

    function self_realmcode()
    {
        $this->emptyelement('realmCode', ['code' => 'US']);
    }

    function self_typeid()
    {
        $this->emptyelement('typeId', ['root' => '2.16.840.1.113883.1.3', 'extension' => 'POCD_HD000040']);
    }

    function self_templateid($id)
    {
        $this->emptyelement('templateId', ['root' => $id]);
    }

    function self_id()
    {
        $this->emptyelement('id', ['root' => $this->unique_id]);
    }

    function self_code()
    {
        $this->emptyelement('code', [ 'code' => '55184-6', 'codeSystem' => '2.16.840.1.113883.6.1', 'codeSystemName' => 'LOINC', 'displayName' => 'Quality Reporting Document Architecture Calculated Summary Report']);
    }

    function add_title($value)
    {
        $this->element('title', $value);
    }

    function self_efftime($value)
    {
        $this->emptyelement('effectiveTime', ['value' => $value]);
    }

    function self_confidentcode()
    {
        $this->emptyelement('confidentialityCode', ['codeSystem' => '2.16.840.1.113883.5.25', 'code' => 'N', 'codeSystemName' => 'HL7Confidentiality']);
    }

    function self_lang()
    {
        $this->emptyelement('languageCode', ['code' => 'en']);
    }

    function self_setid($id)
    {
        $this->emptyelement('setId', ['root' => $id]);
    }

    function self_version()
    {
        $this->emptyelement('versionNumber', ['value' => 1]);
    }


    function self_setpatientRoleid()
    {
        $this->emptyelement('id', ['nullFlavor' => 'NA']);
    }

    function add_patientRole()
    {
        $this->push('patientRole');

        $this->emptyelement('id', ['nullFlavor' => 'NA']);

        $this->pop();
    }

    function open_recordTarget()
    {
        $this->push('recordTarget');
    }

    function close_recordTarget()
    {
        $this->pop();
    }

    function open_author()
    {
        $this->push('author');
    }

    function close_author()
    {
        $this->pop();
    }

    function self_authorTime($value)
    {
        $this->emptyelement('time', ['value' => $value]);
    }

    function open_assignAuthor()
    {
        $this->push('assignedAuthor');
    }

    function close_assignAuthor()
    {
        $this->pop();
    }

    function self_customId($id)
    {
        $this->emptyelement('id', ['root' => $id]);
    }


    function add_authReprestOrginisation($facilArr)
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

    function open_custodian()
    {
        $this->push('custodian');
    }

    function close_custodian()
    {
        $this->pop();
    }

    function open_assgnCustodian()
    {
        $this->push('assignedCustodian');
    }

    function close_assgnCustodian()
    {
        $this->pop();
    }

    function self_reprsntCustId()
    {
        $this->emptyelement('id', ['root' => '2.16.840.1.113883.19.5']);
    }

    function add_represtCustodianOrginisation($facilArr)
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

    function open_infoRecipient()
    {
        $this->push('informationRecipient');
    }

    function close_infoRecipient()
    {
        $this->pop();
    }

    function self_intendedId()
    {
        $this->emptyelement('id', ['root' => '2.16.840.1.113883.3.249.7', 'extension' => 'CPC']);
    }

    function add_indententRecipient()
    {
        $this->push('intendedRecipient');
        $this->self_intendedId();
        $this->pop();
    }

    function open_legalAuthenticator()
    {
        $this->push('legalAuthenticator');
    }

    function close_legalAuthenticator()
    {
        $this->pop();
    }

    function self_legalSignCode()
    {
        $this->emptyelement('signatureCode', ['code' => 'S']);
    }


    function open_assignedEntity()
    {
        $this->push('assignedEntity');
    }

    function close_assignedEntity()
    {
        $this->pop();
    }

    function self_represntOrgId()
    {
        $this->emptyelement('id', ['root' => '2.16.840.1.113883.19.5', 'extension' => '223344']);
    }

    function add_represntOrgName($name)
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

    function open_participant_data($code_type)
    {
        $this->push('participant', ['typeCode' => $code_type]);
    }

    function close_participant_data()
    {
        $this->pop();
    }

    function open_assocEntityData($class_code)
    {
        $this->push('associatedEntity', ['classCode' => $class_code]);
    }

    function close_assocEntityData()
    {
        $this->pop();
    }

    function self_participantCodeDevice()
    {
        $this->emptyelement('code', ['code' => '129465004', 'displayName' => 'medical record, device', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED-CT']);
    }

    function self_participantCodeLocation()
    {
        $this->emptyelement('code', ['code' => '394730007', 'displayName' => 'healthcare related organization', 'codeSystem' => '2.16.840.1.113883.6.96', 'codeSystemName' => 'SNOMED-CT']);
    }

    function self_particpantIdInfo($arr)
    {
        $this->emptyelement('id', $arr);
    }

    function add_facilAddress($addrArr)
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

    function open_mainComponent()
    {
        $this->push('component');
    }

    function close_mainComponent()
    {
        $this->pop();
    }

    function open_structuredBody()
    {
        $this->push('structuredBody');
    }

    function close_structuredBody()
    {
        $this->pop();
    }

    function open_loopComponent()
    {
        $this->push('component');
    }

    function close_loopComponent()
    {
        $this->pop();
    }

    function open_section()
    {
        $this->push('section');
    }

    function close_section()
    {
        $this->pop();
    }

    function self_codeCustom($arr)
    {
        $this->emptyelement('code', $arr);
    }

    function open_text()
    {
        $this->push('text');
    }

    function close_text()
    {
        $this->pop();
    }

    function open_list()
    {
        $this->push('list');
    }

    function close_list()
    {
        $this->pop();
    }

    function add_item($value)
    {
        $this->element('item', $value);
    }

    function open_entry($code_type = '')
    {
        if ($code_type != "") {
            $this->push('entry', ['typeCode' => $code_type]);
        } else {
            $this->push('entry');
        }
    }

    function close_entry()
    {
        $this->pop();
    }

    function open_act($arr)
    {
        $this->push('act', $arr);
    }

    function close_act()
    {
        $this->pop();
    }

    function add_entryEffectTime($arr)
    {
        $this->push('effectiveTime');
        $this->emptyelement('low', ['value' => $arr['low']]);
        if (isset($arr['high'])) {
            $this->emptyelement('high', ['value' => $arr['high']]);
        }

        $this->pop();
    }


    function open_customTag($ele, $arr = [])
    {
        if (count($arr) > 0) {
            $this->push($ele, $arr);
        } else {
            $this->push($ele);
        }
    }

    function close_customTag()
    {
        $this->pop();
    }

    function add_trElementsTitles()
    {
        $this->element('th', 'eMeasure Title');
        $this->element('th', 'Version neutral identifier');
        $this->element('th', 'Version specific identifier');
    }

    function add_trElementsValues($arr = [])
    {
        $this->element('td', $arr[0]);
        $this->element('td', $arr[1]);
        $this->element('td', $arr[2]);
    }

    function innerContent($arr = [])
    {
        $this->xml .= '<content styleCode="Bold">' . $arr['name'] . '</content>:' . trim((string) $arr['value']);
    }

    function self_customTag($tag, $arr)
    {
        $this->emptyelement($tag, $arr);
    }

    function textDispContent($content)
    {
        $this->xml .= '<text>' . $content . '</text>';
    }

    function add_providerName($nameArr)
    {
        $this->push('name');
        $this->element('given', $nameArr['fname']);
        $this->element('family', $nameArr['lname']);
        $this->pop();
    }

    function add_facilName($facilName)
    {
        $this->element('name', $facilName);
    }

    function add_patientAddress($addrArr)
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


    function add_patName($nameArr)
    {
        $this->push('name');
        $this->element('given', $nameArr['fname']);
        $this->element('family', $nameArr['lname']);
        $this->pop();
    }

    function add_userAddress($addrArr)
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

    function add_entryTime($arr)
    {
        $this->push('time');
        $this->emptyelement('low', ['value' => $arr['low']]);
        if (isset($arr['high'])) {
            $this->emptyelement('high', ['value' => $arr['high']]);
        }

        $this->pop();
    }

    function add_entryEffectTimeQRDA($arr)
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

    function add_entryEffectTimeQRDAMed($arr)
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
